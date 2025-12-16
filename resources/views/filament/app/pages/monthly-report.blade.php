<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <form wire:submit.prevent="submit">
            {{ $this->form }}
        </form>

        {{-- Report Table --}}
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
