<x-layout title="Post Session Survey">
    <h1>POST SESSION SURVEY</h1>

    <form method="POST">
        @csrf

        <b>Post Session SRLG</b>
        <table>
            <tr>
                <th style="width:400px;"></th>
                <th style="width:150px;">Strongly Disagree</th>
                <th style="width:150px;">Disagree</th>
                <th style="width:150px;">Neutral</th>
                <th style="width:150px;">Agree</th>
                <th style="width:150px;">Strongly Agree</th>
            </tr>
            @foreach ($post_session_data as $i => $q)
                <tr>
                    <td>{{ $q['question'] }}</td>
                    @for ($x = 1; $x <= 5; $x++)
                        <td style="text-align:center;">
                            <input type="radio" name="post_q{{ $i }}" value="{{ $x }}" required>
                        </td>
                    @endfor
                </tr>
            @endforeach
        </table>

        <br><br>

        <b>Tutee Satisfaction</b>
        <table>
            <tr>
                <th style="width:400px;"></th>
                <th style="width:150px;">Strongly Disagree</th>
                <th style="width:150px;">Disagree</th>
                <th style="width:150px;">Neutral</th>
                <th style="width:150px;">Agree</th>
                <th style="width:150px;">Strongly Agree</th>
            </tr>
            @foreach ($tutee_satisfaction_data as $i => $q)
                <tr>
                    <td>{{ $q['question'] }}</td>
                    @for ($x = 1; $x <= 5; $x++)
                        <td style="text-align:center;">
                            <input type="radio" name="tutee_q{{ $i }}" value="{{ $x }}" required>
                        </td>
                    @endfor
                </tr>
            @endforeach
        </table>

        <br><br>

        <b>Tutor Compatibility</b>

        @foreach ($tutor_compatibility_data as $pref => $items)
            <br>
            <h4>{{ strtoupper(str_replace('_', ' ', $pref)) }}</h4>
            <table>
                <tr>
                    <th style="width:400px;"></th>
                    <th style="width:150px;">Strongly Disagree</th>
                    <th style="width:150px;">Disagree</th>
                    <th style="width:150px;">Neutral</th>
                    <th style="width:150px;">Agree</th>
                    <th style="width:150px;">Strongly Agree</th>
                </tr>
                @foreach ($items as $i => $item)
                    <tr>
                        <td>{{ $item['question'] }}</td>
                        @for ($x = 1; $x <= 5; $x++)
                            <td style="text-align:center;">
                                <input type="radio" name="{{ $pref }}{{ $i }}" value="{{ $x }}" required>
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        @endforeach

        <br><br>

        <b>Tutor Performance Evaluation</b>
        <table>
            <tr>
                <th style="width:400px;"></th>
                <th style="width:150px;">Strongly Disagree</th>
                <th style="width:150px;">Disagree</th>
                <th style="width:150px;">Neutral</th>
                <th style="width:150px;">Agree</th>
                <th style="width:150px;">Strongly Agree</th>
            </tr>
            @foreach ($tutor_performance_data as $i => $q)
                <tr>
                    <td>{{ $q['question'] }}</td>
                    @for ($x = 1; $x <= 5; $x++)
                        <td style="text-align:center;">
                            <input type="radio" name="performance_q{{ $i }}" value="{{ $x }}" required>
                        </td>
                    @endfor
                </tr>
            @endforeach
        </table>

        <br>
        <button type="submit">Submit</button>
    </form>

    @if ($results)
        <hr>
        <h2>Results (Debug View)</h2>

        <h2>Post Session SRLG</h2>
        <p>Prior Understanding: {{ round($results['post_session']['prior_understanding'], 2) }}</p>
        <p>Confidence: {{ round($results['post_session']['confidence'], 2) }}</p>
        <p>Application Readiness: {{ round($results['post_session']['application_readiness'], 2) }}</p>
        <p>Difficulty Awareness: {{ round($results['post_session']['difficulty_awareness'], 2) }}</p>
        <p><b>Post-session SRLG Score: {{ round($results['post_session']['average_score'], 2) }}</b></p>

        <br>
        <h3>Tutee Satisfaction</h3>
        <p>Overall Experience: {{ round($results['tutee_satisfaction']['overall_experience'], 2) }}</p>
        <p>Perceived Usefulness: {{ round($results['tutee_satisfaction']['perceived_usefulness'], 2) }}</p>
        <p>Behavioral Intent: {{ round($results['tutee_satisfaction']['behavioral_intent'], 2) }}</p>
        <p><b>Tutee Satisfaction Score: {{ round($results['tutee_satisfaction']['average_score'], 2) }}</b></p>

        <br>
        <h2>Tutor Compatibility</h2>
        @foreach ($results['tutor_compatibility'] as $pref => $value)
            @if ($pref === 'average_score')
                <p><b>Tutor Compatibility Score: {{ round($value, 2) }}</b></p>
            @else
                <b>{{ strtoupper($pref) }}</b><br>
                @foreach ($value as $dim => $score)
                    <p>{{ $dim }}: {{ round($score, 2) }}</p>
                @endforeach
            @endif
        @endforeach

        <br>
        <h2>Tutor Performance</h2>
        <p>Mastery: {{ round($results['tutor_performance']['mastery'], 2) }}</p>
        <p>Clarity: {{ round($results['tutor_performance']['clarity'], 2) }}</p>
        <p>Responsiveness: {{ round($results['tutor_performance']['responsiveness'], 2) }}</p>
        <p>Engagement: {{ round($results['tutor_performance']['engagement'], 2) }}</p>
        <p>Preparedness: {{ round($results['tutor_performance']['preparedness'], 2) }}</p>
        <p><b>Tutor Performance Evaluation Score: {{ round($results['tutor_performance']['average_score'], 2) }}</b></p>
    @endif
</x-layout>