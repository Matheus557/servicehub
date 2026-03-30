<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketDetail extends Model
{
    protected $fillable = ['ticket_id', 'technical_detail'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}