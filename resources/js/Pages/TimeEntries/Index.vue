<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ entries: Object, projects: Array });

const form = useForm({
  project_id: '',
  work_date: new Date().toISOString().slice(0,10),
  hours: 1,
  rate: '',
  description: '',
});

function submit() {
  form.post(route('time-entries.store'), { preserveScroll: true });
}
</script>

<template>
  <Head title="Time Entries" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-ink-900">Time Entries</h2>
    </template>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-card lg:col-span-1">
        <h3 class="mb-3 text-sm font-medium text-ink-700">Add Entry</h3>
        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-ink-700">Project</label>
            <select v-model="form.project_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" required>
              <option value="">Select project</option>
              <option v-for="p in props.projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-ink-700">Date</label>
              <input v-model="form.work_date" type="date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-ink-700">Hours</label>
              <input v-model.number="form.hours" type="number" step="0.25" min="0.25" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" required />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-ink-700">Rate (€)</label>
              <input v-model="form.rate" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-ink-700">Description</label>
            <input v-model="form.description" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
          </div>
          <div>
            <button type="submit" class="inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700" :disabled="form.processing">Save</button>
          </div>
        </form>
      </div>

      <div class="rounded-xl border border-gray-100 bg-white shadow-card lg:col-span-2 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="e in entries.data" :key="e.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-ink-700">{{ e.work_date }}</td>
              <td class="px-4 py-3 text-sm text-ink-700">#{{ e.project_id }} — {{ e.project?.name }}</td>
              <td class="px-4 py-3 text-sm text-ink-700">{{ Number(e.hours).toFixed(2) }}</td>
              <td class="px-4 py-3 text-sm text-ink-700">{{ e.rate ? '€ ' + Number(e.rate).toFixed(2) : '-' }}</td>
            </tr>
            <tr v-if="entries.data.length === 0">
              <td colspan="4" class="px-4 py-8 text-center text-sm text-ink-600">No entries yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>


