<?php

test('home screen renders login', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
