<?php

namespace App\Http\Controllers;

use App\Services\SurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles the home page and basic session actions.
 * 
 * The heavy logic lives in `SurveyService`; this controller mostly wires routes
 * to service calls and passes state to the view.
 */
class HomeController extends Controller
{
    /**
     * @param SurveyService $survey Domain service for session state and survey workflow.
     */
    public function __construct(private SurveyService $survey) {}

    /**
     * Show the home page with the current workflow state.
     */
    public function show(Request $request)
    {
        // Defaults to user id 1 when auth isn't enabled.
        $userId = (int) $request->session()->get('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        $progress = $this->survey->getSessionProgress($session, $userId);

        if ($session) {
            // Keep the active session id available across survey pages.
            $request->session()->put('tutoring_session_id', $session->tutoring_session_id);
        }

        return view('home_page', [
            'tutoringSession' => $session,
            'progress' => $progress,
            'currentSurveyResult' => $request->session()->get('current_survey_result'),
            'canViewSurveyResults' => !is_null($request->session()->get('pre_session_avg'))
                && !is_null($request->session()->get('post_session_avg'))
                && !is_null($request->session()->get('tutee_evaluation_avg', $request->session()->get('tutor_eval_avg'))),
            'debugResults' => [
                'pre_session' => $request->session()->get('pre_session_results'),
                'post_session' => $request->session()->get('post_session_results'),
                'tutee_satisfaction' => $request->session()->get('tutee_satisfaction_results'),
                'tutor_compatibility' => $request->session()->get('tutor_compatibility_results'),
                'compatibility_breakdown' => $request->session()->get('compatibilityBreakdown'),
                'tutor_performance' => $request->session()->get('tutor_performance_results'),
                'tutee_evaluation' => $request->session()->get('tutee_evaluation_results'),
            ],
        ]);
    }

    /**
     * Start a new tutoring session (if one is not already running).
     */
    public function start(Request $request): RedirectResponse
    {
        $userId = (int) $request->session()->get('user_id', 1);
        $current = $this->survey->getCurrentTutoringSession($userId);

        if ($current && $current->status === 'ongoing') {
            return redirect('/')->with('status_message', 'An ongoing tutoring session already exists.');
        }

        $session = $this->survey->startTutoringSession();
        $request->session()->put('tutoring_session_id', $session->tutoring_session_id);

        // Clear any leftover computed averages so the next session starts clean.
        $request->session()->forget([
            'pre_session_avg',
            'post_session_avg',
            'satisfaction_avg',
            'compatibility_avg',
            'tutee_evaluation_avg',
            'tutor_eval_avg',
            'tutor_evaluation_avg',
            'pre_session_results',
            'post_session_results',
            'tutee_satisfaction_results',
            'tutor_compatibility_results',
            'compatibilityBreakdown',
            'tutor_performance_results',
            'tutee_evaluation_results',
            'current_survey_result',
        ]);

        return redirect('/')->with('status_message', 'Tutoring session started.');
    }

    /**
     * End the current tutoring session after pre-session is done.
     */
    public function end(Request $request): RedirectResponse
    {
        $userId = (int) $request->session()->get('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);

        if (!$session) {
            return redirect('/')->with('status_message', 'No ongoing tutoring session found.');
        }

        if (!$this->survey->hasSubmittedSurvey($session->tutoring_session_id, $userId, 'pre_session')) {
            return redirect('/')->with('status_message', 'Complete the pre-session survey before ending the session.');
        }

        $this->survey->endTutoringSession($session->tutoring_session_id);

        return redirect('/')->with('status_message', 'Tutoring session ended. Please submit post-session surveys.');
    }
}
