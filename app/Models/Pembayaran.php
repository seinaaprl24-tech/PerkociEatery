<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_pesanan',
        'id_metode',
        'jumlah_bayar',
        'kembalian',
        'status_pembayaran',
        'tanggal_pembayaran',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'id_metode', 'id_metode');
    }

    public function transaksi()
    {
        return $this->hasOne(Transaksi::class, 'id_pembayaran', 'id_pembayaran');
    }
}
