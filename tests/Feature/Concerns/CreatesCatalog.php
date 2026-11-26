<?php

namespace Tests\Feature\Concerns;

use App\Models\Book;
use App\Models\Region;

trait CreatesCatalog
{
    protected function createRegion(string $name = 'Alto Magdalena'): Region
    {
        return Region::create(['name' => $name, 'blog_url' => 'https://example.com/blog']);
    }

    protected function createBook(Region $region, string $name = 'La leyenda del Mohán'): Book
    {
        return Book::create([
            'region_id' => $region->id,
            'name' => $name,
            'front_page' => 'https://example.com/portada.jpg',
            'circle_audio' => 'https://open.spotify.com/episode/demo',
            'pencil_audio' => '',
            'planet_image' => '',
            'face_video' => 'https://youtu.be/demo',
            'eye_image' => '',
            'tv_video' => '',
            'message_image' => '',
            'message_text' => 'Mensaje para el docente.',
            'diamond_image' => '',
            'diamond_text' => '',
        ]);
    }
}
