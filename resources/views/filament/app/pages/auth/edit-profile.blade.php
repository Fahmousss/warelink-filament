<x-filament-panels::page>
    {{-- Profile Header Section --}}
    <div>
        <div
            class="flex flex-col sm:flex-row items-center gap-6 p-6 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-primary-200 dark:border-gray-700 shadow-sm">
            {{-- Avatar --}}
            <div class="relative">
                <x-filament::avatar class="w-24 h-24 sm:w-32 sm:h-32"
                    src="{{ auth()->user()->getFilamentAvatarUrl() }}" />
                {{-- Active Badge --}}
                <div
                    class="absolute -bottom-1 -right-1 w-8 h-8 bg-success-500 rounded-full border-4 border-white dark:border-gray-800 flex items-center justify-center">
                    @if (auth()->user()->isActive())
                        <span class="sr-only">Active</span>
                        <x-heroicon-s-check class="w-4 h-4 text-white" />
                    @else
                        <span class="sr-only">Inactive</span>
                        <x-heroicon-s-x-mark class="w-4 h-4 text-white" />
                    @endif
                </div>
            </div>

            {{-- User Info --}}
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ auth()->user()->name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ auth()->user()->email }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                    {{-- Role Badge --}}
                    <x-filament::badge color="primary" icon="heroicon-s-user-group">
                        {{ auth()->user()->role }}
                    </x-filament::badge>

                    {{-- Email Verification Badge --}}
                    @if (auth()->user()->email_verified_at)
                        <x-filament::badge color="success" icon="heroicon-s-check-badge">
                            Email Verified
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="warning" icon="heroicon-s-exclamation-triangle">
                            Email Not Verified
                        </x-filament::badge>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Account Activity Section --}}
    <div>
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clock class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Activity</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View your account history and activity</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Member Since --}}
                    <div class="flex flex-col">
                        <span
                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Member
                            Since</span>
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ auth()->user()->created_at->format('F j, Y') }}
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            {{ auth()->user()->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Last Updated --}}
                    <div class="flex flex-col">
                        <span
                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Last
                            Updated</span>
                        <span class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ auth()->user()->updated_at->format('F j, Y H:i') }}
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            {{ auth()->user()->updated_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Email Status --}}
                    <div class="flex flex-col">
                        <span
                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Email
                            Status</span>
                        @if (auth()->user()->email_verified_at)
                            <span
                                class="inline-flex items-center gap-1.5 text-success-600 dark:text-success-400 font-semibold">
                                <x-heroicon-s-check-circle class="w-5 h-5" />
                                Verified
                            </span>
                            <span class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Verified on {{ auth()->user()->email_verified_at->format('M d, Y') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 text-warning-600 dark:text-warning-400 font-semibold">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                                Not Verified
                            </span>
                            <span class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Please verify your email
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    {{ $this->form }}
    {{--
    <x-filament::button wire:click="save" color="primary" class="block" wire:loading.attr="disabled"
        wire:target="save">
        Save Changes
    </x-filament::button> --}}

</x-filament-panels::page>
