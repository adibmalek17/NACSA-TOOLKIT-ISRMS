<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Assessments | NACSA Toolkit</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f4f6f8; color: #183540; font-family: "Segoe UI", sans-serif; }
        main { max-width: 1200px; margin: auto; }
        section, article { margin: 24px 0; padding: 24px; background: white; border-radius: 12px; }
        a { color: #087c72; }
        p, li { line-height: 1.6; }
        label { display: block; margin: 20px 0; font-weight: 600; }
        input:not([type=checkbox]), select { display: block; width: 100%; max-width: 650px; padding: 12px; margin-top: 8px; font: inherit; border: 1px solid #cbd8dd; border-radius: 6px; }
        button { background: #087c72; color: white; border: 0; padding: 12px 24px; border-radius: 7px; font: inherit; cursor: pointer; }
        :focus-visible { outline: 3px solid #55baa9; outline-offset: 3px; }
        .errors { background: #fff0ee; color: #a13024; padding: 16px; border-radius: 7px; }
        .ready { color: #1e7251; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; text-align: left; }
        td, th { border-bottom: 1px solid #e0e7eb; padding: 12px; vertical-align: top; }
        th { background: #f4f6f8; }
        dd { margin: 6px 0 16px; white-space: pre-wrap; overflow-wrap: anywhere; }
        dt { font-weight: bold; }
        summary { cursor: pointer; padding: 8px 0; }
        @media(max-width:650px) { body { padding: 12px; } section, article { padding: 16px; } }
    </style>
</head>
<body>
<main>
    <p><a href="{{ route('assessments.index') }}">← Risk assessments</a></p>
    <h1>Import assessments from Excel</h1>
    @if ($errors->any())
        <div class="errors" role="alert"><ul>
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif

    @if (!isset($import))
        <section>
            <h2>1. Prepare your workbook</h2>
            <p><a href="{{ route('assessments.import.template') }}">Download example Excel workbook</a></p>
            <p>The workbook contains three fictional examples, one per scoring profile. Replace the example asset codes with active assets in your selected department, or remove examples you do not need.</p>
            <p>Keep the Assessments sheet name and row 1 headers unchanged. Use one assessment per row, up to 100 rows. Upload .xlsx files up to 2 MB. Formulas are not accepted; paste their values instead.</p>
            <p>Use YYYY-MM-DD dates, or Excel date cells. For NACSA profiles, fill Loss and leave Operations, Financial and Reputation blank. For USIM, fill those three impacts and leave Loss blank.</p>
            <h2>2. Upload and preview</h2>
            <form method="POST" action="{{ route('assessments.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <label>Department
                    <select name="department_id" required>
                        <option value="">Select a department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Excel workbook
                    <input type="file" name="workbook" accept=".xlsx" required>
                </label>
                <button type="submit">Preview assessments</button>
            </form>
        </section>
    @else
        <section>
            <h2>Review before importing</h2>
            <p>Department: <strong>{{ $departmentName }}</strong></p>
            <p><strong>{{ $validCount }}</strong> valid row(s); <strong>{{ count($rows) - $validCount }}</strong> invalid or duplicate row(s) will be skipped.</p>
            <p>Nothing has been saved as an assessment yet. This preview expires 30 minutes after upload. Each imported assessment is a new draft. A separate upload of the same file can create additional drafts.</p>
            <p><a href="{{ route('assessments.import') }}">Choose another file or upload corrections</a></p>
        </section>
        @foreach ($rows as $row)
            <article>
                <h2>Excel row {{ $row['row'] }} — {{ $row['values']['asset_code'] }}</h2>
                @if ($row['errors'])
                    <div class="errors"><strong>Skipped unless corrected and uploaded again</strong><ul>
                        @foreach ($row['errors'] as $error)<li>{{ $error }}</li>@endforeach
                    </ul></div>
                @else
                    <p class="ready"><strong>Ready to import</strong> — {{ $row['asset_name'] }}</p>
                    <p>Calculated score: <strong>{{ $row['result']['score'] }}</strong>; level: <strong>{{ $row['result']['label'] }}</strong></p>
                @endif
                <p>Profile: {{ $row['values']['profile'] }} · Date: {{ $row['values']['assessed_on'] }}</p>
                <div class="table-wrap"><table>
                    <thead><tr><th>C</th><th>I</th><th>A</th><th>Likelihood</th><th>Loss</th><th>Operations</th><th>Financial</th><th>Reputation</th></tr></thead>
                    <tbody><tr>
                        @foreach (['c','i','a','likelihood','loss','operations','financial','reputation'] as $key)
                            <td>{{ $row['values'][$key] !== '' ? $row['values'][$key] : '—' }}</td>
                        @endforeach
                    </tr></tbody>
                </table></div>
                <details>
                    <summary>Risk description and owner</summary>
                    <dl>
                        @foreach (['risk_owner' => 'Risk owner', 'threat' => 'Threat', 'vulnerability' => 'Vulnerability', 'existing_controls' => 'Existing controls'] as $key => $label)
                            <dt>{{ $label }}</dt><dd>{{ $row['values'][$key] ?: 'Not provided' }}</dd>
                        @endforeach
                    </dl>
                </details>
            </article>
        @endforeach
        @if ($validCount > 0)
            <section>
                <form method="POST" action="{{ route('assessments.import.store', ['batch' => $import->id]) }}">
                    @csrf
                    <label><input type="checkbox" name="confirm" value="1" required>
                        I have reviewed these rows. Save the {{ $validCount }} valid row(s) as new drafts and skip invalid rows.
                    </label>
                    <button type="submit">Import {{ $validCount }} valid assessment(s)</button>
                </form>
            </section>
        @endif
    @endif
</main>
</body>
</html>
