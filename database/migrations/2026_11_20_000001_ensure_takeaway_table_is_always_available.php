<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Meja takeaway adalah meja virtual multi-session. Statusnya tidak
     * boleh tertinggal sebagai "terisi" seperti meja dine-in.
     */
    public function up(): void
    {
        DB::table('meja')
            ->where(function ($query) {
                $query->where('qr_code', 'TAKEAWAY-KASIR')
                    ->orWhere('no_meja', 'like', '%takeaway%');
            })
            ->update([
                'status' => 'tersedia',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Tidak ada perubahan skema yang perlu dibalik.
    }
};
