<?php

namespace App\Notifications;

use App\Core\Application\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserMentioned extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     *
     * @return void
     */
    public function __construct(public string $mentionUrl, public User $mentioner, public string $icon)
    {
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'path' => $this->mentionUrl,
            'icon' => $this->icon,
            'title' => static::name(),
            'lang' => [
                'key' => 'notifications.user_mentioned',
                'attrs' => [
                    'name' => $this->mentioner->name,
                ],
            ],
        ];
    }
}
