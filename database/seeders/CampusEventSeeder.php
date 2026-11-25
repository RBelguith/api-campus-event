<?php

namespace Database\Seeders;

use App\Models\CampusEvent;
use App\Models\Topic;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class CampusEventSeeder extends Seeder
{
    /**
     * Seed twenty realistic campus events with their topics.
     */
    public function run(): void
    {
        $users = User::all();
        $venues = Venue::all();
        $topics = Topic::all();

        foreach (range(1, 20) as $index) {
            $event = CampusEvent::factory()->create([
                'user_id' => $users[($index - 1) % $users->count()]->id,
                'venue_id' => $venues[($index - 1) % $venues->count()]->id,
                'title' => 'Événement campus '.$index,
                'slug' => 'evenement-campus-'.$index,
            ]);

            $event->topics()->attach(
                collect([
                    $topics[($index - 1) % $topics->count()],
                    $topics[$index % $topics->count()],
                ])->unique('id')->pluck('id')
            );
        }
    }
}
