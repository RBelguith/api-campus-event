<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the two organisers used by the API tests and Postman.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Claire Fontaine',
            'email' => 'organisateur1@campus.test',
        ]);

        User::factory()->create([
            'name' => 'Roland Marchand',
            'email' => 'organisateur2@campus.test',
        ]);
    }
}
