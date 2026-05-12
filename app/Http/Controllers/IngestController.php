<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngestController extends Controller
{
    public function __invoke(Request $request, int $channel): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'user_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $channelModel = Channel::where('id', $channel)
            ->where('url_key', $request->query('url_key'))
            ->first();

        if (! $channelModel) {
            return response()->json(['error' => 'Invalid channel or URL key.'], 404);
        }

        $channelModel->events()->create([
            'title' => $validated['title'],
            'user_id' => $validated['user_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }
}
