<x-layout title="Tutee Evaluation Survey">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tutee Evaluation Survey</h1>

    <form method="POST" class="mt-6 space-y-6">
        @csrf

        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <tr class="bg-slate-100 text-slate-700">
                    <th class="min-w-[320px] px-4 py-3 text-left font-semibold"></th>
                    <th class="px-4 py-3 text-center font-semibold">Strongly Disagree</th>
                    <th class="px-4 py-3 text-center font-semibold">Disagree</th>
                    <th class="px-4 py-3 text-center font-semibold">Neutral</th>
                    <th class="px-4 py-3 text-center font-semibold">Agree</th>
                    <th class="px-4 py-3 text-center font-semibold">Strongly Agree</th>
                </tr>
                @foreach ($tutee_evaluation_data as $i => $q)
                    <tr class="border-t border-slate-200 odd:bg-white even:bg-slate-50">
                        <td class="px-4 py-3 text-slate-700">{{ $q['question'] }}</td>
                        @for ($x = 1; $x <= 5; $x++)
                            <td class="px-4 py-3 text-center">
                                <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500" type="radio" name="q{{ $i }}" value="{{ $x }}" required>
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </div>

        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">Submit</button>
    </form>

    @if ($results)
        <div class="mt-8 border-t border-slate-200 pt-6">
            <h2 class="text-lg font-semibold text-slate-900">Results (Debug View)</h2>

            <h3 class="mt-4 text-base font-semibold text-slate-900">Tutee Evaluation</h3>
            <div class="mt-2 space-y-1 text-sm text-slate-700">
                <p>Understanding: {{ round($results['understanding'], 2) }}</p>
                <p>Participation: {{ round($results['participation'], 2) }}</p>
                <p>Application: {{ round($results['application'], 2) }}</p>
                <p>Effort: {{ round($results['effort'], 2) }}</p>
                <p>Difficulty Indicators: {{ round($results['difficulty_indicators'], 2) }}</p>
                <p class="font-semibold text-slate-900">Tutee Evaluation Score: {{ round($results['average_score'], 2) }}</p>
            </div>
        </div>
    @endif
</x-layout>