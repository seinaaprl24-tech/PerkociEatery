<?php

namespace App\Support;

/**
 * Helper ringan untuk layer frontend Perkoci Eatery.
 * Semua data statis berasal dari config/perkoci.php.
 */
class Perkoci
{
    /**
     * Urutan prioritas ekstensi foto menu di public/images/menu/.
     */
    private const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Aset lama yang tetap dipakai bila foto baru belum tersedia.
     */
    private const LEGACY_IMAGES = [
        'matcha-latte' => 'images/menu/creamy-matcha-latte.svg',
    ];

    /* ------------------------------------------------------------------
     | Formatting
     | ------------------------------------------------------------------ */

    public static function rupiah(int|float $amount, bool $withPrefix = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');

        return $withPrefix ? 'Rp '.$formatted : $formatted;
    }

    /* ------------------------------------------------------------------
     | Katalog
     | ------------------------------------------------------------------ */

    public static function menu(): array
    {
        $items = config('perkoci.menu', []);

        foreach ($items as &$item) {
            $item['image'] = self::imagePath((string) ($item['slug'] ?? ''));
        }

        unset($item);

        return $items;
    }

    /**
     * Path foto menu berdasarkan slug: cek jpg, jpeg, png, webp, lalu aset lama.
     * Tidak ada foto = null (view menampilkan placeholder ikon).
     */
    public static function imagePath(string $slug): ?string
    {
        if ($slug === '') {
            return null;
        }

        foreach (self::PHOTO_EXTENSIONS as $extension) {
            $relative = 'images/menu/'.$slug.'.'.$extension;

            if (is_file(public_path($relative))) {
                return $relative;
            }
        }

        return self::LEGACY_IMAGES[$slug] ?? null;
    }

    public static function menuBySlug(string $slug): ?array
    {
        foreach (self::menu() as $item) {
            if (($item['slug'] ?? null) === $slug) {
                return $item;
            }
        }

        return null;
    }

    public static function categoryName(string $id): string
    {
        foreach (config('perkoci.categories', []) as $category) {
            if (($category['id'] ?? null) === $id) {
                return $category['name'];
            }
        }

        return ucfirst(str_replace('-', ' ', $id));
    }

    /* ------------------------------------------------------------------
     | Keranjang
     | ------------------------------------------------------------------ */

    public static function cartItems(): array
    {
        $items = config('perkoci.cart.items', []);

        foreach ($items as &$item) {
            $item['image'] = self::imagePath((string) ($item['slug'] ?? ''));
        }

        unset($item);

        return $items;
    }

    /**
     * Hitung subtotal dan total dari data config keranjang.
     * Total = subtotal, tanpa biaya tambahan.
     */
    public static function cartTotals(?array $items = null): array
    {
        $items = $items ?? self::cartItems();

        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['qty'] ?? 1);
        }

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'count' => count($items),
        ];
    }

    /* ------------------------------------------------------------------
     | Status Pesanan
     | ------------------------------------------------------------------ */

    /**
     * Ringkasan pesanan aktif untuk halaman /status.
     * PENTING: masih data contoh dari config. Saat endpoint backend siap,
     * ganti isi method ini dengan respons API (lihat catatan di config).
     */
    public static function statusOrder(): array
    {
        $status = config('perkoci.status', []);
        $table = self::table();
        $items = self::cartItems();
        $totals = self::cartTotals($items);

        $status['location'] = $table['location'] ?? '';
        $status['items_data'] = $items;
        $status['totals'] = $totals;

        return $status;
    }

    /**
     * Matriks QR placeholder yang deterministik dari sebuah kode.
     * Bukan QR valid untuk scan — hanya pengganti visual sampai backend
     * menyediakan payload pembayaran sungguhan.
     */
    public static function qrPattern(string $seed, int $size = 21): array
    {
        $grid = array_fill(0, $size, array_fill(0, $size, false));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        $finder = static function (int $ox, int $oy) use (&$grid, &$reserved, $size): void {
            for ($y = 0; $y < 7; $y++) {
                for ($x = 0; $x < 7; $x++) {
                    $gx = $ox + $x;
                    $gy = $oy + $y;

                    if ($gx < 0 || $gy < 0 || $gx >= $size || $gy >= $size) {
                        continue;
                    }

                    $edge = $x === 0 || $x === 6 || $y === 0 || $y === 6;
                    $core = $x >= 2 && $x <= 4 && $y >= 2 && $y <= 4;

                    $grid[$gy][$gx] = $edge || $core;
                    $reserved[$gy][$gx] = true;
                }
            }
        };

        $finder(0, 0);
        $finder($size - 7, 0);
        $finder(0, $size - 7);

        $hash = hash('sha256', $seed, true);
        $length = strlen($hash);
        $i = 0;

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($reserved[$y][$x]) {
                    continue;
                }

                $byte = ord($hash[$i % $length]);
                $grid[$y][$x] = (($byte >> ($i % 8)) & 1) === 1;
                $i++;
            }
        }

        return $grid;
    }

    /* ------------------------------------------------------------------
     | Util
     | ------------------------------------------------------------------ */

    public static function table(): array
    {
        return config('perkoci.table', []);
    }

    public static function badgeClasses(?array $badge): string
    {
        return match ($badge['style'] ?? 'olive') {
            'brand' => 'bg-brand text-white',
            'cocoa' => 'bg-cocoa text-white',
            'butter' => 'bg-butter text-cocoa',
            'lime' => 'bg-lime text-cocoa',
            'olive' => 'bg-olive text-white',
            default => 'bg-olive text-white',
        };
    }
}
