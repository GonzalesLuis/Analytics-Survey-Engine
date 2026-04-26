<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Metric;
use App\Models\Rubric;

/**
 * Seeds metrics and rubric ranges used by the results page.
 *
 * Rubrics provide the interpretation text for each metric score range.
 */
class MetricRubricSeeder extends Seeder
{
    /**
     * Insert metrics, then insert each metric's rubric rows.
     */
    public function run(): void
    {
        $rubrics = [
            'perceived_learning_gain' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'high_progress', 'interpretation' => 'The tutee demonstrates significant improvement in understanding, problem-solving ability, and confidence in the topic.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.50, 'max' => 0.79, 'status_level' => 'moderate_progress', 'interpretation' => 'The tutee shows noticeable improvement but still has some gaps in understanding or application.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.0, 'max' => 0.49, 'status_level' => 'low_progress', 'interpretation' => 'The tutee shows minimal or no improvement and may require additional support or intervention.', 'algo_interpretation' => null, 'recommended_action' => null],
            ],
            'tutee_satisfaction' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'highly_engaged', 'interpretation' => 'The tutee is highly satisfied with the session and actively engaged in the learning process.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.50, 'max' => 0.79, 'status_level' => 'moderately_engaged', 'interpretation' => 'The tutee is somewhat satisfied but engagement may be inconsistent.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.0, 'max' => 0.49, 'status_level' => 'low_engagement', 'interpretation' => 'The tutee is dissatisfied or disengaged during the session.', 'algo_interpretation' => null, 'recommended_action' => null],
            ],
            'tutor_compatability' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'highly_compatible', 'interpretation' => 'The tutor’s teaching style, pacing, and communication strongly align with the tutee’s needs.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.50, 'max' => 0.79, 'status_level' => 'moderately_compatible', 'interpretation' => 'The tutor is generally suitable but with minor mismatches in learning preferences.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.0, 'max' => 0.49, 'status_level' => 'low_compatibility', 'interpretation' => 'The tutor does not match the tutee’s learning needs; reassignment may be necessary.', 'algo_interpretation' => null, 'recommended_action' => null],
            ],
            'total_match_effectiveness_score' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'highly_effective_match', 'interpretation' => 'The tutoring session resulted in strong learning improvement, high satisfaction, and excellent tutor-tutee compatibility', 'algo_interpretation' => 'Optimal match, algorithm successfully identified a highly suitable tutor', 'recommended_action' => 'Maintain pairing, prioritize similar matches in future assignments'],
                ['min' => 0.60, 'max' => 0.79, 'status_level' => 'effective_match', 'interpretation' => 'The session was generally successful with noticeable learning gains and good interaction quality', 'algo_interpretation' => 'Acceptable match, minor gaps in learning or compatibility', 'recommended_action' => 'Continue pairing but monitor, minor adjustments may improve outcomes'],
                ['min' => 0.40, 'max' => 0.59, 'status_level' => 'moderately_effective_match', 'interpretation' => 'Limited learning improvement or moderate mismatch in expectations or teaching style', 'algo_interpretation' => 'Suboptimal match, some issues in engagement, understanding, or compatibility', 'recommended_action' => 'Flag for review, consider alternative tutor in future sessions'],
                ['min' => 0.0, 'max' => 0.39, 'status_level' => 'ineffective_match', 'interpretation' => 'Minimal learning gain and/or poor satisfaction and compatibility', 'algo_interpretation' => 'Poor match, algorithm failed to meet tutee needs', 'recommended_action' => 'Recommend reassignment, trigger intervention or support mechanisms'],
            ],
            'self_reported_learning_gain' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'high_perceived_learning_gain', 'interpretation' => 'The student reports strong improvement after the session, with clear increases in understanding, confidence, and ability to solve problems', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.50, 'max' => 0.79, 'status_level' => 'moderate_perceived_learning_gain', 'interpretation' => 'The student reports some improvement, with partial understanding gained but some concepts still unclear', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.0, 'max' => 0.49, 'status_level' => 'low_perceived_learning_gain', 'interpretation' => 'The student reports little to no improvement, with minimal understanding gained and persistent difficulty', 'algo_interpretation' => null, 'recommended_action' => null],
            ],
            'tutee_evaluation' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'high_observed_improvement', 'interpretation' => 'The tutor observes significant improvement in the student, where the student demonstrates a strong grasp of the concepts and can apply them independently', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.50, 'max' => 0.79, 'status_level' => 'moderate_observed_improvement', 'interpretation' => 'The tutor observes some improvement, where the student shows partial understanding but still needs guidance', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.0, 'max' => 0.49, 'status_level' => 'low_observed_improvement', 'interpretation' => 'The tutor observes minimal improvement, where the student struggles to understand or apply concepts', 'algo_interpretation' => null, 'recommended_action' => null],
            ],
            'tutor_evaluation' => [
                ['min' => 0.80, 'max' => 1.0, 'status_level' => 'high_performance', 'interpretation' => 'The tutor demonstrates strong instructional effectiveness, with clear explanations and effective facilitation of learning.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.50, 'max' => 0.79, 'status_level' => 'moderate_performance', 'interpretation' => 'The tutor demonstrates acceptable performance, but with some gaps in clarity, pacing, or engagement.', 'algo_interpretation' => null, 'recommended_action' => null],
                ['min' => 0.0, 'max' => 0.49, 'status_level' => 'low_performance', 'interpretation' => 'The tutor demonstrates ineffective instructional delivery, with limited clarity and weak support for learning.', 'algo_interpretation' => null, 'recommended_action' => null],
            ],
        ];

        // Create each metric and attach its rubric ranges.
        foreach ($rubrics as $metricName => $rubricRows) {
            $metric = Metric::create(['name' => $metricName]);

            foreach ($rubricRows as $r) {
                Rubric::create([
                    'metric_id'           => $metric->metric_id,
                    'min_score'           => $r['min'],
                    'max_score'           => $r['max'],
                    'status_level'        => $r['status_level'],
                    'interpretation'      => $r['interpretation'],
                    'algo_interpretation' => $r['algo_interpretation'],
                    'recommended_action'  => $r['recommended_action'],
                ]);
            }
        }
    }
}