<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCatalog;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_the_interactive_map_is_public(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_a_region_and_its_books_are_public(): void
    {
        $region = $this->createRegion('Caribe');
        $this->createBook($region);

        $this->get(route('regions.show1', $region->id))
            ->assertOk()
            ->assertSee('Caribe');
    }

    public function test_an_unknown_region_returns_not_found(): void
    {
        $this->get(route('regions.show1', 999))->assertNotFound();
    }

    public function test_self_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }
}
