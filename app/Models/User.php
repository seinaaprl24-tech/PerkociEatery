<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang dipakai sebagai identitas login staf.
     * Sesuai ERD, staf melakukan login memakai "username" (bukan email).
     */
    public const AUTH_CREDENTIAL_FIELD = 'username';

    protected $primaryKey = 'id_user';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_user', 'id_user');
    }

    public function pemasukan()
    {
        return $this->hasMany(Pemasukan::class, 'id_user', 'id_user');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_user', 'id_user');
    }

    /**
     * Cek apakah pengguna memiliki salah satu role yang diberikan.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
