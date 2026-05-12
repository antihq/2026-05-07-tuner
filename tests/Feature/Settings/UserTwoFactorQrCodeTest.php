<?php

use App\Models\User;

test('two factor qr code svg contains zinc-950 foreground color', function () {
    $user = User::factory()->withTwoFactor()->create();

    $svg = $user->twoFactorQrCodeSvg();

    expect($svg)->toContain('<svg')
        ->and($svg)->toContain('fill="#09090b"');
});

test('two factor qr code svg accepts custom url', function () {
    $user = User::factory()->withTwoFactor()->create();

    $svg = $user->twoFactorQrCodeSvg('https://example.com/custom-url');

    expect($svg)->toContain('<svg');
});

test('two factor qr code svg does not use slate-800 foreground color', function () {
    $user = User::factory()->withTwoFactor()->create();

    $svg = $user->twoFactorQrCodeSvg();

    expect($svg)->not->toContain('fill="#2d3738"');
});
