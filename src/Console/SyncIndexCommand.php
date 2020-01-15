<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\Command;
use Rennokki\ElasticScout\Index;

class SyncIndexCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'elasticscout:index:sync {model}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Sync an Index class to the Elasticsearch cluster.';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $modelClass = trim($this->argument('model'));
        $model = new $modelClass;

        $index = $model->getIndex();
        $indexClass = get_class($index);

        if (! $index instanceof Index) {
            throw new InvalidArgumentException(sprintf(
                'The class %s must extend %s.',
                $indexClass,
                Index::class
            ));
        }

        $this->line("Trying to sync the index {$indexClass}...");

        if ($index->sync()) {
            $this->line("The index {$indexClass} was synced to the cluster as {$index->getName()}");

            if ($index->hasAlias()) {
                $this->line("The index alias {$index->getAliasName()} was synced.");
            }

            return true;
        }

        return $this->error('The index couldn\'t be synced.');
    }
}
