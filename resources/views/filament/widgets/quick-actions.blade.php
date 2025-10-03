<x-filament::widget>
    <x-filament::card class="sticky top-2 z-20">
        <div class="flex flex-wrap gap-2 items-center">
            <x-filament::button tag="a" href="{{ route('filament.admin.pages.data-management-page') }}" icon="heroicon-o-megaphone" color="primary">
                Maak aankondiging
            </x-filament::button>
            <x-filament::button tag="a" href="{{ url('/admin/support-tickets/create') }}" icon="heroicon-o-life-buoy" color="success">
                Nieuw ticket
            </x-filament::button>
            <x-filament::button tag="a" href="{{ url('/admin/users/export') }}" icon="heroicon-o-arrow-down-tray" color="gray">
                Exporteer gebruikers
            </x-filament::button>
        </div>
    </x-filament::card>
</x-filament::widget>


