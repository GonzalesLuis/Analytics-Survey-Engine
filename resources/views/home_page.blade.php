<x-layout title="Home">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Homepage</h1>

    @if (session('status_message'))
        <p class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status_message') }}
        </p>
    @endif

    @if ($tutoringSession)
        <div class="mt-6 space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p><span class="font-semibold">Current Session ID:</span> {{ $tutoringSession->tutoring_session_id }}</p>
            <p><span class="font-semibold">Status:</span> {{ ucfirst($tutoringSession->status) }}</p>
            <p><span class="font-semibold">Session Start:</span> {{ $tutoringSession->session_start ?? 'N/A' }}</p>
            <p><span class="font-semibold">Session End:</span> {{ $tutoringSession->session_end ?? 'N/A' }}</p>
            <p><span class="font-semibold">Evaluated At:</span> {{ $tutoringSession->evaluated_at ?? 'N/A' }}</p>
        </div>
    @endif

    <h3 class="mt-6 text-lg font-semibold text-slate-900">{{ $progress['status_text'] }}</h3>

    <div class="mt-4 flex flex-wrap gap-3">
        @if ($progress['can_start'])
            <form method="POST" action="/session/start">
                @csrf
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Start tutoring session
                </button>
            </form>
        @endif

        @if ($progress['can_end'])
            <form method="POST" action="/session/end">
                @csrf
                <button type="submit" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-500">
                    End session
                </button>
            </form>
        @endif

        @if ($progress['show_pre'])
            <form method="GET" action="/pre_session">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                    Pre session survey
                </button>
            </form>
        @endif

        @if ($progress['show_post'])
            <form method="GET" action="/post_session">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                    Post session survey
                </button>
            </form>
        @endif

        @if ($progress['show_eval'])
            <form method="GET" action="/tutee_evaluation">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                    Tutee evaluation survey
                </button>
            </form>
        @endif

        @if (!empty($canViewSurveyResults))
            <form method="GET" action="/survey_results">
                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                    View Survey Results
                </button>
            </form>
        @endif
    </div>

    @php
        $debug = $debugResults ?? [];
        $fmt = function ($v) {
            return round((float) ($v ?? 0), 2);
        };
    @endphp
    
    <h2 class="mt-10 border-t border-slate-200 pt-6 text-xl font-bold text-slate-900">Survey Results</h2>

    @if(!empty($debug['pre_session']))
        @php $pre = $debug['pre_session']; @endphp
        <h3 class="mt-6 text-lg font-semibold text-slate-900">Pre-session Learning Gain</h3>
        <div class="mt-2 space-y-1 text-sm text-slate-700">
            <p>Prior Understanding: {{ $fmt($pre['prior_understanding'] ?? 0) }}</p>
            <p>Confidence: {{ $fmt($pre['confidence'] ?? 0) }}</p>
            <p>Application Readiness: {{ $fmt($pre['application_readiness'] ?? 0) }}</p>
            <p>Difficulty Awareness: {{ $fmt($pre['difficulty_awareness'] ?? 0) }}</p>
            <p class="font-semibold text-slate-900">Pre-session SRLG Score: {{ $fmt($pre['average_score'] ?? 0) }}</p>
        </div>
    @endif

    @if(!empty($debug['post_session']))
        @php $post = $debug['post_session']; @endphp
        <h3 class="mt-6 text-lg font-semibold text-slate-900">Post-session Learning Gain</h3>
        <div class="mt-2 space-y-1 text-sm text-slate-700">
            <p>Prior Understanding: {{ $fmt($post['prior_understanding'] ?? 0) }}</p>
            <p>Confidence: {{ $fmt($post['confidence'] ?? 0) }}</p>
            <p>Application Readiness: {{ $fmt($post['application_readiness'] ?? 0) }}</p>
            <p>Difficulty Awareness: {{ $fmt($post['difficulty_awareness'] ?? 0) }}</p>
            <p class="font-semibold text-slate-900">Post-session SRLG Score: {{ $fmt($post['average_score'] ?? 0) }}</p>
        </div>
    @endif

    @if(!empty($debug['tutee_evaluation']))
        @php $eval = $debug['tutee_evaluation']; @endphp
        <h3 class="mt-6 text-lg font-semibold text-slate-900">Tutee Evaluation</h3>
        <div class="mt-2 space-y-1 text-sm text-slate-700">
            <p>Understanding: {{ $fmt($eval['understanding'] ?? 0) }}</p>
            <p>Participation: {{ $fmt($eval['participation'] ?? 0) }}</p>
            <p>Application: {{ $fmt($eval['application'] ?? 0) }}</p>
            <p>Effort: {{ $fmt($eval['effort'] ?? 0) }}</p>
            <p>Difficulty Indicators: {{ $fmt($eval['difficulty_indicators'] ?? 0) }}</p>
            <p class="font-semibold text-slate-900">Tutee Evaluation Score: {{ $fmt($eval['average_score'] ?? 0) }}</p>
        </div>
    @endif

    @if(!empty($debug['tutee_satisfaction']))
        @php $sat = $debug['tutee_satisfaction']; @endphp
        <h3>Tutee Satisfaction</h3>
        <p>Overall Experience: {{ $fmt($sat['overall_experience'] ?? 0) }}</p>
        <p>Perceived Usefulness: {{ $fmt($sat['perceived_usefulness'] ?? 0) }}</p>
        <p>Behavioral Intent: {{ $fmt($sat['behavioral_intent'] ?? 0) }}</p>
        <p><strong>Tutee Satisfaction Score:</strong> {{ $fmt($sat['average_score'] ?? 0) }}</p><br>
    @endif

    @if(!empty($debug['tutor_compatibility']))
        @php
            $compat = $debug['tutor_compatibility'];
            $breakdown = $debug['compatibility_breakdown'] ?? ($compat['breakdown'] ?? null);
        @endphp
        <h3>Tutor Compatibility</h3>

        <p><strong>ENGAGEMENT_LEVEL</strong></p>
        <p>dialogue_oriented: {{ $fmt(data_get($compat, 'engagement_level.dialogue_oriented', data_get($compat, 'engagement.dialogue_oriented', 0))) }}</p>
        <p>explanation_oriented: {{ $fmt(data_get($compat, 'engagement_level.explanation_oriented', data_get($compat, 'engagement.explanation_oriented', 0))) }}</p>
        <p>practice_oriented: {{ $fmt(data_get($compat, 'engagement_level.practice_oriented', data_get($compat, 'engagement.practice_oriented', 0))) }}</p><br>

        <p><strong>GUIDANCE_LEVEL</strong></p>
        <p>high_guidance: {{ $fmt(data_get($compat, 'guidance_level.high_guidance', data_get($compat, 'guidance.high_guidance', 0))) }}</p>
        <p>low_guidance: {{ $fmt(data_get($compat, 'guidance_level.low_guidance', data_get($compat, 'guidance.low_guidance', 0))) }}</p>
        <p>moderate_guidance: {{ $fmt(data_get($compat, 'guidance_level.moderate_guidance', data_get($compat, 'guidance.moderate_guidance', 0))) }}</p><br>

        <p><strong>LANGUAGE</strong></p>
        <p>english: {{ $fmt(data_get($compat, 'language.english', 0)) }}</p>
        <p>tagalog: {{ $fmt(data_get($compat, 'language.tagalog', 0)) }}</p><br>

        @if(!empty($breakdown['dimensions']))
            <p><strong>Tutor Compatibility Computation (Fallback Logic)</strong></p>
            @foreach($breakdown['dimensions'] as $dimensionName => $dimension)
                <p><strong>{{ strtoupper($dimensionName) }}</strong></p>
                @foreach(($dimension['options'] ?? []) as $optionRow)
                    <p><strong>{{ $optionRow['option'] ?? '' }}</strong></p>
                    <p>Weight: {{ $fmt($optionRow['weight'] ?? 0) }}</p>
                    <p>Tutor Score: {{ $fmt($optionRow['tutor_score'] ?? 0) }}</p>
                    <p>Contribution (if used): {{ $fmt($optionRow['contribution_if_used'] ?? 0) }}</p><br>
                @endforeach
                <p><strong>Selected Score for {{ $dimensionName }}:</strong> {{ $fmt($dimension['selected_score'] ?? 0) }}</p><br>
            @endforeach

            <p><strong>FINAL COMPUTATION</strong></p>
            <p>Total Score (sum of selected per dimension): {{ $fmt($breakdown['final']['total_score'] ?? 0) }}</p>
            <p>Number of Dimensions: {{ $fmt($breakdown['final']['dimension_count'] ?? 0) }}</p>
            <p>Tutor Compatibility Score = Total Score / No. of Dimensions</p>
            <p><strong>Tutor Compatibility Score</strong>: {{ $fmt($breakdown['final']['score'] ?? data_get($compat, 'average_score', 0)) }}</p><br>
        @endif
    @endif

    @if(!empty($debug['tutor_performance']))
        @php $perf = $debug['tutor_performance']; @endphp
        <h3>Tutor Performance Evaluation</h3>
        <p>Mastery: {{ $fmt($perf['mastery'] ?? 0) }}</p>
        <p>Clarity: {{ $fmt($perf['clarity'] ?? 0) }}</p>
        <p>Responsiveness: {{ $fmt($perf['responsiveness'] ?? 0) }}</p>
        <p>Engagement: {{ $fmt($perf['engagement'] ?? 0) }}</p>
        <p>Preparedness: {{ $fmt($perf['preparedness'] ?? 0) }}</p>
        <p><strong>Tutor Performance Evaluation Score:</strong> {{ $fmt($perf['average_score'] ?? 0) }}</p><br>
    @endif
</x-layout>