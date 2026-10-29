<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Domain\Risk\RiskCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class RiskPreviewController extends Controller
{
    public function __invoke(Request $request, RiskCalculator $calculator): JsonResponse
    {
        $profile = $request->validate(['profile' => ['required', Rule::in([
            'nacsa-3-2024', 'nacsa-5-2024', 'usim-2026',
        ])]])['profile'];
        $max = $profile === 'nacsa-5-2024' ? 5 : 3;
        $keys = array_merge(['c','i','a','likelihood'], $profile === 'usim-2026'
            ? ['operations','financial','reputation'] : ['loss']);
        $rules = [];
        foreach ($keys as $key) $rules[$key] = ['required', 'integer', "between:1,$max"];
        $values = $request->validate($rules);
        foreach ($values as $key => $value) $values[$key] = (int) $value;
        return response()->json($calculator->calculate(['profile' => $profile] + $values));
    }
}
