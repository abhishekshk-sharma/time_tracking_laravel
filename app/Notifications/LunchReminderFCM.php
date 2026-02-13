<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LunchReminderFCM extends Notification
{
    use Queueable;

    protected $lunchEndTime;
    protected $employeeName;

    public function __construct(Carbon $lunchEndTime, string $employeeName)
    {
        $this->lunchEndTime = $lunchEndTime;
        $this->employeeName = $employeeName;
    }

    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable)
    {
        $subscriptions = $notifiable->pushSubscriptions;
        
        foreach ($subscriptions as $subscription) {
            $this->sendFCMNotification($subscription->endpoint, [
                'title' => '🍽️ Lunch Break Reminder',
                'body' => "Hi {$this->employeeName}, your lunch break ends at {$this->lunchEndTime->format('h:i A')}. Please return in 5 minutes!",
                'icon' => '/images/notification-icon.png',
                'badge' => '/images/badge-icon.png',
                'data' => [
                    'url' => route('dashboard'),
                    'type' => 'lunch_reminder'
                ]
            ]);
        }
    }

    private function sendFCMNotification($endpoint, $data)
    {
        // Extract FCM token from endpoint
        preg_match('/\/send\/(.+)$/', $endpoint, $matches);
        if (!isset($matches[1])) {
            return;
        }
        
        $fcmToken = $matches[1];
        $serverKey = config('services.fcm.key') ?? env('FCM_SERVER_KEY');

        $notification = [
            'to' => $fcmToken,
            'notification' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'icon' => $data['icon'] ?? '/icon.png',
                'click_action' => $data['data']['url'] ?? '/',
            ],
            'data' => $data['data'] ?? []
        ];

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        \Log::info('FCM Notification sent', ['result' => $result]);
    }
}
