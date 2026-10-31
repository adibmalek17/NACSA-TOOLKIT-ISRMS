<?php

declare(strict_types=1);

namespace App\Services;

final class RiskPresentation
{
    public static function tone(array $result, ?string $profile = null): string
    {
        $profile = $result['profile'] ?? $profile;
        $level = filter_var($result['level'] ?? null, FILTER_VALIDATE_INT);
        $maximum = match ($profile) {
            'nacsa-5-2024' => 5,
            'nacsa-3-2024', 'usim-2026' => 3,
            default => 0,
        };

        if ($level === false || $level < 1 || $level > $maximum) {
            return 'neutral';
        }

        $middle = $maximum === 5 ? 3 : 2;
        return $level < $middle ? 'low' : ($level === $middle ? 'medium' : 'high');
    }
}
