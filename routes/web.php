<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\RiskAssessmentCorrectionController;
use App\Http\Controllers\RiskAssessmentHistoryController;
use App\Http\Controllers\RiskReportController;
use App\Http\Controllers\RiskReviewController;
use App\Http\Controllers\RiskTreatmentController;
use App\Http\Controllers\RiskTreatmentImplementationController;
use App\Http\Controllers\RiskTreatmentReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Users must be signed in and have confirmed MFA enrollment.
Route::middleware(['auth', 'mfa.enrolled'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $memberships = DB::table('department_memberships')
            ->join(
                'departments',
                'department_memberships.department_id',
                '=',
                'departments.id'
            )
            ->where('department_memberships.user_id', $request->user()->id)
            ->select(
                'departments.id',
                'departments.name',
                'department_memberships.role'
            )
            ->orderBy('departments.name')
            ->get();

        $departmentIds = $memberships->pluck('id')->unique()->values();

        $activeAssets = DB::table('risk_assets')
            ->whereIn('department_id', $departmentIds)
            ->whereNull('deleted_at')
            ->count();

        // Include assessment history for archived assets.
        $assessmentCounts = DB::table('risk_assessments')
            ->join(
                'risk_assets',
                'risk_assessments.risk_asset_id',
                '=',
                'risk_assets.id'
            )
            ->whereIn('risk_assets.department_id', $departmentIds)
            ->select('risk_assessments.status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('risk_assessments.status')
            ->pluck('total', 'status');

        // Pending treatment work applies to active assets
        // with verified assessments.
        $treatmentQuery = DB::table('risk_treatments')
            ->join(
                'risk_assessments',
                'risk_treatments.risk_assessment_id',
                '=',
                'risk_assessments.id'
            )
            ->join(
                'risk_assets',
                'risk_assessments.risk_asset_id',
                '=',
                'risk_assets.id'
            )
            ->whereIn('risk_assets.department_id', $departmentIds)
            ->whereNull('risk_assets.deleted_at')
            ->where('risk_assessments.status', 'verified')
            ->whereColumn('risk_treatments.assessment_revision', 'risk_assessments.revision');

        $awaitingImplementation = (clone $treatmentQuery)
            ->whereNull('risk_treatments.implemented_at')
            ->count();

        $awaitingResidualVerification = (clone $treatmentQuery)
            ->whereNotNull('risk_treatments.implemented_at')
            ->whereNotNull('risk_treatments.residual_inputs')
            ->whereNotNull('risk_treatments.residual_result')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('risk_reviews')
                    ->whereColumn(
                        'risk_reviews.risk_treatment_id',
                        'risk_treatments.id'
                    )
                    ->whereColumn(
                        'risk_reviews.risk_assessment_id',
                        'risk_assessments.id'
                    )
                    ->whereColumn(
                        'risk_reviews.revision',
                        'risk_assessments.revision'
                    )
                    ->where('risk_reviews.stage', 'residual')
                    ->where('risk_reviews.decision', 'verified');
            })
            ->count();

        $implementedWithScores = (clone $treatmentQuery)
            ->whereNotNull('risk_treatments.implemented_at')
            ->whereNotNull('risk_treatments.residual_inputs')
            ->whereNotNull('risk_treatments.residual_result')
            ->count();
        $treatmentTotal = (clone $treatmentQuery)->count();

        $stats = [
            'active_assets' => $activeAssets,
            'draft' => (int) ($assessmentCounts['draft'] ?? 0),
            'submitted' => (int) ($assessmentCounts['submitted'] ?? 0),
            'returned' => (int) ($assessmentCounts['returned'] ?? 0),
            'verified' => (int) ($assessmentCounts['verified'] ?? 0),
            'treatment_total' => $treatmentTotal,
            'treatment_verified' => $implementedWithScores - $awaitingResidualVerification,
            'treatment_incomplete' => $treatmentTotal - $awaitingImplementation - $implementedWithScores,
            'awaiting_implementation' => $awaitingImplementation,
            'awaiting_residual_verification' => $awaitingResidualVerification,
        ];

        return view('dashboard', [
            'memberships' => $memberships,
            'stats' => $stats,
        ]);
    })->name('dashboard');

    // Asset register.
    Route::get('/assets', [
        AssetController::class,
        'index',
    ])->name('assets.index');

    Route::post('/assets', [
        AssetController::class,
        'store',
    ])->name('assets.store');

    Route::get('/assets/{asset}/edit', [
        AssetController::class,
        'edit',
    ])->name('assets.edit');

    Route::put('/assets/{asset}', [
        AssetController::class,
        'update',
    ])->name('assets.update');

    Route::delete('/assets/{asset}', [
        AssetController::class,
        'destroy',
    ])->name('assets.destroy');

    // Risk assessments.
    Route::get('/assessments', [
        RiskAssessmentController::class,
        'index',
    ])->name('assessments.index');

    Route::post('/assessments', [
        RiskAssessmentController::class,
        'store',
    ])->name('assessments.store');

    // Keep export before the assessment detail route.
    Route::get('/assessments/export', [
        RiskAssessmentController::class,
        'export',
    ])->name('assessments.export');

    Route::get('/assessments/{assessment}', [
        RiskAssessmentController::class,
        'show',
    ])
        ->whereNumber('assessment')
        ->name('assessments.show');

    // Assessment review.
    Route::get('/assessments/{assessment}/review', [
        RiskReviewController::class,
        'show',
    ])
        ->whereNumber('assessment')
        ->name('assessments.review');

    Route::post('/assessments/{assessment}/submit', [
        RiskReviewController::class,
        'submit',
    ])
        ->whereNumber('assessment')
        ->name('assessments.submit');

    Route::post('/assessments/{assessment}/review', [
        RiskReviewController::class,
        'decide',
    ])
        ->whereNumber('assessment')
        ->name('assessments.review.store');

    // Assessment corrections.
    Route::get('/assessments/{assessment}/correct', [
        RiskAssessmentCorrectionController::class,
        'edit',
    ])
        ->whereNumber('assessment')
        ->name('assessments.correct');

    Route::put('/assessments/{assessment}/correct', [
        RiskAssessmentCorrectionController::class,
        'update',
    ])
        ->whereNumber('assessment')
        ->name('assessments.correct.update');

    // Treatment plans.
    Route::get('/assessments/{assessment}/treatments', [
        RiskTreatmentController::class,
        'index',
    ])
        ->whereNumber('assessment')
        ->name('assessments.treatments');

    Route::post('/assessments/{assessment}/treatments', [
        RiskTreatmentController::class,
        'store',
    ])
        ->whereNumber('assessment')
        ->name('assessments.treatments.store');

    // Treatment implementation.
    Route::get(
        '/assessments/{assessment}/treatments/{treatment}/implementation',
        [
            RiskTreatmentImplementationController::class,
            'show',
        ]
    )
        ->whereNumber('assessment')
        ->whereNumber('treatment')
        ->name('assessments.implementation');

    Route::post(
        '/assessments/{assessment}/treatments/{treatment}/implementation',
        [
            RiskTreatmentImplementationController::class,
            'store',
        ]
    )
        ->whereNumber('assessment')
        ->whereNumber('treatment')
        ->name('assessments.implementation.store');

    // Residual-risk review.
    Route::get(
        '/assessments/{assessment}/treatments/{treatment}/review',
        [
            RiskTreatmentReviewController::class,
            'show',
        ]
    )
        ->whereNumber('assessment')
        ->whereNumber('treatment')
        ->name('assessments.treatment-review');

    Route::post(
        '/assessments/{assessment}/treatments/{treatment}/review',
        [
            RiskTreatmentReviewController::class,
            'store',
        ]
    )
        ->whereNumber('assessment')
        ->whereNumber('treatment')
        ->name('assessments.treatment-review.store');

    // Assessment reports.
    Route::get('/assessments/{assessment}/report', [
        RiskReportController::class,
        'show',
    ])
        ->whereNumber('assessment')
        ->name('assessments.report');

    // Assessment history is also protected by MFA enrollment.
    Route::get('/assessments/{assessment}/history', [
        RiskAssessmentHistoryController::class,
        'index',
    ])
        ->whereNumber('assessment')
        ->name('assessments.history');
});

// Keep security settings accessible before MFA enrollment.
Route::get('/account/security', function (Request $request) {
    return response()
        ->view('auth.two-factor-settings', [
            'user' => $request->user(),
        ])
        ->header('Cache-Control', 'private, no-store')
        ->header('Pragma', 'no-cache');
})
    ->middleware('auth')
    ->name('account.security');

require __DIR__.'/user-management.php';

require __DIR__.'/assessment-import.php';

Route::middleware(['auth', 'mfa.enrolled'])->group(function () {
    Route::delete('/assessments/{assessment}', [
        \App\Http\Controllers\RiskAssessmentLifecycleController::class,
        'destroy',
    ])
        ->whereNumber('assessment')
        ->name('assessments.destroy');

    Route::get('/assessments/{assessment}/edit', [
        RiskAssessmentCorrectionController::class, 'edit',
    ])->whereNumber('assessment')->name('assessments.edit');

    Route::put('/assessments/{assessment}', [
        RiskAssessmentCorrectionController::class, 'update',
    ])->whereNumber('assessment')->name('assessments.update');
});