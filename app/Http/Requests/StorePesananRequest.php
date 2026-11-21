<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    /**
     * Menentukan apakah request diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi data pesanan.
     */
    public function rules(): array
    {
        return [
            // Informasi meja & tipe pesanan
            'id_meja' => [
                'required',
                'exists:meja,id_meja',
            ],

            'tipe_pesanan' => [
                'nullable',
                'in:dine_in,takeaway',
            ],

            // Data pemesan
            'nama_pemesan' => [
                'required',
                'string',
                'max:255',
            ],

            'no_whatsapp' => [
                'nullable',
                'string',
                'max:20',
            ],

            'catatan' => [
                'nullable',
                'string',
                'max:500',
            ],

            // Metode pembayaran
            'metode_pembayaran' => [
                'nullable',
                'in:tunai,qris',
            ],

            // Daftar produk yang dipesan
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.id_produk' => [
                'required',
                'exists:produk,id_produk',
            ],

            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            // Pilihan tambahan produk
            'items.*.topping' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.gula' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.es' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.catatan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Pesan kesalahan validasi.
     */
    public function messages(): array
    {
        return [
            'id_meja.required' => 'Meja belum dipilih.',
            'id_meja.exists' => 'Meja tidak ditemukan.',

            'tipe_pesanan.in' => 'Tipe pesanan tidak valid.',

            'nama_pemesan.required' => 'Nama pemesan wajib diisi.',

            'items.required' => 'Pesanan belum memiliki produk.',
            'items.min' => 'Minimal harus ada satu produk.',

            'items.*.id_produk.required' => 'Produk tidak valid.',
            'items.*.id_produk.exists' => 'Produk tidak ditemukan.',

            'items.*.jumlah.required' => 'Jumlah produk wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah produk minimal satu.',
        ];
    }
}
