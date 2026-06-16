@php
    $suffix = app()->environment('production') ? null : app()->environment();
    $appName = collect([config('app.name'), $suffix])->filter()->implode(' - ');
@endphp
{
  "name": {!! json_encode($appName) !!},
  "icons": [
    { "src": "/app-icon-192.png", "type": "image/png", "sizes": "192x192" },
    { "src": "/app-icon.png", "type": "image/png", "sizes": "512x512" },
    { "src": "/app-icon-192-maskable.png", "type": "image/png", "sizes": "192x192", "purpose": "maskable" }
  ],
  "start_url": "/",
  "display": "standalone",
  "scope": "/"
}
