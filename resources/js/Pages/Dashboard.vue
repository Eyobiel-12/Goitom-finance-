<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    recentInvoices: Array,
    recentExpenses: Array,
    stats: Object,
    monthlyRevenue: Array,
    monthlyExpenses: Array,
});

// Safe fallbacks so the template never crashes
const recentInvoices = computed(() => props.recentInvoices ?? []);
const recentExpenses = computed(() => props.recentExpenses ?? []);
const stats = computed(() => props.stats ?? ({
    totalInvoices: 0,
    totalRevenue: 0,
    totalExpenses: 0,
    overdueInvoices: 0,
    netProfit: 0,
}));

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const formatDate = (date) => new Date(date).toLocaleDateString('nl-NL');

const getStatusColor = (status) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-ink-900">Dashboard</h2>
                <div class="hidden sm:flex gap-3">
                    <Link :href="route('invoices.create')" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700">Nieuwe factuur</Link>
                    <Link :href="route('expenses.create')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-ink-700 shadow-sm hover:bg-gray-50">Nieuwe uitgave</Link>
                </div>
            </div>
        </template>

        <div class="py-8 section-gradient">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <Link :href="route('invoices.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-brand-600/90 rounded-md flex items-center justify-center text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Totaal Facturen</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900">{{ stats.totalInvoices }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('invoices.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-green-600 rounded-md flex items-center justify-center text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Totaal Omzet</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900">{{ formatCurrency(stats.totalRevenue) }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('expenses.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-red-600 rounded-md flex items-center justify-center text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Totaal Uitgaven</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900">{{ formatCurrency(stats.totalExpenses) }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :href="route('invoices.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-yellow-500 rounded-md flex items-center justify-center text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Achterstallige Facturen</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900">{{ stats.overdueInvoices }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Recent Invoices -->
                    <div class="glass-panel">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-ink-900">Recente Facturen</h3>
                                <Link :href="route('invoices.index')" class="text-brand-600 hover:text-brand-700 text-sm font-medium">Alle facturen</Link>
                            </div>
                            <div class="space-y-3">
                                <Link v-for="invoice in recentInvoices" :key="invoice.id" :href="route('invoices.show', invoice.id)" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors focus-visible-ring">
                                    <div>
                                        <p class="font-medium text-ink-900">{{ invoice.client.name }}</p>
                                        <p class="text-sm text-ink-500">{{ invoice.invoice_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-ink-900">{{ formatCurrency(invoice.total_amount) }}</p>
                                        <span :class="getStatusColor(invoice.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ invoice.status }}
                                        </span>
                                    </div>
                                </Link>
                                <div v-if="recentInvoices.length === 0" class="text-center py-4 text-ink-500">Nog geen facturen</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Expenses -->
                    <div class="glass-panel">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-ink-900">Recente Uitgaven</h3>
                                <Link :href="route('expenses.index')" class="text-brand-600 hover:text-brand-700 text-sm font-medium">Alle uitgaven</Link>
                            </div>
                            <div class="space-y-3">
                                <Link v-for="expense in recentExpenses" :key="expense.id" :href="route('expenses.show', expense.id)" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors focus-visible-ring">
                                    <div>
                                        <p class="font-medium text-ink-900">{{ expense.description }}</p>
                                        <p class="text-sm text-ink-500">{{ expense.category }} • {{ formatDate(expense.expense_date) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-ink-900">{{ formatCurrency(expense.amount) }}</p>
                                    </div>
                                </Link>
                                <div v-if="recentExpenses.length === 0" class="text-center py-4 text-ink-500">Nog geen uitgaven</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 glass-panel">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-ink-900 mb-4">Snelle Acties</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <Link :href="route('invoices.create')" class="flex items-center p-4 bg-brand-50 rounded-lg hover:bg-brand-100 transition-colors">
                                <div class="w-10 h-10 bg-brand-600 rounded-lg flex items-center justify-center mr-4 text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-ink-900">Nieuwe Factuur</p>
                                    <p class="text-sm text-ink-500">Maak een nieuwe factuur</p>
                                </div>
                            </Link>

                            <Link :href="route('clients.create')" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-4 text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-ink-900">Nieuwe Klant</p>
                                    <p class="text-sm text-ink-500">Voeg een nieuwe klant toe</p>
                                </div>
                            </Link>

                            <Link :href="route('expenses.create')" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-4 text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-ink-900">Nieuwe Uitgave</p>
                                    <p class="text-sm text-ink-500">Registreer een uitgave</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

