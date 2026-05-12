<?php

use App\Models\Channel;
use App\Models\Event;
use Illuminate\Support\Facades\URL;

test('events can be ingested via signed url', function () {
    $channel = Channel::factory()->create();
    $url = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);

    $response = $this->postJson($url, [
        'title' => 'User registered',
    ]);

    $response->assertOk()
        ->assertJson(['ok' => true]);

    $this->assertDatabaseHas('events', [
        'channel_id' => $channel->id,
        'title' => 'User registered',
        'user_id' => null,
        'description' => null,
    ]);
});

test('ingestion fails with invalid url_key', function () {
    $channel = Channel::factory()->create();
    $url = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => 'wrongkey']);

    $response = $this->postJson($url, [
        'title' => 'User registered',
    ]);

    $response->assertStatus(404);

    $this->assertDatabaseMissing('events', [
        'channel_id' => $channel->id,
    ]);
});

test('ingestion fails with invalid signature', function () {
    $channel = Channel::factory()->create();
    $url = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);
    $tamperedUrl = str_replace('signature=', 'signature=tampered', $url);

    $response = $this->postJson($tamperedUrl, [
        'title' => 'User registered',
    ]);

    $response->assertStatus(403);
});

test('ingestion fails without signature', function () {
    $channel = Channel::factory()->create();
    $url = route('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);

    $response = $this->postJson($url, [
        'title' => 'User registered',
    ]);

    $response->assertStatus(403);
});

test('ingestion validates required title', function () {
    $channel = Channel::factory()->create();
    $url = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);

    $response = $this->postJson($url, [
        'title' => '',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('ingestion creates event with optional fields', function () {
    $channel = Channel::factory()->create();
    $url = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);

    $response = $this->postJson($url, [
        'title' => 'Payment received',
        'user_id' => '42',
        'description' => 'Monthly subscription renewed',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('events', [
        'channel_id' => $channel->id,
        'title' => 'Payment received',
        'user_id' => '42',
        'description' => 'Monthly subscription renewed',
    ]);
});

test('ingestion creates event with only title', function () {
    $channel = Channel::factory()->create();
    $url = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);

    $response = $this->postJson($url, [
        'title' => 'Deployment completed',
    ]);

    $response->assertOk();

    expect(Event::where('channel_id', $channel->id)->first())
        ->title->toBe('Deployment completed')
        ->user_id->toBeNull()
        ->description->toBeNull()
        ->created_at->not->toBeNull();
});

test('rotating url key invalidates old signed urls', function () {
    $channel = Channel::factory()->create();
    $oldUrl = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->url_key]);

    $channel->rotateUrlKey();

    $response = $this->postJson($oldUrl, [
        'title' => 'Should not work',
    ]);

    $response->assertStatus(404);
});

test('new signed url works after rotation', function () {
    $channel = Channel::factory()->create();
    $channel->rotateUrlKey();

    $newUrl = URL::signedRoute('ingest', ['channel' => $channel->id, 'url_key' => $channel->fresh()->url_key]);

    $response = $this->postJson($newUrl, [
        'title' => 'Works after rotation',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('events', [
        'channel_id' => $channel->id,
        'title' => 'Works after rotation',
    ]);
});
