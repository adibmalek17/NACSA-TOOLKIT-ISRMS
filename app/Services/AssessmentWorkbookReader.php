<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use ZipArchive;

final class AssessmentWorkbookReader
{
    public const HEADERS = [
        'asset_code', 'profile', 'assessed_on', 'threat', 'vulnerability',
        'existing_controls', 'risk_owner', 'c', 'i', 'a', 'likelihood',
        'loss', 'operations', 'financial', 'reputation',
    ];

    public const EXPECTED_HEADERS = ['expected_c', 'expected_i', 'expected_a', 'expected_likelihood', 'expected_loss', 'expected_operations', 'expected_financial', 'expected_reputation'];

    public function read(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            $this->fail('PHP ZIP is not enabled in the running web server. Enable extension=zip and restart php artisan serve.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            $this->fail('Upload a valid .xlsx workbook.');
        }
        try {
            $size = 0;
            if ($zip->numFiles > 1000) {
                $this->fail('The workbook is too complex. Use the supplied template.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                $size += $entry['size'];
                if ($size > 20 * 1024 * 1024) {
                    $this->fail('The expanded workbook exceeds 20 MB. Use a smaller workbook.');
                }
                if (str_contains(strtolower($entry['name']), 'vbaproject')) {
                    $this->fail('Macro-enabled workbooks are not supported.');
                }
            }
        } finally {
            $zip->close();
        }

        $book = null;
        try {
            $reader = new Xlsx();
            // Retain number formats to distinguish Excel dates from ordinary numbers.
            $reader->setReadDataOnly(false);
            $reader->setIncludeCharts(false);
            $info = collect($reader->listWorksheetInfo($path))->firstWhere('worksheetName', 'Assessments');
            if (! $info) {
                $this->fail('The workbook must contain a sheet named Assessments.');
            }
            if ($info['totalRows'] > 101 || $info['totalColumns'] > count(self::HEADERS) + count(self::EXPECTED_HEADERS)) {
                $this->fail('Use at most 100 assessment rows and at most 23 supported columns.');
            }
            $reader->setLoadSheetsOnly(['Assessments']);
            $book = $reader->load($path);
            $sheet = $book->getSheetByName('Assessments');
            $headers = [];
            for ($column = 1; $column <= $info['totalColumns']; $column++) {
                $headers[] = trim((string) $sheet->getCell([$column, 1])->getValue());
            }
            $allowed = array_merge(self::HEADERS, self::EXPECTED_HEADERS);
            if (count($headers) !== count(array_unique($headers)) || in_array('', $headers, true)
                || array_diff($headers, $allowed) !== []) {
                $this->fail('Use unique supported column names in row 1. Expected scores use expected_c, expected_i, expected_a, expected_likelihood and the profile impact columns.');
            }

            $rows = [];
            for ($rowNumber = 2; $rowNumber <= $info['totalRows']; $rowNumber++) {
                $values = array_fill_keys(array_merge(self::HEADERS, self::EXPECTED_HEADERS), '');
                $errors = [];
                foreach ($headers as $index => $header) {
                    $cell = $sheet->getCell([$index + 1, $rowNumber]);
                    $value = $cell->getValue();
                    if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                        $errors[] = "$header: formulas are not accepted. Paste values instead.";
                        $value = '[formula]';
                    } elseif ($cell->getDataType() === DataType::TYPE_ERROR || is_bool($value)) {
                        $errors[] = "$header: enter a value, not an Excel error or boolean.";
                        $value = '[invalid cell]';
                    } elseif ($value instanceof RichText) {
                        $value = $value->getPlainText();
                    }
                    if ($header === 'assessed_on' && is_numeric($value) && Date::isDateTime($cell)) {
                        $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
                    }
                    $value = $value === null ? '' : trim((string) $value);
                    if (mb_strlen($value) > 5000) {
                        $errors[] = "$header: maximum 5000 characters.";
                        $value = mb_substr($value, 0, 5000);
                    }
                    $values[$header] = $value;
                }
                if (count(array_filter($values, fn ($v) => $v !== '')) === 0) {
                    continue;
                }
                $rows[] = ['row' => $rowNumber, 'values' => $values, 'errors' => $errors];
            }
            if (! $rows) {
                $this->fail('The Assessments sheet contains no assessment rows.');
            }
            return $rows;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->fail('The workbook could not be read. Save it as .xlsx using the supplied template.');
        } finally {
            $book?->disconnectWorksheets();
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['workbook' => $message]);
    }
}
