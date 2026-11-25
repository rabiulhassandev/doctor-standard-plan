<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the application opens the default Bangla homepage', function () {
    $response = $this->get('/');

    $response->assertRedirect('/bn');
});
