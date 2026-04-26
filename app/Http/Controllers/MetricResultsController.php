<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SurveyService;

/**
 * Shows the metrics results page.
 * 
 * Reads the averages saved in session by the survey controllers, then asks
 * `SurveyService` to compute metrics and load the dimension breakdown.
 */
class MetricResultsController extends Controller
{
    /**
     * @param SurveyService $survey Domain service that computes and retrieves results.
     */
    public function __construct(private SurveyService $survey) {}

    /**
     * Render metric results when all required inputs are present.
     */
    public function show(Request $request)
    {
        // Averages are written to the session by each survey's submit handler.
        $pre_avg           = $request->session()->get('pre_session_avg');
        $post_avg          = $request->session()->get('post_session_avg');
        $tutee_eval        = $request->session()->get('tutee_evaluation_avg', $request->session()->get('tutor_eval_avg'));
        $satisfaction      = $request->session()->get('satisfaction_avg');
        $compatibility     = $request->session()->get('compatibility_avg');
        $tutor_evaluation  = $request->session()->get('tutor_evaluation_avg');

        // The results screen is only "ready" once all required inputs exist.
        $ready = !is_null($pre_avg)
            && !is_null($post_avg)
            && !is_null($tutee_eval)
            && !is_null($satisfaction)
            && !is_null($compatibility)
            && !is_null($tutor_evaluation);

        $metrics = null;
        $dimensionScores = [];
        $compatibilityBreakdown = $request->session()->get('compatibilityBreakdown');

        if ($ready) {
            // All computations/results are stored per tutoring session.
            $tutoringSessionId = $request->session()->get('tutoring_session_id');
            // Default user id used.
            $userId            = $request->session()->get('user_id', 1);

            if (!is_null($tutoringSessionId)) {
                $metrics = $this->survey->computeMetrics(
                    $pre_avg, $post_avg, $tutee_eval,
                    $satisfaction, $compatibility, $tutor_evaluation,
                    $tutoringSessionId, $userId
                );

                $dimensionScores = $this->survey->getDimensionScoresForSession(
                    $tutoringSessionId,
                    $userId
                );
            }
        }

        return view('Surveys.metric_results', [
            'metrics' => $metrics,
            'dimensionScores' => $dimensionScores,
            'compatibilityBreakdown' => $compatibilityBreakdown,
            'ready'   => $ready,
        ]);
    }
}