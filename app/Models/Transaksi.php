<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $primaryKey = 'id_transaksi';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_pembayaran',
        'id_user',
        'tanggal_transaksi',
        'status_transaksi',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function pemasukan()
    {
        return $this->hasMany(Pemasukan::class, 'id_transaksi', 'id_transaksi');
    }
}
