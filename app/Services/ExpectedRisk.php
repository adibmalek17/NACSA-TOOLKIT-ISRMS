<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Risk\RiskCalculator;
use Illuminate\Support\Facades\Validator;

final class ExpectedRisk
{
    public const KEYS = ['c', 'i', 'a', 'likelihood', 'loss', 'operations', 'financial', 'reputation'];

    public static function keys(string $profile): array
    {
        return array_merge(['c', 'i', 'a', 'likelihood'], $profile === 'usim-2026'
            ? ['operations', 'financial', 'reputation'] : ['loss']);
    }

    // Empty scores remain unassessed. Once any score is supplied, require the full profile.
    public static function calculate(array $data, string $profile, RiskCalculator $calculator): array
    {
        $base = Validator::make($data, ['expected' => ['nullable', 'array:'.implode(',', self::KEYS)]])->validate();
        $values = $base['expected'] ?? [];
        $hasValues = collect($values)->contains(fn ($value) => $value !== null && $value !== '');
        if (! $hasValues) {
            return ['inputs' => null, 'result' => null];
        }
        $keys = self::keys($profile);
        $maximum = $profile === 'nacsa-5-2024' ? 5 : 3;
        $rules = [];
        foreach (self::KEYS as $key) {
            $rules['expected.'.$key] = in_array($key, $keys, true)
                ? ['required', 'integer', 'between:1,'.$maximum] : ['prohibited'];
        }
        $validated = Validator::make(['expected' => $values], $rules)->validate();
        $inputs = ['profile' => $profile];
        foreach ($keys as $key) {
            $inputs[$key] = (int) $validated['expected'][$key];
        }
        return ['inputs' => $inputs, 'result' => $calculator->calculate($inputs)];
    }

    public static function decode(mixed $value): array
    {
        return is_array($value) ? $value : ($value ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : []);
    }

    public static function columns(array $expected): array
    {
        return [
            'expected_inputs' => $expected['inputs'] === null ? null : json_encode($expected['inputs'], JSON_THROW_ON_ERROR),
            'expected_result' => $expected['result'] === null ? null : json_encode($expected['result'], JSON_THROW_ON_ERROR),
        ];
    }
}
