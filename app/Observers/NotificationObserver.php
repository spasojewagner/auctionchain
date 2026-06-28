<?php

namespace App\Observers;

use App\Mail\UserNotificationMail;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

/**
 * Kada se kreira nova notifikacija u bazi, automatski se šalje i email korisniku.
 * Sve je u try/catch da neuspeh slanja mejla nikad ne pokvari licitiranje/aukciju.
 * Tokom seed-a / artisan komandi mejlovi se preskaču.
 */
class NotificationObserver
{
    public function created(Notification $notification): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        try {
            $user = $notification->user;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new UserNotificationMail($notification));
            }
        } catch (\Throwable $e) {
            logger()->warning('Email notifikacije nije poslat: ' . $e->getMessage());
        }
    }
}
