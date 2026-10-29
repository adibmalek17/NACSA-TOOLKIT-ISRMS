<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RiskAsset;
use App\Services\AssessmentWorkbookReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class RiskAssessmentImportController extends Controller
{
    private const PROFILES = ['nacsa-3-2024', 'nacsa-5-2024', 'usim-2026'];

    public function index()
    {
        return redirect()->to(route('assessments.index').'#create-assessment');
    }

    private function authorizeDepartment(Request $request, int $departmentId): void
    {
        $allowed = DB::table('department_memberships')
            ->where('user_id', $request->user()->id)
            ->where('department_id', $departmentId)
            ->whereIn('role', ['admin', 'assessor'])->exists();
        abort_unless($allowed, 403, 'An Admin or Assessor assignment in this department is required.');
    }

    public function fill(Request $request, AssessmentWorkbookReader $reader)
    {
        $base = $request->validate([
            'risk_asset_id' => ['required', 'integer'],
            'profile' => ['required', Rule::in(self::PROFILES)],
            'workbook' => ['required', 'file', 'extensions:xlsx', 'mimes:xlsx', 'max:2048'],
        ]);
        $asset = RiskAsset::query()->whereNull('deleted_at')->findOrFail($base['risk_asset_id']);
        $this->authorizeDepartment($request, (int) $asset->department_id);
        $profile = $base['profile'];
        $rows = $reader->read($request->file('workbook')->getRealPath());

        // The earlier three-profile example is still supported. Never silently pick the first row.
        $matches = array_values(array_filter($rows, function ($row) use ($asset, $profile) {
            return ($row['values']['profile'] === '' || $row['values']['profile'] === $profile)
                && ($row['values']['asset_code'] === '' || $row['values']['asset_code'] === $asset->code);
        }));
        if (count($matches) !== 1) {
            $message = count($matches) === 0
                ? 'No Excel row matches the selected asset and scoring profile. Use the example for your selected profile, or correct the asset_code/profile columns in the older workbook.'
                : 'More than one Excel row matches. Keep one assessment row for the selected asset and profile, then upload again.';
            throw ValidationException::withMessages(['workbook' => $message]);
        }
        $row = $matches[0];
        if ($row['errors'] !== []) {
            throw ValidationException::withMessages(['workbook' => $row['errors']]);
        }
        $values = $row['values'];
        $isUsim = $profile === 'usim-2026';
        $max = $profile === 'nacsa-5-2024' ? 5 : 3;
        $scoreKeys = array_merge(['c', 'i', 'a', 'likelihood'], $isUsim
            ? ['operations', 'financial', 'reputation'] : ['loss']);
        $rules = [
            // These may be completed by the user after filling the form.
            'assessed_on' => ['nullable', 'date_format:Y-m-d'],
            'threat' => ['nullable', 'string', 'max:5000'],
            'vulnerability' => ['nullable', 'string', 'max:5000'],
            'existing_controls' => ['nullable', 'string', 'max:5000'],
            'risk_owner' => ['nullable', 'string', 'max:180'],
        ];
        foreach ($scoreKeys as $key) {
            $rules[$key] = ['required', 'integer', "between:1,$max"];
        }
        foreach ($isUsim ? ['loss'] : ['operations', 'financial', 'reputation'] as $key) {
            $rules[$key] = ['prohibited'];
        }
        $validated = Validator::make($values, $rules)->validate();
        $fields = [];
        foreach (['assessed_on', 'threat', 'vulnerability', 'existing_controls', 'risk_owner'] as $key) {
            $fields[$key] = $validated[$key] ?? '';
        }
        foreach ($scoreKeys as $key) {
            $fields[$key] = (int) $validated[$key];
        }
        // No database writes: the existing assessment store action remains the only save step.
        return response()->json([
            'fields' => $fields,
            'row' => $row['row'],
            'risk_asset_id' => (int) $asset->id,
            'profile' => $profile,
            'message' => 'Excel values filled in. You can edit them below. Nothing has been saved yet.',
        ])->header('Cache-Control', 'private, no-store');
    }

    public function template(Request $request)
    {
        $base = $request->validate(['profile' => ['required', Rule::in(self::PROFILES)]]);
        abort_unless(DB::table('department_memberships')->where('user_id', $request->user()->id)
            ->whereIn('role', ['admin', 'assessor'])->exists(), 403);
        $profile = $base['profile'];
        $headers = array_values(array_diff(AssessmentWorkbookReader::HEADERS, ['asset_code', 'profile']));
        $values = [
            now()->timezone('Asia/Kuala_Lumpur')->toDateString(),
            'DEMO: Ransomware interrupts student records services.',
            'DEMO: Irregular patching and accessible backups.',
            'DEMO: Antivirus and daily backups.',
            'ICT Infrastructure Manager',
            3, 3, 3, 2, 3, '', '', '',
        ];
        if ($profile === 'nacsa-5-2024') {
            $values = array_replace($values, [5 => 5, 6 => 4, 7 => 4, 8 => 4, 9 => 4]);
        } elseif ($profile === 'usim-2026') {
            $values = array_replace($values, [9 => '', 10 => 3, 11 => 2, 12 => 3]);
        }
        return response()->streamDownload(function () use ($headers, $values) {
            $book = new Spreadsheet();
            try {
                $sheet = $book->getActiveSheet();
                $sheet->setTitle('Assessments');
                foreach ($headers as $index => $header) {
                    $sheet->setCellValueExplicit([$index + 1, 1], $header, DataType::TYPE_STRING);
                    $value = $values[$index];
                    $sheet->setCellValueExplicit([$index + 1, 2], $value,
                        is_int($value) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
                }
                $sheet->getStyle('A1:M1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A1:M1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF183540');
                $sheet->getStyle('A2:M2')->getAlignment()->setWrapText(true)->setVertical('center');
                $sheet->getStyle('A2')->getNumberFormat()->setFormatCode('@');
                foreach (range('A', 'M') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(in_array($col, ['B', 'C', 'D', 'E'], true) ? 35 : 20);
                }
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(65);
                $sheet->freezePane('A2');
                $writer = new Xlsx($book);
                $writer->setPreCalculateFormulas(false);
                $writer->save('php://output');
            } finally {
                $book->disconnectWorksheets();
            }
        }, 'assessment-'.$profile.'-example.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
