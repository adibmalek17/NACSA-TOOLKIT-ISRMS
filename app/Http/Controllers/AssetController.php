<?php

namespace App\Http\Controllers;

use App\Models\RiskAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    private const CATEGORIES = [
        'Perkakasan',
        'Perisian',
        'Hardcopy',
        'Softcopy',
        'Manusia',
        'Perkhidmatan',
    ];

    public function index(Request $request)
    {
        $memberships = DB::table('department_memberships')
            ->join(
                'departments',
                'department_memberships.department_id',
                '=',
                'departments.id'
            )
            ->where('department_memberships.user_id', $request->user()->id)
            ->select(
                'departments.id',
                'departments.name',
                'department_memberships.role'
            )
            ->get();

        $assets = RiskAsset::query()
            ->join(
                'departments',
                'risk_assets.department_id',
                '=',
                'departments.id'
            )
            ->whereIn('risk_assets.department_id', $memberships->pluck('id'))
            ->select('risk_assets.*', 'departments.name as department_name')
            ->orderByDesc('risk_assets.id')
            ->paginate(15);

        $writableDepartments = $memberships
            ->whereIn('role', ['admin', 'assessor']);

        $adminDepartments = $memberships
            ->where('role', 'admin');

        return view('assets.index', [
            'assets' => $assets,
            'writableDepartments' => $writableDepartments,
            'adminDepartments' => $adminDepartments,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => ['required', 'integer'],
        ]);

        $departmentId = (int) $request->input('department_id');

        $canCreate = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $departmentId)
            ->whereIn('role', ['admin', 'assessor'])
            ->exists();

        abort_unless(
            $canCreate,
            403,
            'You cannot add assets to this department.'
        );

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('risk_assets', 'code')
                    ->where('department_id', $departmentId),
            ],
            'name' => ['required', 'string', 'max:180'],
            'serial_number' => ['nullable', 'string', 'max:180'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'owner' => ['required', 'string', 'max:180'],
            'location' => ['required', 'string', 'max:180'],
        ]);

        DB::transaction(function () use (
            $validated,
            $departmentId,
            $request
        ) {
            $asset = RiskAsset::create([
                ...$validated,
                'department_id' => $departmentId,
            ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => null,
                'action' => 'asset.created',
                'metadata' => json_encode([
                    'asset_id' => $asset->id,
                    'department_id' => $departmentId,
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset registered successfully.');
    }

    private function ensureCanEdit(Request $request, RiskAsset $asset): void
    {
        $allowed = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $asset->department_id)
            ->whereIn('role', ['admin', 'assessor'])
            ->exists();

        abort_unless(
            $allowed,
            403,
            'You cannot edit assets in this department.'
        );
    }

    public function edit(Request $request, RiskAsset $asset)
    {
        $this->ensureCanEdit($request, $asset);

        $departmentName = DB::table('departments')
            ->where('id', $asset->department_id)
            ->value('name');

        return view('assets.edit', [
            'asset' => $asset,
            'departmentName' => $departmentName,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, RiskAsset $asset)
    {
        $this->ensureCanEdit($request, $asset);

        $validated = $request->validate([
            'department_id' => ['prohibited'],
            'code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('risk_assets', 'code')
                    ->where('department_id', $asset->department_id)
                    ->ignore($asset),
            ],
            'name' => ['required', 'string', 'max:180'],
            'serial_number' => ['nullable', 'string', 'max:180'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'owner' => ['required', 'string', 'max:180'],
            'location' => ['required', 'string', 'max:180'],
        ]);

        unset($validated['department_id']);

        DB::transaction(function () use ($asset, $validated, $request) {
            $asset->fill($validated);

            $changedFields = array_keys($asset->getDirty());

            $asset->save();

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => null,
                'action' => 'asset.updated',
                'metadata' => json_encode([
                    'asset_id' => $asset->id,
                    'department_id' => $asset->department_id,
                    'changed_fields' => $changedFields,
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(Request $request, RiskAsset $asset)
    {
        $canArchive = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $asset->department_id)
            ->where('role', 'admin')
            ->exists();

        abort_unless(
            $canArchive,
            403,
            'You cannot archive assets in this department.'
        );

        DB::transaction(function () use ($asset, $request) {
            $asset->delete();

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => null,
                'action' => 'asset.archived',
                'metadata' => json_encode([
                    'asset_id' => $asset->id,
                    'department_id' => $asset->department_id,
                    'code' => $asset->code,
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset archived successfully.');
    }
}