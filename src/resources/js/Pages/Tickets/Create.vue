<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  companies: Array,
  errors: Object,
});

const form = useForm({
  title: '',
  description: '',
  project_id: '',
  attachment: null,
});

const selectedCompanyId = ref('');

const projectOptions = computed(() => {
  const company = props.companies.find((item) => item.id === Number(selectedCompanyId.value));
  return company ? company.projects : [];
});

function submit() {
  form.post('/tickets', {
    forceFormData: true,
    onSuccess: () => {
      form.reset('title', 'description', 'project_id', 'attachment');
      selectedCompanyId.value = '';
      window.location.href = '/dashboard';
    },
    onError: () => {
      // errors are handled by form.errors
    },
  });
}

function onFileChange(event) {
  const file = event.target.files[0];
  form.attachment = file || null;
}
</script>

<template>
  <Head title="Criar Ticket" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Criar Ticket</h2>
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
                <select v-model="form.project_id" class="mt-1 block w-full border-gray-300 rounded-md">
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
                  Criar Ticket
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
