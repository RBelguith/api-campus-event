<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Seed the campus venues.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Amphithéâtre A', 'slug' => 'amphi-a', 'address' => 'Bâtiment Sud', 'seats' => 320],
            ['name' => 'Bibliothèque', 'slug' => 'bibliotheque', 'address' => 'Cours central', 'seats' => 120],
            ['name' => 'Salle des fêtes', 'slug' => 'salle-des-fetes', 'address' => 'Pavillon étudiant', 'seats' => 500],
        ] as $venue) {
            Venue::factory()->withSlug($venue['slug'])->create($venue);
        }
    }
}
