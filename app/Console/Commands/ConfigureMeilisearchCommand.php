<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Meilisearch\Client;

class ConfigureMeilisearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:configure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply Meilisearch index settings';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): int
    {
        $index = $client->index((new Product)->searchableAs());

        $index->updateSettings([
            'searchableAttributes' => [
                'name',
                'description',
                'category_names',
                'vendor_name',
                'tags',
            ],

            'filterableAttributes' => [
                'vendor_id',
                'category_ids',
                'price',
                'rating_avg',
                'in_stock',
            ],

            'sortableAttributes' => [
                'price',
                'rating_avg',
                'created_at',
            ],

            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => [
                    'oneTypo' => 4,
                    'twoTypos' => 8,
                ],
            ],
        ]);

        $this->info('Meilisearch settings updated.');

        return self::SUCCESS;
    }
}
