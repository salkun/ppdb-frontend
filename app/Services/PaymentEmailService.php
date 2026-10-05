<?php

namespace App\Services;

use App\Models\PpdbRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentEmailService
{
    /**
     * Kirim email konfirmasi pembayaran lunas ke pendaftar. Gagal kirim tidak mengganggu proses verifikasi.
     */
    public static function sendVerified(PpdbRegistration $reg): bool
    {
        try {
            $reg->loadMissing('account');
            $email = $reg->account?->email;
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $name = e($reg->account->full_name ?? 'Calon Siswa');
            $amtVal = isset($reg->payment_amount) && $reg->payment_amount !== null && $reg->payment_amount !== '' ? (float)$reg->payment_amount : 400000.0;
            $amount = $amtVal == 0 ? 'Rp 0 (Bebas Biaya / Jalur Prestasi)' : ('Rp ' . number_format($amtVal, 0, ',', '.'));
            $method = $reg->payment_method === 'cash' ? 'Tunai (Cash di Loket)' : 'Transfer Bank';
            $time = ($reg->payment_verified_at ?? now())->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB';
            $loginUrl = url('/dashboard');

            $html = "<div style=\"font-family:Arial,sans-serif;max-width:520px;margin:auto;color:#1f2937;\">"
                . "<h2 style=\"color:#16a34a;\">Pembayaran Terverifikasi &#10003;</h2>"
                . "<p>Assalamu'alaikum <b>{$name}</b>,</p>"
                . "<p>Pembayaran biaya registrasi PPDB Anda telah <b>diverifikasi LUNAS</b> oleh panitia.</p>"
                . "<table style=\"border-collapse:collapse;width:100%;font-size:14px;\">"
                . "<tr><td style=\"padding:6px 0;color:#6b7280;\">Nominal</td><td><b>{$amount}</b></td></tr>"
                . "<tr><td style=\"padding:6px 0;color:#6b7280;\">Metode</td><td>{$method}</td></tr>"
                . "<tr><td style=\"padding:6px 0;color:#6b7280;\">Diverifikasi</td><td>{$time}</td></tr>"
                . "</table>"
                . "<p>Silakan login ke dashboard untuk melengkapi formulir pendaftaran dan mengunggah berkas.</p>"
                . "<p><a href=\"{$loginUrl}\" style=\"display:inline-block;padding:10px 18px;background:#0d9488;color:#fff;text-decoration:none;border-radius:8px;\">Buka Dashboard PPDB</a></p>"
                . "<p style=\"font-size:12px;color:#6b7280;\">Email ini dikirim otomatis. Mohon tidak membalas email ini.</p>"
                . "</div>";

            Mail::html($html, function ($message) use ($email) {
                $message->to($email)->subject('Pembayaran PPDB Anda Telah Diverifikasi');
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email verifikasi pembayaran: ' . $e->getMessage());
            return false;
        }
    }
}
