<?php

namespace FilaMan\Pages\Commands;

use FilaMan\Pages\Models\Page;
use Illuminate\Console\Command;

class ListPagesCommand extends Command
{
    protected $signature = 'pages:list {--published} {--category=}';

    protected $description = 'List all pages';

    public function handle()
    {
        $pages = Page::getAllFromFiles();

        if ($this->option('published')) {
            $pages = array_filter($pages, fn ($page) => $page->published);
        }

        if ($category = $this->option('category')) {
            $pages = array_filter($pages, fn ($page) => $page->category === $category);
        }

        if (empty($pages)) {
            $this->info('No pages found.');

            return 0;
        }

        $headers = ['Slug', 'Title', 'Category', 'Published', 'Order'];
        $rows = [];

        foreach ($pages as $page) {
            $rows[] = [
                $page->slug,
                $page->title,
                $page->category,
                $page->published ? 'Yes' : 'No',
                $page->order,
            ];
        }

        $this->table($headers, $rows);

        $this->info('Total pages: '.count($pages));

        return 0;
    }
}
