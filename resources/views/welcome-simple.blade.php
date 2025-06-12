<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md mx-auto text-center">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    Welcome to {{ config('app.name') }}
                </h1>
                <p class="text-gray-600 mb-6">
                    Your FilaMan installation is ready! This is a bare bones installation with no plugins.
                </p>
                <div class="space-y-4">
                    <p class="text-sm text-gray-500">
                        Add plugins to the <code class="bg-gray-100 px-2 py-1 rounded">plugins/</code> directory to get started.
                    </p>
                    <div class="border-t pt-4">
                        <p class="text-xs text-gray-400">
                            Powered by FilaMan v{{ config('app.version', '1.0.0') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>