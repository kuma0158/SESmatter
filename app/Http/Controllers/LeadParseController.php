<?php

namespace App\Http\Controllers;

use App\Services\LeadParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LeadParseController extends Controller
{
    public function __invoke(Request $request, LeadParserService $parser): JsonResponse
    {
        $data = $request->validate([
            'raw_text' => ['required', 'string', 'min:10', 'max:20000'],
        ]);

        try {
            $parsed = $parser->parse($data['raw_text']);
        } catch (RuntimeException $e) {
            Log::warning('LeadParse failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => $e->getMessage(),
                'fallback' => true,
            ], 422);
        }

        return response()->json([
            'message' => 'parsed',
            'data' => $parsed,
            'raw_text' => $data['raw_text'],
        ]);
    }
}
