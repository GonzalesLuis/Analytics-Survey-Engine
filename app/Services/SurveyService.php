<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\Metric;
use App\Models\Dimension;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\DimensionScore;
use App\Models\MetricResult;
use App\Models\RubricResult;
use App\Models\Rubric;
use App\Models\TutoringSession;

/**
 * Central domain service for the survey workflow.
 *
 * Responsibilities:
 * - Load survey questions from the database in the structure expected by controllers/views
 * - Persist survey responses and derived dimension scores
 * - Compute per-survey aggregates and cross-survey metrics (SRLG, PLG, TMES)
 * - Match computed metric scores to rubric interpretations and persist results
 *
 * Notes:
 * - This class is intentionally "fat": it keeps the full pipeline in one place so controllers stay thin.
 * - It uses Eloquent for reads/writes and relies on session state (via controllers) for orchestration.
 */
class SurveyService
{
    /**
     * Static test preferences (primary=1.0, secondary=0.5).
     *
     * NOTE: Eventually these will be retrieved from a DB table (not implemented yet).
     *
     * @return array<string, array<string, float>>
     */
    private function getStaticTuteePreferences(): array
    {
        return [
            'guidance' => [
                'high_guidance' => 1.0,
                'moderate_guidance' => 0.5,
            ],
            'engagement' => [
                'dialogue_oriented' => 1.0,
                'practice_oriented' => 0.5,
            ],
            'language' => [
                'english' => 1.0,
            ],
        ];
    }

    /**
     * Build the tutor compatibility evaluation array
     * Values come from the computed tutor compatibility option/dimension scores (normalized to 0-1).
     *
     * @param array<string, array<string, float>> $tcNormalized Category => option => score
     * @return array<string, array<string, float>>
     */
    private function buildTutorCompatibilityEvaluation(array $tcNormalized): array
    {
        $evaluation = [
            'guidance' => [
                'high_guidance' => 0.0,
                'moderate_guidance' => 0.0,
                'low_guidance' => 0.0,
            ],
            'engagement' => [
                'dialogue_oriented' => 0.0,
                'practice_oriented' => 0.0,
                'explanation_oriented' => 0.0,
            ],
            'language' => [
                'english' => 0.0,
                'tagalog' => 0.0,
            ],
        ];

        foreach ($tcNormalized as $category => $options) {
            $cat = strtolower((string) $category);

            $bucket = null;
            if (str_contains($cat, 'guidance')) {
                $bucket = 'guidance';
            } elseif (str_contains($cat, 'engagement')) {
                $bucket = 'engagement';
            } elseif (str_contains($cat, 'language')) {
                $bucket = 'language';
            }

            // If category labels are unexpected, skip rather than mis-assign.
            if (!$bucket) {
                continue;
            }

            foreach ($options as $option => $score) {
                if (array_key_exists($option, $evaluation[$bucket])) {
                    $evaluation[$bucket][$option] = (float) $score;
                }
            }
        }

        return $evaluation;
    }

    /**
     * computeCompatibilityScore():
     * - Sort preferences so primary is checked first (arsort)
     * - Select the first option whose tutor score > 0
     * - Selected score is the tutor score (not weighted)
     * - Final tutor compatibility score is the average of selected scores across dimensions
     *
     * @param array<string, array<string, float>> $tuteePreferences
     * @param array<string, array<string, float>> $tutorCompatibilityEvaluation
     * @return array{score: float, breakdown: array<string, mixed>}
     */
    public function computeCompatibilityScore(
        array $tuteePreferences,
        array $tutorCompatibilityEvaluation
    ): array {
        // Accumulate one chosen score per dimension, then average at the end.
        $totalScore = 0.0;
        $dimensionCount = 0;

        $breakdown = [
            'dimensions' => [],
            'final' => [
                'total_score' => 0.0,
                'dimension_count' => 0,
                'score' => 0.0,
            ],
        ];

        foreach ($tuteePreferences as $dimension => $options) {
            // Sort primary option first.
            arsort($options);

            $selectedOption = null;
            $selectedScore = 0.0;

            $rows = [];
            foreach ($options as $option => $weight) {
                $tutorScore = (float) ($tutorCompatibilityEvaluation[$dimension][$option] ?? 0);
                $contribution = (float) $weight * $tutorScore;

                $used = false;
                // First option with a non-zero tutor score is selected for the dimension.
                if ($selectedOption === null && $tutorScore > 0) {
                    $selectedOption = $option;
                    $selectedScore = $tutorScore;
                    $used = true;
                }

                $rows[] = [
                    'option' => $option,
                    'weight' => (float) $weight,
                    'tutor_score' => (float) $tutorScore,
                    'contribution_if_used' => (float) $contribution,
                    'used' => $used,
                ];
            }

            $breakdown['dimensions'][$dimension] = [
                'dimension' => $dimension,
                'options' => $rows,
                'selected_option' => $selectedOption,
                'selected_score' => (float) $selectedScore,
            ];

            $totalScore += $selectedScore;
            $dimensionCount++;
        }

        $finalScore = $dimensionCount > 0 ? $totalScore / $dimensionCount : 0.0;

        $breakdown['final'] = [
            'total_score' => (float) $totalScore,
            'dimension_count' => (int) $dimensionCount,
            'score' => (float) $finalScore,
        ];

        return [
            'score' => (float) $finalScore,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Compute the average of a list of numeric scores.
     *
     * Returns 0 when the input is empty to avoid division-by-zero.
     *
     * @param array<int, float|int> $scores
     */
    public function getAverage(array $scores): float
    {
        return count($scores) ? array_sum($scores) / count($scores) : 0;
    }

    /**
     * Normalize a 1-5 Likert average into a 0-1 scale.
     *
     * Formula: (avg - 1) / 4
     */
    public function normalize(float $avg): float
    {
        return ($avg - 1) / 4;
    }

    /**
     * Reverse-score a 1-5 Likert item.
     *
     * Ex. 1 -> 5, 2 -> 4, 3 -> 3.
     */
    public function reverseScore(int $score): int
    {
        return 6 - $score;
    }

    /**
     * Return the most recent "ongoing" tutoring session.
     *
     * Controllers use this to decide whether to show "start session", "end session",
     * and which surveys are allowed at a given point in time.
     */
    public function getCurrentTutoringSession(int $userId): ?TutoringSession
    {
        return TutoringSession::query()
            ->where('status', 'ongoing')
            ->orderByDesc('tutoring_session_id')
            ->first();
    }

    /**
     * Create a new tutoring session in an "ongoing" state.
     *
     * This is the entry point for the workflow; survey submissions are later tied
     * to the created `tutoring_session_id`.
     */
    public function startTutoringSession(): TutoringSession
    {
        return TutoringSession::create([
            'assignment_id' => null,
            'session_start' => now(),
            'session_end' => null,
            'evaluated_at' => null,
            'status' => 'ongoing',
        ]);
    }

    /**
     * Mark a tutoring session as ended (writes the first end timestamp only).
     *
     * The extra `whereNull('session_end')` makes this idempotent if called twice.
     */
    public function endTutoringSession(int $tutoringSessionId): void
    {
        TutoringSession::where('tutoring_session_id', $tutoringSessionId)
            ->whereNull('session_end')
            ->update(['session_end' => now()]);
    }

    /**
     * Check whether a user has already submitted a specific survey for a session.
     *
     * Joins `surveys` to translate a human-readable survey name into the survey id
     * stored on `responses`.
     */
    public function hasSubmittedSurvey(int $tutoringSessionId, int $userId, string $surveyName): bool
    {
        return Response::query()
            ->join('surveys', 'surveys.survey_id', '=', 'responses.survey_id')
            ->where('responses.tutoring_session_id', $tutoringSessionId)
            ->where('responses.user_id', $userId)
            ->where('surveys.name', $surveyName)
            ->exists();
    }

    /**
     * Mark a session as evaluated/completed once all required surveys exist.
     *
     * @return bool True when the session transitioned to "completed"; false when not all surveys are submitted yet.
     */
    public function markSessionEvaluatedIfComplete(int $tutoringSessionId, int $userId): bool
    {
        $allSubmitted = $this->hasSubmittedSurvey($tutoringSessionId, $userId, 'pre_session')
            && $this->hasSubmittedSurvey($tutoringSessionId, $userId, 'post_session')
            && $this->hasSubmittedSurvey($tutoringSessionId, $userId, 'tutee_evaluation');

        if (!$allSubmitted) {
            return false;
        }

        TutoringSession::where('tutoring_session_id', $tutoringSessionId)
            ->update([
                'evaluated_at' => now(),
                'status' => 'completed',
            ]);

        return true;
    }

    /**
     * Pre-session survey can only be taken while the session is ongoing.
     */
    public function canAccessPreSessionSurvey(?TutoringSession $session, int $userId): bool
    {
        if (!$session || !is_null($session->session_end)) {
            return false;
        }

        return !$this->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'pre_session');
    }

    /**
     * Post-session survey can only be taken after the session has ended.
     */
    public function canAccessPostSessionSurvey(?TutoringSession $session, int $userId): bool
    {
        if (!$session || is_null($session->session_end)) {
            return false;
        }

        return !$this->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'post_session');
    }

    /**
     * Tutee evaluation can only be taken after the session has ended.
     */
    public function canAccessTuteeEvaluationSurvey(?TutoringSession $session, int $userId): bool
    {
        if (!$session || is_null($session->session_end)) {
            return false;
        }

        return !$this->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'tutee_evaluation');
    }

    /**
     * Builds small view-model describing which actions/surveys should be available.
     *
     * @return array{can_start: bool, can_end: bool, show_pre: bool, show_post: bool, show_eval: bool, status_text: string}
     */
    public function getSessionProgress(?TutoringSession $session, int $userId): array
    {
        if (!$session) {
            return [
                'can_start' => true,
                'can_end' => false,
                'show_pre' => false,
                'show_post' => false,
                'show_eval' => false,
                'status_text' => 'Start tutoring session',
            ];
        }

        $preDone = $this->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'pre_session');
        $postDone = $this->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'post_session');
        $evalDone = $this->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'tutee_evaluation');
        $ended = !is_null($session->session_end);
        $completed = $session->status === 'completed';

        if ($completed) {
            return [
                'can_start' => true,
                'can_end' => false,
                'show_pre' => false,
                'show_post' => false,
                'show_eval' => false,
                'status_text' => 'Start a session again',
            ];
        }

        return [
            'can_start' => false,
            'can_end' => !$ended && $preDone,
            'show_pre' => !$preDone && !$ended,
            'show_post' => $ended && !$postDone,
            'show_eval' => $ended && !$evalDone,
            'status_text' => $ended
                ? 'Session ended - waiting for surveys'
                : ($preDone ? 'End session' : 'Session started - complete pre-session survey'),
        ];
    }

    /**
     * Load questions for a survey and flatten them into a simple array.
     *
     * Each item includes its dimension/category metadata because controllers and views
     * need those labels to group/render questions.
     *
     * @return array<int, array{question_id: int, dimension: string, category: string, question: string, is_reverse: bool, score: int|null}>
     */
    private function getSurveyQuestions(string $surveyName): array
    {
        $survey = Survey::where('name', $surveyName)
            ->with(['dimensions.questions'])
            ->firstOrFail();

        $questions = [];
        foreach ($survey->dimensions as $dimension) {
            foreach ($dimension->questions as $question) {
                $questions[] = [
                    'question_id' => $question->question_id,
                    'dimension'   => $dimension->name,
                    'category'    => $dimension->category,
                    'question'    => $question->question_text,
                    'is_reverse'  => $question->is_reverse,
                    'score'       => null,
                ];
            }
        }

        return $questions;
    }

    /**
     * Data for the pre-session survey screen.
     */
    public function getPreSessionData(): array
    {
        return $this->getSurveyQuestions('pre_session');
    }

    /**
     * Data for the post-session survey screen.
     */
    public function getPostSessionData(): array
    {
        return $this->getSurveyQuestions('post_session');
    }

    /**
     * Data for the tutee satisfaction survey screen.
     */
    public function getTuteeSatisfactionData(): array
    {
        return $this->getSurveyQuestions('tutee_satisfaction');
    }

    /**
     * Data for the tutor compatibility survey screen, grouped by category.
     *
     * Some controllers/views expect compatibility questions nested by `category`,
     * so we reshape the flat list from `getSurveyQuestions()`.
     *
     * @return array<string, array<int, array{question_id: int, dimension: string, category: string, question: string, is_reverse: bool, score: int|null}>>
     */
    public function getTutorCompatibilityData(): array
    {
        $questions = $this->getSurveyQuestions('tutor_compatibility');

        // Rebuild the nested-by-category structure the controllers expect.
        $grouped = [];
        foreach ($questions as $q) {
            $grouped[$q['category']][] = $q;
        }

        return $grouped;
    }

    /**
     * Data for the tutor performance survey screen.
     */
    public function getTutorPerformanceData(): array
    {
        return $this->getSurveyQuestions('tutor_performance');
    }

    /**
     * Data for the tutee evaluation survey screen.
     */
    public function getTuteeEvaluationData(): array
    {
        return $this->getSurveyQuestions('tutee_evaluation');
    }

    /**
     * Persist a survey response and its selected answers.
     *
     * Expected input is the "questions array" built by `getSurveyQuestions()` where each
     * element has a `question_id` and a `score` set by the controller from form input.
     */
    public function saveResponse(string $surveyName, array $questions, int $tutoringSessionId, int $userId): void
    {
        $survey = Survey::where('name', $surveyName)->firstOrFail();

        $response = Response::create([
            'tutoring_session_id' => $tutoringSessionId,
            'user_id'             => $userId,
            'survey_id'           => $survey->survey_id,
            'created_at'          => now(),
        ]);

        foreach ($questions as $q) {
            if (!is_null($q['score'])) {
                ResponseAnswer::create([
                    'response_id' => $response->response_id,
                    'question_id' => $q['question_id'],
                    'score'       => $q['score'],
                ]);
            }
        }
    }

    /**
     * Alias kept for readability: saves a "flat" list of questions (most surveys).
     */
    public function saveFlatResponses(string $surveyName, array $questions, int $tutoringSessionId, int $userId): void
    {
        $this->saveResponse($surveyName, $questions, $tutoringSessionId, $userId);
    }

    /**
     * Save a grouped question structure by flattening it first.
     *
     * Compatibility questions are grouped by category at the UI layer, but the DB layer
     * stores answers the same way regardless of grouping.
     *
     * @param array<string, array<int, array{question_id: int, score: int|null}>> $groupedQuestions
     */
    public function saveGroupedResponses(string $surveyName, array $groupedQuestions, int $tutoringSessionId, int $userId): void
    {
        $flat = [];
        foreach ($groupedQuestions as $group) {
            foreach ($group as $q) {
                $flat[] = $q;
            }
        }
        $this->saveResponse($surveyName, $flat, $tutoringSessionId, $userId);
    }

    /**
     * Translate a (survey name + dimension name) pair into a database dimension id.
     *
     * Dimension ids are scoped by survey, so we need both pieces of information.
     */
    private function getDimensionId(string $surveyName, string $dimensionName): int
    {
        $survey = Survey::where('name', $surveyName)->firstOrFail();

        return Dimension::where('survey_id', $survey->survey_id)
            ->where('name', $dimensionName)
            ->firstOrFail()
            ->dimension_id;
    }

    /**
     * Persist a single dimension score row (average + normalized score).
     *
     * Uses `updateOrCreate` so repeated submissions don't duplicate data.
     */
    private function saveDimensionScore(
        int $tutoringSessionId,
        int $userId,
        int $dimensionId,
        float $avg,
        float $normalized
    ): void
    {
        DimensionScore::updateOrCreate(
            [
                'tutoring_session_id' => $tutoringSessionId,
                'user_id'             => $userId,
                'dimension_id'        => $dimensionId,
            ],
            [
                'avg_score'        => $avg,
                'normalized_score' => $normalized,
            ]
        );
    }

    /**
     * Compute and persist pre-session dimension scores.
     *
     * Reverse-scored items are converted before aggregating.
     */
    public function savePreSessionDimensionScores(array $data, int $tutoringSessionId, int $userId): void
    {
        $groups = [];

        foreach ($data as $q) {
            $score = $q['is_reverse'] ? $this->reverseScore($q['score']) : $q['score'];
            $groups[$q['dimension']][] = $score;
        }

        foreach ($groups as $dimensionName => $scores) {
            $avg = $this->getAverage($scores);
            $this->saveDimensionScore(
                $tutoringSessionId,
                $userId,
                $this->getDimensionId('pre_session', $dimensionName),
                $avg,
                $this->normalize($avg)
            );
        }
    }

    /**
     * Compute and persist all post-session-related dimension scores.
     *
     * This covers multiple survey forms captured around the post-session stage:
     * - post_session
     * - tutee_satisfaction
     * - tutor_compatibility (category-grouped on the UI)
     * - tutor_performance
     */
    public function savePostSessionDimensionScores(
        array $postSessionData,
        array $tuteeSatisfactionData,
        array $tutorCompatibilityData,
        array $tutorPerformanceData,
        int $tutoringSessionId,
        int $userId
    ): void {
        // 1) Save post-session survey dimension scores.
        $postGroups = [];
        foreach ($postSessionData as $q) {
            $score = $q['is_reverse'] ? $this->reverseScore($q['score']) : $q['score'];
            $postGroups[$q['dimension']][] = $score;
        }
        foreach ($postGroups as $dimensionName => $scores) {
            $avg = $this->getAverage($scores);
            $this->saveDimensionScore(
                $tutoringSessionId,
                $userId,
                $this->getDimensionId('post_session', $dimensionName),
                $avg,
                $this->normalize($avg)
            );
        }

        // 2) Save tutee satisfaction dimension scores.
        $satisfactionGroups = [];
        foreach ($tuteeSatisfactionData as $q) {
            $satisfactionGroups[$q['dimension']][] = $q['score'];
        }
        foreach ($satisfactionGroups as $dimensionName => $scores) {
            $avg = $this->getAverage($scores);
            $this->saveDimensionScore(
                $tutoringSessionId,
                $userId,
                $this->getDimensionId('tutee_satisfaction', $dimensionName),
                $avg,
                $this->normalize($avg)
            );
        }

        // 3) Save tutor compatibility dimension scores (input arrives grouped by category).
        $compatibilityGroups = [];
        foreach ($tutorCompatibilityData as $category => $items) {
            foreach ($items as $q) {
                $compatibilityGroups[$q['dimension']][] = $q['score'];
            }
        }
        foreach ($compatibilityGroups as $dimensionName => $scores) {
            $avg = $this->getAverage($scores);
            $this->saveDimensionScore(
                $tutoringSessionId,
                $userId,
                $this->getDimensionId('tutor_compatibility', $dimensionName),
                $avg,
                $this->normalize($avg)
            );
        }

        // 4) Save tutor performance dimension scores.
        $performanceGroups = [];
        foreach ($tutorPerformanceData as $q) {
            $performanceGroups[$q['dimension']][] = $q['score'];
        }
        foreach ($performanceGroups as $dimensionName => $scores) {
            $avg = $this->getAverage($scores);
            $this->saveDimensionScore(
                $tutoringSessionId,
                $userId,
                $this->getDimensionId('tutor_performance', $dimensionName),
                $avg,
                $this->normalize($avg)
            );
        }
    }

    /**
     * Compute and persist tutee evaluation dimension scores.
     */
    public function saveTuteeEvaluationDimensionScores(array $data, int $tutoringSessionId, int $userId): void
    {
        $groups = [];

        foreach ($data as $q) {
            $score = $q['is_reverse'] ? $this->reverseScore($q['score']) : $q['score'];
            $groups[$q['dimension']][] = $score;
        }

        foreach ($groups as $dimensionName => $scores) {
            $avg = $this->getAverage($scores);
            $this->saveDimensionScore(
                $tutoringSessionId,
                $userId,
                $this->getDimensionId('tutee_evaluation', $dimensionName),
                $avg,
                $this->normalize($avg)
            );
        }
    }

    /**
     * Compute Self-Reported Learning Gain (SRLG).
     *
     * Uses normalized offset so the result lands in a 0-1 range.
     */
    public function computeSRLG(float $pre_avg, float $post_avg): float
    {
        return (($post_avg - $pre_avg) + 4) / 8;
    }

    /**
     * Compute Perceived Learning Gain (PLG) using SRLG and tutee evaluation.
     */
    public function computePLG(float $srlg, float $tutor_eval): float
    {
        return (0.6 * $srlg) + (0.4 * $tutor_eval);
    }

    /**
     * Compute Total Match Effectiveness Score (TMES).
     */
    public function computeTMES(float $srlg, float $satisfaction, float $compatibility): float
    {
        return (0.4 * $srlg) + (0.3 * $satisfaction) + (0.3 * $compatibility);
    }

    /**
     * Return acceptable metric name aliases used in the database.
     *
     *
     * @return array<int, string>
     */
    private function rubricMetricAliases(string $metricName): array
    {
        return match ($metricName) {
            'tutor_compatibility' => ['tutor_compatibility', 'tutor_compatability'],
            'tutor_compatability' => ['tutor_compatability', 'tutor_compatibility'],
            default => [$metricName],
        };
    }

    /**
     * Find the matching rubric row for a metric score.
     *
     * Rubrics are stored as score ranges. We select the row that contains the score.
     *
     * @return array{status_level: string, interpretation: string, algorithm_interpretation: string, recommended_action: string}|null
     */
    private function matchRubric(string $metricName, float $score): ?array
    {
        $metricNames = $this->rubricMetricAliases($metricName);

        $rubric = Rubric::query()
            ->whereIn('metric_id', function ($q) use ($metricNames) {
                $q->select('metric_id')->from('metrics')->whereIn('name', $metricNames);
            })
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderByDesc('min_score')
            ->first();

        if (!$rubric) return null;

        return [
            'status_level'            => $rubric->status_level,
            'interpretation'          => $rubric->interpretation,
            'algorithm_interpretation'=> $rubric->algo_interpretation,
            'recommended_action'      => $rubric->recommended_action,
        ];
    }

    /**
     * Persist a metric result row and return the model instance.
     *
     * Returns null when the metric name can't be resolved to a `metrics` row.
     */
    private function saveMetricResult(
        string $metricName,
        float $score,
        int $tutoringSessionId,
        int $userId
    ): ?MetricResult {
        $metric = Metric::whereIn('name', $this->rubricMetricAliases($metricName))->first();
        if (!$metric) {
            return null;
        }

        $metricResult = MetricResult::updateOrCreate(
            [
                'tutoring_session_id' => $tutoringSessionId,
                'user_id'             => $userId,
                'metric_id'           => $metric->metric_id,
            ],
            [
                'metric_score' => $score,
            ]
        );

        return $metricResult;
    }

    /**
     * Compute the full metric set for a tutoring session, persist results, and attach rubric interpretations.
     *
     * @return array<string, mixed>
     */
    public function computeMetrics(
        float $pre_avg,
        float $post_avg,
        float $tutee_eval,
        float $satisfaction,
        float $compatibility,
        float $tutor_eval,
        int   $tutoringSessionId,
        int   $userId
    ): array {
        $srlg                   = $this->computeSRLG($pre_avg, $post_avg);
        $perceived_learning_gain = $this->computePLG($srlg, $tutee_eval);
        $tmes                   = $this->computeTMES($srlg, $satisfaction, $compatibility);

        $metricScores = [
            'pre_session_average'            => $pre_avg,
            'post_session_average'           => $post_avg,
            'self_reported_learning_gain'     => $srlg,
            'tutee_evaluation'                => $tutee_eval,
            'perceived_learning_gain'         => $perceived_learning_gain,
            'tutee_satisfaction'              => $satisfaction,
            'tutor_compatability'             => $compatibility,
            'tutor_evaluation'                => $tutor_eval,
            'total_match_effectiveness_score' => $tmes,
        ];

        // Save each metric and (if available) pair it with its rubric interpretation.
        $rubricResults = [];

        foreach ($metricScores as $metricName => $score) {
            $metricResult = $this->saveMetricResult(
                $metricName,
                $score,
                $tutoringSessionId,
                $userId
            );

            $rubric = $this->matchRubric($metricName, $score);

            if ($rubric && $metricResult) {
                RubricResult::updateOrCreate(
                    ['metric_result_id' => $metricResult->metric_result_id],
                    [
                        'tutoring_session_id'      => $tutoringSessionId,
                        'user_id'                  => $userId,
                        'status_level'             => $rubric['status_level'],
                        'interpretation'           => $rubric['interpretation'],
                        'algorithm_interpretation' => $rubric['algorithm_interpretation'],
                        'recommended_action'       => $rubric['recommended_action'],
                    ]
                );
            }

            $rubricResults[$metricName] = $rubric;
        }

        return [
            'pre_session_avg'         => $pre_avg,
            'post_session_avg'        => $post_avg,
            'self_reported_learning_gain' => $srlg,
            'srlg'                    => $srlg, 
            'tutee_evaluation'        => $tutee_eval,
            'perceived_learning_gain' => $perceived_learning_gain,
            'tutee_satisfaction'      => $satisfaction,
            'tutor_compatability'     => $compatibility,
            'tutor_evaluation'        => $tutor_eval,
            'satisfaction'            => $satisfaction, 
            'compatibility'           => $compatibility, 
            'tmes'                    => $tmes,
            'total_match_effectiveness_score' => $tmes,
            'rubric_results'          => $rubricResults,
        ];
    }

    /**
     * Return stored dimension scores for a tutoring session, with survey/dimension labels.
     *
     * Used to render a breakdown table next to metric results.
     *
     * @return array<int, array{survey_name: string, category: string, dimension_name: string, avg_score: float, normalized_score: float}>
     */
    public function getDimensionScoresForSession(int $tutoringSessionId, int $userId): array
    {
        $rows = DimensionScore::query()
            ->join('dimensions', 'dimensions.dimension_id', '=', 'dimension_scores.dimension_id')
            ->join('surveys', 'surveys.survey_id', '=', 'dimensions.survey_id')
            ->where('dimension_scores.tutoring_session_id', $tutoringSessionId)
            ->where('dimension_scores.user_id', $userId)
            ->orderBy('surveys.name')
            ->orderBy('dimensions.category')
            ->orderBy('dimensions.name')
            ->get([
                'surveys.name as survey_name',
                'dimensions.category',
                'dimensions.name as dimension_name',
                'dimension_scores.avg_score',
                'dimension_scores.normalized_score',
            ]);

        return $rows->map(fn ($row) => [
            'survey_name' => $row->survey_name,
            'category' => $row->category,
            'dimension_name' => $row->dimension_name,
            'avg_score' => (float) $row->avg_score,
            'normalized_score' => (float) $row->normalized_score,
        ])->toArray();
    }

    /**
     * Compute normalized dimension scores for the pre-session survey payload.
     *
     * Input is the per-question structure built by `getSurveyQuestions()` with `score` filled in.
     */
    public function computePreSession(array $data): array
    {
        $groups = [
            'prior_understanding'   => [],
            'confidence'            => [],
            'application_readiness' => [],
            'difficulty_awareness'  => [],
        ];
        $all = [];

        foreach ($data as $q) {
            $score = $q['is_reverse']
                ? $this->reverseScore($q['score'])
                : $q['score'];

            $groups[$q['dimension']][] = $score;
            $all[] = $score;
        }

        return [
            'prior_understanding'   => $this->normalize($this->getAverage($groups['prior_understanding'])),
            'confidence'            => $this->normalize($this->getAverage($groups['confidence'])),
            'application_readiness' => $this->normalize($this->getAverage($groups['application_readiness'])),
            'difficulty_awareness'  => $this->normalize($this->getAverage($groups['difficulty_awareness'])),
            'average_score'         => $this->getAverage($all),
        ];
    }

    /**
     * Compute normalized aggregates for all post-session stage surveys.
     *
     * Returns computed values only (for rendering in the UI) 
     * Does not write to the database.
     */
    public function computePostSession(
        array $post_session_data,
        array $tutee_satisfaction_data,
        array $tutor_compatibility_data,
        array $tutor_performance_data
    ): array {
        // Post-session survey (same dimension keys as pre-session).
        $ps_all = [];
        $ps     = [];
        foreach ($post_session_data as $q) {
            $score = $q['is_reverse'] ? $this->reverseScore($q['score']) : $q['score'];
            $ps[$q['dimension']][] = $score;
            $ps_all[]              = $score;
        }

        // Tutee satisfaction survey.
        $ts_all = [];
        $ts     = [];
        foreach ($tutee_satisfaction_data as $q) {
            $ts[$q['dimension']][] = $q['score'];
            $ts_all[]              = $q['score'];
        }

        // Tutor compatibility survey (grouped by category).
        $tc_all = [];
        $tc     = [];
        foreach ($tutor_compatibility_data as $category => $items) {
            foreach ($items as $q) {
                $tc[$category][$q['dimension']][] = $q['score'];
                $tc_all[]                         = $q['score'];
            }
        }
        $tc_normalized = [];
        foreach ($tc as $category => $dims) {
            foreach ($dims as $dim => $scores) {
                $tc_normalized[$category][$dim] = $this->normalize($this->getAverage($scores));
            }
        }

        // Convert normalized compatibility scores into the exact shape required by the matcher.
        $tutorCompatibilityEvaluation = $this->buildTutorCompatibilityEvaluation($tc_normalized);

        // Pull tutee preferences (still static for now).
        $tuteePreferences = $this->getStaticTuteePreferences();

        // Compute compatibility using priority-based matching, not a simple average of all options.
        $compatibilityComputed = $this->computeCompatibilityScore(
            $tuteePreferences,
            $tutorCompatibilityEvaluation
        );

        // Tutor performance survey.
        $tp_all = [];
        $tp     = [];
        foreach ($tutor_performance_data as $q) {
            $tp[$q['dimension']][] = $q['score'];
            $tp_all[]              = $q['score'];
        }

        return [
            'post_session' => [
                'prior_understanding'   => $this->normalize($this->getAverage($ps['prior_understanding'])),
                'confidence'            => $this->normalize($this->getAverage($ps['confidence'])),
                'application_readiness' => $this->normalize($this->getAverage($ps['application_readiness'])),
                'difficulty_awareness'  => $this->normalize($this->getAverage($ps['difficulty_awareness'])),
                'average_score'         => $this->getAverage($ps_all),
            ],
            'tutee_satisfaction' => [
                'overall_experience'   => $this->normalize($this->getAverage($ts['overall_experience'])),
                'perceived_usefulness' => $this->normalize($this->getAverage($ts['perceived_usefulness'])),
                'behavioral_intent'    => $this->normalize($this->getAverage($ts['behavioral_intent'])),
                'average_score'        => $this->normalize($this->getAverage($ts_all)),
            ],
            'tutor_compatibility' => $tc_normalized + [
                'average_score' => (float) $compatibilityComputed['score'],
                'tuteePreferences' => $tuteePreferences,
                'tutorCompatibilityEvaluation' => $tutorCompatibilityEvaluation,
                'breakdown' => $compatibilityComputed['breakdown'],
            ],
            'tutor_performance' => [
                'mastery'        => $this->normalize($this->getAverage($tp['mastery'])),
                'clarity'        => $this->normalize($this->getAverage($tp['clarity'])),
                'responsiveness' => $this->normalize($this->getAverage($tp['responsiveness'])),
                'engagement'     => $this->normalize($this->getAverage($tp['engagement'])),
                'preparedness'   => $this->normalize($this->getAverage($tp['preparedness'])),
                'average_score'  => $this->normalize($this->getAverage($tp_all)),
            ],
        ];
    }

    /**
     * Compute normalized dimension scores for the tutee evaluation survey payload.
     */
    public function computeTuteeEvaluation(array $data): array
    {
        $dims = [];
        $all  = [];

        foreach ($data as $q) {
            $score = $q['is_reverse'] ? $this->reverseScore($q['score']) : $q['score'];
            $dims[$q['dimension']][] = $score;
            $all[]                   = $score;
        }

        return [
            'understanding'         => $this->normalize($this->getAverage($dims['understanding'])),
            'participation'         => $this->normalize($this->getAverage($dims['participation'])),
            'application'           => $this->normalize($this->getAverage($dims['application'])),
            'effort'                => $this->normalize($this->getAverage($dims['effort'])),
            'difficulty_indicators' => $this->normalize($this->getAverage($dims['difficulty_indicators'])),
            'average_score'         => $this->normalize($this->getAverage($all)),
        ];
    }
}