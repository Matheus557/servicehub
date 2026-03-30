<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['title', 'description', 'project_id', 'user_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function detail()
    {
        return $this->hasOne(TicketDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}