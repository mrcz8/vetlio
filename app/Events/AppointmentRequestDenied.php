<?php

namespace App\Events;

use App\Models\AppointmentRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentRequestDenied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AppointmentRequest $appointmentRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(AppointmentRequest $appointmentRequest)
    {
        $this->appointmentRequest = $appointmentRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
