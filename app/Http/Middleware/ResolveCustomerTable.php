<?php

namespace App\Http\Middleware;

use App\Models\Meja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class ResolveCustomerTable
{
    public function handle(Request $request, Closure $next): Response
    {
        $kode = $request->query('meja');

        if (is_string($kode) && $kode !== '' && $request->hasSession()) {
            $meja = Meja::where('qr_code', $kode)->first();

            if ($meja) {
                // Meja takeaway selalu tersedia meski ada pesanan aktif.
                $meja->ensureAvailableIfTakeaway();

                $request->session()->put('meja', [
                    'id' => (int) $meja->id_meja,
                    'no' => (string) $meja->no_meja,
                    'takeaway' => $meja->isTakeaway(),
                ]);
            }
        }

        $meja = $request->hasSession() ? $request->session()->get('meja') : null;
        $isTakeaway = is_array($meja) && ! empty($meja['takeaway']);

        config([
            'perkoci.table.id_meja' => $meja['id'] ?? null,
            'perkoci.table.is_takeaway' => $isTakeaway,
            'perkoci.table.order_mode' => $isTakeaway ? 'Takeaway (Bungkus)' : 'Dine In',
            'perkoci.table.location' => $meja
                ? ($isTakeaway ? 'Takeaway Kasir • Bungkus' : 'Meja '.$meja['no'])
                : 'Scan QR di meja kamu',
        ]);

        return $next($request);
    }
}
