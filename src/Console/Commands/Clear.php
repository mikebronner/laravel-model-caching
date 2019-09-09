<?php namespace GeneaLabs\LaravelModelCaching\Console\Commands;

use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Clear extends Command
{
    protected $signature = 'modelCache:clear {--model=} {--database=}';
    protected $description = 'Flush cache for a given model. If no model is given, entire model-cache is flushed.';

    public function handle()
    {
        $model = $this->option('model');
        $database = $this->option('database');

        if ($model) {
            return $this->flushModelCache($model);
        }

        if ($database) {
            return $this->flushDatabaseCache($database);
        }

        return $this->flushEntireCache();
    }

    protected function flushEntireCache() : int
    {
        $config = Container::getInstance()
            ->make("config")
            ->get('laravel-model-caching.store');

        Container::getInstance()
            ->make("cache")
            ->store($config)
            ->flush();

        $this->info("✔︎ Entire model cache has been flushed.");

        return 0;
    }

    protected function flushDatabaseCache(string $database) : int
    {
        $useDatabaseKeying = Container::getInstance()
            ->make("config")
            ->get("laravel-model-caching.use-database-keying");
        
        if (! $useDatabaseKeying) {
            $this->error("Database keying must be enabled in 'config/laravel-model-caching.php'.");
            $this->line("No caches were flushed.");

            return 1;
        }

        // get all tags
        $tags = collect()
            ->filter(function ($tag) use ($database) {
                $parts = explode(":", $tag);

                return ($parts[3] ?? "") === $database;
            })
            ->toArray();

        (new CachedModel)->flushCache($tags);
        $this->info("✔︎ Cache for database '{$database}' has been flushed.");

        return 0;
    }

    protected function flushModelCache(string $modelClass) : int
    {
        $model = new $modelClass;
        $usesCachableTrait = $this->getAllTraitsUsedByClass($modelClass)
            ->contains("GeneaLabs\LaravelModelCaching\Traits\Cachable");

        if (! $usesCachableTrait) {
            $this->error("'{$modelClass}' is not an instance of CachedModel.");
            $this->line("Only CachedModel instances can be flushed.");

            return 1;
        }

        $model->flushCache();
        $this->info("✔︎ Cache for model '{$modelClass}' has been flushed.");

        return 0;
    }

    /** @SuppressWarnings(PHPMD.BooleanArgumentFlag) */
    protected function getAllTraitsUsedByClass(
        string $classname,
        bool $autoload = true
    ) : Collection {
        $traits = collect();

        if (class_exists($classname, $autoload)) {
            $traits = collect(class_uses($classname, $autoload));
        }

        $parentClass = get_parent_class($classname);

        if ($parentClass) {
            $traits = $traits
                ->merge($this->getAllTraitsUsedByClass($parentClass, $autoload));
        }

        return $traits;
    }
}
