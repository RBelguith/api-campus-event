<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Seed the topics used to tag campus events.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Musique', 'slug' => 'musique', 'description' => 'Concerts et ateliers musicaux.'],
            ['name' => 'Sport', 'slug' => 'sport', 'description' => 'Compétitions et séances collectives.'],
            ['name' => 'Robotique', 'slug' => 'robotique', 'description' => 'Projets techniques et ateliers.'],
            ['name' => 'Cinéma', 'slug' => 'cinema', 'description' => 'Projections et discussions.'],
            ['name' => 'Développement web', 'slug' => 'developpement-web', 'description' => 'Codelabs et hackathons.'],
        ] as $topic) {
            Topic::factory()->create($topic);
        }
    }
}
