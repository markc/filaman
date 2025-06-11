{{-- plugins/pages/resources/views/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse all available pages in the FilaMan plugin manager">
    <title>{{ $title }} - FilaMan</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
    @include('filaman-pages::partials.navbar')
    
    <main class="container mx-auto px-4 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            <p class="text-xl text-gray-600">
                Discover all available pages in the FilaMan documentation system.
            </p>
        </header>
        
        <div class="bg-white rounded-lg shadow-sm p-8">
            @if(empty($pages))
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📄</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Pages Found</h3>
                    <p class="text-gray-500">There are no published pages available at the moment.</p>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach($pages as $page)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="{{ route('filaman.pages.show', ['slug' => $page['slug']]) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition-colors">
                                    {{ $page['title'] }}
                                </a>
                            </h3>
                            
                            @if($page['description'])
                                <p class="text-gray-600 mb-4">{{ $page['description'] }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span class="bg-gray-100 px-2 py-1 rounded">
                                    /pages/{{ $page['slug'] }}
                                </span>
                                
                                @if(isset($page['order']))
                                    <span class="text-xs">Order: {{ $page['order'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="text-center text-gray-500">
                        <p>{{ count($pages) }} {{ Str::plural('page', count($pages)) }} available</p>
                    </div>
                </div>
            @endif
        </div>
    </main>
    
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="container mx-auto px-4 max-w-4xl text-center">
            <p>&copy; {{ date('Y') }} FilaMan - Filament v4.x Plugin Manager</p>
        </div>
    </footer>
</body>
</html>