<?php namespace GeneaLabs\LaravelModelCaching\Console\Commands;

use Illuminate\Console\Command;

class Flush extends Command
{
    protected $signature = 'modelCache:flush {--model=}';
    protected $description = 'Flush cache for a given model. If no model is given, entire model-cache is flushed.';

    public function handle()
    {
        $option = $this->option('model');

        if (! $option) {
            cache()
                ->store(config('laravel-model-caching.store'))
                ->flush();

            $this->info("✔︎ Entire model cache has been flushed.");

            return 0;
        }

        $model = new $option;
        $usesCachableTrait = collect(class_uses($model))
            ->contains("GeneaLabs\LaravelModelCaching\Traits\Cachable");

        if (! $usesCachableTrait) {
            $this->error("'{$option}' is not an instance of CachedModel.");
            $this->line("Only CachedModel instances can be flushed.");

            return 1;
        }

        $model->flushCache();
        $this->info("✔︎ Cache for model '{$option}' has been flushed.");
    }
}
