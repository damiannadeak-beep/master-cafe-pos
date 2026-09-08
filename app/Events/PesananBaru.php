<?php

namespace App\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesananBaru implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesanan;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Pesanan $pesanan)
    {
        if ($pesanan->id_meja && !$pesanan->relationLoaded('meja')) {
            $pesanan->load('meja');
        }
        $this->pesanan = $pesanan;
        $mejaStr = ($pesanan->relationLoaded('meja') && $pesanan->meja) ? $pesanan->meja->nama_meja_atau_nomor : ($pesanan->id_meja ? 'Meja' : 'Takeaway');
        $this->message = "Pesanan Baru #$pesanan->id dari $mejaStr";
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
        return 'PesananBaru';
    }

    /**
     * Data payload yang dikirim ke client.
     */
    public function broadcastWith(): array
    {
        $mejaStr = ($this->pesanan->relationLoaded('meja') && $this->pesanan->meja) 
            ? $this->pesanan->meja->nama_meja_atau_nomor 
            : ($this->pesanan->id_meja ? (\App\Models\Meja::find($this->pesanan->id_meja)?->nama_meja_atau_nomor ?? 'Meja') : 'Takeaway');

        return [
            'id' => $this->pesanan->id,
            'meja' => $mejaStr,
            'total' => (float) $this->pesanan->total,
            'message' => "Pesanan Baru #{$this->pesanan->id} ({$mejaStr})",
            'created_at' => $this->pesanan->created_at ? $this->pesanan->created_at->format('H:i') : date('H:i'),
        ];
    }
}
