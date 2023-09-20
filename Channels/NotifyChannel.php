<?php

declare(strict_types=1);

namespace Modules\Common\Channels;

use Guanguans\Notify\Factory;
use Illuminate\Notifications\Notification;

class NotifyChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return array|null
     */
    public function send(mixed $notifiable, Notification $notification): ?array
    {
        $message = $notification->toNotify($notifiable);

        $channel = $notifiable->routeNotificationFor('notify');

        $config = config("notify.channels.{$channel}");

        if (empty($config['driver'])) {
            throw new \RuntimeException('The notify driver in use does not support channels.');
        }

        return Factory::{$config['driver']}(array_filter_filled($config['config']))->setMessage($message)->send();
    }
}
