<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\Command;
use Rennokki\ElasticScout\Index;

class MigrateIndexCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'elasticscout:index:migrate {model} {name}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete an Index class from the Elasticsearch cluster.';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $modelClass = trim($this->argument('model'));
        $model = new $modelClass;

        $currentIndex = $model->getIndex();
        $currentIndexClass = get_class($currentIndex);

        $targetIndexClass = trim($this->argument('index'));
        $targetIndex = new $targetIndex;

        if (! $currentIndex instanceof Index) {
            throw new InvalidArgumentException(sprintf(
                'The class %s must extend %s.',
                $currentIndex,
                Index::class
            ));
        }

        if (! $targetIndex instanceof Index) {
            throw new InvalidArgumentException(sprintf(
                'The target index class %s must extend %s.',
                $targetIndex,
                Index::class
            ));
        }

        $this->line("Trying to migrate the index {$currentIndexClass} to {$targetIndexClass}...");

        $this->line("Syncing the current index {$currentIndexClass}...");
        $currentIndex->sync();

        $this->line("Syncing the target index {$targetIndexClass}...");
        $targetIndex->sync();

        $this->line("Importing the models to {$targetIndexClass}...");

        $this->call('scout:import', [
            'model' => get_class($model),
        ]);

        $this->line("Deleting the source index {$currentIndexClass}...");
        $currentIndex->delete();

        return $this->error("The index got migrated from {$currentIndexClass} to {$targetIndexClass}!");
    }
}
