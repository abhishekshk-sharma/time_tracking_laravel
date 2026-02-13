<?php
// app/Notifications/LunchReminderNotification.php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LunchReminderNotification extends Notification
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
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🍽️ Lunch Break Reminder')
            ->icon('/images/notification-icon.png')
            ->body("Hi {$this->employeeName}, your lunch break ends at {$this->lunchEndTime->format('h:i A')}. Please return in 5 minutes!")
            ->action('View Dashboard', 'view_dashboard')
            ->data(['url' => route('dashboard')])
            ->badge('/images/badge-icon.png');
    }
}