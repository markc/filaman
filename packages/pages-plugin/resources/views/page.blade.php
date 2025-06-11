{{-- packages/pages-plugin/resources/views/page.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description ?: config('filaman-pages.seo.default_description', 'Filament v4.x Plugin Manager') }}">
    <meta name="keywords" content="{{ $frontMatter['keywords'] ?? config('filaman-pages.seo.default_keywords', 'filament, laravel, plugins') }}">
    <meta name="author" content="{{ $frontMatter['author'] ?? 'FilaMan' }}">
    
    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }} - {{ config('filaman-pages.seo.site_name', 'FilaMan') }}">
    <meta property="og:description" content="{{ $description ?: config('filaman-pages.seo.default_description') }}">
    <meta property="og:url" content="{{ request()->url() }}">
    
    <title>{{ $title }} - {{ config('filaman-pages.seo.site_name', 'FilaMan') }}</title>
    
    {{-- Tailwind CSS via CDN for now - could be optimized later --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Custom styles for markdown content --}}
    <style>
        /* Custom styles for better markdown rendering */
        .markdown-content {
            line-height: 1.7;
        }
        
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .markdown-content h1 {
            font-size: 2.25rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }
        
        .markdown-content h2 {
            font-size: 1.875rem;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 0.25rem;
        }
        
        .markdown-content h3 {
            font-size: 1.5rem;
        }
        
        .markdown-content p {
            margin-bottom: 1rem;
        }
        
        .markdown-content ul,
        .markdown-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .markdown-content li {
            margin-bottom: 0.5rem;
        }
        
        .markdown-content blockquote {
            border-left: 4px solid #3b82f6;
            background-color: #f8fafc;
            padding: 1rem;
            margin: 1.5rem 0;
            font-style: italic;
        }
        
        .markdown-content pre {
            background-color: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1.5rem 0;
        }
        
        .markdown-content code {
            background-color: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }
        
        .markdown-content pre code {
            background-color: transparent;
            padding: 0;
        }
        
        .markdown-content a {
            color: #3b82f6;
            text-decoration: underline;
            transition: color 0.2s ease;
        }
        
        .markdown-content a:hover {
            color: #1d4ed8;
        }
        
        .markdown-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }
        
        .markdown-content th,
        .markdown-content td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
        }
        
        .markdown-content th {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        
        .markdown-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }
        
        .page-meta {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    @include('filaman-pages::partials.navbar')
    
    <main class="container mx-auto px-4 max-w-4xl">
        {{-- Page Header --}}
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            
            @if($description)
                <p class="text-xl text-gray-600 mb-4">{{ $description }}</p>
            @endif
            
            {{-- Page Metadata --}}
            @if(isset($frontMatter['date']) || isset($frontMatter['author']))
                <div class="page-meta">
                    @if(isset($frontMatter['date']))
                        <span>Published: {{ \Carbon\Carbon::parse($frontMatter['date'])->format('F j, Y') }}</span>
                    @endif
                    
                    @if(isset($frontMatter['author']))
                        <span class="ml-4">By: {{ $frontMatter['author'] }}</span>
                    @endif
                    
                    @if(isset($frontMatter['tags']))
                        <div class="mt-2">
                            <span>Tags: </span>
                            @foreach(explode(',', $frontMatter['tags']) as $tag)
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </header>
        
        {{-- Page Content --}}
        <article class="markdown-content bg-white rounded-lg shadow-sm p-8 mb-8">
            {!! $content !!}
        </article>
        
        {{-- Page Navigation --}}
        <nav class="flex justify-between items-center py-8 border-t border-gray-200">
            @php
                $currentIndex = array_search($slug, array_column($pages, 'slug'));
                $prevPage = $currentIndex > 0 ? $pages[$currentIndex - 1] : null;
                $nextPage = $currentIndex !== false && $currentIndex < count($pages) - 1 ? $pages[$currentIndex + 1] : null;
            @endphp
            
            <div class="flex-1">
                @if($prevPage)
                    <a href="{{ route('filaman.pages.show', ['slug' => $prevPage['slug']]) }}" 
                       class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        {{ $prevPage['title'] }}
                    </a>
                @endif
            </div>
            
            <div class="flex-1 text-right">
                @if($nextPage)
                    <a href="{{ route('filaman.pages.show', ['slug' => $nextPage['slug']]) }}" 
                       class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        {{ $nextPage['title'] }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </nav>
    </main>
    
    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="container mx-auto px-4 max-w-4xl text-center">
            <p>&copy; {{ date('Y') }} FilaMan - Filament v4.x Plugin Manager</p>
            <p class="text-gray-400 text-sm mt-2">
                Built with ❤️ using 
                <a href="https://laravel.com" class="text-blue-400 hover:text-blue-300">Laravel</a>, 
                <a href="https://filamentphp.com" class="text-blue-400 hover:text-blue-300">Filament</a>, and 
                <a href="https://claude.ai/code" class="text-blue-400 hover:text-blue-300">Claude Code</a>
            </p>
        </div>
    </footer>
</body>
</html>