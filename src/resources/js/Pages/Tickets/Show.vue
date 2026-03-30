<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  ticket: Object,
});
</script>

<template>
  <Head title="Detalhes do Ticket" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Ticket #{{ props.ticket.id }}</h2>
        <Link
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500"
          :href="route('tickets.edit', props.ticket.id)"
        >
          Editar
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 space-y-4">
            <div><strong>Título:</strong> {{ props.ticket.title }}</div>
            <div><strong>Descrição:</strong> {{ props.ticket.description || 'Sem descrição' }}</div>
            <div><strong>Empresa:</strong> {{ props.ticket.project.company.name }}</div>
            <div><strong>Projeto:</strong> {{ props.ticket.project.name }}</div>
            <div><strong>Responsável:</strong> {{ props.ticket.user?.name || 'N/A' }}</div>
            <div><strong>Status do detalhe:</strong> {{ props.ticket.detail ? 'Processado' : 'Pendente' }}</div>
            <div v-if="props.ticket.detail">
              <strong>Detalhe técnico:</strong>
              <pre class="bg-gray-100 p-3 rounded">{{ props.ticket.detail.technical_detail }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
