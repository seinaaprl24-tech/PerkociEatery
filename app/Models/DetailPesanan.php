<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pesanan';

    protected $primaryKey = 'id_detail';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_pesanan',
        'id_produk',
        'jumlah',
        'harga',
        'subtotal',
        'topping',
        'gula',
        'es',
        'catatan',
        'customisasi',
    ];

    protected $casts = [
        'customisasi' => 'array',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}

