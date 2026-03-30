<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Notifications\TicketProcessed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessTicketAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Ticket $ticket;
    public string $path;

    public function __construct(Ticket $ticket, string $path)
    {
        $this->ticket = $ticket;
        $this->path = $path;
    }

    public function handle(): void
    {
        $content = Storage::get($this->path);
        $technicalDetail = '';

        if (str_contains($this->path, '.json')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $technicalDetail = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                $technicalDetail = $content;
            }
        } else {
            $technicalDetail = $content;
        }

        TicketDetail::updateOrCreate(
            ['ticket_id' => $this->ticket->id],
            ['technical_detail' => $technicalDetail]
        );

        $responsibleUser = $this->ticket->user ?? $this->ticket->refresh()->user;
        if ($responsibleUser) {
            $responsibleUser->notify(new TicketProcessed($this->ticket));
        }
    }
}
