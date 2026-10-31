<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Risk\RiskCalculator;
use App\Models\RiskAsset;
use App\Services\RiskComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class RiskAssessmentController extends Controller
{
    private const PROFILES = [
        'nacsa-3-2024',
        'nacsa-5-2024',
        'usim-2026',
    ];

    private const STATUSES = [
    'draft',
    'submitted',
    'returned',
    'verified',
    'archived',
];

    public function index(Request $request)
    {
        $filters = $this->assessmentFilters($request);

        $memberships = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->get();

        $departmentIds = $memberships->pluck('department_id');

        $writableDepartmentIds = $memberships
            ->whereIn('role', ['admin', 'assessor'])
            ->pluck('department_id');

        $assets = RiskAsset::query()
            ->join(
                'departments',
                'risk_assets.department_id',
                '=',
                'departments.id'
            )
            ->whereIn('risk_assets.department_id', $writableDepartmentIds)
            ->select(
                'risk_assets.*',
                'departments.name as department_name'
            )
            ->orderBy('risk_assets.code')
            ->get();

        $assessments = $this->assessmentQuery($departmentIds, $filters)
            ->paginate(15)
            ->appends(array_filter($filters, fn ($value) => $value !== ''))
            ->fragment('saved-assessments');

        $assessments->getCollection()->transform(function ($assessment) {
            $assessment->result = json_decode(
                $assessment->result,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return $assessment;
        });

        return view('assessments.index', [
            'riskComparisons' => RiskComparison::forAssessments($assessments->getCollection()),
            'assets' => $assets,
            'assessments' => $assessments,
            'profiles' => self::PROFILES,
            'statuses' => self::STATUSES,
            'filters' => $filters,
            'canCreate' => $writableDepartmentIds->isNotEmpty(),
            'departmentRoles' => $memberships->pluck('role', 'department_id'),
        ]);
    }

    private function assessmentFilters(Request $request): array
    {
        // Normalize GET filters; ignore unsupported values and array inputs.
        $search = $request->query('q', '');
        $status = $request->query('status', '');
        $filterProfile = $request->query('filter_profile', '');

        return [
            'q' => is_string($search) ? mb_substr(trim($search), 0, 180) : '',
            'status' => in_array($status, self::STATUSES, true) ? $status : '',
            'filter_profile' => in_array($filterProfile, self::PROFILES, true)
                ? $filterProfile : '',
        ];

    }

    private function assessmentQuery($departmentIds, array $filters)
    {
        // Assessment history remains visible for archived assets.
        return DB::table('risk_assessments')
            ->join(
                'risk_assets',
                'risk_assessments.risk_asset_id',
                '=',
                'risk_assets.id'
            )
            ->join(
                'departments',
                'risk_assets.department_id',
                '=',
                'departments.id'
            )
            ->whereIn('risk_assets.department_id', $departmentIds)
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                // Group both matches so department access applies to either one.
                // Escape LIKE wildcards to search for the literal text entered.
                $pattern = '%' . str_replace(
                    ['!', '%', '_'],
                    ['!!', '!%', '!_'],
                    $filters['q']
                ) . '%';

                $query->where(function ($searchQuery) use ($pattern) {
                    $searchQuery
                        ->whereRaw("risk_assets.code LIKE ? ESCAPE '!'", [$pattern])
                        ->orWhereRaw("risk_assets.name LIKE ? ESCAPE '!'", [$pattern]);
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('risk_assessments.status', $filters['status']);
            })
            ->when($filters['filter_profile'] !== '', function ($query) use ($filters) {
                $query->where('risk_assessments.profile', $filters['filter_profile']);
            })
         ->select(
    'risk_assessments.*',
    'risk_assets.code as asset_code',
    'risk_assets.name as asset_name',
    'risk_assets.department_id as department_id',
                'risk_assets.deleted_at as asset_deleted_at',
    'departments.name as department_name'
)
->orderByDesc('risk_assessments.id');
    }

    public function export(Request $request)
    {
        $filters = $this->assessmentFilters($request);
        $departmentIds = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->select('department_id');

        $query = $this->assessmentQuery($departmentIds, $filters);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                throw new \RuntimeException('Unable to open CSV output.');
            }

            try {
                // UTF-8 BOM helps Excel display non-ASCII text correctly.
                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, [
                    'Assessment ID', 'Asset code', 'Asset name', 'Department',
                    'Assessment date', 'Profile', 'Initial risk score', 'Initial risk level',
                    'Status', 'Revision', 'Risk owner', 'Scoring basis', 'Inherent score', 'Inherent level', 'Residual risk by current-revision plan',
                ], ',', '"', '');

                // Export all matching rows, not only the current table page.
                foreach ($query->cursor() as $assessment) {
                    $result = json_decode(
                        $assessment->result, true, 512, JSON_THROW_ON_ERROR
                    );
                    $comparison = RiskComparison::forAssessments(collect([$assessment]))->get($assessment->id, []);
                    $residual = collect($comparison)->map(fn ($item) => 'Plan #'.$item['plan_id'].': '.
                        ($item['result'] === null ? 'Not assessed' : $item['result']['score'].' / '.($item['result']['label'] ?? '').' / '.$item['status']))->implode('; ');
                    $row = [
                        $assessment->id,
                        $assessment->asset_code,
                        $assessment->asset_name,
                        $assessment->department_name,
                        $assessment->assessed_on,
                        $assessment->profile,
                        $result['score'] ?? '',
                        $result['label'] ?? '',
                        ucfirst($assessment->status),
                        $assessment->revision,
                        $assessment->risk_owner,
                        RiskComparison::basisLabel($assessment),
                        $assessment->risk_basis === 'inherent' ? ($result['score'] ?? '') : '',
                        $assessment->risk_basis === 'inherent' ? ($result['label'] ?? '') : '',
                        $residual ?: 'Not assessed',
                    ];
                    fputcsv(
                        $output,
                        array_map(fn ($value) => $this->csvCell($value), $row),
                        ',', '"', ''
                    );
                }
            } finally {
                fclose($output);
            }
        }, 'risk-assessments-' . now()->format('Y-m-d-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function csvCell($value): string
    {
        $text = (string) ($value ?? '');
        // Prevent formula-like text, including leading whitespace/control bytes.
        if (preg_match('/^[\x00-\x20]*[=+\-@]/', $text)
            || preg_match('/^[\t\r\n]/', $text)) {
            return "'" . $text;
        }
        return $text;
    }

    public function show(Request $request, string $assessment)
    {
        $departmentIds = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->select('department_id');

        // Scope the lookup to the user's departments before returning details.
        $record = DB::table('risk_assessments')
            ->join(
                'risk_assets',
                'risk_assessments.risk_asset_id',
                '=',
                'risk_assets.id'
            )
            ->join(
                'departments',
                'risk_assets.department_id',
                '=',
                'departments.id'
            )
            ->leftJoin(
                'users',
                'risk_assessments.assessor_id',
                '=',
                'users.id'
            )
            ->where('risk_assessments.id', $assessment)
            ->whereIn('risk_assets.department_id', $departmentIds)
            ->select(
                'risk_assessments.*',
                'risk_assets.code as asset_code',
                'risk_assets.name as asset_name',
                'risk_assets.deleted_at as asset_deleted_at',
                'departments.name as department_name',
                'users.name as assessor_name'
            )
            ->first();

        abort_if($record === null, 404);

        $record->inputs = json_decode(
            $record->inputs,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $record->result = json_decode(
            $record->result,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return view('assessments.show', [
            'assessment' => $record,
            'riskComparison' => RiskComparison::forAssessments(collect([$record]))->get($record->id, []),
        ]);
    }

    public function store(Request $request, RiskCalculator $calculator)
    {
        $base = $request->validate([
            'risk_asset_id' => ['required', 'integer'],
            'profile' => ['required', Rule::in(self::PROFILES)],
        ]);

        $asset = RiskAsset::findOrFail($base['risk_asset_id']);

        $allowed = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $asset->department_id)
            ->whereIn('role', ['admin', 'assessor'])
            ->exists();

        abort_unless(
            $allowed,
            403,
            'You cannot assess assets in this department.'
        );

        $profile = $base['profile'];
        $max = $profile === 'nacsa-5-2024' ? 5 : 3;

        $scoreKeys = array_merge(
            ['c', 'i', 'a', 'likelihood'],
            $profile === 'usim-2026'
                ? ['operations', 'financial', 'reputation']
                : ['loss']
        );

        $rules = [
            'assessed_on' => ['required', 'date_format:Y-m-d'],
            'threat' => ['required', 'string', 'max:5000'],
            'vulnerability' => ['required', 'string', 'max:5000'],
            'existing_controls' => ['nullable', 'string', 'max:5000'],
            'risk_owner' => ['required', 'string', 'max:180'],
        ];

        foreach ($scoreKeys as $key) {
            $rules[$key] = ['required', 'integer', "between:1,$max"];
        }

        $validated = $request->validate($rules);

        $inputs = ['profile' => $profile];

        foreach ($scoreKeys as $key) {
            $inputs[$key] = (int) $validated[$key];
        }

        // Always calculate the result on the server.
        $result = $calculator->calculate($inputs);

        DB::transaction(function () use (
            $request,
            $asset,
            $profile,
            $validated,
            $inputs,
            $result
        ) {
            $currentAsset = RiskAsset::query()
                ->whereKey($asset->id)
                ->lockForUpdate()
                ->firstOrFail();

            $allowed = DB::table('department_memberships')
                ->where('user_id', $request->user()->id)
                ->where('department_id', $currentAsset->department_id)
                ->whereIn('role', ['admin', 'assessor'])
                ->exists();

            abort_unless(
                $allowed,
                403,
                'You cannot assess assets in this department.'
            );

            $now = now();

            $assessmentId = DB::table('risk_assessments')->insertGetId([
                'risk_asset_id' => $currentAsset->id,
                'assessor_id' => $request->user()->id,
                'assessed_on' => $validated['assessed_on'],
                'profile' => $profile,
                'engine_version' => $result['version'],
                'threat' => $validated['threat'],
                'vulnerability' => $validated['vulnerability'],
                'existing_controls' => $validated['existing_controls'] ?? null,
                'risk_owner' => $validated['risk_owner'],
                'inputs' => json_encode($inputs, JSON_THROW_ON_ERROR),
                'result' => json_encode($result, JSON_THROW_ON_ERROR),
                'risk_basis' => 'inherent',
                'status' => 'draft',
                'revision' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('risk_audit_events')->insert([
                'actor_id' => $request->user()->id,
                'risk_assessment_id' => $assessmentId,
                'action' => 'assessment.created',
                'metadata' => json_encode([
                    'asset_id' => $currentAsset->id,
                    'department_id' => $currentAsset->department_id,
                    'profile' => $profile,
                    'engine_version' => $result['version'],
                    'risk_basis' => 'inherent',
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        });

        return redirect()
            ->route('assessments.index')
            ->with('success', 'Risk assessment saved as a draft.');
    }
}