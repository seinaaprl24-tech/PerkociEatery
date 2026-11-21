<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    /**
     * QR code meja virtual untuk pesanan takeaway lewat kasir.
     */
    public const TAKEAWAY_QR_CODE = 'TAKEAWAY-KASIR';

    public const TAKEAWAY_NAME = 'Takeaway Kasir';

    protected $table = 'meja';

    protected $primaryKey = 'id_meja';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'no_meja',
        'qr_code',
        'status',
        'kode_qr',
    ];

    /**
     * Apakah meja ini adalah meja virtual takeaway kasir?
     */
    public function isTakeaway(): bool
    {
        return strtoupper(trim((string) $this->qr_code)) === self::TAKEAWAY_QR_CODE
            || stripos((string) $this->no_meja, 'takeaway') !== false;
    }

    /**
     * Meja takeaway tidak boleh berstatus "terisi". Statusnya selalu
     * "tersedia" agar banyak pelanggan bisa memesan bungkus bersamaan.
     */
    public function ensureAvailableIfTakeaway(): void
    {
        if ($this->isTakeaway() && $this->status !== 'tersedia') {
            $this->forceFill(['status' => 'tersedia'])->save();
        }
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja', 'id_meja');
    }
}

