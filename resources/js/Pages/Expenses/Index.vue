<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    expenses: Object,
});

const search = ref('');

const deleteExpense = (expense) => {
    if (confirm('Weet je zeker dat je deze uitgave wilt verwijderen?')) {
        router.delete(route('expenses.destroy', expense.id));
    }
};

const formatCurrency = (amount) => new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(amount);
const formatDate = (date) => new Date(date).toLocaleDateString('nl-NL');

const getCategoryColor = (category) => {
    const colors = {
        'Marketing': 'bg-blue-100 text-blue-800',
        'Reizen': 'bg-green-100 text-green-800',
        'Kantoor': 'bg-purple-100 text-purple-800',
        'Software': 'bg-yellow-100 text-yellow-800',
        'Hardware': 'bg-red-100 text-red-800',
        'Telefoon': 'bg-indigo-100 text-indigo-800',
        'Internet': 'bg-pink-100 text-pink-800',
        'Overig': 'bg-gray-100 text-gray-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Uitgaven" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-ink-900">Uitgaven</h2>
                <Link :href="route('expenses.create')" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700">Nieuwe Uitgave</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="mb-6 glass-panel">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input v-model="search" type="text" placeholder="Zoek uitgaven..." class="w-full px-3 py-2 rounded-md border border-gray-300 focus:border-brand-500 focus:ring-brand-500" />
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Beschrijving</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Categorie</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Datum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Bedrag</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-ink-500 uppercase tracking-wider">Factureerbaar</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-ink-500 uppercase tracking-wider">Acties</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-ink-900">{{ expense.description }}</div>
                                        <div v-if="expense.notes" class="text-sm text-ink-500">{{ expense.notes }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getCategoryColor(expense.category)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">{{ expense.category }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-ink-900">{{ expense.project ? expense.project.name : '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-500">{{ formatDate(expense.expense_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-ink-900">{{ formatCurrency(expense.amount) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="expense.is_billable" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ja</span>
                                        <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Nee</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('expenses.show', expense.id)" class="text-brand-600 hover:text-brand-700">Bekijk</Link>
                                            <Link :href="route('expenses.edit', expense.id)" class="text-indigo-600 hover:text-indigo-800">Bewerk</Link>
                                            <button @click="deleteExpense(expense)" class="text-red-600 hover:text-red-800">Verwijder</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="expenses.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <h3 class="mt-2 text-sm font-medium text-ink-900">Geen uitgaven</h3>
                        <p class="mt-1 text-sm text-ink-500">Begin met het registreren van je eerste uitgave.</p>
                        <div class="mt-6">
                            <Link :href="route('expenses.create')" class="inline-flex items-center px-4 py-2 rounded-md text-white bg-brand-600 hover:bg-brand-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Nieuwe Uitgave
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="expenses.data.length > 0" class="px-4 py-3 flex items-center justify-between border-t border-gray-100 sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-ink-500">Toont <span class="font-medium">{{ expenses.from }}</span> tot <span class="font-medium">{{ expenses.to }}</span> van <span class="font-medium">{{ expenses.total }}</span> resultaten</p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link v-if="expenses.prev_page_url" :href="expenses.prev_page_url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-ink-500 hover:bg-gray-50">Vorige</Link>
                                    <Link v-if="expenses.next_page_url" :href="expenses.next_page_url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-ink-500 hover:bg-gray-50">Volgende</Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
