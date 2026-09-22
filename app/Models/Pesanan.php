<?php 
 
namespace App\Models; 
 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\DetailPesanan; 
use App\Models\Pembayaran; 
use App\Models\User; 
use App\Models\Meja; 
 
class Pesanan extends Model 
{ 
    use SoftDeletes;
    
    protected $table = 'pesanan'; 

    protected $fillable = [
        'id_konsumen', 'guest_name', 'guest_phone', 'order_token', 'id_meja', 'id_kasir', 'tipe_pesanan', 'tanggal',
        'total', 'total_hpp', 'discount_amount', 'promo_id', 'status',
    ]; 

    public function getCustomerNameAttribute()
    {
        if (!empty($this->guest_name)) {
            return $this->guest_name;
        }
        if ($this->relationLoaded('konsumen')) {
            return $this->konsumen?->name ?? 'Tamu';
        }
        if ($this->id_konsumen) {
            return $this->konsumen?->name ?? 'Tamu';
        }
        return 'Tamu';
    }

    public function getNamaPemesanAttribute()
    {
        return $this->customer_name;
    } 

    public function detail_pesanan() 
    { 
        return $this->hasMany(DetailPesanan::class, 'id_pesanan'); 
    } 

    public function detailPesanan() 
    { 
        return $this->detail_pesanan(); 
    } 

    public function pembayaran() 
    { 
        return $this->hasOne(Pembayaran::class, 'id_pesanan'); 
    } 

    public function konsumen() 
    { 
        return $this->belongsTo(User::class, 'id_konsumen'); 
    } 

    public function kasir() 
    { 
        return $this->belongsTo(User::class, 'id_kasir'); 
    } 

    public function meja() 
    { 
        return $this->belongsTo(Meja::class, 'id_meja'); 
    } 

    public function rating()
    {
        return $this->hasOne(Rating::class, 'id_pesanan');
    }

    public function voidLog()
    {
        return $this->hasOne(VoidLog::class, 'pesanan_id');
    }

    /**
     * Mengembalikan stok menu dan bahan baku saat pesanan dibatalkan.
     */
    public function restoreStock()
    {
        $this->loadMissing(['detail_pesanan.menu.bahans']);

        foreach ($this->detail_pesanan as $detail) {
            if ($detail->menu) {
                // Kembalikan stok menu
                $detail->menu->increment('stok', $detail->jumlah);

                // Kembalikan stok bahan baku jika menu memiliki resep
                if ($detail->menu->bahans && $detail->menu->bahans->isNotEmpty()) {
                    foreach ($detail->menu->bahans as $bahan) {
                        $jumlahDibutuhkan = (float) ($bahan->pivot->jumlah_dibutuhkan ?? 0);
                        if ($jumlahDibutuhkan > 0) {
                            $bahan->increment('stok', $jumlahDibutuhkan * $detail->jumlah);
                        }
                    }
                }
            }
        }
    }

    /**
     * Membatalkan pesanan: set status cancelled dan kembalikan stok.
     */
    public function cancelOrder()
    {
        if ($this->status !== 'cancelled') {
            $this->update(['status' => 'cancelled']);
            $this->restoreStock();
            
            // Kembalikan status meja menjadi tersedia jika ada
            if ($this->id_meja) {
                $meja = Meja::find($this->id_meja);
                if ($meja) {
                    $meja->update(['is_available' => true]);
                    try {
                        broadcast(new \App\Events\MejaStatusUpdated($meja));
                    } catch (\Throwable $e) {
                        // ignore broadcast error if offline
                    }
                }
            }

            // Update status pembayaran menjadi cancelled agar riwayat/jejak audit transaksi tidak hilang
            if ($this->pembayaran) {
                $this->pembayaran->update(['status' => 'cancelled']);
            }

            // Soft delete agar pesanan tidak muncul di list aktif
            $this->delete();
        }
    }
}