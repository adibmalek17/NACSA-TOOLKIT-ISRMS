@php
    $departmentRole = $departmentRoles->get($assessment->department_id);
    $canManageAssessment = $departmentRole === 'admin'
        || ($departmentRole === 'assessor' && (int) $assessment->assessor_id === (int) auth()->id());
@endphp
@if ($canManageAssessment)
    <div style="margin-top: 10px;">
        @if ($assessment->asset_deleted_at === null)
            <a href="{{ route('assessments.edit', ['assessment' => $assessment->id]) }}">Edit</a>
        @else
            <span>Restore the asset to edit</span>
        @endif
        <form method="POST"
              action="{{ route('assessments.destroy', ['assessment' => $assessment->id]) }}"
              style="margin-top: 10px;"
              onsubmit="return confirm('Permanently delete this assessment and ALL its linked reviews and treatment plans? The asset and audit events will remain. This cannot be undone.');">
            @csrf
            @method('DELETE')
            <input type="hidden" name="revision" value="{{ $assessment->revision }}">
            <input type="hidden" name="status" value="{{ $assessment->status }}">
            <label style="font-weight: normal; font-size: 0.9rem;">
                <input type="checkbox" name="confirm_delete" value="1" required
                       style="display: inline; width: auto; margin: 0;">
                Delete this assessment and its reviews/treatments
            </label>
            <button type="submit" style="background: #a13024; padding: 8px 12px;">Delete</button>
        </form>
    </div>
@else
    <p style="margin: 8px 0;">View only</p>
@endif
