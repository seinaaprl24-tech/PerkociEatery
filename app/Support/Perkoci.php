<?php

namespace App\Support;

use App\Models\Produk;
use Illuminate\Support\Str;

/**
 * Helper ringan untuk layer frontend Perkoci Eatery.
 *
 * Menu kini diambil dari database (tabel produk + stok), sama dengan sumber
 * data endpoint /api/produk. config/perkoci.php hanya dipakai untuk urutan
 * tampilan, satuan, deskripsi cadangan, dan teks statis lain.
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

    /**
     * Di database "Nasi" dan "Lontong" dua produk, sedangkan config/foto
     * memakai satu entri "nasi-lontong". Alias ini menyatukan urutan & foto.
     */
    private const SLUG_ALIAS = [
        'nasi' => 'nasi-lontong',
        'lontong' => 'nasi-lontong',
    ];

    /**
     * Cache per-request agar menu tidak di-query berulang.
     */
    private static ?array $menuCache = null;

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

    /**
     * Menu aktif dari database, urut sesuai config (yang tidak ada di
     * config ditaruh di akhir).
     *
     * Kunci tiap item: id, slug, name, price, description, category, unit,
     * image, stock, available.
     */
    public static function menu(): array
    {
        if (self::$menuCache !== null) {
            return self::$menuCache;
        }

        $configBySlug = [];
        $order = [];

        foreach (config('perkoci.menu', []) as $index => $entry) {
            $configBySlug[$entry['slug']] = $entry;
            $order[$entry['slug']] = $index;
        }

        $items = [];

        foreach (Produk::with('stok')->where('status', 'aktif')->get() as $produk) {
            $slug = Str::slug((string) $produk->nama_produk);
            $fallback = $configBySlug[$slug] ?? [];
            $alias = self::SLUG_ALIAS[$slug] ?? '';
            $category = self::categoryIdFor((string) $produk->kategori);
            $stock = (int) ($produk->stok?->jumlah_stok ?? 0);

            $description = trim((string) ($produk->deskripsi ?? ''));

            $items[] = [
                'id' => (int) $produk->id_produk,
                'slug' => $slug,
                'name' => (string) $produk->nama_produk,
                'price' => (int) round((float) $produk->harga),
                'description' => $description !== '' ? $description : (string) ($fallback['description'] ?? ''),
                'category' => $category,
                'unit' => $fallback['unit'] ?? ($category === 'minuman' ? 'gelas' : 'porsi'),
                'image' => self::imagePath($slug) ?? self::imagePath($alias),
                'stock' => $stock,
                'available' => $stock > 0,
                '_position' => $order[$slug] ?? $order[$alias] ?? (1000 + (int) $produk->id_produk),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            return [$a['_position'], $a['id']] <=> [$b['_position'], $b['id']];
        });

        foreach ($items as &$item) {
            unset($item['_position']);
        }

        unset($item);

        return self::$menuCache = $items;
    }

    /**
     * Data ringan untuk JavaScript (keranjang): id => detail menu.
     */
    public static function catalog(?array $menu = null): array
    {
        $menu = $menu ?? self::menu();
        $catalog = [];

        foreach ($menu as $item) {
            $catalog[$item['id']] = [
                'id' => $item['id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'price' => $item['price'],
                'unit' => $item['unit'],
                'stock' => $item['stock'],
                'available' => $item['available'],
                'url' => url('/menu/'.$item['slug']),
            ];
        }

        return $catalog;
    }

    /**
     * Kategori database (mis. "Signature Coffee", "Snack & Dessert") dipetakan
     * ke dua kategori tampilan di config: minuman atau makanan.
     */
    private static function categoryIdFor(string $kategori): string
    {
        $value = Str::lower($kategori);

        foreach (['kopi', 'coffee', 'teh', 'tea', 'minum', 'jus', 'juice', 'susu', 'drink', 'beverage'] as $keyword) {
            if (str_contains($value, $keyword)) {
                return 'minuman';
            }
        }

        return 'makanan';
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

    /**
     * Keranjang sungguhan disimpan di browser (localStorage) dan dirender
     * oleh JavaScript, jadi dari sisi server selalu kosong.
     */
    public static function cartItems(): array
    {
        return [];
    }

    /**
     * Keranjang CONTOH dari config. Hanya dipakai halaman /status sampai
     * halaman itu disambungkan ke API.
     */
    public static function demoCartItems(): array
    {
        $items = config('perkoci.cart.items', []);

        foreach ($items as &$item) {
            $item['image'] = self::imagePath((string) ($item['slug'] ?? ''));
        }

        unset($item);

        return $items;
    }

    /**
     * Hitung subtotal dan total dari daftar item.
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
     * PENTING: masih data contoh dari config. Saat halaman status
     * disambungkan ke GET /api/pesanan/{id}, ganti isi method ini.
     */
    public static function statusOrder(): array
    {
        $status = config('perkoci.status', []);
        $table = self::table();
        $items = self::demoCartItems();
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

    /**
     * Data meja aktif. Nilai location dan id_meja ditimpa per-request oleh
     * middleware ResolveCustomerTable (berdasarkan QR / sesi).
     */
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
