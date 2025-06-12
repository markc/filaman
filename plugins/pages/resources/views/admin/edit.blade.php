@extends('filaman-pages::admin.layout')

@section('title', 'Edit Page - ' . $page->title)
@section('page-title', 'Edit Page')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Page</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $page->slug }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('pages.admin.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
            <a href="{{ route('pages.admin.show', $page->slug) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                View Details
            </a>
        </div>
    </div>

    <form action="{{ route('pages.admin.update', $page->slug) }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $page->title) }}"
                           required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    @if(session()->has('errors') && session('errors')->has('title'))
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ session('errors')->first('title') }}</p>
                    @endif
                </div>
                
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           value="{{ $page->slug }}"
                           disabled
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Slug cannot be changed after creation</p>
                </div>
            </div>
            
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          placeholder="Brief description of the page content..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">{{ old('description', $page->description) }}</textarea>
                @if(session()->has('errors') && session('errors')->has('description'))
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ session('errors')->first('description') }}</p>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                    <select id="category" 
                            name="category" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Category</option>
                        <option value="General" {{ old('category', $page->category) == 'General' ? 'selected' : '' }}>General</option>
                        <option value="Documentation" {{ old('category', $page->category) == 'Documentation' ? 'selected' : '' }}>Documentation</option>
                        <option value="Tutorials" {{ old('category', $page->category) == 'Tutorials' ? 'selected' : '' }}>Tutorials</option>
                        <option value="News" {{ old('category', $page->category) == 'News' ? 'selected' : '' }}>News</option>
                        <option value="About" {{ old('category', $page->category) == 'About' ? 'selected' : '' }}>About</option>
                    </select>
                    @if(session()->has('errors') && session('errors')->has('category'))
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ session('errors')->first('category') }}</p>
                    @endif
                </div>
                
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order *</label>
                    <input type="number" 
                           id="order" 
                           name="order" 
                           value="{{ old('order', $page->order) }}"
                           required 
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    @if(session()->has('errors') && session('errors')->has('order'))
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ session('errors')->first('order') }}</p>
                    @endif
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="published" 
                               name="published" 
                               value="1"
                               {{ old('published', $page->published) ? 'checked' : '' }}
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="published" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Published</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Content</h2>
            
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Markdown Content *</label>
                <textarea id="content" 
                          name="content" 
                          rows="20"
                          required
                          placeholder="Write your markdown content here..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white font-mono">{{ old('content', $page->content) }}</textarea>
                @if(session()->has('errors') && session('errors')->has('content'))
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ session('errors')->first('content') }}</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">SEO & Advanced</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="seo_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SEO Title</label>
                    <input type="text" 
                           id="seo_title" 
                           name="seo_title" 
                           value="{{ old('seo_title', $page->seo_title) }}"
                           placeholder="Custom title for search engines"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="seo_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SEO Description</label>
                    <textarea id="seo_description" 
                              name="seo_description" 
                              rows="3"
                              placeholder="Meta description for search engines"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">{{ old('seo_description', $page->seo_description) }}</textarea>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="custom_css" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom CSS</label>
                    <textarea id="custom_css" 
                              name="custom_css" 
                              rows="6"
                              placeholder=".my-custom-style { color: red; }"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white font-mono">{{ old('custom_css', $page->custom_css) }}</textarea>
                </div>
                
                <div>
                    <label for="custom_js" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom JavaScript</label>
                    <textarea id="custom_js" 
                              name="custom_js" 
                              rows="6"
                              placeholder="console.log('Hello from custom JS!');"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white font-mono">{{ old('custom_js', $page->custom_js) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('pages.admin.show', $page->slug) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md transition-colors">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Update Page
            </button>
        </div>
    </form>
</div>
@endsection