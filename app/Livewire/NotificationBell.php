<?php

namespace App\Livewire;

use App\Models\ErrorLog;
use Livewire\Component;

class NotificationBell extends Component
{
    public $open = false;
    public $notifications = [];
    public $unreadCount = 0;

    protected $listeners = ['refreshNotifications' => '$refresh'];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $logs = ErrorLog::with('device')
            ->unresolved()
            ->latest('logged_at')
            ->take(15)
            ->get();

        $this->notifications = $logs->map(function ($log) {
            return [
                'id'       => $log->id,
                'message'  => $log->message,
                'type'     => $log->error_type,
                'severity' => $log->severity,
                'device'   => $log->device?->name ?? 'Inconnu',
                'ip'       => $log->device?->ip_address ?? '',
                'time'     => $log->logged_at?->diffForHumans() ?? '',
            ];
        })->toArray();

        $this->unreadCount = min(count($this->notifications), 99);
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->loadNotifications();
        }
    }

    public function dismiss($id): void
    {
        ErrorLog::where('id', $id)->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);
        $this->loadNotifications();
    }

    public function dismissAll(): void
    {
        ErrorLog::unresolved()->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);
        $this->open = false;
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
