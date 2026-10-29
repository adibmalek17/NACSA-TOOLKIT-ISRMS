<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RiskAssessmentHistoryController extends Controller
{
    public function index(Request $request, string $assessment)
    {
        $departmentIds = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->select('department_id');

        // Find the assessment within the user's assigned departments.
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
            ->where('risk_assessments.id', $assessment)
            ->whereIn('risk_assets.department_id', $departmentIds)
            ->select(
                'risk_assessments.id',
                'risk_assessments.status',
                'risk_assessments.revision',
                'risk_assets.code as asset_code',
                'risk_assets.name as asset_name',
                'departments.name as department_name'
            )
            ->first();

        abort_if($record === null, 404);

        // Retrieve activity and the account associated with each event.
        $events = DB::table('risk_audit_events')
            ->leftJoin(
                'users',
                'risk_audit_events.actor_id',
                '=',
                'users.id'
            )
            ->where('risk_audit_events.risk_assessment_id', $record->id)
            ->select(
                'risk_audit_events.id',
                'risk_audit_events.action',
                'risk_audit_events.created_at',
                'risk_audit_events.actor_id',
                'users.name as actor_name',
                'users.email as actor_email'
            )
            ->orderByDesc('risk_audit_events.created_at')
            ->orderByDesc('risk_audit_events.id')
            ->paginate(20);

        return view('assessments.history', [
            'assessment' => $record,
            'events' => $events,
        ]);
    }
}