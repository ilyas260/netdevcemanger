<?php

namespace App\Notifications;

use App\Models\TonerAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TonerLowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TonerAlert $alert;

    /**
     * Create a new notification instance.
     */
    public function __construct(TonerAlert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Alerte Toner Bas : ' . $this->alert->device->name)
            ->line('Le niveau de toner pour la couleur ' . $this->alert->toner_color . ' est critique.')
            ->line('Niveau actuel : ' . $this->alert->level_pct . '%')
            ->line('Seuil d\'alerte : ' . $this->alert->threshold_pct . '%')
            ->action('Voir l\'imprimante', url('/devices/' . $this->alert->device_id))
            ->line('Merci d\'intervenir rapidement.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'device_id' => $this->alert->device_id,
            'device_name' => $this->alert->device->name,
            'toner_color' => $this->alert->toner_color,
            'level' => $this->alert->level_pct,
            'message' => 'Niveau de toner bas (' . $this->alert->level_pct . '%) pour ' . $this->alert->toner_color,
        ];
    }
}
