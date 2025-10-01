<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    projects: Object,
});

const search = ref('');

const deleteProject = (project) => {
    if (confirm('Weet je zeker dat je dit project wilt verwijderen?')) {
        router.delete(route('projects.destroy', project.id));
    }
};

const formatCurrency = (amount) => new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(amount);
const formatDate = (date) => new Date(date).toLocaleDateString('nl-NL');

const getStatusColor = (status) => {
    const colors = {
        'active': 'bg-green-100 text-green-800',
        'completed': 'bg-blue-100 text-blue-800',
        'on_hold': 'bg-yellow-100 text-yellow-800',
        'cancelled': 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => ({
    'active': 'Actief',
    'completed': 'Voltooid',
    'on_hold': 'On Hold',
    'cancelled': 'Geannuleerd',
})[status] || status;
</script>

<template>
    <Head title="Projecten" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-ink-900">Projecten</h2>
                <Link :href="route('projects.create')" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700">Nieuw Project</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="mb-6 glass-panel">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input v-model="search" type="text" placeholder="Zoek projecten..." class="w-full px-3 py-2 rounded-md border border-gray-300 focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="glass-panel">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/60">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Project Naam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Klant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Start Datum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Budget</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Facturen/Uitgaven</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-ink-500 uppercase tracking-wider">Acties</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="project in projects.data" :key="project.id" class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-ink-900">{{ project.name }}</div>
                                        <div v-if="project.description" class="text-sm text-ink-500">{{ project.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-ink-900">{{ project.client.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(project.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">{{ getStatusText(project.status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-500">{{ project.start_date ? formatDate(project.start_date) : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-ink-900">{{ project.budget ? formatCurrency(project.budget) : '-' }}</div>
                                        <div v-if="project.hourly_rate" class="text-sm text-ink-500">{{ formatCurrency(project.hourly_rate) }}/uur</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-ink-900">{{ project.invoices_count }} facturen</div>
                                        <div class="text-sm text-ink-500">{{ project.expenses_count }} uitgaven</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('projects.show', project.id)" class="text-brand-600 hover:text-brand-700">Bekijk</Link>
                                            <Link :href="route('projects.edit', project.id)" class="text-indigo-600 hover:text-indigo-800">Bewerk</Link>
                                            <button @click="deleteProject(project)" class="text-red-600 hover:text-red-800">Verwijder</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="projects.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <h3 class="mt-2 text-sm font-medium text-ink-900">Geen projecten</h3>
                        <p class="mt-1 text-sm text-ink-500">Begin met het maken van je eerste project.</p>
                        <div class="mt-6">
                            <Link :href="route('projects.create')" class="inline-flex items-center px-4 py-2 rounded-md text-white bg-brand-600 hover:bg-brand-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Nieuw Project
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="projects.data.length > 0" class="px-4 py-3 flex items-center justify-between border-t border-gray-100 sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-ink-500">Toont <span class="font-medium">{{ projects.from }}</span> tot <span class="font-medium">{{ projects.to }}</span> van <span class="font-medium">{{ projects.total }}</span> resultaten</p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link v-if="projects.prev_page_url" :href="projects.prev_page_url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-ink-500 hover:bg-gray-50">Vorige</Link>
                                    <Link v-if="projects.next_page_url" :href="projects.next_page_url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-ink-500 hover:bg-gray-50">Volgende</Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
