<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RiskReportController extends Controller
{
    public function show(Request $request, string $assessment)
    {
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
            ->whereIn('risk_assets.department_id', function ($query) use ($request) {
                $query->select('department_id')
                    ->from('department_memberships')
                    ->where('user_id', $request->user()->id);
            })
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

        $reviews = DB::table('risk_reviews')
            ->leftJoin('users', 'risk_reviews.reviewer_id', '=', 'users.id')
            ->where('risk_assessment_id', $record->id)
            ->select('risk_reviews.*', 'users.name as reviewer_name')
            ->orderBy('risk_reviews.id')
            ->get();

        $treatments = DB::table('risk_treatments')
            ->where('risk_assessment_id', $record->id)
            ->where('assessment_revision', $record->revision)
            ->orderBy('id')
            ->get();

        foreach ($treatments as $treatment) {
            $treatment->residual_result = $treatment->residual_result !== null
                ? json_decode(
                    $treatment->residual_result,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                )
                : null;

            $treatment->residual_inputs = $treatment->residual_inputs !== null
                ? json_decode(
                    $treatment->residual_inputs,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                )
                : null;
        }

        return view('assessments.report', [
            'assessment' => $record,
            'assessmentReviews' => $reviews
                ->whereNull('risk_treatment_id'),
            'residualReviews' => $reviews
                ->whereNotNull('risk_treatment_id')
                ->where('stage', 'residual')
                ->where('revision', $record->revision)
                ->keyBy('risk_treatment_id'),
            'treatments' => $treatments,
            'generatedAt' => now()->toIso8601String(),
        ]);
    }
}