<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    use HasFactory;

    protected $table = 'metode_pembayaran';

    protected $primaryKey = 'id_metode';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama_metode',
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_metode', 'id_metode');
    }
}
