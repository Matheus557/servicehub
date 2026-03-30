<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $companyA = \App\Models\Company::firstOrCreate(['name' => 'Empresa A']);
        $projectA1 = \App\Models\Project::firstOrCreate(['name' => 'Projeto A1', 'company_id' => $companyA->id]);
        $projectA2 = \App\Models\Project::firstOrCreate(['name' => 'Projeto A2', 'company_id' => $companyA->id]);

        $companyB = \App\Models\Company::firstOrCreate(['name' => 'Empresa B']);
        $projectB1 = \App\Models\Project::firstOrCreate(['name' => 'Projeto B1', 'company_id' => $companyB->id]);
        $projectB2 = \App\Models\Project::firstOrCreate(['name' => 'Projeto B2', 'company_id' => $companyB->id]);

        $ticket = \App\Models\Ticket::firstOrCreate([
            'title' => 'Ticket de exemplo',
            'project_id' => $projectA1->id,
        ], [
            'description' => 'Descrição inicial do ticket',
            'user_id' => $user->id,
        ]);

        \App\Models\TicketDetail::firstOrCreate([
            'ticket_id' => $ticket->id,
        ], [
            'technical_detail' => 'Ticket criado sem anexo ainda.',
        ]);
    }
}
