<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Risk\RiskCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RiskCalculatorTest extends TestCase
{
    public function test_nacsa_three_level_scoring(): void
    {
        $result = (new RiskCalculator())->calculate([
            'profile' => 'nacsa-3-2024',
            'c' => 3,
            'i' => 2,
            'a' => 2,
            'loss' => 2,
            'likelihood' => 2,
        ]);

        $this->assertSame(7, $result['ciaTotal']);
        $this->assertSame(3, $result['asset']);
        $this->assertSame(6, $result['impactRaw']);
        $this->assertSame(3, $result['impact']);
        $this->assertSame(6, $result['score']);
        $this->assertSame('Tinggi', $result['label']);
        $this->assertFalse($result['override']);
    }

    public function test_nacsa_five_level_scoring(): void
    {
        $result = (new RiskCalculator())->calculate([
            'profile' => 'nacsa-5-2024',
            'c' => 4,
            'i' => 3,
            'a' => 3,
            'loss' => 4,
            'likelihood' => 3,
        ]);

        $this->assertSame(10, $result['ciaTotal']);
        $this->assertSame(4, $result['asset']);
        $this->assertSame(16, $result['impactRaw']);
        $this->assertSame(4, $result['impact']);
        $this->assertSame(12, $result['score']);
        $this->assertSame('Sederhana', $result['label']);
    }

    public function test_nacsa_risk_matrix_labels(): void
    {
        $matrices = [
            'nacsa-3-2024' => [
                ['Rendah', 'Rendah', 'Sederhana'],
                ['Rendah', 'Sederhana', 'Tinggi'],
                ['Sederhana', 'Tinggi', 'Tinggi'],
            ],
            'nacsa-5-2024' => [
                ['Sangat Rendah', 'Sangat Rendah', 'Sangat Rendah', 'Sangat Rendah', 'Rendah'],
                ['Sangat Rendah', 'Sangat Rendah', 'Rendah', 'Rendah', 'Sederhana'],
                ['Sangat Rendah', 'Rendah', 'Rendah', 'Sederhana', 'Tinggi'],
                ['Sangat Rendah', 'Rendah', 'Sederhana', 'Tinggi', 'Sangat Tinggi'],
                ['Rendah', 'Sederhana', 'Tinggi', 'Sangat Tinggi', 'Sangat Tinggi'],
            ],
        ];

        // These asset/loss pairs produce impact levels 1 through 3 or 5.
        $pairs = [
            'nacsa-3-2024' => [[1, 1], [1, 3], [3, 3]],
            'nacsa-5-2024' => [[1, 1], [1, 5], [2, 5], [3, 5], [5, 5]],
        ];

        $calculator = new RiskCalculator();

        foreach ($matrices as $profile => $rows) {
            foreach ($rows as $index => $labels) {
                [$asset, $loss] = $pairs[$profile][$index];

                foreach ($labels as $column => $expectedLabel) {
                    $result = $calculator->calculate([
                        'profile' => $profile,
                        'c' => $asset,
                        'i' => $asset,
                        'a' => $asset,
                        'loss' => $loss,
                        'likelihood' => $column + 1,
                    ]);

                    $message = "$profile: impact "
                        . ($index + 1)
                        . ', likelihood '
                        . ($column + 1);

                    $this->assertSame(
                        $index + 1,
                        $result['impact'],
                        $message
                    );

                    $this->assertSame(
                        $expectedLabel,
                        $result['label'],
                        $message
                    );
                }
            }
        }
    }

    public function test_usim_uses_highest_cia_value(): void
    {
        $result = (new RiskCalculator())->calculate([
            'profile' => 'usim-2026',
            'c' => 1,
            'i' => 3,
            'a' => 1,
            'operations' => 1,
            'financial' => 2,
            'reputation' => 1,
            'likelihood' => 2,
        ]);

        $this->assertSame(3, $result['asset']);
        $this->assertSame(4, $result['impact']);
        $this->assertSame(24, $result['score']);
        $this->assertSame('Sederhana', $result['label']);
        $this->assertFalse($result['override']);
    }

    public function test_usim_risk_bands(): void
    {
        $cases = [
            // CIA value, O, F, R, likelihood, expected score, label
            [1, 1, 1, 1, 1, 3, 'Rendah'],
            [1, 1, 2, 2, 2, 10, 'Rendah'],
            [2, 1, 1, 1, 2, 12, 'Sederhana'],
            [3, 2, 2, 3, 2, 42, 'Sederhana'],
            [2, 2, 3, 3, 3, 48, 'Tinggi'],
            [3, 3, 3, 3, 3, 81, 'Tinggi'],
        ];

        foreach ($cases as $case) {
            [$cia, $operations, $financial, $reputation,
                $likelihood, $score, $label] = $case;

            $result = (new RiskCalculator())->calculate([
                'profile' => 'usim-2026',
                'c' => $cia,
                'i' => $cia,
                'a' => $cia,
                'operations' => $operations,
                'financial' => $financial,
                'reputation' => $reputation,
                'likelihood' => $likelihood,
            ]);

            $this->assertSame($score, $result['score']);
            $this->assertSame($label, $result['label']);
            $this->assertFalse($result['override']);
        }
    }

    public function test_usim_maximum_impact_override(): void
    {
        $result = (new RiskCalculator())->calculate([
            'profile' => 'usim-2026',
            'c' => 3,
            'i' => 3,
            'a' => 3,
            'operations' => 3,
            'financial' => 3,
            'reputation' => 3,
            'likelihood' => 1,
        ]);

        $this->assertSame(27, $result['score']);
        $this->assertSame(3, $result['level']);
        $this->assertSame('Tinggi', $result['label']);
        $this->assertTrue($result['override']);
    }

    public function test_out_of_range_score_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RiskCalculator())->calculate([
            'profile' => 'nacsa-3-2024',
            'c' => 4,
            'i' => 1,
            'a' => 1,
            'loss' => 1,
            'likelihood' => 1,
        ]);
    }

    public function test_unknown_profile_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RiskCalculator())->calculate([
            'profile' => 'unknown',
        ]);
    }
}