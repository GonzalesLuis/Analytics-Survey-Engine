<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SurveyService;

/**
 * Handles the pre-session survey flow.
 *
 * This captures baseline answers before tutoring starts and stores both
 * the raw responses and computed averages.
 */
class PreSessionSurveyController extends Controller
{
    /**
     * @param SurveyService $survey Domain service for question loading, compute, and persistence.
     */
    public function __construct(private SurveyService $survey) {}

    /**
     * Show the pre-session form when it's available.
     */
    public function show()
    {
        $userId = (int) session('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        if (!$this->survey->canAccessPreSessionSurvey($session, $userId)) {
            return redirect('/')->with('status_message', 'Pre-session survey is not available right now.');
        }

        return view('Surveys.pre_session_survey', [
            'pre_session_data' => $this->survey->getPreSessionData(),
            'results'          => null,
        ]);
    }

    /**
     * Handle pre-session form submission and save results.
     */
    public function submit(Request $request)
    {
        $pre_session_data = $this->survey->getPreSessionData();

        // Form inputs use sequential keys (q1, q2, ...) aligned with the rendered question order.
        foreach ($pre_session_data as $i => $q) {
            $pre_session_data[$i]['score'] = $request->input("q" . ($i + 1));
        }

        $results = $this->survey->computePreSession($pre_session_data);

        $userId = (int) $request->session()->get('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        if (!$session || !$this->survey->canAccessPreSessionSurvey($session, $userId)) {
            return redirect('/')->with('status_message', 'Pre-session survey is not available right now.');
        }
        $tutoringSessionId = $session->tutoring_session_id;
        $request->session()->put('tutoring_session_id', $tutoringSessionId);

        // Persist the raw answers and also store derived dimension averages for reporting.
        $this->survey->saveFlatResponses('pre_session', $pre_session_data, $tutoringSessionId, $userId);
        $this->survey->savePreSessionDimensionScores($pre_session_data, $tutoringSessionId, $userId);

        // Store the session-level average so other pages (and metrics computation) can reuse it.
        $request->session()->put('pre_session_avg', $results['average_score']);
        // Store computed dimension results for home-page debugging.
        $request->session()->put('pre_session_results', $results);
        $request->session()->put('current_survey_result', [
            'type' => 'pre_session',
            'label' => 'Pre-session Results',
            'data' => $results,
        ]);

        // If all other required averages exist already, compute and persist the metrics immediately.
        $postAvg         = $request->session()->get('post_session_avg');
        $tuteeEvalAvg    = $request->session()->get('tutee_evaluation_avg', $request->session()->get('tutor_eval_avg'));
        $satisfactionAvg = $request->session()->get('satisfaction_avg');
        $compatibilityAvg = $request->session()->get('compatibility_avg');
        $tutorEvalAvg    = $request->session()->get('tutor_evaluation_avg');

        if (!is_null($postAvg) && !is_null($tuteeEvalAvg) && !is_null($satisfactionAvg) && !is_null($compatibilityAvg) && !is_null($tutorEvalAvg)) {
            $this->survey->computeMetrics(
                $results['average_score'],
                $postAvg,
                $tuteeEvalAvg,
                $satisfactionAvg,
                $compatibilityAvg,
                $tutorEvalAvg,
                $tutoringSessionId,
                $userId
            );
        }

        return redirect('/')->with('status_message', 'Pre-session survey submitted.');
    }
}