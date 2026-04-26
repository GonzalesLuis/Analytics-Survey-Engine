<x-layout title="Tutee Evaluation Survey">
    <h1>TUTEE EVALUATION SURVEY</h1>

    <form method="POST">
        @csrf
        <table>
            <tr>
                <th style="width:400px;"></th>
                <th style="width:150px;">Strongly Disagree</th>
                <th style="width:150px;">Disagree</th>
                <th style="width:150px;">Neutral</th>
                <th style="width:150px;">Agree</th>
                <th style="width:150px;">Strongly Agree</th>
            </tr>
            @foreach ($tutee_evaluation_data as $i => $q)
                <tr>
                    <td>{{ $q['question'] }}</td>
                    @for ($x = 1; $x <= 5; $x++)
                        <td style="text-align:center;">
                            <input type="radio" name="q{{ $i }}" value="{{ $x }}" required>
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

        <h3>Tutee Evaluation</h3>
        <p>Understanding: {{ round($results['understanding'], 2) }}</p>
        <p>Participation: {{ round($results['participation'], 2) }}</p>
        <p>Application: {{ round($results['application'], 2) }}</p>
        <p>Effort: {{ round($results['effort'], 2) }}</p>
        <p>Difficulty Indicators: {{ round($results['difficulty_indicators'], 2) }}</p>
        <p><b>Tutee Evaluation Score: {{ round($results['average_score'], 2) }}</b></p>
    @endif
</x-layout>