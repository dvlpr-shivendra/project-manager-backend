<?php

namespace App\Http\Controllers;

use App\Services\LLMService;
use Illuminate\Http\Request;

class LLMController extends Controller
{
    public function rephrase(Request $request, LLMService $llm)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        return response()->json([
            'result' => $llm->rephrase($request->text),
        ]);
    }

    public function generateDescription(Request $request, LLMService $llm)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        return response()->json([
            'result' => $llm->generateDescription($request->title),
        ]);
    }

    public function generateTitle(Request $request, LLMService $llm)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        return response()->json([
            'result' => $llm->generateTitle($request->description),
        ]);
    }
}
