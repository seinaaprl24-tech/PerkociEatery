<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemasukan extends Model
{
    use HasFactory;

    protected $table = 'pemasukan';

    protected $primaryKey = 'id_pemasukan';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_transaksi',
        'id_user',
        'jumlah',
        'keterangan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
