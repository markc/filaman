<?php

namespace FilaMan\Pages\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePageCommand extends Command
{
    protected $signature = 'pages:make {title} {--slug=} {--category=Main} {--description=} {--published}';

    protected $description = 'Create a new page';

    public function handle()
    {
        $title = $this->argument('title');
        $slug = $this->option('slug') ?: Str::slug($title);
        $category = $this->option('category');
        $description = $this->option('description') ?: '';
        $published = $this->option('published') !== null;

        $pagesPath = __DIR__.'/../../resources/views/pages/';
        $filePath = $pagesPath.$slug.'.md';

        if (File::exists($filePath)) {
            $this->error("Page '{$slug}' already exists!");

            return 1;
        }

        if (! File::exists($pagesPath)) {
            File::makeDirectory($pagesPath, 0755, true);
        }

        $frontMatter = [
            'title' => $title,
            'description' => $description,
            'published' => $published,
            'category' => $category,
            'order' => 999,
        ];

        $yamlString = '---'.PHP_EOL;
        foreach ($frontMatter as $key => $value) {
            if (is_bool($value)) {
                $yamlString .= $key.': '.($value ? 'true' : 'false').PHP_EOL;
            } else {
                $yamlString .= $key.': '.$value.PHP_EOL;
            }
        }
        $yamlString .= '---'.PHP_EOL.PHP_EOL;

        $content = $yamlString."# {$title}".PHP_EOL.PHP_EOL.'Your content goes here...';

        File::put($filePath, $content);

        $this->info("Page '{$slug}' created successfully!");
        $this->line("File: {$filePath}");
        $this->line("URL: /pages/{$slug}");

        return 0;
    }
}
