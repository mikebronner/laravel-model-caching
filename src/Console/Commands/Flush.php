<?php namespace GeneaLabs\LaravelModelCaching\Console\Commands;

use Illuminate\Console\Command;

class Flush extends Command
{
    protected $signature = 'modelCache:flush {--model=}';
    protected $description = 'Flush cache for a given model.';

    public function handle()
    {
        $option = $this->option('model');
        $model = new $option;
        $model->flushCache();
        $this->info("Cache for model '{$option}' flushed.");
    }
}
