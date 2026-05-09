<?php

use App\Services\ChannelAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'youtube-channel-insight-api',
    ]);
});

Route::post('/analysis', function (Request $request, ChannelAnalysisService $analysisService) {
    $validated = $request->validate([
        'channel_url' => ['required', 'string', 'max:255'],
    ]);

    return response()->json([
        'data' => $analysisService->analyze($validated['channel_url']),
    ]);
});
