<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import ForecastChart from '@/Components/ForecastChart.vue';

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

// Forecast data
const forecastData = ref(null);
const forecastLoading = ref(false);
const forecastError = ref(null);

// Count-up animation refs
const animatedStats = ref({
    totalInvoices: 0,
    totalRevenue: 0,
    totalExpenses: 0,
    overdueInvoices: 0,
});

// Count-up animation function
const animateValue = (start, end, duration, callback) => {
    const startTime = performance.now();
    const difference = end - start;
    
    const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = start + (difference * easeOut);
        
        callback(Math.round(current));
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
};

const loadForecast = async () => {
    forecastLoading.value = true;
    forecastError.value = null;
    
    try {
        const response = await axios.get('/dashboard/forecast?months=6&window=3');
        forecastData.value = response.data;
    } catch (error) {
        console.error('Failed to load forecast:', error);
        forecastError.value = error.response?.data?.message || 'Forecast kon niet worden geladen. Probeer het later opnieuw.';
    } finally {
        forecastLoading.value = false;
    }
};

// Calculate trend indicators
const getTrendIndicator = (current, previous) => {
    if (!previous || previous === 0) return '📊';
    const change = ((current - previous) / previous) * 100;
    if (change > 10) return '📈';
    if (change < -10) return '📉';
    return '➡️';
};

const getTrendColor = (current, previous) => {
    if (!previous || previous === 0) return 'text-gray-600';
    const change = ((current - previous) / previous) * 100;
    if (change > 0) return 'text-green-600';
    if (change < 0) return 'text-red-600';
    return 'text-gray-600';
};

// Initialize animations on mount
onMounted(async () => {
    await loadForecast();
    
    // Start count-up animations after a short delay
    await nextTick();
    setTimeout(() => {
        const currentStats = stats.value;
        
        animateValue(0, currentStats.totalInvoices, 1000, (value) => {
            animatedStats.value.totalInvoices = value;
        });
        
        animateValue(0, currentStats.totalRevenue, 1200, (value) => {
            animatedStats.value.totalRevenue = value;
        });
        
        animateValue(0, currentStats.totalExpenses, 1200, (value) => {
            animatedStats.value.totalExpenses = value;
        });
        
        animateValue(0, currentStats.overdueInvoices, 800, (value) => {
            animatedStats.value.overdueInvoices = value;
        });
    }, 300);
});
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
                <TransitionGroup name="fade-slide" tag="div" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <Link :key="'kpi-invoices'" :href="route('invoices.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow duration-300 hover:-translate-y-0.5">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-brand-600/90 rounded-md flex items-center justify-center text-white transition-transform duration-300 hover:scale-110 hover:rotate-3">
                                        <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Totaal Facturen</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900 transition-all duration-300">{{ animatedStats.totalInvoices }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :key="'kpi-revenue'" :href="route('invoices.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow duration-300 hover:-translate-y-0.5">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-green-600 rounded-md flex items-center justify-center text-white transition-transform duration-300 hover:scale-110 hover:rotate-3">
                                        <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Totaal Omzet</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900 transition-all duration-300">{{ formatCurrency(animatedStats.totalRevenue) }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :key="'kpi-expenses'" :href="route('expenses.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow duration-300 hover:-translate-y-0.5">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-red-600 rounded-md flex items-center justify-center text-white transition-transform duration-300 hover:scale-110 hover:rotate-3">
                                        <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Totaal Uitgaven</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900 transition-all duration-300">{{ formatCurrency(animatedStats.totalExpenses) }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :key="'kpi-overdue'" :href="route('invoices.index')" class="glass-panel block focus-visible-ring hover:shadow-cardStrong transition-shadow duration-300 hover:-translate-y-0.5">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-9 h-9 bg-yellow-500 rounded-md flex items-center justify-center text-white transition-transform duration-300 hover:scale-110 hover:rotate-3">
                                        <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xs font-medium text-ink-500">Achterstallige Facturen</p>
                                    <p class="mt-1 text-2xl font-semibold text-ink-900 transition-all duration-300">{{ animatedStats.overdueInvoices }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </TransitionGroup>

                <!-- Forecast Widget -->
                <div class="glass-panel">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-ink-900">📈 Financiële Voorspelling</h3>
                            <button @click="loadForecast" :disabled="forecastLoading" class="text-brand-600 hover:text-brand-700 text-sm font-medium disabled:opacity-50 focus-visible-ring">
                                {{ forecastLoading ? 'Laden...' : 'Vernieuwen' }}
                            </button>
                        </div>
                        
                        <!-- Error State -->
                        <div v-if="forecastError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Forecast Error</p>
                                    <p class="text-sm text-red-700">{{ forecastError }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loading State -->
                        <div v-if="forecastLoading && !forecastData" class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600 mx-auto mb-4"></div>
                                <p class="text-gray-600">Forecast wordt berekend...</p>
                            </div>
                        </div>
                        
                        <!-- Forecast Content -->
                        <div v-else-if="forecastData && !forecastError && forecastData.forecast">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Left: Chart (2 cols) -->
                                <div class="lg:col-span-2 bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-700 mb-4">📊 Voorspelling Grafiek</h4>
                                    <ForecastChart :forecast-data="forecastData" :loading="forecastLoading" />
                                </div>

                                <!-- Right: Summary + Insights (stack) -->
                                <div class="space-y-4">
                                    <!-- Summary cards -->
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="bg-green-50 p-4 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-green-600">Verwachte Inkomsten</p>
                                                    <p class="text-xl font-bold text-green-900">
                                                        {{ formatCurrency(Object.values(forecastData.forecast.income || {}).reduce((a, b) => a + b, 0)) }}
                                                    </p>
                                                    <p class="text-xs text-green-600">Volgende 6 maanden</p>
                                                </div>
                                                <div class="text-2xl">
                                                    {{ getTrendIndicator(
                                                        Object.values(forecastData.forecast.income || {}).slice(-1)[0] || 0,
                                                        Object.values(forecastData.history?.income || {}).slice(-1)[0] || 0
                                                    ) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-red-50 p-4 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-red-600">Verwachte Uitgaven</p>
                                                    <p class="text-xl font-bold text-red-900">
                                                        {{ formatCurrency(Object.values(forecastData.forecast.expenses || {}).reduce((a, b) => a + b, 0)) }}
                                                    </p>
                                                    <p class="text-xs text-red-600">Volgende 6 maanden</p>
                                                </div>
                                                <div class="text-2xl">
                                                    {{ getTrendIndicator(
                                                        Object.values(forecastData.forecast.expenses || {}).slice(-1)[0] || 0,
                                                        Object.values(forecastData.history?.expenses || {}).slice(-1)[0] || 0
                                                    ) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-blue-600">Verwachte Winst</p>
                                                    <p class="text-xl font-bold text-blue-900">
                                                        {{ formatCurrency(Object.values(forecastData.forecast.net_profit || {}).reduce((a, b) => a + b, 0)) }}
                                                    </p>
                                                    <p class="text-xs text-blue-600">Volgende 6 maanden</p>
                                                </div>
                                                <div class="text-2xl">
                                                    {{ getTrendIndicator(
                                                        Object.values(forecastData.forecast.net_profit || {}).slice(-1)[0] || 0,
                                                        (Object.values(forecastData.history?.income || {}).slice(-1)[0] || 0) - (Object.values(forecastData.history?.expenses || {}).slice(-1)[0] || 0)
                                                    ) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Insights -->
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-yellow-800 mb-3">💡 Slimme Inzichten</h4>
                                        <div class="space-y-2">
                                            <div v-if="Object.values(forecastData.forecast.net_profit || {}).every(p => p > 0)" class="flex items-center text-sm text-green-700">
                                                <span class="mr-2">✅</span>
                                                <span>Alle voorspelde maanden tonen winst - uitstekend!</span>
                                            </div>
                                            <div v-else-if="Object.values(forecastData.forecast.net_profit || {}).some(p => p < 0)" class="flex items-center text-sm text-orange-700">
                                                <span class="mr-2">⚠️</span>
                                                <span>Sommige maanden voorspellen verlies - overweeg kostenbeheersing</span>
                                            </div>
                                            
                                            <div v-if="Object.values(forecastData.forecast.income || {}).length > 0" class="flex items-center text-sm text-blue-700">
                                                <span class="mr-2">📈</span>
                                                <span>Gemiddelde maandelijkse groei: {{ Math.round(((Object.values(forecastData.forecast.income || {}).slice(-1)[0] || 0) / Math.max(1, Object.values(forecastData.history?.income || {}).slice(-1)[0] || 1) - 1) * 100) }}%</span>
                                            </div>
                                            
                                            <div v-if="Object.values(forecastData.forecast.net_profit || {}).length > 0" class="flex items-center text-sm text-purple-700">
                                                <span class="mr-2">🎯</span>
                                                <span>Beste maand: {{ Object.keys(forecastData.forecast.net_profit || {}).reduce((a, b) => forecastData.forecast.net_profit[a] > forecastData.forecast.net_profit[b] ? a : b) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No Data State -->
                        <div v-else-if="!forecastLoading && !forecastData" class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <p>Geen forecast data beschikbaar</p>
                            <button @click="loadForecast" class="mt-2 text-brand-600 hover:text-brand-700 text-sm font-medium">
                                Probeer opnieuw
                            </button>
                        </div>
                    </div>
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

