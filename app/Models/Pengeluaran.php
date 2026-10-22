<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $primaryKey = 'id_pengeluaran';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_user',
        'jumlah',
        'keterangan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
