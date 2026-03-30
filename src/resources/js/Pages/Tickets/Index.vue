<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  tickets: Array,
});
</script>

<template>
  <Head title="Tickets" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Meus Tickets</h2>
        <Link
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500"
          :href="route('tickets.create')"
        >
          Criar Ticket
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Título</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Projeto</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Situação</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="ticket in props.tickets" :key="ticket.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ticket.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ticket.title }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ticket.project.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ticket.detail ? 'Processado' : 'Pendente' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <Link
                      class="text-indigo-600 hover:text-indigo-900"
                      :href="route('tickets.show', ticket.id)"
                    >
                      Ver
                    </Link>
                  </td>
                </tr>
                <tr v-if="props.tickets.length === 0">
                  <td class="px-6 py-4" colspan="5">Nenhum ticket encontrado.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
