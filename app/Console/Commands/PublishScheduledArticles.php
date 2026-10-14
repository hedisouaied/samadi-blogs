<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publish any scheduled articles whose publication time has arrived';

    public function handle(ArticleService $articles): int
    {
        $count = $articles->publishDueScheduled();

        if ($count === 0) {
            $this->info('No scheduled articles were due for publication.');

            return self::SUCCESS;
        }

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
