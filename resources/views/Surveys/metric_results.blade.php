<x-layout title="Survey Results">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Survey Results</h1>

    @if ($tutoringSession)
        <div class="mt-6 space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p><span class="font-semibold">Current Session ID:</span> {{ $tutoringSession->tutoring_session_id }}</p>
            <p><span class="font-semibold">Status:</span> {{ ucfirst($tutoringSession->status) }}</p>
            <p><span class="font-semibold">Session Start:</span> {{ $tutoringSession->session_start ?? 'N/A' }}</p>
            <p><span class="font-semibold">Session End:</span> {{ $tutoringSession->session_end ?? 'N/A' }}</p>
            <p><span class="font-semibold">Evaluated At:</span> {{ $tutoringSession->evaluated_at ?? 'N/A' }}</p>
        </div>
    @endif

    @if (!$ready)
        <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Results are not yet available. Please ensure all three surveys have been submitted:
            Pre-Session Survey, Post-Session Survey, and Tutor Evaluation Survey.
        </p>
    @else
        @php
            $allDimensionScores = collect($dimensionScores ?? []);
            $dimensionLookup = $allDimensionScores
                ->groupBy('survey_name')
                ->map(fn ($rows) => collect($rows)->keyBy('dimension_name'));

            $compatibilityByCategory = $allDimensionScores
                ->where('survey_name', 'tutor_compatibility')
                ->groupBy(fn ($row) => strtoupper((string) ($row['category'] ?? 'OTHER')));

            $getDimensionNormalized = function (string $surveyName, string $dimensionName) use ($dimensionLookup): float {
                return (float) data_get($dimensionLookup, "{$surveyName}.{$dimensionName}.normalized_score", 0);
            };

            $formatStatusLevel = function (?string $status): string {
                if (!$status) {
                    return 'N/A';
                }

                return \Illuminate\Support\Str::of($status)
                    ->replace('_', ' ')
                    ->title()
                    ->toString();
            };

            $renderRubric = function (?array $rubric) use ($formatStatusLevel) {
            if (!$rubric) {
                echo '<p>Rubric scoring is not available for this metric.</p><br>';
                return;
            }

            $hasAlgoOrRec = !is_null($rubric['algorithm_interpretation'] ?? null) || !is_null($rubric['recommended_action'] ?? null);
            echo "<br>";
            echo '<p class="mt-2 text-base font-semibold text-slate-900">Rubric Scoring:</p>';
            echo '<div class="mt-2 overflow-x-auto rounded-lg border border-slate-200">';
            echo '<table class="min-w-full divide-y divide-slate-200 text-sm">';
            echo '<thead><tr class="bg-slate-100 text-slate-700">';
            echo '<th class="px-4 py-2 text-left font-semibold">Status Level</th>';
            echo '<th class="px-4 py-2 text-left font-semibold">Interpretation</th>';

            if ($hasAlgoOrRec) {
                echo '<th class="px-4 py-2 text-left font-semibold">Algorithm Interpretation</th>';
                echo '<th class="px-4 py-2 text-left font-semibold">Recommendation</th>';
            }

            echo '</tr></thead>';
            echo '<tbody><tr class="border-t border-slate-200 bg-white">';
            echo '<td class="px-4 py-2 text-slate-700">' . e($formatStatusLevel($rubric['status_level'] ?? null)) . '</td>';
            echo '<td class="px-4 py-2 text-slate-700">' . e($rubric['interpretation'] ?? 'N/A') . '</td>';

            if ($hasAlgoOrRec) {
                echo '<td class="px-4 py-2 text-slate-700">' . e($rubric['algorithm_interpretation'] ?? 'N/A') . '</td>';
                echo '<td class="px-4 py-2 text-slate-700">' . e($rubric['recommended_action'] ?? 'N/A') . '</td>';
            }

            echo '</tr></tbody>';
            echo '</table>';
            echo '</div>';
        };
        @endphp
        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Pre-session Learning Gain</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p>Prior Understanding: {{ round($getDimensionNormalized('pre_session', 'prior_understanding'), 2) }}</p>
                <p>Confidence: {{ round($getDimensionNormalized('pre_session', 'confidence'), 2) }}</p>
                <p>Application Readiness: {{ round($getDimensionNormalized('pre_session', 'application_readiness'), 2) }}</p>
                <p>Difficulty Awareness: {{ round($getDimensionNormalized('pre_session', 'difficulty_awareness'), 2) }}</p>
                <p class="font-semibold text-slate-900">Pre-session SRLG Score: {{ round($metrics['pre_session_avg'], 2) }}</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-xl font-semibold text-slate-900">Post-session Learning Gain</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p>Prior Understanding: {{ round($getDimensionNormalized('post_session', 'prior_understanding'), 2) }}</p>
                <p>Confidence: {{ round($getDimensionNormalized('post_session', 'confidence'), 2) }}</p>
                <p>Application Readiness: {{ round($getDimensionNormalized('post_session', 'application_readiness'), 2) }}</p>
                <p>Difficulty Awareness: {{ round($getDimensionNormalized('post_session', 'difficulty_awareness'), 2) }}</p>
                <p class="font-semibold text-slate-900">Post-session SRLG Score: {{ round($metrics['post_session_avg'], 2) }}</p>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Self-Reported Learning Gain (SRLG)</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p><span class="font-semibold text-slate-900">Pre-Session Average:</span> {{ round($metrics['pre_session_avg'], 2) }}</p>
                <p><span class="font-semibold text-slate-900">Post-Session Average:</span> {{ round($metrics['post_session_avg'], 2) }}</p>
            </div>
        </div>
        @php($renderRubric($metrics['rubric_results']['self_reported_learning_gain'] ?? null))
        <br>


        <div class="mt-6">
            <h3 class="text-xl font-semibold text-slate-900">Tutee Evaluation</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p>Understanding: {{ round($getDimensionNormalized('tutee_evaluation', 'understanding'), 2) }}</p>
                <p>Participation: {{ round($getDimensionNormalized('tutee_evaluation', 'participation'), 2) }}</p>
                <p>Application: {{ round($getDimensionNormalized('tutee_evaluation', 'application'), 2) }}</p>
                <p>Effort: {{ round($getDimensionNormalized('tutee_evaluation', 'effort'), 2) }}</p>
                <p>Difficulty Indicators: {{ round($getDimensionNormalized('tutee_evaluation', 'difficulty_indicators'), 2) }}</p>
                <p class="font-semibold text-slate-900">Tutee Evaluation Score: {{ round($metrics['tutee_evaluation'], 2) }}</p>
            </div>
        </div>
        
        @php($renderRubric($metrics['rubric_results']['tutee_evaluation'] ?? null))
        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Perceived Learning Gain (PLG)</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p><span class="font-semibold text-slate-900">Self reported Learning Gain:</span> {{ round($metrics['self_reported_learning_gain'], 2) }}</p>
                <p><span class="font-semibold text-slate-900">Tutee Evaluation Score:</span> {{ round($metrics['tutee_evaluation'], 2) }}</p>
                <p><span class="font-semibold text-slate-900">Perceived Learning Gain:</span> {{ round($metrics['perceived_learning_gain'], 2) }}</p>
            </div>
        </div>
        @php($renderRubric($metrics['rubric_results']['perceived_learning_gain'] ?? null))
        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Tutee Satisfaction</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p>Overall Experience: {{ round($getDimensionNormalized('tutee_satisfaction', 'overall_experience'), 2) }}</p>
                <p>Perceived Usefulness: {{ round($getDimensionNormalized('tutee_satisfaction', 'perceived_usefulness'), 2) }}</p>
                <p>Behavioral Intent: {{ round($getDimensionNormalized('tutee_satisfaction', 'behavioral_intent'), 2) }}</p>
                <p class="font-semibold text-slate-900">Tutee Satisfaction Score: {{ round($metrics['tutee_satisfaction'], 2) }}</p>
            </div>
        </div>
        @php($renderRubric($metrics['rubric_results']['tutee_satisfaction'] ?? null))
        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Tutor Compatibility</h3>
            <div class="mt-2 space-y-4 text-base text-slate-700">
                @foreach($compatibilityByCategory as $category => $rows)
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">{{ $category }}</p>
                        @foreach($rows as $row)
                            <p>{{ $row['dimension_name'] }}: {{ round($row['normalized_score'], 2) }}</p>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
       

        @if(!empty($compatibilityBreakdown) && !empty($compatibilityBreakdown['dimensions']))
            <div class="mt-6 border-t border-slate-200 pt-6">
                <h3 class="text-xl font-semibold text-slate-900">Tutor Compatibility Computation (Fallback Logic)</h3>
                <div class="mt-2 space-y-4 text-base text-slate-700">

                    @foreach($compatibilityBreakdown['dimensions'] as $dimensionKey => $dim)
                        <div class="space-y-1">
                            <p class="font-semibold text-slate-900">{{ strtoupper($dimensionKey) }}</p>

                            @foreach(($dim['options'] ?? []) as $opt)
                                <div class="space-y-1 rounded-md border border-slate-200 bg-slate-50 p-3">
                                    <p><span class="font-semibold text-slate-900">{{ $opt['option'] }}</span></p>
                                    <p>Weight: {{ rtrim(rtrim(number_format((float) ($opt['weight'] ?? 0), 2, '.', ''), '0'), '.') }}</p>
                                    <p>Tutor Score: {{ round((float) ($opt['tutor_score'] ?? 0), 2) }}</p>
                                    <p>Contribution: {{ round((float) ($opt['contribution_if_used'] ?? 0), 2) }}</p>
                                    @if(!empty($opt['used']))
                                        <p class="font-semibold text-slate-900">USED</p>
                                    @endif
                                </div>
                            @endforeach

                            <p class="font-semibold text-slate-900">Selected Score for {{ $dimensionKey }}: {{ round((float) ($dim['selected_score'] ?? 0), 2) }}</p>
                        </div>
                    @endforeach

                    @php($final = $compatibilityBreakdown['final'] ?? [])
                    <div class="space-y-1">
                        <br>
                        <p class="font-semibold text-slate-900">FINAL COMPUTATION</p>
                        <p>Total Score (sum of selected per dimension): {{ round((float) ($final['total_score'] ?? 0), 2) }}</p>
                        <p>Number of Dimensions: {{ (int) ($final['dimension_count'] ?? 0) }}</p>
                        <p>Tutor Compatibility Score = Total Score / No. of Dimensions</p>
                        <p class="font-semibold text-slate-900">Tutor Compatibility Score: {{ round((float) ($final['score'] ?? 0), 2) }}</p>
                    </div>
                </div>
            </div>
            @php($renderRubric($metrics['rubric_results']['tutor_compatability'] ?? ($metrics['rubric_results']['tutor_compatibility'] ?? null)))
            

        @endif
        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Tutor Performance Evaluation</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p>Mastery: {{ round($getDimensionNormalized('tutor_performance', 'mastery'), 2) }}</p>
                <p>Clarity: {{ round($getDimensionNormalized('tutor_performance', 'clarity'), 2) }}</p>
                <p>Responsiveness: {{ round($getDimensionNormalized('tutor_performance', 'responsiveness'), 2) }}</p>
                <p>Engagement: {{ round($getDimensionNormalized('tutor_performance', 'engagement'), 2) }}</p>
                <p>Preparedness: {{ round($getDimensionNormalized('tutor_performance', 'preparedness'), 2) }}</p>
                <p class="font-semibold text-slate-900">Tutor Performance Evaluation Score: {{ round($metrics['tutor_evaluation'], 2) }}</p>
            </div>
        </div>
        @php($renderRubric($metrics['rubric_results']['tutor_evaluation'] ?? null))
        <div class="mt-6 border-t border-slate-200 pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Total Match Effectiveness Score (TMES)</h3>
            <div class="mt-2 space-y-1 text-base text-slate-700">
                <p class="font-semibold text-slate-900">TMES: {{ round($metrics['total_match_effectiveness_score'], 2) }}</p>
            </div>
        </div>
        @php($renderRubric($metrics['rubric_results']['total_match_effectiveness_score'] ?? null))
    @endif
</x-layout>