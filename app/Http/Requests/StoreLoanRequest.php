<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'book_ids' => ['required', 'array', 'min:1', 'max:10'],
            'book_ids.*' => ['required', 'integer', 'distinct', 'exists:books,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Pilih anggota yang akan meminjam.',
            'member_id.integer' => 'Anggota yang dipilih tidak valid.',
            'member_id.exists' => 'Anggota yang dipilih tidak ditemukan.',
            'book_ids.required' => 'Pilih setidaknya satu buku untuk dipinjam.',
            'book_ids.array' => 'Daftar buku yang dipilih tidak valid.',
            'book_ids.min' => 'Pilih setidaknya satu buku untuk dipinjam.',
            'book_ids.max' => 'Satu transaksi dapat berisi maksimal :max judul buku.',
            'book_ids.*.integer' => 'Salah satu buku yang dipilih tidak valid.',
            'book_ids.*.distinct' => 'Judul buku yang sama tidak dapat dipilih lebih dari satu kali.',
            'book_ids.*.exists' => 'Salah satu buku yang dipilih tidak ditemukan.',
        ];
    }
}
