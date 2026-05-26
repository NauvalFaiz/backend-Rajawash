<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send an in-app notification and optionally an FCM push notification.
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param array $data
     * @return \App\Models\Notification
     */
    public static function send($userId, $title, $body, $data = [])
    {
        // 1. Create DB Notification
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        // 2. Mock Firebase Cloud Messaging Dispatch
        $user = User::find($userId);
        if ($user && $user->fcm_token) {
            // Here you can use GuzzleHTTP or Laravel Http facade to send payload to FCM
            // e.g., Http::withToken(config('services.fcm.key'))->post('https://fcm.googleapis.com/fcm/send', [...])
            Log::info("FCM Push Notification triggered for User #{$userId}", [
                'fcm_token' => $user->fcm_token,
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);
        } else {
            Log::info("No FCM token found for User #{$userId}. Only DB notification saved.");
        }

        return $notification;
    }
}
