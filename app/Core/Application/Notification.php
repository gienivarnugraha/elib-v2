<?php

namespace App\Core\Application;

use App\Core\Contracts\Metable;
use App\Core\Facades\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Str;

class Notification extends BaseNotification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  \App\Innoclapps\Contracts\Metable  $notifiable
     * @return array
     */
    public function via(Metable $notifiable)
    {
        // First, get the notifiable notifications settings
        $settings = Application::notificationSettings(static::key(), $notifiable);

        // Next we will check if the notifiable has notifications settings configured, if nothing configured
        // we will notify via all channels, by default notifications are enabled for all channels
        if (count($settings) === 0) {
            return static::availableChannels();
        }

        // Next, we will filter the channels the user specifically turned off notifications
        $except = array_keys(array_filter($settings, fn ($notify) => $notify === false));

        return array_values(array_diff(static::availableChannels(), $except));
    }

    /**
     * Get the mail representation of the notification.
     *
     * NOTE: When using database mail templates the locale
     * must be configured for the Mailable
     *
     * @param  \App\Innoclapps\MailableTemplates\MailableTemplate  $mailable
     * @param  \App\Innoclapps\Contracts\Metable  $notifiable
     * @return \App\Innoclapps\MailableTemplates\MailableTemplate
     */
    /* public function viaMailableTemplate(MailableTemplate $mailable, Metable $notifiable)
    {
        if ($notifiable instanceof HasLocalePreference) {
            $mailable->locale($notifiable->preferredLocale());
        }

        // Automatically add the notifiable as "To"
        if (count($mailable->to) === 0 && is_a($notifiable, Innoclapps::getUserRepository()->model(), true)) {
            $mailable->to($notifiable);
        }

        return $mailable;
    } */

    /**
     * Determine if the notification should be sent.
     *
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend(Metable $notifiable, $channel)
    {
        if (Application::notificationsDisabled()) {
            return false;
        }

        // When the user turned off all notifications, only the broadcast will be available
        // In this case, we don't need to send any notification as the broadcast will broadcast invalid notification
        return ! ($channel === 'broadcast' && count($this->via($notifiable)) === 1);
    }

    /**
     * Get the notification available delivery channels
     */
    public static function availableChannels(): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the notification unique key identifier
     */
    public static function key(): string
    {
        return Str::snake(class_basename(get_called_class()), '-');
    }

    /**
     * Get the displayable name of the notification
     */
    public static function name(): string
    {
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Get the notification description
     */
    public static function description(): ?string
    {
        return null;
    }
}
