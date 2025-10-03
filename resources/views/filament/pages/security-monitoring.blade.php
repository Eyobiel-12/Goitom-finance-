<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Security Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->getSecurityStats()['total_logs'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Totaal Audit Logs</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->getSecurityStats()['logs_24h'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Activiteit (24h)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->getSecurityStats()['deletions_24h'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Verwijderingen (24h)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Overview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                🔍 Recente Activiteit
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Real-time monitoring van alle gebruikersactiviteiten en systeemwijzigingen.
            </p>
            
            {{ $this->table }}
        </div>

        <!-- Security Alerts -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                🚨 Security Alerts
            </h3>
            
            @php
                $stats = $this->getSecurityStats();
                $alerts = [];
                
                if ($stats['deletions_24h'] > 10) {
                    $alerts[] = [
                        'type' => 'warning',
                        'message' => 'Hoge hoeveelheid verwijderingen gedetecteerd in de laatste 24 uur.',
                        'count' => $stats['deletions_24h']
                    ];
                }
                
                if ($stats['logs_24h'] > 1000) {
                    $alerts[] = [
                        'type' => 'info',
                        'message' => 'Hoge activiteit gedetecteerd in de laatste 24 uur.',
                        'count' => $stats['logs_24h']
                    ];
                }
                
                if ($stats['unique_ips'] > 50) {
                    $alerts[] = [
                        'type' => 'success',
                        'message' => 'Veel verschillende IP-adressen gedetecteerd.',
                        'count' => $stats['unique_ips']
                    ];
                }
            @endphp
            
            @if(empty($alerts))
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Geen alerts</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Alle systemen functioneren normaal.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($alerts as $alert)
                        <div class="flex items-center p-4 rounded-lg border
                            @if($alert['type'] === 'warning') bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800
                            @elseif($alert['type'] === 'info') bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800
                            @else bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800
                            @endif">
                            <div class="flex-shrink-0">
                                @if($alert['type'] === 'warning')
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($alert['type'] === 'info')
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium
                                    @if($alert['type'] === 'warning') text-yellow-800 dark:text-yellow-200
                                    @elseif($alert['type'] === 'info') text-blue-800 dark:text-blue-200
                                    @else text-green-800 dark:text-green-200
                                    @endif">
                                    {{ $alert['message'] }}
                                </p>
                                <p class="text-xs
                                    @if($alert['type'] === 'warning') text-yellow-600 dark:text-yellow-300
                                    @elseif($alert['type'] === 'info') text-blue-600 dark:text-blue-300
                                    @else text-green-600 dark:text-green-300
                                    @endif">
                                    Aantal: {{ $alert['count'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
