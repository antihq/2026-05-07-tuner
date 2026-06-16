<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ErrorTracker
{
    protected array $queue = [];

    public function capture(Throwable $e): void
    {
        if (! config('error-tracker.enabled')) {
            return;
        }

        foreach (config('error-tracker.excluded_exceptions', []) as $class) {
            if ($e instanceof $class) {
                return;
            }
        }

        $this->queue[] = [
            'title' => Str::limit(get_class($e).': '.$e->getMessage(), 255),
            'user_id' => Auth::id(),
            'description' => $e->getFile().':'.$e->getLine(),
        ];
    }

    public function flush(): void
    {
        $url = config('error-tracker.url');

        if (! $url || empty($this->queue)) {
            return;
        }

        foreach ($this->queue as $payload) {
            try {
                Http::timeout(5)->post($url, $payload);
            } catch (Throwable $e) {
                Log::warning('Error tracker flush failed: '.$e->getMessage());
            }
        }

        $this->queue = [];
    }
}
