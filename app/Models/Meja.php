<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    protected $table = 'meja';

    protected $primaryKey = 'id_meja';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'no_meja',
        'qr_code',
        'status',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja', 'id_meja');
    }
}
