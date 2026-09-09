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
        'id_konsumen', 'guest_name', 'order_token', 'id_meja', 'id_kasir', 'tipe_pesanan', 'tanggal',
        'total', 'total_hpp', 'discount_amount', 'promo_id', 'status',
    ]; 

    public function getCustomerNameAttribute()
    {
        return $this->guest_name ?: ($this->konsumen?->name ?? 'Tamu');
    }

    public function getNamaPemesanAttribute()
    {
        return $this->guest_name ?: ($this->konsumen?->name ?? 'Tamu');
    } 

    public function detail_pesanan() 
    { 
        return $this->hasMany(DetailPesanan::class, 'id_pesanan'); 
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

    /**
     * Mengembalikan stok menu dan bahan baku yang sudah terpotong.
     */
    public function restoreStock()
    {
        foreach ($this->detail_pesanan as $detail) {
            $menu = Menu::lockForUpdate()->find($detail->id_menu);
            if ($menu) {
                // Kembalikan stok produk jadi/menu
                $menu->increment('stok', $detail->jumlah);
                
                // Kembalikan stok bahan baku yang terikat dengan menu
                $bahanIds = $menu->bahans->pluck('id')->all();
                $bahans = \App\Models\Bahan::whereIn('id', $bahanIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($menu->bahans as $bahan) {
                    $dibutuhkan = $bahan->pivot->jumlah_dibutuhkan * $detail->jumlah;
                    $bahans->get($bahan->id)?->increment('stok', $dibutuhkan);
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

            // Opsional: hapus record pembayaran jika ada
            if ($this->pembayaran) {
                $this->pembayaran->delete();
            }

            // Soft delete agar pesanan tidak muncul di list aktif
            $this->delete();
        }
    }
}