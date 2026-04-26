<x-layout title="Survey Results">
    <h1>SURVEY RESULTS</h1>

    @if (!$ready)
        <p>Results are not yet available. Please ensure all three surveys have been submitted:
           Pre-Session Survey, Post-Session Survey, and Tutor Evaluation Survey.</p>
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

            echo '<p><strong>Rubric Scoring:</strong></p>';
            echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 90%;">';
            echo '<thead><tr>';
            echo '<th>Status Level</th>';
            echo '<th>Interpretation</th>';

            if ($hasAlgoOrRec) {
                echo '<th>Algorithm Interpretation</th>';
                echo '<th>Recommendation</th>';
            }

            echo '</tr></thead>';
            echo '<tbody><tr>';
            echo '<td>' . e($formatStatusLevel($rubric['status_level'] ?? null)) . '</td>';
            echo '<td>' . e($rubric['interpretation'] ?? 'N/A') . '</td>';

            if ($hasAlgoOrRec) {
                echo '<td>' . e($rubric['algorithm_interpretation'] ?? 'N/A') . '</td>';
                echo '<td>' . e($rubric['recommended_action'] ?? 'N/A') . '</td>';
            }

            echo '</tr></tbody>';
            echo '</table>';
            echo '<br>';
        };
        @endphp
        <hr><br>
        <h3>Pre-session Learning Gain</h3>
        <p>Prior Understanding: {{ round($getDimensionNormalized('pre_session', 'prior_understanding'), 2) }}</p>
        <p>Confidence: {{ round($getDimensionNormalized('pre_session', 'confidence'), 2) }}</p>
        <p>Application Readiness: {{ round($getDimensionNormalized('pre_session', 'application_readiness'), 2) }}</p>
        <p>Difficulty Awareness: {{ round($getDimensionNormalized('pre_session', 'difficulty_awareness'), 2) }}</p>
        <p><strong>Pre-session SRLG Score:</strong> {{ round($metrics['pre_session_avg'], 2) }}</p>
        <br>

        <h3>Post-session Learning Gain</h3>
        <p>Prior Understanding: {{ round($getDimensionNormalized('post_session', 'prior_understanding'), 2) }}</p>
        <p>Confidence: {{ round($getDimensionNormalized('post_session', 'confidence'), 2) }}</p>
        <p>Application Readiness: {{ round($getDimensionNormalized('post_session', 'application_readiness'), 2) }}</p>
        <p>Difficulty Awareness: {{ round($getDimensionNormalized('post_session', 'difficulty_awareness'), 2) }}</p>
        <p><strong>Post-session SRLG Score:</strong> {{ round($metrics['post_session_avg'], 2) }}</p>
        <br><hr><br>

        <h3>Self-Reported Learning Gain (SRLG)</h3>
        <p><strong>Pre-Session Average:</strong> {{ round($metrics['pre_session_avg'], 2) }}</p>
        <p><strong>Post-Session Average:</strong> {{ round($metrics['post_session_avg'], 2) }}</p>
        <br>
        @php($renderRubric($metrics['rubric_results']['self_reported_learning_gain'] ?? null))

        <h3>Tutee Evaluation</h3>
        <p>Understanding: {{ round($getDimensionNormalized('tutee_evaluation', 'understanding'), 2) }}</p>
        <p>Participation: {{ round($getDimensionNormalized('tutee_evaluation', 'participation'), 2) }}</p>
        <p>Application: {{ round($getDimensionNormalized('tutee_evaluation', 'application'), 2) }}</p>
        <p>Effort: {{ round($getDimensionNormalized('tutee_evaluation', 'effort'), 2) }}</p>
        <p>Difficulty Indicators: {{ round($getDimensionNormalized('tutee_evaluation', 'difficulty_indicators'), 2) }}</p>
        <p><strong>Tutee Evaluation Score:</strong> {{ round($metrics['tutee_evaluation'], 2) }}</p>
        <br>
        @php($renderRubric($metrics['rubric_results']['tutee_evaluation'] ?? null))
        <br><hr><br>
        <h3>Perceived Learning Gain (PLG)</h3>
        <p><strong>Self reported Learning Gain:</strong> {{ round($metrics['self_reported_learning_gain'], 2) }}</p>
        <p><strong>Tutee Evaluation Score:</strong> {{ round($metrics['tutee_evaluation'], 2) }}</p>
        <p><strong>Perceived Learning Gain:</strong> {{ round($metrics['perceived_learning_gain'], 2) }}</p>
        <br>
        @php($renderRubric($metrics['rubric_results']['perceived_learning_gain'] ?? null))
        <br><hr><br>
        <h3>Tutee Satisfaction</h3>
        <p>Overall Experience: {{ round($getDimensionNormalized('tutee_satisfaction', 'overall_experience'), 2) }}</p>
        <p>Perceived Usefulness: {{ round($getDimensionNormalized('tutee_satisfaction', 'perceived_usefulness'), 2) }}</p>
        <p>Behavioral Intent: {{ round($getDimensionNormalized('tutee_satisfaction', 'behavioral_intent'), 2) }}</p>
        <p><strong>Tutee Satisfaction Score:</strong> {{ round($metrics['tutee_satisfaction'], 2) }}</p>
        <br>
        @php($renderRubric($metrics['rubric_results']['tutee_satisfaction'] ?? null))
        <br><hr><br>
        <h3>Tutor Compatibility</h3>
        @foreach($compatibilityByCategory as $category => $rows)
            <p><strong>{{ $category }}</strong></p>
            @foreach($rows as $row)
                <p>{{ $row['dimension_name'] }}: {{ round($row['normalized_score'], 2) }}</p>
            @endforeach
            <br>
        @endforeach
       

        @if(!empty($compatibilityBreakdown) && !empty($compatibilityBreakdown['dimensions']))
            <h3>Tutor Compatibility Computation (Fallback Logic)</h3>

            @foreach($compatibilityBreakdown['dimensions'] as $dimensionKey => $dim)
                <p><strong>{{ strtoupper($dimensionKey) }}</strong></p>

                @foreach(($dim['options'] ?? []) as $opt)
                    <p>
                        <strong>{{ $opt['option'] }}</strong><br>
                        Weight: {{ rtrim(rtrim(number_format((float) ($opt['weight'] ?? 0), 2, '.', ''), '0'), '.') }}<br>
                        Tutor Score: {{ round((float) ($opt['tutor_score'] ?? 0), 2) }}<br>
                        Contribution (if used): {{ round((float) ($opt['contribution_if_used'] ?? 0), 2) }}<br>
                        @if(!empty($opt['used']))
                            <strong>USED (fallback logic)</strong>
                        @endif
                    </p>
                @endforeach

                <p><strong>Selected Score for {{ $dimensionKey }}: {{ round((float) ($dim['selected_score'] ?? 0), 2) }}</strong></p>
                <br>
            @endforeach

            @php($final = $compatibilityBreakdown['final'] ?? [])
            <p><strong>FINAL COMPUTATION</strong></p>
            <p>Total Score (sum of selected per dimension): {{ round((float) ($final['total_score'] ?? 0), 2) }}</p>
            <p>Number of Dimensions: {{ (int) ($final['dimension_count'] ?? 0) }}</p>
            <p>Tutor Compatibility Score = Total Score / No. of Dimensions</p>
            <p><strong>Tutor Compatibility Score</strong>: {{ round((float) ($final['score'] ?? 0), 2) }}</p>
            <br>
            @php($renderRubric($metrics['rubric_results']['tutor_compatability'] ?? ($metrics['rubric_results']['tutor_compatibility'] ?? null)))
            

        @endif
        <br><hr><br>
        <h3>Tutor Performance Evaluation</h3>
        <p>Mastery: {{ round($getDimensionNormalized('tutor_performance', 'mastery'), 2) }}</p>
        <p>Clarity: {{ round($getDimensionNormalized('tutor_performance', 'clarity'), 2) }}</p>
        <p>Responsiveness: {{ round($getDimensionNormalized('tutor_performance', 'responsiveness'), 2) }}</p>
        <p>Engagement: {{ round($getDimensionNormalized('tutor_performance', 'engagement'), 2) }}</p>
        <p>Preparedness: {{ round($getDimensionNormalized('tutor_performance', 'preparedness'), 2) }}</p>
        <p><strong>Tutor Performance Evaluation Score:</strong> {{ round($metrics['tutor_evaluation'], 2) }}</p>
        <br>
        @php($renderRubric($metrics['rubric_results']['tutor_evaluation'] ?? null))
        <br><hr><br>
        <h3>Total Match Effectiveness Score (TMES)</h3>
        <p><strong>TMES:</strong> {{ round($metrics['total_match_effectiveness_score'], 2) }}</p>
        <br>
        @php($renderRubric($metrics['rubric_results']['total_match_effectiveness_score'] ?? null))
    @endif
</x-layout>