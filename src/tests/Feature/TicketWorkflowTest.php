<?php

namespace Tests\Feature;

use App\Jobs\ProcessTicketAttachment;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketProcessed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_create_and_process_attachment_job(): void
    {
        Storage::fake('local');
        Bus::fake();
        Notification::fake();

        $user = User::factory()->create();
        $company = Company::create(['name' => 'ACME']);
        $project = Project::create(['name' => 'Primeiro Projeto', 'company_id' => $company->id]);

        $file = UploadedFile::fake()->createWithContent('anexo.json', json_encode(['key' => 'value']));

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Ticket teste',
            'description' => 'Descrição teste',
            'project_id' => $project->id,
            'attachment' => $file,
        ]);

        $response->assertRedirect('/dashboard');

        $ticket = Ticket::where('title', 'Ticket teste')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($user->id, $ticket->user_id);

        Bus::assertDispatched(ProcessTicketAttachment::class, function ($job) use ($ticket) {
            return $job->ticket->id === $ticket->id;
        });

        $attachmentPaths = Storage::disk('local')->files('attachments');
        $this->assertCount(1, $attachmentPaths);

        $job = new ProcessTicketAttachment($ticket, $attachmentPaths[0]);
        $job->handle();

        $this->assertDatabaseHas('ticket_details', [
            'ticket_id' => $ticket->id,
            'technical_detail' => json_encode(['key' => 'value'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        Notification::assertSentTo($user, TicketProcessed::class);
    }

    public function test_ticket_show_and_update(): void
    {
        $user = User::factory()->create();
        $company = Company::firstOrCreate(['name' => 'Empresa A']);
        $project = Project::firstOrCreate(['name' => 'Projeto A1', 'company_id' => $company->id]);

        $ticket = Ticket::create([
            'title' => 'Ticket para ver',
            'description' => 'Descrição original',
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/tickets/'.$ticket->id);
        $response->assertStatus(200);
        $response->assertSee('Ticket para ver');

        $response = $this->actingAs($user)->put('/tickets/'.$ticket->id, [
            'title' => 'Ticket atualizado',
            'description' => 'Descrição editada',
            'project_id' => $project->id,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'title' => 'Ticket atualizado', 'description' => 'Descrição editada']);
    }
}
