<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SurveyService;

/**
 * Handles all post-session surveys on one page.
 * 
 * Maps request inputs into the survey structures from `SurveyService`,
 * then delegates computation and persistence back to the service.
 */
class PostSessionSurveyController extends Controller
{
    /**
     * @param SurveyService $survey Domain service for question loading, compute, and persistence.
     */
    public function __construct(private SurveyService $survey) {}

    /**
     * Show the post-session survey forms when available.
     */
    public function show()
    {
        $userId = (int) session('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        if (!$this->survey->canAccessPostSessionSurvey($session, $userId)) {
            return redirect('/')->with('status_message', 'Post-session survey is not available right now.');
        }

        return view('Surveys.post_session_survey', [
            'post_session_data'        => $this->survey->getPostSessionData(),
            'tutee_satisfaction_data'  => $this->survey->getTuteeSatisfactionData(),
            'tutor_compatibility_data' => $this->survey->getTutorCompatibilityData(),
            'tutor_performance_data'   => $this->survey->getTutorPerformanceData(),
            'results'                  => null,
        ]);
    }

    /**
     * Handle all post-session submissions and store computed averages.
     */
    public function submit(Request $request)
    {
        $post_session_data        = $this->survey->getPostSessionData();
        $tutee_satisfaction_data  = $this->survey->getTuteeSatisfactionData();
        $tutor_compatibility_data = $this->survey->getTutorCompatibilityData();
        $tutor_performance_data   = $this->survey->getTutorPerformanceData();

        // Request input keys are aligned with how the form names its fields.
        foreach ($post_session_data as $i => $q) {
            $post_session_data[$i]['score'] = $request->input("post_q{$i}");
        }
        foreach ($tutee_satisfaction_data as $i => $q) {
            $tutee_satisfaction_data[$i]['score'] = $request->input("tutee_q{$i}");
        }
        foreach ($tutor_compatibility_data as $pref => $items) {
            foreach ($items as $i => $item) {
                $tutor_compatibility_data[$pref][$i]['score'] = $request->input("{$pref}{$i}");
            }
        }
        foreach ($tutor_performance_data as $i => $q) {
            $tutor_performance_data[$i]['score'] = $request->input("performance_q{$i}");
        }

        $results = $this->survey->computePostSession(
            $post_session_data,
            $tutee_satisfaction_data,
            $tutor_compatibility_data,
            $tutor_performance_data
        );

        $userId = (int) $request->session()->get('user_id', 1);
        $session = $this->survey->getCurrentTutoringSession($userId);
        if (!$session || !$this->survey->canAccessPostSessionSurvey($session, $userId)) {
            return redirect('/')->with('status_message', 'Post-session survey is not available right now.');
        }
        $tutoringSessionId = $session->tutoring_session_id;
        $request->session()->put('tutoring_session_id', $tutoringSessionId);

        // Persist each survey's responses. Compatibility is grouped in the UI but stored flat in the DB.
        $this->survey->saveFlatResponses('post_session',       $post_session_data,       $tutoringSessionId, $userId);
        $this->survey->saveFlatResponses('tutee_satisfaction', $tutee_satisfaction_data, $tutoringSessionId, $userId);
        $this->survey->saveGroupedResponses('tutor_compatibility', $tutor_compatibility_data, $tutoringSessionId, $userId);
        $this->survey->saveFlatResponses('tutor_performance',  $tutor_performance_data,  $tutoringSessionId, $userId);

        // Store derived dimension scores for reporting (dimension breakdown tables).
        $this->survey->savePostSessionDimensionScores(
            $post_session_data,
            $tutee_satisfaction_data,
            $tutor_compatibility_data,
            $tutor_performance_data,
            $tutoringSessionId,
            $userId
        );

        // Cache the stage averages for later metric computation/results rendering.
        $request->session()->put('post_session_avg',  $results['post_session']['average_score']);
        $request->session()->put('satisfaction_avg',  $results['tutee_satisfaction']['average_score']);
        $request->session()->put('compatibility_avg', $results['tutor_compatibility']['average_score']);
        $request->session()->put('compatibilityBreakdown', $results['tutor_compatibility']['breakdown'] ?? null);
        $request->session()->put('tutor_evaluation_avg', $results['tutor_performance']['average_score']);

        // Store computed dimension results for home-page debugging.
        $request->session()->put('post_session_results', $results['post_session'] ?? null);
        $request->session()->put('tutee_satisfaction_results', $results['tutee_satisfaction'] ?? null);
        $request->session()->put('tutor_compatibility_results', $results['tutor_compatibility'] ?? null);
        $request->session()->put('tutor_performance_results', $results['tutor_performance'] ?? null);
        $request->session()->put('current_survey_result', [
            'type' => 'post_session',
            'label' => 'Post-session Results',
            'data' => $results['post_session'] ?? [],
        ]);

        // If the remaining averages exist, compute and persist the full metrics set now.
        $preAvg      = $request->session()->get('pre_session_avg');
        $tuteeEvalAvg = $request->session()->get('tutee_evaluation_avg', $request->session()->get('tutor_eval_avg'));

        if (!is_null($preAvg) && !is_null($tuteeEvalAvg)) {
            $this->survey->computeMetrics(
                $preAvg,
                $results['post_session']['average_score'],
                $tuteeEvalAvg,
                $results['tutee_satisfaction']['average_score'],
                $results['tutor_compatibility']['average_score'],
                $results['tutor_performance']['average_score'],
                $tutoringSessionId,
                $userId
            );
        }

        // Once all required surveys exist, mark the session as completed.
        $this->survey->markSessionEvaluatedIfComplete($tutoringSessionId, $userId);

        return redirect('/')->with('status_message', 'Post-session survey submitted.');
    }
}