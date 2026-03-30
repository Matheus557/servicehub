<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTicketAttachment;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['project.company', 'detail', 'user'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create()
    {
        $companies = Company::with('projects')->get();

        return Inertia::render('Tickets/Create', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'attachment' => ['nullable', 'file', 'mimetypes:application/json,text/plain,text/json', 'max:10240'],
        ]);

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'project_id' => $validated['project_id'],
            'user_id' => Auth::id(),
        ]);

        if ($request->hasFile('attachment')) {
            $uploaded = $request->file('attachment');
            $path = $uploaded->store('attachments');

            ProcessTicketAttachment::dispatch($ticket, $path);
        }

        return redirect()->route('dashboard')->with('success', 'Ticket criado com sucesso.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['project.company', 'detail', 'user']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Ticket $ticket)
    {
        $ticket->load(['project.company', 'detail', 'user']);
        $companies = Company::with('projects')->get();

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        \Log::debug('Ticket update request', $request->only(['title', 'description', 'project_id']));

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'attachment' => ['nullable', 'file', 'mimetypes:application/json,text/plain,text/json', 'max:10240'],
        ]);

        $ticket->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'project_id' => $validated['project_id'],
        ]);

        if ($request->hasFile('attachment')) {
            $uploaded = $request->file('attachment');
            $path = $uploaded->store('attachments');
            ProcessTicketAttachment::dispatch($ticket, $path);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket atualizado com sucesso.');
    }
}
