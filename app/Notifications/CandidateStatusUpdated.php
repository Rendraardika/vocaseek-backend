<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CandidateStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $jobTitle;
    protected $companyName;

    public function __construct($status, $jobTitle, $companyName)
    {
        $this->status = $status;
        $this->jobTitle = $jobTitle;
        $this->companyName = $companyName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Kirim lewat Email & simpan di Dashboard User
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject("Update Lamaran: {$this->jobTitle} di {$this->companyName}")
            ->greeting("Halo, {$notifiable->nama}!");

        if ($this->status === 'OFFER') {
            $message->line("Selamat! Kami senang memberitahu bahwa kamu mendapatkan penawaran (OFFER) untuk posisi **{$this->jobTitle}**.")
                    ->action('Lihat Detail Penawaran', url('/intern/applications'))
                    ->line('Segera konfirmasi kehadiranmu melalui platform Vocaseek.');
        } elseif ($this->status === 'REJECTED') {
            $message->line("Terima kasih telah melamar untuk posisi **{$this->jobTitle}**.")
                    ->line('Setelah melakukan review, mohon maaf saat ini kami belum bisa melanjutkan proses lamaranmu ke tahap berikutnya.')
                    ->line('Tetap semangat dan terus kembangkan *skill*-mu!');
        } else {
            $message->line("Status lamaranmu untuk posisi **{$this->jobTitle}** telah diperbarui menjadi: **{$this->status}**.");
        }

        return $message->line('Terima kasih telah menggunakan Vocaseek!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Status lamaranmu di {$this->companyName} berubah menjadi {$this->status}",
            'job_title' => $this->jobTitle,
        ];
    }
}