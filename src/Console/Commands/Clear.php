<?php namespace GeneaLabs\LaravelModelCaching\Console\Commands;

use GeneaLabs\LaravelModelCaching\Traits\Caching;
use Illuminate\Console\Command;

class Clear extends Command
{
    protected $signature = 'modelCache:clear {--model=}';
    protected $description = 'Flush cache for a given model. If no model is given, entire model-cache is flushed.';

    public function handle()
    {
        $option = $this->option('model');

        if (! $option) {
            return $this->flushEntireCache();
        }

        return $this->flushModelCache($option);
    }

    protected function flushEntireCache() : int
    {
        app('cache')
            ->store(config('laravel-model-caching.store'))
            ->flush();

        $this->info("✔︎ Entire model cache has been flushed.");

        return 0;
    }

    protected function flushModelCache(string $option) : int
    {
        $model = new $option;
        $usesCachableTrait = Caching::getAllTraitsUsedByClass($option)
            ->contains("GeneaLabs\LaravelModelCaching\Traits\Cachable");

        if (! $usesCachableTrait) {
            $this->error("'{$option}' is not an instance of CachedModel.");
            $this->line("Only CachedModel instances can be flushed.");

            return 1;
        }

        $model->flushCache();
        $this->info("✔︎ Cache for model '{$option}' has been flushed.");

        return 0;
    }
}
