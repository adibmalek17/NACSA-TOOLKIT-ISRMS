<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class DashboardRiskAnalytics
{
    /** Department IDs must come from the authenticated user's memberships. */
    public static function forDepartments(array $departmentIds): array
    {
        $inherent = ['high' => 0, 'medium' => 0, 'low' => 0, 'neutral' => 0];
        $expected = $inherent;
        $residual = $inherent;
        if ($departmentIds === []) {
            return compact('inherent', 'expected', 'residual');
        }

        $assessments = DB::table('risk_assessments as assessment')
            ->join('risk_assets as asset', 'assessment.risk_asset_id', '=', 'asset.id')
            ->whereIn('asset.department_id', $departmentIds)
            ->whereNull('asset.deleted_at');

        foreach ((clone $assessments)->select('assessment.result', 'assessment.expected_result', 'assessment.profile')->cursor() as $row) {
            $inherent[self::band($row->result, $row->profile)]++;
            $expectedResult = json_decode($row->expected_result ?? '', true);
            if (is_array($expectedResult) && isset($expectedResult['score'])) {
                $expected[self::band($row->expected_result, $row->profile)]++;
            }
        }

        $plans = (clone $assessments)
            ->join('risk_treatments as plan', 'plan.risk_assessment_id', '=', 'assessment.id')
            ->where('assessment.status', 'verified')
            ->whereColumn('plan.assessment_revision', 'assessment.revision')
            ->whereNotNull('plan.implemented_at')
            ->whereNotNull('plan.residual_inputs')
            ->whereNotNull('plan.residual_result')
            ->select('plan.residual_result', 'assessment.profile');

        foreach ($plans->cursor() as $row) {
            $residual[self::band($row->residual_result, $row->profile)]++;
        }

        return compact('inherent', 'expected', 'residual');
    }

    private static function band(?string $json, string $profile): string
    {
        $result = json_decode($json ?? '', true);
        if (!is_array($result) || !isset($result['score'])) {
            return 'neutral';
        }
        return RiskPresentation::tone($result, $profile);
    }
}
