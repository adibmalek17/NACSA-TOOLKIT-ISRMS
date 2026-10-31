@php
    $departmentRole = $departmentRoles->get($assessment->department_id);
    $canManageAssessment = $departmentRole === 'admin'
        || ($departmentRole === 'assessor' && (int) $assessment->assessor_id === (int) auth()->id());
@endphp
@if ($canManageAssessment)
    <div class="assessment-actions" style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
        @if ($assessment->asset_deleted_at === null)
            <a class="edit-action" href="{{ route('assessments.edit', ['assessment' => $assessment->id]) }}">Edit</a>
        @else
            <span>Restore the asset to edit</span>
        @endif
        <form method="POST"
              action="{{ route('assessments.destroy', ['assessment' => $assessment->id]) }}"
              style="margin: 0;"
              onsubmit="return confirm('Permanently delete this assessment and ALL its linked reviews and treatment plans? The asset and audit events will remain. This cannot be undone.');">
            @csrf
            @method('DELETE')
            <input type="hidden" name="revision" value="{{ $assessment->revision }}">
            <input type="hidden" name="status" value="{{ $assessment->status }}">
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="danger">Delete</button>
        </form>
    </div>
@else
    <p style="margin: 8px 0;">View only</p>
@endif
