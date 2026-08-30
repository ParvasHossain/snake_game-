<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'score' => 'required|integer|min:0',
        ]);

        $score = Score::create([
            'username' => $validated['username'],
            'score' => $validated['score'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Score saved successfully!',
            'data' => $score
        ]);
    }

    // Show scores from database
    public function index()
    {
        $scores = Score::orderBy('score', 'desc')
                       ->orderBy('created_at', 'asc')
                       ->get();

        return view('scores', compact('scores'));
    }
}