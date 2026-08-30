<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>High Scores - Snake Game</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-900 text-white min-h-screen font-sans">

    <div class="max-w-4xl mx-auto px-4 py-10">

        <!-- Header -->
        <div class="text-center mb-10">

            <h1 class="text-4xl font-extrabold text-emerald-400">
                🏆 HIGH SCORES
            </h1>

            <p class="text-slate-400 mt-2">
                Snake Game Leaderboard
            </p>

        </div>

        <!-- Score Table -->
        <div class="bg-slate-800 rounded-xl shadow-xl overflow-hidden">

            <table class="w-full">

                <thead class="bg-slate-700">

                    <tr>

                        <th class="px-6 py-4 text-left">
                            Rank
                        </th>

                        <th class="px-6 py-4 text-left">
                            Player
                        </th>

                        <th class="px-6 py-4 text-right">
                            Score
                        </th>

                        <th class="px-6 py-4 text-right">
                            Date
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($scores as $index => $score)

                        <tr class="border-b border-slate-700 hover:bg-slate-700">

                            <td class="px-6 py-4 font-bold">

                                @if($index == 0)
                                    🥇
                                @elseif($index == 1)
                                    🥈
                                @elseif($index == 2)
                                    🥉
                                @else
                                    {{ $index + 1 }}
                                @endif

                            </td>

                            <td class="px-6 py-4">
                                {{ $score->username }}
                            </td>

                            <td class="px-6 py-4 text-right text-emerald-400 font-bold text-lg">
                                {{ $score->score }}
                            </td>

                            <td class="px-6 py-4 text-right text-slate-400">
                                {{ $score->created_at->format('d M Y') }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4"
                                class="px-6 py-10 text-center text-slate-400">

                                No scores have been saved yet.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- Back Button -->
        <div class="text-center mt-8">

            <a href="/"
               class="inline-block bg-emerald-500 hover:bg-emerald-400
                      text-slate-900 font-bold py-3 px-8
                      rounded-full transition">

                ← Back to Game

            </a>

        </div>

    </div>

</body>
</html>