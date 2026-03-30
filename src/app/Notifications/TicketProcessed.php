<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketProcessed extends Notification
{
    use Queueable;

    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ticket processado')
            ->line('Seu ticket foi processado com o anexo e os detalhes foram atualizados.')
            ->line('Título: ' . $this->ticket->title)
            ->action('Ver ticket', url('/tickets/' . $this->ticket->id))
            ->line('Obrigado por usar o sistema.');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'message' => 'Anexo processado e TicketDetail atualizado.',
        ];
    }
}
