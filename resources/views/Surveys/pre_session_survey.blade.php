<x-layout title="Pre Session Survey">
    <h1>PRE SESSION SURVEY</h1>

    <form method="POST">
        @csrf

        <table>
            <tr>
                <th style="width: 400px;"></th>
                <th>Strongly Disagree</th>
                <th>Disagree</th>
                <th>Neutral</th>
                <th>Agree</th>
                <th>Strongly Agree</th>
            </tr>

            @foreach ($pre_session_data as $i => $q)
                <tr>
                    <td>{{ $q['question'] }}</td>

                    @for ($x = 1; $x <= 5; $x++)
                        <td style="text-align:center;">
                            <input type="radio"
                                   name="q{{ $i + 1 }}"
                                   value="{{ $x }}"
                                   required>
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

        <p>Prior Understanding: {{ round($results['prior_understanding'], 2) }}</p>
        <p>Confidence: {{ round($results['confidence'], 2) }}</p>
        <p>Application Readiness: {{ round($results['application_readiness'], 2) }}</p>
        <p>Difficulty Awareness: {{ round($results['difficulty_awareness'], 2) }}</p>
        <p><b>Pre-session SRLG Score: {{ round($results['average_score'], 2) }}</b></p>
    @endif

</x-layout>