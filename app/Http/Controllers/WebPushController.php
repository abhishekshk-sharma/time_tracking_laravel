<?php
// app/Http/Controllers/WebPushController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\PushSubscription;

class WebPushController extends Controller
{
    /**
     * Store the PushSubscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $subscription = $user->updatePushSubscription(
                $request->endpoint,
                $request->keys['p256dh'],
                $request->keys['auth'],
                $request->contentEncoding ?? 'aesgcm'
            );

            Log::info('Push subscription saved for user: ' . $user->id);

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Push subscription error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete the specified PushSubscription.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $user->deletePushSubscription($request->endpoint);
            
            Log::info('Push subscription removed for user: ' . $user->id);

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Push subscription deletion error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}