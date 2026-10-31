<div class="assessment-context" aria-label="Assessment summary">
    <div><span>Asset</span><strong>{{ $assessment->asset_code }}</strong></div>
    <div><span>Assessment</span><strong>#{{ $assessment->id }}</strong></div>
    <div><span>Revision</span><strong>{{ $assessment->revision }}</strong></div>
    <div><span>Status</span><strong>{{ ucfirst($assessment->status) }}</strong></div>
</div>
