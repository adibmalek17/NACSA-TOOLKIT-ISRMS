<nav class="assessment-nav" aria-label="Assessment navigation">
    <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}" @if ($activeTab === 'overview') aria-current="page" @endif>Overview</a>
    <a href="{{ route('assessments.review', ['assessment' => $assessment->id]) }}" @if ($activeTab === 'review') aria-current="page" @endif>Review</a>
    <a href="{{ route('assessments.treatments', ['assessment' => $assessment->id]) }}" @if ($activeTab === 'treatments') aria-current="page" @endif>Treatment plans</a>
    <a href="{{ route('assessments.history', ['assessment' => $assessment->id]) }}" @if ($activeTab === 'history') aria-current="page" @endif>Activity history</a>
    <a href="{{ route('assessments.report', ['assessment' => $assessment->id]) }}" @if ($activeTab === 'report') aria-current="page" @endif>Report</a>
</nav>
