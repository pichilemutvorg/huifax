<?php

namespace Tests\Feature;

use App\Display\DisplayPayload;
use App\Filament\Resources\FeaturedPrograms\Pages\ManageFeaturedPrograms;
use App\Models\FeaturedProgram;
use App\Models\Slide;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FeaturedProgramsTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_active_featured_program_is_its_own_page(): void
    {
        $now = CarbonImmutable::parse('2026-11-23 12:00');
        Slide::factory()->ofType('featured_programs')->create(['duration' => 12]);
        FeaturedProgram::factory()->create(['name' => 'Noticiero', 'label' => 'En vivo', 'sort_order' => 1, 'duration' => 20]);
        FeaturedProgram::factory()->create(['name' => 'Documental', 'sort_order' => 2]);
        FeaturedProgram::factory()->create(['name' => 'Ya terminó', 'ends_at' => '2026-11-22 00:00']);
        FeaturedProgram::factory()->create(['name' => 'Todavía no', 'starts_at' => '2026-11-24 00:00']);
        FeaturedProgram::factory()->disabled()->create(['name' => 'Oculto']);

        $slides = app(DisplayPayload::class)->build($now)['slides'];

        $this->assertSame(['Noticiero', 'Documental'], array_column(array_column($slides, 'data'), 'name'));
        $this->assertSame([20, 12], array_column($slides, 'duration'));
        $this->assertSame('En vivo', $slides[0]['data']['label']);
        $this->assertStringEndsWith('.webp', $slides[0]['data']['image']);
    }

    public function test_the_slide_is_skipped_without_active_programs(): void
    {
        Slide::factory()->ofType('featured_programs')->create();

        $this->assertSame([], app(DisplayPayload::class)->build()['slides']);
    }

    public function test_an_editor_can_create_a_featured_program_with_an_optimized_image(): void
    {
        Storage::fake(FeaturedProgram::DISK);
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageFeaturedPrograms::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Conversaciones',
                'tagline' => 'Entrevistas a vecinas y vecinos',
                'schedule' => 'Martes y jueves, 19:00',
                'label' => 'Estreno',
                'path' => UploadedFile::fake()->image('arte.jpg', 2400, 1350),
                'is_enabled' => true,
            ])
            ->assertHasNoActionErrors();

        $program = FeaturedProgram::query()->sole();
        $this->assertStringStartsWith(FeaturedProgram::DIRECTORY.'/', $program->path);
        $this->assertStringEndsWith('.webp', $program->path);
        Storage::disk(FeaturedProgram::DISK)->assertExists($program->path);
    }

    public function test_deleting_a_featured_program_removes_its_image(): void
    {
        Storage::fake(FeaturedProgram::DISK);
        Storage::disk(FeaturedProgram::DISK)->put('featured-programs/a.webp', 'image');
        $program = FeaturedProgram::factory()->create(['path' => 'featured-programs/a.webp']);

        $program->delete();

        Storage::disk(FeaturedProgram::DISK)->assertMissing('featured-programs/a.webp');
    }
}
