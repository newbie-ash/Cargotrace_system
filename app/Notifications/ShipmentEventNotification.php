<?php

namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Shipment $shipment,
        public string $title,
        public string $message,
        public string $eventType = 'STATUS_CHANGE',
        public ?string $status = null
    ) {
        $this->status = $status ?? $shipment->current_status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'shipment_id' => $this->shipment->id,
            'shipment_number' => $this->shipment->shipment_number,
            'title' => $this->title,
            'message' => $this->message,
            'event_type' => $this->eventType,
            'status' => $this->status,
            'is_delayed' => (bool) $this->shipment->is_delayed,
            'occurred_at' => now()->toISOString(),
        ];
    }
}
