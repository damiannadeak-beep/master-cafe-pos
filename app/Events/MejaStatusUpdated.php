<?php

namespace App\Events;

use App\Models\Meja;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MejaStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meja;

    /**
     * Create a new event instance.
     */
    public function __construct(Meja $meja)
    {
        $this->meja = $meja;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('kasir-notifications'),
        ];
    }

    /**
     * Nama event yang dibroadcast ke client.
     */
    public function broadcastAs(): string
    {
        return 'MejaStatusUpdated';
    }

    /**
     * Data payload yang dikirim ke client.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->meja->id,
            'nama' => $this->meja->nama_meja_atau_nomor,
            'is_available' => (bool) $this->meja->is_available,
        ];
    }
}
