<?php namespace GeneaLabs\LaravelModelCaching\Telescope;

use \Laravel\Telescope\Watchers\ModelWatcher as PackageWatcher;

class ModelWatcher extends PackageWatcher
{
    public function recordAction($event, $data)
    {
        $modifiedData = $data;
        $modifiedData[0] = $modifiedData[0] ?? $modifiedData['model'];

        parent::recordAction($event, $modifiedData);
    }
}
