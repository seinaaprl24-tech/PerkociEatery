# HANDOFF — Perkoci Eatery (Frontend Only)

Dokumen serah-terima singkat untuk melanjutkan integrasi backend/auth. Semua halaman saat ini memakai data statis dari `config/`.

## 1. Struktur `resources/views`

- `layouts/app.blade.php` — HTML shell, font, Vite, toast stack, `@yield('body')`, `@stack('head'|'scripts')`.
- `layouts/customer.blade.php` — kontainer mobile `max-w-[430px]`, header, bottom nav, badge keranjang, padding safe-area.
- `layouts/staff.blade.php` — sidebar + drawer mobile + topbar untuk dashboard staff.
- `layouts/auth.blade.php` — panel brand desktop + area form.
- `cover.blade.php` — cover/splash, redirect otomatis ke `/menu`.
- `menu/index.blade.php`, `menu/show.blade.php` — katalog dan detail menu.
- `keranjang.blade.php`, `status.blade.php` — keranjang dan status pesanan.
- `staff/{manager,kasir,admin}.blade.php` — dashboard preview staff.
- `auth/login.blade.php` — halaman login preview.
- `components/logo.blade.php` — logo dari `public/images/`.
- `components/menu-thumb.blade.php` — foto menu / placeholder ikon (auto-detect file).
- `components/auth/login-form.blade.php`, `components/auth/alert.blade.php`.
- `components/ui/` — `badge`, `design-note`, `empty-state`, `field`, `modal`, `page-header`, `preview-banner`, `section`, `stat-card`, `table`.

Class CSS utama di `resources/css/app.css`: `card`, `panel`, `pill`, `pill-sm`, `btn-primary/secondary/ghost/danger/icon/sm`, `chip`, `chip-active`, `field`, `field-label`, `field-hint`, `field-error`, `field-invalid`, `login-spinner`, `badge-*`, `page-header`, `table-wrap`, `data-table`, `toast*`, `modal`, `scrollbar-none`, `no-tap-highlight`, `ms`, `ms-fill`, `ms-wght`.

## 2. Data statis yang harus diganti sumber nyata

| Data | Lokasi sekarang | Pemakai |
|---|---|---|
| Brand, cover, nav, meja | `config/perkoci.php` (`brand`, `cover`, `nav`, `table`) | `layouts/customer`, `layouts/auth`, `cover`, `menu/*`, `keranjang`, `status` |
| Katalog + kategori | `config/perkoci.php` (`menu`, `categories`) | `App\Support\Perkoci::menu()/menuBySlug()/categoryName()` → `menu/index`, `menu/show`, thumbnail admin |
| Keranjang + total | `config/perkoci.php` (`cart.items`) | `Perkoci::cartItems()/cartTotals()` → `keranjang`, bar katalog, badge nav, `statusOrder()` |
| Pesanan/status, QR | `config/perkoci.php` (`status`) | `Perkoci::statusOrder()`, `Perkoci::qrPattern()` → `status` |
| Form pemesan, catatan, pembayaran | `config/perkoci.php` (`customer`, `note`, `payment_methods`) | `keranjang`, `menu/show` |
| User/nav/role staff | `config/perkoci.php` (`staff`) | `layouts/staff`, `layouts/auth` |
| Dashboard staff (angka, tabel, stok) | `config/preview.php` (`roles.manager/kasir/admin`) | `staff/*` via `PreviewController` |
| Foto menu | `public/images/menu/{slug}.jpg|jpeg|png|webp` | `Perkoci::imagePath()`; fallback SVG Matcha |

Catatan: perubahan qty di katalog/keranjang hanya di sisi browser (tanpa persistensi). Saat backend siap, ganti dengan state server/session dan hapus perhitungan total di inline script.

## 3. Kontrak layout & props komponen

- `layouts.customer`: section `title`, `header` (kosong = sembunyikan header), `header_title` (mode detail), `back_href`, `bottomnav` (kosong = tanpa nav), `content`.
- `layouts.staff`: section `title`, `staff_role`, `active_nav`, `topbar_left`, `topbar_right`, `sidebar_footer`, `content`.
- `layouts.auth`: section `title`, `content`.
- `menu-thumb`: `item`, `size`, `width`, `height`.
- `ui.section`: `title`, `description`, `flush`; slot `actions`.
- `ui.table`: `head`, `caption`, `empty`; slot baris `<tr>`.
- `ui.page-header`: `title`, `subtitle`, `eyebrow`; slot aksi.
- `ui.stat-card`: `label`, `value`, `icon`, `hint`, `trend`, `up`, `tone`.
- `ui.badge`: `tone`, `icon`; `ui.design-note`: `title`.
- `ui.field`: `name`, `label`, `type`, `value`, `placeholder`, `hint`, `error`, `icon`, `required`.
- `ui.preview-banner`: `current`; `ui.modal`: `id`, `title`, `description`, `closeLabel`; `ui.empty-state`: `icon`, `title`, `description`.
- `auth.login-form`: `action`, `emailLabel`, `emailPlaceholder`, `submitLabel`, `error`, `preview`; `auth.alert`: `type`.

## 4. Bagian sementara yang harus dihapus saat integrasi

- Route `/login` dan `/preview/{manager,kasir,admin}` di `routes/web.php`.
- `app/Http/Controllers/PreviewController.php`.
- `config/preview.php` (termasuk `notice`, `figma_note`, `roles`).
- `resources/views/staff/*.blade.php` (kerangka dashboard, data contoh).
- `resources/views/components/ui/preview-banner.blade.php`.
- Blok "Preview Dashboard" di `resources/views/auth/login.blade.php`.
- Validasi/loading preview `[data-preview-auth]` di `resources/js/ui.js`.
- Tombol aksi `data-toast-message` di view staff (placeholder, belum terhubung).
- `resources/views/layouts/staff.blade.php` hanya dipakai dashboard preview; pertahankan hanya jika dipakai ulang untuk dashboard nyata.

## 5. Catatan versi & aset

- Laravel 12 (`laravel/framework ^12.0`), PHP `^8.2`.
- Tailwind CSS 4 (`@tailwindcss/vite`), Vite 7, Axios.
- Font: Plus Jakarta Sans (sans), Space Grotesk (display), Material Symbols Outlined (ikon CDN).
- Palet: brand `#195905`, brand-dark `#0f3d03`, brand-soft `#2e7d12`, khaki `#bdb76b`, cocoa `#3d2b1f`, cocoa-soft `#6f5b4c`, cream `#f9f8f3`, sand `#efeee9`, lime `#aaf68c`, butter `#ebe79a`, olive `#5f6020`, danger `#ba1a1a`.
- Logo: `public/images/logo-perkoci-trim.png` (via `config('perkoci.brand.logo')`).
- `public/favicon.ico` masih 0 byte; ganti sebelum produksi.
