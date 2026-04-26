<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SurveyService;

/**
 * Handles the tutee evaluation survey flow.
 *
 * After the session ends, this captures evaluation answers and stores
 * both raw responses and computed dimension averages.
 */
class TutorSurveyController extends Controller
{
    /**
     * @param SurveyService $survey Domain service for question loading, compute, and persistence.
     */
    public function __construct(private SurveyService $survey) {}

    /**
     * Show the tutee evaluation form when it's available.
     */
    public function show()
    {
        $userId = (int) session('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        if (!$this->survey->canAccessTuteeEvaluationSurvey($session, $userId)) {
            return redirect('/')->with('status_message', 'Tutee evaluation survey is not available right now.');
        }

        return view('Surveys.tutee_evaluation_survey', [
            'tutee_evaluation_data' => $this->survey->getTuteeEvaluationData(),
            'results'               => null,
        ]);
    }

    /**
     * Handle evaluation submission and save computed results.
     */
    public function submit(Request $request)
    {
        $tutee_evaluation_data = $this->survey->getTuteeEvaluationData();

        // Form inputs use keys q0..qN (aligned with how the view names fields).
        foreach ($tutee_evaluation_data as $i => $q) {
            $tutee_evaluation_data[$i]['score'] = $request->input("q{$i}");
        }

        $results = $this->survey->computeTuteeEvaluation($tutee_evaluation_data);

        $userId = (int) $request->session()->get('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        if (!$session || !$this->survey->canAccessTuteeEvaluationSurvey($session, $userId)) {
            return redirect('/')->with('status_message', 'Tutee evaluation survey is not available right now.');
        }
        $tutoringSessionId = $session->tutoring_session_id;
        $request->session()->put('tutoring_session_id', $tutoringSessionId);

        // Persist raw answers and derived dimension scores for reporting.
        $this->survey->saveFlatResponses('tutee_evaluation', $tutee_evaluation_data, $tutoringSessionId, $userId);
        $this->survey->saveTuteeEvaluationDimensionScores($tutee_evaluation_data, $tutoringSessionId, $userId);

        // Cache the stage average; both keys are kept for compatibility with older code paths/views.
        $request->session()->put('tutee_evaluation_avg', $results['average_score']);
        $request->session()->put('tutor_eval_avg', $results['average_score']);
        // Store computed dimension results for home-page debugging.
        $request->session()->put('tutee_evaluation_results', $results);
        $request->session()->put('current_survey_result', [
            'type' => 'tutee_evaluation',
            'label' => 'Tutee Evaluation Results',
            'data' => $results,
        ]);

        // If all required averages exist, compute and persist metrics now.
        $preAvg          = $request->session()->get('pre_session_avg');
        $postAvg         = $request->session()->get('post_session_avg');
        $satisfactionAvg = $request->session()->get('satisfaction_avg');
        $compatibilityAvg = $request->session()->get('compatibility_avg');
        $tutorEvalAvg    = $request->session()->get('tutor_evaluation_avg');

        if (!is_null($preAvg) && !is_null($postAvg) && !is_null($satisfactionAvg) && !is_null($compatibilityAvg) && !is_null($tutorEvalAvg)) {
            $this->survey->computeMetrics(
                $preAvg,
                $postAvg,
                $results['average_score'],
                $satisfactionAvg,
                $compatibilityAvg,
                $tutorEvalAvg,
                $tutoringSessionId,
                $userId
            );
        }

        // Once all required surveys exist, mark the session as completed.
        $this->survey->markSessionEvaluatedIfComplete($tutoringSessionId, $userId);

        return redirect('/')->with('status_message', 'Tutee evaluation survey submitted.');
    }
}