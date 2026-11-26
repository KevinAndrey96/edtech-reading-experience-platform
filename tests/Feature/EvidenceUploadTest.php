<?php

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesCatalog;
use Tests\TestCase;

class EvidenceUploadTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_teacher_registers_an_evidence_with_an_image(): void
    {
        $teacher = User::factory()->create();
        $book = $this->createBook($this->createRegion());

        $this->actingAs($teacher)->post(route('evidences.store'), [
            'student_name' => 'Valentina Gómez',
            'class_room' => 'Grado 6A',
            'book_id' => $book->id,
            'attachment_media' => UploadedFile::fake()->image('dibujo.png'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $evidence = Evidence::sole();
        $this->assertSame($teacher->id, $evidence->user_id);
        $this->assertSame("/storage/evidences_images/{$evidence->id}.png", $evidence->attachment_media_url);
        Storage::disk('public')->assertExists("evidences_images/{$evidence->id}.png");
    }

    public function test_a_teacher_cannot_attribute_an_evidence_to_someone_else(): void
    {
        $teacher = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($teacher)->post(route('evidences.store'), [
            'student_name' => 'Santiago Moreno',
            'class_room' => 'Grado 7B',
            'user_id' => $other->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($teacher->id, Evidence::sole()->user_id);
    }

    public function test_an_admin_can_attribute_an_evidence_to_a_teacher(): void
    {
        $teacher = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->post(route('evidences.store'), [
            'student_name' => 'Mariana Díaz',
            'class_room' => 'Grado 8C',
            'user_id' => $teacher->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($teacher->id, Evidence::sole()->user_id);
    }

    public function test_executable_files_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post(route('evidences.store'), [
            'student_name' => 'Mateo Vargas',
            'class_room' => 'Grado 5A',
            'attachment_media' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
        ])->assertSessionHasErrors('attachment_media');

        $this->assertSame(0, Evidence::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_the_stored_extension_comes_from_the_content_not_the_name(): void
    {
        $this->actingAs(User::factory()->create())->post(route('evidences.store'), [
            'student_name' => 'Camila Ospina',
            'class_room' => 'Grado 6B',
            'attachment_media' => UploadedFile::fake()->image('foto.JPG.png', 20, 20),
        ])->assertSessionHasNoErrors();

        $this->assertStringEndsWith('.png', Evidence::sole()->attachment_media_url);
    }
}
