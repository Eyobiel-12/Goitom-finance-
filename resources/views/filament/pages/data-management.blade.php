<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Export Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                📤 Data Export
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Exporteer uw data naar CSV bestanden voor backup of analyse doeleinden.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center mb-2">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <h4 class="font-medium text-blue-900 dark:text-blue-100">Gebruikers</h4>
                    </div>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">Exporteer alle gebruikersgegevens</p>
                    <x-filament::button wire:click="exportUsers" color="primary" size="sm">
                        Exporteer
                    </x-filament::button>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center mb-2">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h4 class="font-medium text-green-900 dark:text-green-100">Facturen</h4>
                    </div>
                    <p class="text-sm text-green-700 dark:text-green-300 mb-3">Exporteer alle factuurgegevens</p>
                    <x-filament::button wire:click="exportInvoices" color="success" size="sm">
                        Exporteer
                    </x-filament::button>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="flex items-center mb-2">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <h4 class="font-medium text-yellow-900 dark:text-yellow-100">Uitgaven</h4>
                    </div>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">Exporteer alle uitgavegegevens</p>
                    <x-filament::button wire:click="exportExpenses" color="warning" size="sm">
                        Exporteer
                    </x-filament::button>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                    <div class="flex items-center mb-2">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h4 class="font-medium text-purple-900 dark:text-purple-100">Klanten</h4>
                    </div>
                    <p class="text-sm text-purple-700 dark:text-purple-300 mb-3">Exporteer alle klantgegevens</p>
                    <x-filament::button wire:click="exportClients" color="info" size="sm">
                        Exporteer
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Import Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                📥 Data Import
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Importeer data vanuit CSV bestanden. Zorg ervoor dat de bestandsstructuur overeenkomt met de export formaten.
            </p>
            
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">⚠️ Belangrijke Opmerkingen:</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• CSV bestanden moeten UTF-8 gecodeerd zijn</li>
                    <li>• Gebruik puntkomma (;) als scheidingsteken</li>
                    <li>• Eerste rij moet kolomkoppen bevatten</li>
                    <li>• Maak altijd een backup voordat je importeert</li>
                </ul>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                📊 Database Statistieken
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ \App\Models\User::count() }}</div>
                    <div class="text-sm text-blue-800 dark:text-blue-200">Gebruikers</div>
                </div>
                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ \App\Models\Invoice::count() }}</div>
                    <div class="text-sm text-green-800 dark:text-green-200">Facturen</div>
                </div>
                <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ \App\Models\Expense::count() }}</div>
                    <div class="text-sm text-yellow-800 dark:text-yellow-200">Uitgaven</div>
                </div>
                <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ \App\Models\Client::count() }}</div>
                    <div class="text-sm text-purple-800 dark:text-purple-200">Klanten</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
