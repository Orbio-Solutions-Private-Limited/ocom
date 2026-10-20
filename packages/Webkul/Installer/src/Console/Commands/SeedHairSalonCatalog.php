<?php

namespace Webkul\Installer\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Installer\Database\Seeders\HairSalonCatalogSeeder;

class SeedHairSalonCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto:seed-hair-salon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add hair salon categories and products without removing existing catalog data.';

    /**
     * Execute the console command.
     */
    public function handle(HairSalonCatalogSeeder $seeder): int
    {
        $seeder->setCommand($this);
        $seeder->run();

        return self::SUCCESS;
    }
}
