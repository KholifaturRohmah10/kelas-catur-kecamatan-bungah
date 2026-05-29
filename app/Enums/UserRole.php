<?php

namespace App\Enums;

enum UserRole: string
{
    case Pemohon = 'pemohon';
    case Operator = 'operator';
    case Pimpinan = 'pimpinan';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Pemohon => 'User / Pemohon',
            self::Operator => 'Operator',
            self::Pimpinan => 'Pimpinan / Pak Camat',
            self::Admin => 'Admin',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Pemohon => 'Login, pilih jenis surat, isi form, dan upload lampiran.',
            self::Operator => 'Cek kelengkapan, approve atau reject, dan wajib isi alasan jika menolak.',
            self::Pimpinan => 'Memberi approval akhir atau menjadi pihak penandatangan.',
            self::Admin => 'Mengatur nomor urut awal, kode tiap jenis surat, data master, dan user/role.',
        };
    }

    /**
     * @return list<string>
     */
    public function responsibilities(): array
    {
        return match ($this) {
            self::Pemohon => [
                'Login ke aplikasi.',
                'Memilih jenis surat yang diajukan.',
                'Mengisi form permohonan.',
                'Mengunggah lampiran pendukung.',
            ],
            self::Operator => [
                'Memeriksa kelengkapan data dan lampiran.',
                'Menyetujui atau menolak pengajuan.',
                'Mengisi alasan penolakan saat reject.',
            ],
            self::Pimpinan => [
                'Memberikan persetujuan akhir.',
                'Menjadi pihak penandatangan surat.',
            ],
            self::Admin => [
                'Mengatur nomor urut awal surat.',
                'Mengelola kode per jenis surat.',
                'Mengelola data master.',
                'Mengelola user dan role.',
            ],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }
}
