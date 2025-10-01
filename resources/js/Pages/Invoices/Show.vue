<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    invoice: Object,
});

const showReminderModal = ref(false);
const reminderForm = useForm({
    message: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const sendReminder = () => {
    reminderForm.post(route('invoices.reminder', props.invoice.id), {
        onSuccess: () => {
            showReminderModal.value = false;
            reminderForm.reset();
        },
    });
};

const isOverdue = () => {
    return props.invoice.due_date < new Date().toISOString().split('T')[0] && props.invoice.status !== 'paid';
};
</script>

<template>
    <Head :title="`Factuur ${invoice.invoice_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Factuur {{ invoice.invoice_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Factuur Details</h3>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p><strong>Factuurnummer:</strong> {{ invoice.invoice_number }}</p>
                                <p><strong>Klant:</strong> {{ invoice.client.name }}</p>
                                <p><strong>Status:</strong> {{ invoice.status }}</p>
                            </div>
                            <div>
                                <p><strong>Totaal:</strong> {{ formatCurrency(invoice.total_amount) }}</p>
                                <p><strong>Subtotaal:</strong> {{ formatCurrency(invoice.subtotal) }}</p>
                                <p><strong>BTW:</strong> {{ formatCurrency(invoice.tax_amount) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="text-md font-medium mb-2">Factuurregels</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beschrijving</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aantal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prijs</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Totaal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in invoice.items" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.unit_price) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.total_price) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a :href="route('invoices.pdf', invoice.id)" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg focus-visible-ring">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF Download
                            </a>
                            
                            <button @click="showReminderModal = true" v-if="invoice.status !== 'paid'" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg focus-visible-ring">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586-2.586a2 2 0 012.828 0L12 6l1.586-1.586a2 2 0 012.828 0L19 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z" />
                                </svg>
                                {{ isOverdue() ? 'Herinnering Versturen' : 'Herinnering Versturen' }}
                            </button>
                            
                            <Link :href="route('invoices.edit', invoice.id)" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg focus-visible-ring">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Bewerken
                            </Link>
                            
                            <Link :href="route('invoices.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg focus-visible-ring">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Terug
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reminder Modal -->
        <div v-if="showReminderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Herinnering Versturen</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Stuur een betalingherinnering naar <strong>{{ invoice.client.name }}</strong> ({{ invoice.client.email }})
                    </p>
                    
                    <form @submit.prevent="sendReminder">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Aangepast bericht (optioneel)</label>
                            <textarea 
                                v-model="reminderForm.message" 
                                rows="4" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Laat leeg voor standaard herinneringstekst..."
                            ></textarea>
                            <div v-if="reminderForm.errors.message" class="text-red-600 text-sm mt-1">{{ reminderForm.errors.message }}</div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="showReminderModal = false"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md"
                            >
                                Annuleren
                            </button>
                            <button 
                                type="submit" 
                                :disabled="reminderForm.processing"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-md focus-visible-ring disabled:opacity-50"
                            >
                                {{ reminderForm.processing ? 'Versturen...' : 'Versturen' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>