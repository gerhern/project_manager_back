<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class RefreshDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the database and restore demo data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning dirty data...');

        Artisan::call('migrate:fresh', ['--force' => true]);

        $this->info('Cleaned, Seeding...');

        Artisan::call('db:seed', ['--force' => true]);

        $this->info('Successful, project restored.');
    }
}
