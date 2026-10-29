<?php
declare(strict_types=1);

namespace App\Domain\Risk;

use InvalidArgumentException;

/** Pure domain service. Keep profile/version with every assessment snapshot. */
final class RiskCalculator
{
    public function calculate(array $input): array
    {
        $profile = $input['profile'] ?? '';
        $max = match ($profile) {
            'nacsa-3-2024', 'usim-2026' => 3,
            'nacsa-5-2024' => 5,
            default => throw new InvalidArgumentException('Unknown scoring profile'),
        };
        $keys = array_merge(['c', 'i', 'a', 'likelihood'], $profile === 'usim-2026'
            ? ['operations', 'financial', 'reputation'] : ['loss']);
        foreach ($keys as $key) {
            if (!isset($input[$key]) || !is_int($input[$key]) || $input[$key] < 1 || $input[$key] > $max) {
                throw new InvalidArgumentException("$key must be an integer from 1 to $max");
            }
        }
        $sum = $input['c'] + $input['i'] + $input['a'];
        if ($profile === 'usim-2026') {
            $asset = max($input['c'], $input['i'], $input['a']);
            $impact = $input['operations'] + $input['financial'] + $input['reputation'];
            $raw = $impact;
            $score = $asset * $impact * $input['likelihood'];
            $override = $asset === 3 && $impact === 9 && $input['likelihood'] === 1;
            $level = $override || $score >= 48 ? 3 : ($score >= 11 ? 2 : 1);
            $labels = ['Rendah', 'Sederhana', 'Tinggi'];
        } else {
            $asset = (int) ceil($sum / 3);
            $bounds = $max === 3 ? [2, 4, 9] : [4, 9, 14, 19, 25];
            $labels = $max === 3 ? ['Rendah', 'Sederhana', 'Tinggi']
                : ['Sangat Rendah', 'Rendah', 'Sederhana', 'Tinggi', 'Sangat Tinggi'];
            $raw = $asset * $input['loss'];
            $impact = $this->band($raw, $bounds);
            $score = $impact * $input['likelihood'];
            $level = $this->band($score, $bounds);
            $override = false;
        }
        return ['profile' => $profile, 'version' => '0.1', 'ciaTotal' => $sum,
            'asset' => $asset, 'impactRaw' => $raw, 'impact' => $impact,
            'score' => $score, 'level' => $level, 'label' => $labels[$level - 1], 'override' => $override];
    }

    private function band(int $value, array $bounds): int
    {
        foreach ($bounds as $index => $bound) {
            if ($value <= $bound) return $index + 1;
        }
        throw new InvalidArgumentException('Value exceeds profile bounds');
    }
}
