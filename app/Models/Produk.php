<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $primaryKey = 'id_produk';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama_produk',
        'kategori',
        'harga',
        'deskripsi',
        'status',
    ];

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_produk', 'id_produk');
    }

    public function stok()
    {
        return $this->hasOne(Stok::class, 'id_produk', 'id_produk');
    }
}
