<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $amenities = [
            [
                'id' => 1,
                'name' => 'Water',
                'image' => 'ph-drop',
            ],
            [
                'id' => 2,
                'name' => 'Light',
                'image' => 'ph-lightbulb',
            ],
            [
                'id' => 3,
                'name' => 'Air conditioner',
                'image' => 'ph-wind',
            ],
            [
                'id' => 4,
                'name' => 'Wifi',
                'image' => 'ph-wifi-high',
            ],
            [
                'id' => 5,
                'name' => 'Fan',
                'image' => 'ph-fan',
            ],
            [
                'id' => 6,
                'name' => 'Wardrobe',
                'image' => 'ph-door',
            ],
            [
                'id' => 7,
                'name' => 'Bed',
                'image' => 'ph-bed',
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['id' => $amenity['id']],
                [
                    'name' => $amenity['name'],
                    'image' => $amenity['image'],
                ]
            );
        }
    }
}
