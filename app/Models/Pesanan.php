<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_meja',
        'nomor_antrean',
        'tanggal_pesan',
        'total_harga',
        'status_pesanan',
    ];

    protected $casts = [
        'tanggal_pesan' => 'datetime',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan', 'id_pesanan');
    }
}

