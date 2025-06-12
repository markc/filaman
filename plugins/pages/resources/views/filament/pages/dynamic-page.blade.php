@php
    $metaTags = $this->getMetaTags();
@endphp

@push('head')
    <title>{{ $metaTags['title'] }}</title>
    <meta name="description" content="{{ $metaTags['description'] }}">
    <link rel="canonical" href="{{ $metaTags['canonical'] }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $metaTags['og:title'] }}">
    <meta property="og:description" content="{{ $metaTags['og:description'] }}">
    <meta property="og:url" content="{{ $metaTags['og:url'] }}">
    <meta property="og:type" content="{{ $metaTags['og:type'] }}">
    <meta property="og:site_name" content="{{ $metaTags['og:site_name'] }}">
    @if($this->getFeaturedImage())
        <meta property="og:image" content="{{ asset('storage/' . $this->getFeaturedImage()) }}">
        <meta name="twitter:image" content="{{ asset('storage/' . $this->getFeaturedImage()) }}">
    @endif
    
    <!-- Twitter -->
    <meta name="twitter:card" content="{{ $metaTags['twitter:card'] }}">
    <meta name="twitter:title" content="{{ $metaTags['twitter:title'] }}">
    <meta name="twitter:description" content="{{ $metaTags['twitter:description'] }}">
    
    <!-- Prism.js for syntax highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" rel="stylesheet" media="(prefers-color-scheme: dark)" />
    
    <!-- Custom markdown styles -->
    <style>
        /* Custom styles for better markdown rendering with dark mode support */
        .markdown-content {
            line-height: 1.7 !important;
        }
        
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            margin-top: 2rem !important;
            margin-bottom: 1rem !important;
            font-weight: 600 !important;
            line-height: 1.3 !important;
        }
        
        .markdown-content h1 {
            font-size: 2.25rem !important;
            border-bottom: 2px solid #e5e7eb !important;
            padding-bottom: 0.5rem !important;
        }
        
        .dark .markdown-content h1 {
            border-bottom-color: #374151 !important;
        }
        
        .markdown-content h2 {
            font-size: 1.875rem !important;
            border-bottom: 1px solid #f3f4f6 !important;
            padding-bottom: 0.25rem !important;
        }
        
        .dark .markdown-content h2 {
            border-bottom-color: #1f2937 !important;
        }
        
        .markdown-content h3 {
            font-size: 1.5rem !important;
        }
        
        .markdown-content h4 {
            font-size: 1.25rem !important;
        }
        
        .markdown-content h5 {
            font-size: 1.125rem !important;
        }
        
        .markdown-content h6 {
            font-size: 1rem !important;
            font-weight: 700 !important;
        }
        
        .markdown-content p {
            margin-bottom: 1rem !important;
        }
        
        .markdown-content ul,
        .markdown-content ol {
            margin-bottom: 1rem !important;
            padding-left: 2rem !important;
        }
        
        .markdown-content li {
            margin-bottom: 0.5rem !important;
        }
        
        .markdown-content blockquote {
            border-left: 4px solid #3b82f6 !important;
            background-color: #f8fafc !important;
            padding: 1rem !important;
            margin: 1.5rem 0 !important;
            font-style: italic !important;
            border-radius: 0.375rem !important;
        }
        
        .dark .markdown-content blockquote {
            background-color: #1e293b !important;
        }
        
        .markdown-content pre {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
            padding: 1rem !important;
            border-radius: 0.5rem !important;
            overflow-x: auto !important;
            margin: 1.5rem 0 !important;
        }
        
        .dark .markdown-content pre {
            background-color: #0f172a !important;
        }
        
        .markdown-content code {
            background-color: #f3f4f6 !important;
            color: #1f2937 !important;
            padding: 0.25rem 0.5rem !important;
            border-radius: 0.25rem !important;
            font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace !important;
            font-size: 0.875rem !important;
        }
        
        .dark .markdown-content code {
            background-color: #374151 !important;
            color: #e5e7eb !important;
        }
        
        .markdown-content pre code {
            background-color: transparent !important;
            padding: 0 !important;
            color: inherit !important;
        }
        
        .markdown-content a {
            color: #2563eb !important;
            text-decoration: underline !important;
            transition: color 0.2s ease !important;
        }
        
        .markdown-content a:hover {
            color: #1d4ed8 !important;
        }
        
        .dark .markdown-content a {
            color: #60a5fa !important;
        }
        
        .dark .markdown-content a:hover {
            color: #93c5fd !important;
        }
        
        .markdown-content table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 1.5rem 0 !important;
            border-radius: 0.5rem !important;
            overflow: hidden !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        
        .markdown-content th,
        .markdown-content td {
            border: 1px solid #d1d5db !important;
            padding: 0.75rem !important;
            text-align: left !important;
        }
        
        .dark .markdown-content th,
        .dark .markdown-content td {
            border-color: #4b5563 !important;
        }
        
        .markdown-content th {
            background-color: #f9fafb !important;
            font-weight: 600 !important;
        }
        
        .dark .markdown-content th {
            background-color: #374151 !important;
        }
        
        .markdown-content tbody tr:nth-child(even) {
            background-color: #f8fafc !important;
        }
        
        .dark .markdown-content tbody tr:nth-child(even) {
            background-color: #1f2937 !important;
        }
        
        .markdown-content img {
            max-width: 100% !important;
            height: auto !important;
            border-radius: 0.5rem !important;
            margin: 1.5rem 0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        
        .dark .markdown-content img {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
        }
        
        /* Task list styling */
        .markdown-content .task-list-item {
            list-style: none !important;
            margin-left: -2rem !important;
            padding-left: 2rem !important;
        }
        
        .markdown-content .task-list-item input[type="checkbox"] {
            margin-right: 0.5rem !important;
            margin-left: 0 !important;
        }
        
        /* Horizontal rules */
        .markdown-content hr {
            border: none !important;
            border-top: 2px solid #e5e7eb !important;
            margin: 2rem 0 !important;
        }
        
        .dark .markdown-content hr {
            border-top-color: #374151 !important;
        }
        
        /* Override any Filament styles that might interfere */
        .markdown-content * {
            color: inherit !important;
        }
        
        .markdown-content h1, 
        .markdown-content h2, 
        .markdown-content h3, 
        .markdown-content h4, 
        .markdown-content h5, 
        .markdown-content h6 {
            color: inherit !important;
        }
    </style>
    
    <!-- Custom CSS -->
    @if($this->getCustomCss())
        <style>
            {!! $this->getCustomCss() !!}
        </style>
    @endif
@endpush

@push('scripts')
    <!-- Prism.js for syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    
    <!-- Custom JavaScript -->
    @if($this->getCustomJs())
        <script>
            {!! $this->getCustomJs() !!}
        </script>
    @endif
@endpush

<div class="fi-main-content-ctn">
    <div class="fi-main-content">
        <div class="fi-main-content-inner">
    {{-- Breadcrumbs --}}
    <div class="mt-4 mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/pages" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600">
                        <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6z"/>
                        </svg>
                        Pages
                    </a>
                </li>
                @if(isset($this->frontMatter['category']) && $this->frontMatter['category'] !== 'Main')
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500">{{ $this->frontMatter['category'] }}</span>
                        </div>
                    </li>
                @endif
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500">{{ $this->pageTitle }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    {{-- Page Content --}}
    <div class="fi-section bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 rounded-xl">
        <div class="fi-section-content p-6">
            {{-- Custom styled markdown content --}}
            <div class="markdown-content">
                <style>
                    /* Reset and base styles */
                    .markdown-content {
                        line-height: 1.7;
                        color: #1f2937;
                    }
                    
                    .dark .markdown-content {
                        color: #f9fafb;
                    }
                    
                    /* Headings */
                    .markdown-content h1,
                    .markdown-content h2,
                    .markdown-content h3,
                    .markdown-content h4,
                    .markdown-content h5,
                    .markdown-content h6 {
                        font-weight: 600;
                        line-height: 1.3;
                        margin-top: 2rem;
                        margin-bottom: 1rem;
                        color: #111827;
                    }
                    
                    .markdown-content h1 {
                        margin-top: 0;
                    }
                    
                    .dark .markdown-content h1,
                    .dark .markdown-content h2,
                    .dark .markdown-content h3,
                    .dark .markdown-content h4,
                    .dark .markdown-content h5,
                    .dark .markdown-content h6 {
                        color: #f9fafb;
                    }
                    
                    .markdown-content h1 {
                        font-size: 2.25rem;
                        border-bottom: 2px solid #e5e7eb;
                        padding-bottom: 0.5rem;
                    }
                    
                    .dark .markdown-content h1 {
                        border-bottom-color: #374151;
                    }
                    
                    .markdown-content h2 {
                        font-size: 1.875rem;
                        border-bottom: 1px solid #f3f4f6;
                        padding-bottom: 0.25rem;
                    }
                    
                    .dark .markdown-content h2 {
                        border-bottom-color: #1f2937;
                    }
                    
                    .markdown-content h3 {
                        font-size: 1.5rem;
                    }
                    
                    .markdown-content h4 {
                        font-size: 1.25rem;
                    }
                    
                    .markdown-content h5 {
                        font-size: 1.125rem;
                    }
                    
                    .markdown-content h6 {
                        font-size: 1rem;
                        font-weight: 700;
                    }
                    
                    /* Paragraphs */
                    .markdown-content p {
                        margin-bottom: 1rem;
                    }
                    
                    /* Lists - Critical styling */
                    .markdown-content ul {
                        list-style-type: disc;
                        margin-left: 0;
                        margin-right: 0;
                        padding-left: 2rem;
                        margin-top: 1rem;
                        margin-bottom: 1rem;
                    }
                    
                    .markdown-content ol {
                        list-style-type: decimal;
                        margin-left: 0;
                        margin-right: 0;
                        padding-left: 2rem;
                        margin-top: 1rem;
                        margin-bottom: 1rem;
                    }
                    
                    .markdown-content li {
                        display: list-item;
                        margin-bottom: 0.5rem;
                        padding-left: 0.25rem;
                    }
                    
                    /* Nested lists */
                    .markdown-content ul ul {
                        list-style-type: circle;
                        margin-top: 0.5rem;
                        margin-bottom: 0.5rem;
                        padding-left: 2rem;
                    }
                    
                    .markdown-content ol ol {
                        list-style-type: lower-alpha;
                        margin-top: 0.5rem;
                        margin-bottom: 0.5rem;
                        padding-left: 2rem;
                    }
                    
                    .markdown-content ul ul ul {
                        list-style-type: square;
                        padding-left: 2rem;
                    }
                    
                    .markdown-content ol ol ol {
                        list-style-type: lower-roman;
                        padding-left: 2rem;
                    }
                    
                    /* Task lists */
                    .markdown-content .task-list-item {
                        list-style-type: none;
                        margin-left: -2rem;
                        padding-left: 2rem;
                    }
                    
                    .markdown-content .task-list-item input[type="checkbox"] {
                        margin-right: 0.5rem;
                        margin-left: 0;
                    }
                    
                    /* Links */
                    .markdown-content a {
                        color: #2563eb;
                        text-decoration: underline;
                        transition: color 0.2s ease;
                    }
                    
                    .markdown-content a:hover {
                        color: #1d4ed8;
                    }
                    
                    .dark .markdown-content a {
                        color: #60a5fa;
                    }
                    
                    .dark .markdown-content a:hover {
                        color: #93c5fd;
                    }
                    
                    /* Inline code (not in pre blocks) */
                    .markdown-content code:not(pre code) {
                        background-color: #f3f4f6;
                        color: #1f2937;
                        padding: 0.25rem 0.5rem;
                        border-radius: 0.25rem;
                        font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
                        font-size: 0.875rem;
                    }
                    
                    .dark .markdown-content code:not(pre code) {
                        background-color: #374151;
                        color: #e5e7eb;
                    }
                    
                    /* Code blocks - let Prism.js handle the styling */
                    .markdown-content pre {
                        padding: 1rem;
                        border-radius: 0.5rem;
                        overflow-x: auto;
                        margin: 1.5rem 0;
                        font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
                    }
                    
                    /* Remove our background from code blocks, let Prism handle it */
                    .markdown-content pre code {
                        background: none !important;
                        padding: 0 !important;
                        border-radius: 0 !important;
                        font-size: inherit !important;
                    }
                    
                    /* Ensure Prism.js colors work in dark mode */
                    .dark .markdown-content pre[class*="language-"] {
                        background: #282c34 !important;
                    }
                    
                    .dark .markdown-content pre[class*="language-"] code {
                        color: inherit !important;
                        background: none !important;
                    }
                    
                    /* Tables */
                    .markdown-content table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 1.5rem 0;
                        border-radius: 0.5rem;
                        overflow: hidden;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    
                    .markdown-content th,
                    .markdown-content td {
                        border: 1px solid #d1d5db;
                        padding: 0.75rem;
                        text-align: left;
                    }
                    
                    .dark .markdown-content th,
                    .dark .markdown-content td {
                        border-color: #4b5563;
                    }
                    
                    .markdown-content th {
                        background-color: #f9fafb;
                        font-weight: 600;
                    }
                    
                    .dark .markdown-content th {
                        background-color: #374151;
                    }
                    
                    .markdown-content tbody tr:nth-child(even) {
                        background-color: #f8fafc;
                    }
                    
                    .dark .markdown-content tbody tr:nth-child(even) {
                        background-color: #1f2937;
                    }
                    
                    /* Blockquotes */
                    .markdown-content blockquote {
                        border-left: 4px solid #3b82f6;
                        background-color: #f8fafc;
                        padding: 1rem;
                        margin: 1.5rem 0;
                        font-style: italic;
                        border-radius: 0.375rem;
                    }
                    
                    .dark .markdown-content blockquote {
                        background-color: #1e293b;
                    }
                    
                    /* Images */
                    .markdown-content img {
                        max-width: 100%;
                        height: auto;
                        border-radius: 0.5rem;
                        margin: 1.5rem 0;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    
                    .dark .markdown-content img {
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
                    }
                    
                    /* Horizontal rules */
                    .markdown-content hr {
                        border: none;
                        border-top: 2px solid #e5e7eb;
                        margin: 2rem 0;
                    }
                    
                    .dark .markdown-content hr {
                        border-top-color: #374151;
                    }
                    
                    /* Details and Summary elements */
                    .markdown-content details {
                        border: 1px solid #e5e7eb;
                        border-radius: 0.5rem;
                        padding: 0;
                        margin: 1rem 0;
                        background-color: #f9fafb;
                    }
                    
                    .dark .markdown-content details {
                        border-color: #374151;
                        background-color: #1f2937;
                    }
                    
                    .markdown-content summary {
                        padding: 1rem;
                        font-weight: 600;
                        cursor: pointer;
                        background-color: #f3f4f6;
                        border-radius: 0.5rem 0.5rem 0 0;
                        border-bottom: 1px solid #e5e7eb;
                        transition: background-color 0.2s ease;
                    }
                    
                    .dark .markdown-content summary {
                        background-color: #374151;
                        border-bottom-color: #4b5563;
                    }
                    
                    .markdown-content summary:hover {
                        background-color: #e5e7eb;
                    }
                    
                    .dark .markdown-content summary:hover {
                        background-color: #4b5563;
                    }
                    
                    .markdown-content details[open] summary {
                        border-radius: 0.5rem 0.5rem 0 0;
                    }
                    
                    .markdown-content details:not([open]) summary {
                        border-radius: 0.5rem;
                        border-bottom: none;
                    }
                    
                    .markdown-content details > *:not(summary) {
                        padding: 1rem;
                    }
                    
                    /* Custom arrow for details */
                    .markdown-content summary::marker {
                        content: '';
                    }
                    
                    .markdown-content summary::before {
                        content: '▶';
                        display: inline-block;
                        margin-right: 0.5rem;
                        transition: transform 0.2s ease;
                        color: #6b7280;
                    }
                    
                    .markdown-content details[open] summary::before {
                        transform: rotate(90deg);
                    }
                    
                    /* Normal code block styling */
                    .markdown-content pre {
                        font-family: 'SF Mono', Menlo, Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace !important;
                        font-size: 14px !important;
                        line-height: 1.5 !important;
                        font-weight: 400 !important;
                        letter-spacing: 0 !important;
                    }
                    
                    /* Specific styling for code content */
                    .markdown-content pre code {
                        font-family: inherit !important;
                        font-size: inherit !important;
                        line-height: inherit !important;
                        letter-spacing: 0 !important;
                        word-spacing: 0 !important;
                        white-space: pre !important;
                        tab-size: 4 !important;
                    }
                </style>
                
                {!! $htmlContent !!}
            </div>
        </div>
    </div>
    
    {{-- Prism.js syntax highlighting with proper dependency order --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    
    {{-- Load base language dependencies first --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    
    {{-- Load specific language components in dependency order --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>
    
    {{-- Load autoloader last as fallback for other languages --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    
    {{-- Initialize syntax highlighting using Filament's Alpine.js patterns --}}
    <script>
        document.addEventListener('livewire:navigated', function() {
            if (typeof Prism !== 'undefined') {
                Prism.highlightAll();
            }
        });
        
        // Also run on initial load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Prism !== 'undefined') {
                Prism.highlightAll();
            }
        });
    </script>
        </div>
    </div>
</div>