<?php

namespace Tests\Feature;

use App\Domain\Risk\RiskCalculator;
use App\Services\ExpectedRisk;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ExpectedRiskValidationTest extends TestCase
{
    public function test_all_profiles_calculate_expected_scores_with_the_existing_engine(): void
    {
        foreach (['nacsa-3-2024','nacsa-5-2024','usim-2026'] as $profile) {
            $values = array_fill_keys(ExpectedRisk::keys($profile), $profile === 'nacsa-5-2024' ? 5 : 3);
            $calculator = new RiskCalculator();
            $result = ExpectedRisk::calculate(['expected'=>$values], $profile, $calculator);
            $this->assertSame($calculator->calculate(['profile'=>$profile] + $values), $result['result']);
        }
    }

    public function test_partial_expected_scores_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        ExpectedRisk::calculate(['expected'=>['c'=>1]], 'nacsa-3-2024', new RiskCalculator());
    }

    public function test_inputs_for_the_wrong_profile_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        ExpectedRisk::calculate(['expected'=>array_fill_keys(ExpectedRisk::KEYS,1)], 'usim-2026', new RiskCalculator());
    }

    public function test_absent_expected_scores_remain_unassessed(): void
    {
        $this->assertSame(['inputs'=>null,'result'=>null], ExpectedRisk::calculate([], 'usim-2026', new RiskCalculator()));
    }
}
