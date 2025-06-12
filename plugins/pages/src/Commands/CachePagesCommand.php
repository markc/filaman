<?php

namespace FilaMan\Pages\Commands;

use FilaMan\Pages\Services\PageCacheService;
use Illuminate\Console\Command;

class CachePagesCommand extends Command
{
    protected $signature = 'pages:cache {action=warm : Action to perform (warm, clear)}';

    protected $description = 'Manage page cache (warm or clear)';

    public function handle(PageCacheService $cacheService)
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'warm':
                $this->info('Warming page cache...');
                $cacheService->warmCache();
                $this->info('Page cache warmed successfully!');
                break;

            case 'clear':
                $this->info('Clearing page cache...');
                $cacheService->clearAllPagesCache();
                $this->info('Page cache cleared successfully!');
                break;

            default:
                $this->error("Unknown action: {$action}");
                $this->line('Available actions: warm, clear');

                return 1;
        }

        return 0;
    }
}
