<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
  ticket: Object,
  companies: Array,
  errors: Object,
});

const form = useForm({
  title: props.ticket.title || '',
  description: props.ticket.description || '',
  project_id: props.ticket.project_id || '',
  attachment: null,
});

const selectedCompanyId = ref(props.ticket.project?.company?.id ?? '');

const projectOptions = computed(() => {
  const company = props.companies.find((item) => item.id === Number(selectedCompanyId.value));
  return company ? company.projects : [];
});

watch(selectedCompanyId, (newCompanyId) => {
  const company = props.companies.find((item) => item.id === Number(newCompanyId));
  if (!company) {
    form.project_id = '';
    return;
  }

  if (!company.projects.some((project) => project.id === Number(form.project_id))) {
    form.project_id = '';
  }
});

watch(() => form.title, () => {
  form.clearErrors('title');
});

watch(() => form.project_id, () => {
  form.clearErrors('project_id');
});

function submit() {
  form.clearErrors();

  if (!form.title || !form.title.trim()) {
    form.setError('title', 'O título é obrigatório.');
    return;
  }

  if (!form.project_id) {
    form.setError('project_id', 'O projeto é obrigatório.');
    return;
  }

  const selectedCompany = props.companies.find((c) => c.id === Number(selectedCompanyId.value));
  if (!selectedCompany || !selectedCompany.projects.some((project) => Number(project.id) === Number(form.project_id))) {
    form.setError('project_id', 'Selecione um projeto válido para a empresa.');
    return;
  }

  form.put(route('tickets.update', props.ticket.id), {
    forceFormData: true,
    onSuccess: () => {
      window.location.href = route('tickets.show', props.ticket.id);
    },
  });
}

function onFileChange(event) {
  const file = event.target.files[0];
  form.attachment = file || null;
}
</script>

<template>
  <Head title="Editar Ticket" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar Ticket #{{ props.ticket.id }}</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" enctype="multipart/form-data">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Empresa</label>
                <select
                  v-model="selectedCompanyId"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  @change="form.project_id = ''"
                >
                  <option value="">Selecione</option>
                  <option v-for="company in props.companies" :key="company.id" :value="company.id">
                    {{ company.name }}
                  </option>
                </select>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Projeto</label>
                <select v-model="form.project_id" required class="mt-1 block w-full border-gray-300 rounded-md">
                  <option value="">Selecione</option>
                  <option v-for="project in projectOptions" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
                <p v-if="errors.project_id" class="text-sm text-red-600">{{ errors.project_id }}</p>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Título</label>
                <input
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  required
                />
                <p v-if="errors.title" class="text-sm text-red-600">{{ errors.title }}</p>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                <textarea
                  v-model="form.description"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  rows="4"
                ></textarea>
                <p v-if="errors.description" class="text-sm text-red-600">{{ errors.description }}</p>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Arquivo (JSON/Text, opcional)</label>
                <input
                  type="file"
                  @change="onFileChange"
                  class="mt-1 block w-full"
                  accept=".json,.txt,text/*"
                />
                <p v-if="errors.attachment" class="text-sm text-red-600">{{ errors.attachment }}</p>
              </div>

              <div>
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500"
                >
                  Salvar alterações
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
