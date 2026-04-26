<x-layout title="Post Session Survey">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Post Session Survey</h1>

    <form method="POST" class="mt-6 space-y-8">
        @csrf

        @php
            $labels = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
        @endphp

        @foreach ([
            ['title' => 'Post Session SRLG', 'data' => $post_session_data, 'namePrefix' => 'post_q'],
            ['title' => 'Tutee Satisfaction', 'data' => $tutee_satisfaction_data, 'namePrefix' => 'tutee_q'],
        ] as $section)
            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2>
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <tr class="bg-slate-100 text-slate-700">
                            <th class="min-w-[320px] px-4 py-3 text-left font-semibold"></th>
                            @foreach ($labels as $label)
                                <th class="px-4 py-3 text-center font-semibold">{{ $label }}</th>
                            @endforeach
                        </tr>
                        @foreach ($section['data'] as $i => $q)
                            <tr class="border-t border-slate-200 odd:bg-white even:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">{{ $q['question'] }}</td>
                                @for ($x = 1; $x <= 5; $x++)
                                    <td class="px-4 py-3 text-center">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500" type="radio" name="{{ $section['namePrefix'] . $i }}" value="{{ $x }}" required>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>
                </div>
            </section>
        @endforeach

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900">Tutor Compatibility</h2>
            @foreach ($tutor_compatibility_data as $pref => $items)
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-slate-800">{{ strtoupper(str_replace('_', ' ', $pref)) }}</h3>
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <tr class="bg-slate-100 text-slate-700">
                                <th class="min-w-[320px] px-4 py-3 text-left font-semibold"></th>
                                @foreach ($labels as $label)
                                    <th class="px-4 py-3 text-center font-semibold">{{ $label }}</th>
                                @endforeach
                            </tr>
                            @foreach ($items as $i => $item)
                                <tr class="border-t border-slate-200 odd:bg-white even:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-700">{{ $item['question'] }}</td>
                                    @for ($x = 1; $x <= 5; $x++)
                                        <td class="px-4 py-3 text-center">
                                            <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500" type="radio" name="{{ $pref }}{{ $i }}" value="{{ $x }}" required>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Tutor Performance Evaluation</h2>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <tr class="bg-slate-100 text-slate-700">
                        <th class="min-w-[320px] px-4 py-3 text-left font-semibold"></th>
                        @foreach ($labels as $label)
                            <th class="px-4 py-3 text-center font-semibold">{{ $label }}</th>
                        @endforeach
                    </tr>
                    @foreach ($tutor_performance_data as $i => $q)
                        <tr class="border-t border-slate-200 odd:bg-white even:bg-slate-50">
                            <td class="px-4 py-3 text-slate-700">{{ $q['question'] }}</td>
                            @for ($x = 1; $x <= 5; $x++)
                                <td class="px-4 py-3 text-center">
                                    <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500" type="radio" name="performance_q{{ $i }}" value="{{ $x }}" required>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
            </div>
        </section>

        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">Submit</button>
    </form>

    @if ($results)
        <div class="mt-8 border-t border-slate-200 pt-6">
            <h2 class="text-lg font-semibold text-slate-900">Results (Debug View)</h2>

            <h2 class="mt-4 text-base font-semibold text-slate-900">Post Session SRLG</h2>
            <div class="mt-2 space-y-1 text-sm text-slate-700">
                <p>Prior Understanding: {{ round($results['post_session']['prior_understanding'], 2) }}</p>
                <p>Confidence: {{ round($results['post_session']['confidence'], 2) }}</p>
                <p>Application Readiness: {{ round($results['post_session']['application_readiness'], 2) }}</p>
                <p>Difficulty Awareness: {{ round($results['post_session']['difficulty_awareness'], 2) }}</p>
                <p class="font-semibold text-slate-900">Post-session SRLG Score: {{ round($results['post_session']['average_score'], 2) }}</p>
            </div>

            <h3 class="mt-5 text-base font-semibold text-slate-900">Tutee Satisfaction</h3>
            <div class="mt-2 space-y-1 text-sm text-slate-700">
                <p>Overall Experience: {{ round($results['tutee_satisfaction']['overall_experience'], 2) }}</p>
                <p>Perceived Usefulness: {{ round($results['tutee_satisfaction']['perceived_usefulness'], 2) }}</p>
                <p>Behavioral Intent: {{ round($results['tutee_satisfaction']['behavioral_intent'], 2) }}</p>
                <p class="font-semibold text-slate-900">Tutee Satisfaction Score: {{ round($results['tutee_satisfaction']['average_score'], 2) }}</p>
            </div>

            <h2 class="mt-5 text-base font-semibold text-slate-900">Tutor Compatibility</h2>
        @foreach ($results['tutor_compatibility'] as $pref => $value)
            @if ($pref === 'average_score')
                <p class="mt-2 text-sm font-semibold text-slate-900">Tutor Compatibility Score: {{ round($value, 2) }}</p>
            @else
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ strtoupper($pref) }}</p>
                @foreach ($value as $dim => $score)
                    <p class="text-sm text-slate-700">{{ $dim }}: {{ round($score, 2) }}</p>
                @endforeach
            @endif
        @endforeach

            <h2 class="mt-5 text-base font-semibold text-slate-900">Tutor Performance</h2>
            <div class="mt-2 space-y-1 text-sm text-slate-700">
                <p>Mastery: {{ round($results['tutor_performance']['mastery'], 2) }}</p>
                <p>Clarity: {{ round($results['tutor_performance']['clarity'], 2) }}</p>
                <p>Responsiveness: {{ round($results['tutor_performance']['responsiveness'], 2) }}</p>
                <p>Engagement: {{ round($results['tutor_performance']['engagement'], 2) }}</p>
                <p>Preparedness: {{ round($results['tutor_performance']['preparedness'], 2) }}</p>
                <p class="font-semibold text-slate-900">Tutor Performance Evaluation Score: {{ round($results['tutor_performance']['average_score'], 2) }}</p>
            </div>
        </div>
    @endif
</x-layout>