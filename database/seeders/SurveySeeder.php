<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Survey;
use App\Models\Dimension;
use App\Models\Question;

/**
 * Seeds all survey definitions used by the app.
 *
 * This includes surveys, their dimensions, and every question.
 */
class SurveySeeder extends Seeder
{
    /**
     * Insert the default surveys and question banks.
     */
    public function run(): void
    {
        $surveys = [
            'pre_session' => [
                'dimensions' => [
                    ['name' => 'prior_understanding', 'category' => null, 'questions' => [
                        ['text' => 'I have a clear understanding of the topic',         'is_reverse' => false],
                        ['text' => 'I am familiar with the key concepts of this topic', 'is_reverse' => false],
                    ]],
                    ['name' => 'confidence', 'category' => null, 'questions' => [
                        ['text' => 'I feel confident answering questions about this topic', 'is_reverse' => false],
                        ['text' => 'I can explain this topic to others',                   'is_reverse' => false],
                    ]],
                    ['name' => 'application_readiness', 'category' => null, 'questions' => [
                        ['text' => 'I can apply this topic to solve problems',    'is_reverse' => false],
                        ['text' => 'I can relate this topic to previous lessons', 'is_reverse' => false],
                    ]],
                    ['name' => 'difficulty_awareness', 'category' => null, 'questions' => [
                        ['text' => 'I find this topic confusing',                        'is_reverse' => true],
                        ['text' => 'I struggle to solve problems related to this topic', 'is_reverse' => true],
                    ]],
                ],
            ],
            'post_session' => [
                'dimensions' => [
                    ['name' => 'prior_understanding',   'category' => null, 'questions' => [
                        ['text' => 'I have a clear understanding of the topic.',         'is_reverse' => false],
                        ['text' => 'I am familiar with the key concepts of this topic.', 'is_reverse' => false],
                    ]],
                    ['name' => 'confidence', 'category' => null, 'questions' => [
                        ['text' => 'I feel confident answering questions about this topic.', 'is_reverse' => false],
                        ['text' => 'I can explain this topic to others.',                   'is_reverse' => false],
                    ]],
                    ['name' => 'application_readiness', 'category' => null, 'questions' => [
                        ['text' => 'I can apply this topic to solve problems.',    'is_reverse' => false],
                        ['text' => 'I can relate this topic to previous lessons.', 'is_reverse' => false],
                    ]],
                    ['name' => 'difficulty_awareness', 'category' => null, 'questions' => [
                        ['text' => 'I find this topic confusing.',                        'is_reverse' => true],
                        ['text' => 'I struggle to solve problems related to this topic.', 'is_reverse' => true],
                    ]],
                ],
            ],
            'tutee_satisfaction' => [
                'dimensions' => [
                    ['name' => 'overall_experience', 'category' => null, 'questions' => [
                        ['text' => 'I am satisfied with the tutoring session.', 'is_reverse' => false],
                    ]],
                    ['name' => 'perceived_usefulness', 'category' => null, 'questions' => [
                        ['text' => 'The session met my expectations.',           'is_reverse' => false],
                        ['text' => 'The session helped improve my understanding.','is_reverse' => false],
                        ['text' => 'The session was worth my time.',             'is_reverse' => false],
                    ]],
                    ['name' => 'behavioral_intent', 'category' => null, 'questions' => [
                        ['text' => 'I would attend another session.', 'is_reverse' => false],
                        ['text' => 'I would recommend this tutor.',   'is_reverse' => false],
                    ]],
                ],
            ],
            'tutor_compatibility' => [
                'dimensions' => [
                    ['name' => 'high_guidance',     'category' => 'guidance_level', 'questions' => [
                        ['text' => 'The tutor provided step-by-step explanations that helped me understand the topic.', 'is_reverse' => false],
                        ['text' => 'The tutor guided me closely throughout the session when needed.',                   'is_reverse' => false],
                    ]],
                    ['name' => 'moderate_guidance', 'category' => 'guidance_level', 'questions' => [
                        ['text' => 'The tutor worked with me collaboratively to solve problems.',      'is_reverse' => false],
                        ['text' => 'The tutor balanced guidance and independence effectively during the session.',        'is_reverse' => false],
                    ]],
                    ['name' => 'low_guidance',      'category' => 'guidance_level', 'questions' => [
                        ['text' => 'The tutor allowed me to work independently when appropriate.', 'is_reverse' => false],
                        ['text' => 'The tutor encouraged me to solve problems on my own.',        'is_reverse' => false],
                    ]],
                    ['name' => 'practice_oriented',    'category' => 'engagement_level', 'questions' => [
                        ['text' => 'The tutor provided exercises or practice tasks that helped reinforce my learning.', 'is_reverse' => false],
                        ['text' => 'I was actively involved in solving problems during the session.',                  'is_reverse' => false],
                    ]],
                    ['name' => 'explanation_oriented', 'category' => 'engagement_level', 'questions' => [
                        ['text' => 'The tutor encouraged me to explain my thinking or reasoning.',       'is_reverse' => false],
                        ['text' => 'I was able to construct my own understanding through explanation and reflection.','is_reverse' => false],
                    ]],
                    ['name' => 'dialogue_oriented',    'category' => 'engagement_level', 'questions' => [
                        ['text' => 'The tutor engaged me in meaningful discussion throughout the session.', 'is_reverse' => false],
                        ['text' => 'There was active back-and-forth interaction that improved my understanding.',   'is_reverse' => false],
                    ]],
                    ['name' => 'english', 'category' => 'language', 'questions' => [
                        ['text' => 'The tutor communicated effectively in English.', 'is_reverse' => false],
                    ]],
                    ['name' => 'tagalog', 'category' => 'language', 'questions' => [
                        ['text' => 'The tutor communicated effectively in Tagalog.', 'is_reverse' => false],
                    ]],
                ],
            ],
            'tutor_performance' => [
                'dimensions' => [
                    ['name' => 'mastery', 'category' => null, 'questions' => [
                        ['text' => 'The tutor demonstrated strong knowledge of the topic.', 'is_reverse' => false],
                        ['text' => 'The tutor answered questions accurately.',              'is_reverse' => false],
                    ]],
                    ['name' => 'clarity', 'category' => null, 'questions' => [
                        ['text' => 'The tutor explained concepts clearly.',      'is_reverse' => false],
                        ['text' => 'The explanations were easy to understand.',  'is_reverse' => false],
                    ]],
                    ['name' => 'responsiveness', 'category' => null, 'questions' => [
                        ['text' => 'The tutor responded to my questions effectively.', 'is_reverse' => false],
                        ['text' => 'The tutor addressed my difficulties.',             'is_reverse' => false],
                    ]],
                    ['name' => 'engagement', 'category' => null, 'questions' => [
                        ['text' => 'The tutor kept me engaged.', 'is_reverse' => false],
                        ['text' => 'The session was interactive.','is_reverse' => false],
                    ]],
                    ['name' => 'preparedness', 'category' => null, 'questions' => [
                        ['text' => 'The tutor was well-prepared.', 'is_reverse' => false],
                        ['text' => 'The session was organized.',   'is_reverse' => false],
                    ]],
                ],
            ],
            'tutee_evaluation' => [
                'dimensions' => [
                    ['name' => 'understanding', 'category' => null, 'questions' => [
                        ['text' => 'The tutee understood the key concepts.', 'is_reverse' => false],
                        ['text' => 'The tutee demonstrated comprehension.',  'is_reverse' => false],
                    ]],
                    ['name' => 'participation', 'category' => null, 'questions' => [
                        ['text' => 'The tutee actively participated.',   'is_reverse' => false],
                        ['text' => 'The tutee asked relevant questions.','is_reverse' => false],
                    ]],
                    ['name' => 'application', 'category' => null, 'questions' => [
                        ['text' => 'The tutee can apply the concepts.',    'is_reverse' => false],
                        ['text' => 'The tutee solved problems correctly.', 'is_reverse' => false],
                    ]],
                    ['name' => 'effort', 'category' => null, 'questions' => [
                        ['text' => 'The tutee showed effort in learning.', 'is_reverse' => false],
                        ['text' => 'The tutee was attentive.',             'is_reverse' => false],
                    ]],
                    ['name' => 'difficulty_indicators', 'category' => null, 'questions' => [
                        ['text' => 'The tutee struggled to understand the topic.', 'is_reverse' => true],
                        ['text' => 'The tutee had difficulty applying concepts.',  'is_reverse' => true],
                    ]],
                ],
            ],
        ];

        // Create surveys, then their dimensions, then their questions.
        foreach ($surveys as $surveyName => $surveyData) {
            $survey = Survey::create(['name' => $surveyName]);

            foreach ($surveyData['dimensions'] as $dimData) {
                $dimension = Dimension::create([
                    'survey_id' => $survey->survey_id,
                    'category'  => $dimData['category'],
                    'name'      => $dimData['name'],
                ]);

                foreach ($dimData['questions'] as $qData) {
                    Question::create([
                        'dimension_id'  => $dimension->dimension_id,
                        'question_text' => $qData['text'],
                        'is_reverse'    => $qData['is_reverse'],
                    ]);
                }
            }
        }
    }
}