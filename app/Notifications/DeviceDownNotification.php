<?php

namespace App\Notifications;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviceDownNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Device $device;

    /**
     * Create a new notification instance.
     */
    public function __construct(Device $device)
    {
        $this->device = $device;
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
            ->subject('ALERTE : Appareil Hors Ligne - ' . $this->device->name)
            ->line('L\'appareil ' . $this->device->name . ' (IP: ' . $this->device->ip_address . ') est injoignable.')
            ->line('Dernière détection : ' . ($this->device->last_seen_at ? $this->device->last_seen_at->format('d/m/Y H:i:s') : 'Jamais'))
            ->action('Lancer un diagnostic', url('/devices/' . $this->device->id . '/ping-history'))
            ->line('Veuillez vérifier l\'alimentation ou la connectivité réseau de l\'équipement.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'device_id' => $this->device->id,
            'device_name' => $this->device->name,
            'ip_address' => $this->device->ip_address,
            'message' => 'L\'appareil ' . $this->device->name . ' est hors ligne.',
            'severity' => 'CRITICAL',
        ];
    }
}
