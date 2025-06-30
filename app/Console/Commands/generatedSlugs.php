<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;

class generatedSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generated-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Property::all()->each(function ($property) {
            $slug = $property->generateSlug();
            $property->slug = $slug;
            $property->save();
            $this->info("Generated slug for property ID {$property->id}: {$slug}");
        });
    }
}
