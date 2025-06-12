<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="prose dark:prose-invert max-w-none">
                <h1>Welcome to Our Website</h1>
                <p>Browse through our pages using the links below or the navigation sidebar.</p>
            </div>
        </x-filament::card>

        <!-- Search Section -->
        <x-filament::card>
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Search Pages</h2>
                <div class="flex gap-2">
                    <x-filament::input
                        wire:model.live="searchQuery"
                        placeholder="Search pages..."
                        class="flex-1"
                    />
                    @if($searchQuery)
                        <x-filament::button
                            wire:click="$set('searchQuery', '')"
                            color="gray"
                            size="sm"
                        >
                            Clear
                        </x-filament::button>
                    @endif
                </div>
                @if($searchQuery)
                    <p class="text-sm text-gray-600">
                        Showing results for: <strong>{{ $searchQuery }}</strong>
                    </p>
                @endif
            </div>
        </x-filament::card>

        <!-- Pages by Category -->
        <div class="space-y-6">
            @foreach($this->getPagesByCategory() as $category => $pages)
                <x-filament::card>
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            {{ $category }}
                            <span class="text-sm text-gray-500 font-normal">({{ count($pages) }})</span>
                        </h2>
                        
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($pages as $page)
                                <div class="p-4 border rounded-lg hover:border-primary-300 transition-colors">
                                    <h3 class="font-medium mb-2">
                                        <a href="{{ $page['url'] }}" class="text-primary-600 hover:text-primary-700">
                                            {{ $page['title'] }}
                                        </a>
                                    </h3>
                                    @if($page['description'])
                                        <p class="text-sm text-gray-600 mb-2">{{ $page['description'] }}</p>
                                    @endif
                                    <div class="text-xs text-gray-500">
                                        Updated: {{ date('M j, Y', strtotime($page['updated_at'])) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-filament::card>
            @endforeach
        </div>

        @if(empty($this->getPages()))
            <x-filament::card>
                <div class="text-center py-8">
                    <p class="text-gray-500">
                        @if($searchQuery)
                            No pages found matching "{{ $searchQuery }}".
                        @else
                            No pages available.
                        @endif
                    </p>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>