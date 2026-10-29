<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Assessment #{{ $assessment->id }} | NACSA Toolkit</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
            color: #183540;
            font-family: "Segoe UI", sans-serif;
        }

        main { max-width: 1000px; margin: auto; }

        section {
            margin-top: 24px;
            padding: 28px;
            background: white;
            border-radius: 12px;
        }

        a { color: #087c72; }
        p { line-height: 1.7; }
        label { display: block; font-weight: 600; }

        select, textarea {
            display: block;
            width: 100%;
            margin-top: 8px;
            padding: 12px;
            border: 1px solid #cbd8dd;
            border-radius: 6px;
            font: inherit;
        }

        textarea { min-height: 130px; resize: vertical; }

        button {
            margin-top: 20px;
            padding: 12px 24px;
            border: 0;
            border-radius: 7px;
            background: #087c72;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        :focus-visible {
            outline: 3px solid #55baa9;
            outline-offset: 3px;
        }

        .success, .errors {
            padding: 15px;
            margin-top: 20px;
            border-radius: 7px;
        }

        .success { background: #e5f5ee; color: #1e7251; }
        .errors { background: #fff0ee; color: #a13024; }

        article {
            padding: 18px 0;
            border-bottom: 1px solid #e0e7eb;
        }

        .comment {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        @media (max-width: 650px) {
            body { padding: 12px; }
            section { padding: 18px; }
        }
    </style>
</head>
<body>
<main>
    <p>
        <a href="{{ route('assessments.index') }}">← Risk assessments</a>
        ·
        <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}">
            View assessment details
        </a>
        ·
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </p>

    <h1>Review assessment #{{ $assessment->id }}</h1>

    @if (
    $canEdit
    && $assessment->asset_deleted_at === null
)
    <p>
        <a href="{{ route('assessments.edit', ['assessment' => $assessment->id]) }}">
            Edit this assessment
        </a>
    </p>
@endif

    <p>
        Asset: <strong>{{ $assessment->asset_code }}</strong><br>
        Status: <strong>{{ ucfirst($assessment->status) }}</strong><br>
        Revision: {{ $assessment->revision }}
    </p>

    @if (session('success'))
        <div class="success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section>
        <h2>Workflow</h2>

        @if ($assessment->asset_deleted_at)
            <p>
                This asset is archived. Submission and review are unavailable.
                Existing review history remains visible.
            </p>
        @elseif ($assessment->status === 'draft')
            @if ($canSubmit)
                <p>
                    Check the assessment details before submitting it
                    to a Reviewer.
                </p>

                <form
                    method="POST"
                    action="{{ route('assessments.submit', ['assessment' => $assessment->id]) }}"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="revision"
                        value="{{ $assessment->revision }}"
                    >

                    <button type="submit">Submit for review</button>
                </form>
            @else
                <p>
                    This assessment is still a draft.
                    An Admin or Assessor must submit it before review.
                </p>
            @endif
        @elseif ($assessment->status === 'submitted')
            @if ($canReview)
                <p>
                    Read the assessment details, then record your decision
                    and explanation.
                </p>

                <form
                    method="POST"
                    action="{{ route('assessments.review.store', ['assessment' => $assessment->id]) }}"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="revision"
                        value="{{ $assessment->revision }}"
                    >

                    <label>
                        Decision
                        <select name="decision" required>
                            <option value="">Select a decision</option>
                            <option
                                value="verified"
                                @selected(old('decision') === 'verified')
                            >
                                Verify assessment
                            </option>
                            <option
                                value="returned"
                                @selected(old('decision') === 'returned')
                            >
                                Return for correction
                            </option>
                        </select>
                    </label>

                    <p>
                        <label>
                            Review comment
                            <textarea
                                name="comment"
                                maxlength="5000"
                                required
                            >{{ old('comment') }}</textarea>
                        </label>
                    </p>

                    <button type="submit">Save review decision</button>
                </form>
            @else
                <p>
                    Awaiting review by an eligible Reviewer in this department.
                    The assessor cannot review their own assessment.
                </p>
            @endif
        @elseif ($assessment->status === 'verified')
            <p>This assessment has been verified. See the review history below.</p>
        @elseif ($assessment->status === 'returned')
            <p>
                This assessment was returned for correction.
                Read the Reviewer’s comment below.
            </p>
        @else
            <p>No workflow action is available for the current status.</p>
        @endif
    </section>

    <section>
        <h2>Review history</h2>

        @forelse ($reviews as $review)
            <article>
                <h3>{{ ucfirst($review->decision) }}</h3>
                <p>
                    Reviewer: {{ $review->reviewer_name ?? 'Unavailable' }}<br>
                    Revision: {{ $review->revision }}<br>
                    Stage: {{ ucfirst($review->stage) }}<br>
                    Recorded at: {{ $review->created_at }}
                </p>
                <p class="comment">{{ $review->comment }}</p>
            </article>
        @empty
            <p>No review decisions have been recorded yet.</p>
        @endforelse
    </section>
</main>
</body>
</html>