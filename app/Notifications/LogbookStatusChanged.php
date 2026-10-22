<?php

namespace App\Notifications;

use App\Models\Logbook;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LogbookStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Logbook $logbook,
        private readonly string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jenis = match ($this->logbook->jenis) {
            Logbook::HARIAN => 'Logbook Harian',
            Logbook::LEMBUR => 'Lembur',
            Logbook::ONCALL => 'On Call',
            default => 'Logbook',
        };

        $message = "{$jenis} tanggal {$this->logbook->tanggal->format('d/m/Y')} ";
        $message .= $this->status === Logbook::STATUS_DISETUJUI
            ? 'telah disetujui.'
            : 'ditolak.';

        if ($this->status === Logbook::STATUS_DITOLAK && $this->logbook->keterangan_hc) {
            $message .= ' Keterangan HC: ' . $this->logbook->keterangan_hc;
        }

        return [
            'logbook_id' => $this->logbook->id,
            'status' => $this->status,
            'message' => $message,
        ];
    }
}
