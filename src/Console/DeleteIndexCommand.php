<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\Command;
use Rennokki\ElasticScout\Index;

class DeleteIndexCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'elasticscout:index:delete {model}';

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

        $index = $model->getIndex();
        $indexClass = get_class($index);

        if (! $index instanceof Index) {
            throw new InvalidArgumentException(sprintf(
                'The class %s must extend %s.',
                $indexClass,
                Index::class
            ));
        }

        $indexName = $index->getName();
        $indexAliasName = $index->getAliasName();

        $this->line("Trying to delete the index {$indexClass}...");

        if ($index->delete()) {
            $this->line("The index {$indexClass} (named {$indexName}) was deleted from the cluster.");

            if ($index->hasAlias()) {
                $this->line("The index alias {$indexAliasName} was also deleted from the cluster.");
            }

            return true;
        }

        return $this->error('The index couldn\'t be deleted.');
    }
}
