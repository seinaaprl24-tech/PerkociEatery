<?php

namespace App\Http\Middleware;

use App\Models\Meja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menentukan meja aktif customer.
 *
 * - Link QR berbentuk  /menu?meja=MEJA-01  (nilai = kolom qr_code pada meja).
 * - Meja yang valid disimpan di sesi, jadi halaman lain tidak perlu parameter.
 * - Hasilnya ditulis ke config('perkoci.table') supaya semua view yang
 *   sudah memakai config tersebut otomatis menampilkan meja yang benar.
 */
class ResolveCustomerTable
{
    public function handle(Request $request, Closure $next): Response
    {
        $kode = $request->query('meja');

        if (is_string($kode) && $kode !== '' && $request->hasSession()) {
            $meja = Meja::where('qr_code', $kode)->first();

            if ($meja) {
                $request->session()->put('meja', [
                    'id' => (int) $meja->id_meja,
                    'no' => (string) $meja->no_meja,
                ]);
            }
        }

        $meja = $request->hasSession() ? $request->session()->get('meja') : null;

        config([
            'perkoci.table.id_meja' => $meja['id'] ?? null,
            'perkoci.table.location' => $meja ? 'Meja '.$meja['no'] : 'Scan QR di meja kamu',
        ]);

        return $next($request);
    }
}
