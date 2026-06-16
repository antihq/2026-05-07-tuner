<?php

use App\Services\ErrorTracker;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    Config::set('error-tracker.enabled', true);
    Config::set('error-tracker.url', 'https://example.com/api/ingest');
    Config::set('error-tracker.excluded_exceptions', [
        NotFoundHttpException::class,
        ValidationException::class,
    ]);
    Http::fake();
});

test('capture queues exception when enabled', function () {
    $tracker = new ErrorTracker;
    $e = new RuntimeException('Something broke');

    $tracker->capture($e);

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);

    expect($queue)->toHaveCount(1);
    expect($queue[0]['title'])->toBe('RuntimeException: Something broke');
    expect($queue[0]['description'])->toContain('ErrorTrackerTest.php');
});

test('capture skips when disabled', function () {
    Config::set('error-tracker.enabled', false);
    $tracker = new ErrorTracker;

    $tracker->capture(new RuntimeException('Error'));

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);
    expect($queue)->toHaveCount(0);
});

test('capture skips excluded exception', function () {
    $tracker = new ErrorTracker;

    $tracker->capture(new NotFoundHttpException('Not found'));

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);
    expect($queue)->toHaveCount(0);
});

test('capture sets user_id to null for guests', function () {
    Auth::shouldReceive('id')->andReturn(null);
    $tracker = new ErrorTracker;

    $tracker->capture(new RuntimeException('Error'));

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);
    expect($queue[0]['user_id'])->toBeNull();
});

test('capture sets user_id when authenticated', function () {
    Auth::shouldReceive('id')->andReturn(42);
    $tracker = new ErrorTracker;

    $tracker->capture(new RuntimeException('Error'));

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);
    expect($queue[0]['user_id'])->toBe(42);
});

test('capture queues multiple exceptions', function () {
    $tracker = new ErrorTracker;

    $tracker->capture(new RuntimeException('First'));
    $tracker->capture(new LogicException('Second'));

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);
    expect($queue)->toHaveCount(2);
});

test('flush does nothing when queue is empty', function () {
    $tracker = new ErrorTracker;

    $tracker->flush();

    Http::assertNothingSent();
});

test('flush does nothing when url is not configured', function () {
    Config::set('error-tracker.url', null);
    $tracker = new ErrorTracker;
    $tracker->capture(new RuntimeException('Error'));

    $tracker->flush();

    Http::assertNothingSent();
});

test('flush posts each queued error', function () {
    $tracker = new ErrorTracker;
    $tracker->capture(new RuntimeException('First'));
    $tracker->capture(new LogicException('Second'));

    $tracker->flush();

    Http::assertSentCount(2);
});

test('flush sends correct payload shape', function () {
    Auth::shouldReceive('id')->andReturn(99);
    $tracker = new ErrorTracker;
    $e = new RuntimeException('Test error');
    $tracker->capture($e);

    $tracker->flush();

    Http::assertSent(function ($request) use ($e) {
        $body = $request->data();

        expect($body)->toHaveKeys(['title', 'user_id', 'description']);
        expect($body['title'])->toBe('RuntimeException: Test error');
        expect($body['user_id'])->toBe(99);
        expect($body['description'])->toContain($e->getFile().':'.$e->getLine());

        return true;
    });
});

test('flush clears queue after sending', function () {
    $tracker = new ErrorTracker;
    $tracker->capture(new RuntimeException('Error'));

    $tracker->flush();

    $queue = (new ReflectionClass($tracker))->getProperty('queue')->getValue($tracker);
    expect($queue)->toHaveCount(0);
});

test('flush logs warning on http failure', function () {
    Http::fake(fn () => throw new ConnectionException('Timeout'));
    Log::shouldReceive('warning')->once()->withArgs(fn ($msg) => str_contains($msg, 'Error tracker flush failed'));

    $tracker = new ErrorTracker;
    $tracker->capture(new RuntimeException('Error'));

    $tracker->flush();
});

test('flush continues posting after a failure', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
            throw new ConnectionException('Timeout');
        }

        return Http::response([], 200);
    });
    Log::shouldReceive('warning')->once();

    $tracker = new ErrorTracker;
    $tracker->capture(new RuntimeException('First'));
    $tracker->capture(new RuntimeException('Second'));

    $tracker->flush();

    expect($callCount)->toBe(2);
});
