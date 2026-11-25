<?php

use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);
test('draft and withdrawn image URLs are private while published images are served', function () {
    Storage::fake('local');
    Storage::disk('local')->put('media/example.webp', 'test image');
    $item = GalleryItem::factory()->published()->create(['image_path' => 'media/example.webp']);
    $this->get('/images/media/example.webp')->assertOk()->assertHeader('Content-Type', 'image/webp');
    $item->update(['status' => 'draft']);
    $this->get('/images/media/example.webp')->assertNotFound();
    $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/images/media/example.webp')->assertOk();
});
test('private image endpoint rejects paths outside generated media names', function () {
    $this->get('/images/secret.txt')->assertNotFound();
    $this->get('/images/media/secret.php')->assertNotFound();
});
