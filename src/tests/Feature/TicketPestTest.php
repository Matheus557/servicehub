<?php

use App\Jobs\ProcessTicketAttachment;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\User;
use App\Notifications\TicketProcessed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');

it('creates company project ticket relations', function () {
    $user = User::factory()->create();

    $company = Company::create(['name' => 'Empresa A']);
    $project = Project::create(['name' => 'Projeto A1', 'company_id' => $company->id]);
    $ticket = Ticket::create(['title' => 'Ticket A', 'project_id' => $project->id, 'user_id' => $user->id]);
    $detail = TicketDetail::create(['ticket_id' => $ticket->id, 'technical_detail' => 'Ok']);

    expect($company->projects()->exists())->toBeTrue();
    expect($project->tickets()->exists())->toBeTrue();
    expect($ticket->detail->technical_detail)->toBe('Ok');
    expect($ticket->user->id)->toBe($user->id);
    expect($user->tickets()->where('id', $ticket->id)->exists())->toBeTrue();
});

it('processes attachment job and updates ticket detail + notifies user', function () {
    Storage::fake('local');
    Bus::fake();
    Notification::fake();

    $user = User::factory()->create();
    $company = Company::create(['name' => 'Empresa B']);
    $project = Project::create(['name' => 'Projeto B1', 'company_id' => $company->id]);
    $ticket = Ticket::create(['title' => 'Ticketjob', 'project_id' => $project->id, 'user_id' => $user->id]);

    $file = UploadedFile::fake()->createWithContent('data.json', json_encode(['foo' => 'bar']));
    $path = $file->store('attachments');

    $job = new ProcessTicketAttachment($ticket, $path);
    $job->handle();

    $this->assertDatabaseHas('ticket_details', ['ticket_id' => $ticket->id]);

    Notification::assertSentTo($user, TicketProcessed::class);
});

it('supports ticket routes with auth and validation', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/tickets')->assertStatus(200);

    $company = Company::create(['name' => 'Empresa C']);
    $project = Project::create(['name' => 'Projeto C1', 'company_id' => $company->id]);

    $this->actingAs($user)->post('/tickets', [
        'title' => 'Teste rota',
        'project_id' => $project->id,
        'description' => 'Descrição rota',
    ])->assertRedirect('/dashboard');

    $ticket = Ticket::where('title', 'Teste rota')->first();
    expect($ticket)->not->toBeNull();
});
