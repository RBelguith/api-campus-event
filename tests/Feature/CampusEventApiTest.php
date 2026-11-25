<?php

use App\Models\CampusEvent;
use App\Models\Topic;
use App\Models\User;
use App\Models\Venue;
use Laravel\Sanctum\Sanctum;

it('lists campus events with their loaded relations', function () {
    $topic = Topic::factory()->create();
    CampusEvent::factory()->count(2)->create()->each(
        fn (CampusEvent $event) => $event->topics()->attach($topic)
    );

    $this->getJson('/api/campus-events')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['*' => [
                'id', 'title', 'slug', 'description', 'starts_at', 'ends_at',
                'capacity', 'is_public', 'organizer', 'venue', 'topics', 'topics_count',
            ]],
            'links',
            'meta',
        ])
        ->assertJsonCount(2, 'data');
});

it('filters campus events by venue slug', function () {
    $venue = Venue::factory()->create(['slug' => 'amphi-a']);
    $otherVenue = Venue::factory()->create(['slug' => 'bibliotheque']);
    $matching = CampusEvent::factory()->for($venue, 'venue')->create();
    CampusEvent::factory()->for($otherVenue, 'venue')->create();

    $this->getJson('/api/campus-events?venue=amphi-a')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id)
        ->assertJsonPath('data.0.venue.slug', 'amphi-a');
});

it('requires a token to create a campus event', function () {
    $this->postJson('/api/campus-events', [
        'title' => 'Événement sans jeton',
    ])->assertUnauthorized();
});

it('forbids updating another organiser campus event', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $event = CampusEvent::factory()->for($owner)->create();

    Sanctum::actingAs($other);

    $this->putJson("/api/campus-events/{$event->id}", [
        'title' => 'Tentative de modification',
        'slug' => 'tentative-de-modification',
        'description' => 'Une description suffisamment longue pour passer la validation.',
        'venue_id' => $event->venue_id,
        'starts_at' => '2026-12-10 10:00:00',
        'ends_at' => '2026-12-10 12:00:00',
        'capacity' => 40,
    ])->assertForbidden();
});

it('rejects an invalid campus event with French messages', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/campus-events', [
        'title' => 'Atelier trop court',
        'slug' => 'atelier-trop-court',
        'description' => 'court',
        'venue_id' => 999999,
        'starts_at' => 'pas-une-date',
        'ends_at' => 'avant',
        'capacity' => 0,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['description', 'venue_id', 'starts_at', 'ends_at', 'capacity'])
        ->assertJsonFragment(['La capacité doit être au moins 1.']);
});

it('creates a campus event and stores its topics', function () {
    $user = User::factory()->create();
    $venue = Venue::factory()->create();
    $topics = Topic::factory()->count(2)->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/campus-events', [
        'title' => 'Atelier API du campus',
        'slug' => 'atelier-api-du-campus',
        'description' => 'Un atelier suffisamment long pour passer la validation.',
        'venue_id' => $venue->id,
        'starts_at' => '2026-12-10 10:00:00',
        'ends_at' => '2026-12-10 12:00:00',
        'capacity' => 50,
        'is_public' => true,
        'topics' => $topics->pluck('id')->all(),
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Atelier API du campus')
        ->assertJsonPath('data.organizer.name', $user->name)
        ->assertJsonCount(2, 'data.topics');

    $this->assertDatabaseHas('campus_events', [
        'id' => $response->json('data.id'),
        'slug' => 'atelier-api-du-campus',
        'user_id' => $user->id,
        'venue_id' => $venue->id,
    ]);

    $this->assertDatabaseHas('campus_event_topic', [
        'campus_event_id' => $response->json('data.id'),
        'topic_id' => $topics->first()->id,
    ]);
});
