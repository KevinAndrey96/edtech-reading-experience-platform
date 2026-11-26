<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCatalog;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public function adminOnlyPages(): array
    {
        return [
            'usuarios' => ['/users'],
            'crear usuario' => ['/users/create'],
            'regiones' => ['/regions'],
            'libros' => ['/books'],
            'crear libro' => ['/books/create'],
            'evidencias' => ['/evidences'],
        ];
    }

    /**
     * @dataProvider adminOnlyPages
     */
    public function test_guests_are_sent_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect('/login');
    }

    /**
     * @dataProvider adminOnlyPages
     */
    public function test_teachers_cannot_open_admin_pages(string $uri): void
    {
        $this->actingAs(User::factory()->create())->get($uri)->assertForbidden();
    }

    /**
     * @dataProvider adminOnlyPages
     */
    public function test_admins_can_open_admin_pages(string $uri): void
    {
        $this->actingAs(User::factory()->admin()->create())->get($uri)->assertOk();
    }

    public function test_teachers_can_open_their_pages(): void
    {
        $region = $this->createRegion();
        $book = $this->createBook($region);
        $teacher = User::factory()->create();

        foreach ([
            route('books.by-region', $region->id),
            route('experiences.index'),
            route('experiences.show', $book->id),
            route('evidences.create'),
            route('evidences.show', $book->id),
            route('evidences.by-book', $book->id),
        ] as $uri) {
            $this->actingAs($teacher)->get($uri)->assertOk();
        }

        $this->actingAs($teacher)->get(route('books.show', $book))->assertRedirect(route('regions.show1', $region->id));
    }

    public function test_a_teacher_comments_in_a_book_forum(): void
    {
        $book = $this->createBook($this->createRegion());
        $teacher = User::factory()->create(['name' => 'Profe Lucía']);

        $this->actingAs($teacher)->post(route('experiences.store'), [
            'book_id' => $book->id,
            'content' => 'Mis estudiantes dibujaron al Mohán.',
        ])->assertRedirect(route('experiences.show', $book->id));

        $this->actingAs($teacher)->get(route('experiences.show', $book->id))
            ->assertOk()
            ->assertSee('Mis estudiantes dibujaron al Mohán.')
            ->assertSee('Profe Lucía');
    }

    public function test_a_teacher_edits_their_own_experience(): void
    {
        $book = $this->createBook($this->createRegion());
        $teacher = User::factory()->create();
        $experience = Experience::create(['user_id' => $teacher->id, 'book_id' => $book->id, 'content' => 'Primera versión']);

        $this->actingAs($teacher)->get(route('experiences.edit', $experience))->assertOk()->assertSee('Primera versión');
        $this->actingAs($teacher)->put(route('experiences.update', $experience), [
            'book_id' => $book->id,
            'content' => 'Versión corregida',
        ])->assertRedirect();

        $this->assertSame('Versión corregida', $experience->fresh()->content);
    }

    public function test_home_sends_each_role_to_its_start_page(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/home')->assertRedirect(route('users.index'));
        $this->actingAs(User::factory()->create())->get('/home')->assertRedirect(route('welcome'));
    }

    public function test_teachers_cannot_create_users(): void
    {
        $this->actingAs(User::factory()->create())->post('/users', [
            'name' => 'Nuevo admin',
            'email' => 'nuevo@example.com',
            'role' => 'admin',
            'password' => 'secret123',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.com']);
    }

    public function test_teachers_can_only_delete_their_own_experiences(): void
    {
        $book = $this->createBook($this->createRegion());
        $author = User::factory()->create();
        $other = User::factory()->create();
        $experience = Experience::create(['user_id' => $author->id, 'book_id' => $book->id, 'content' => 'Reflexión']);

        $this->actingAs($other)->delete(route('experiences.destroy', $experience))->assertForbidden();
        $this->assertDatabaseHas('experiences', ['id' => $experience->id]);

        $this->actingAs($author)->delete(route('experiences.destroy', $experience))->assertRedirect();
        $this->assertDatabaseMissing('experiences', ['id' => $experience->id]);
    }
}
