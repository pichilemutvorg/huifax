<?php

namespace Tests\Feature\Schedule;

use App\Filament\Resources\Programs\Pages\ManagePrograms;
use App\Filament\Resources\ScheduleExceptions\Pages\ManageScheduleExceptions;
use App\Filament\Resources\ScheduleSlots\Pages\ManageScheduleSlots;
use App\Filament\Resources\SpecialBroadcasts\Pages\ManageSpecialBroadcasts;
use App\Models\Program;
use App\Models\ScheduleException;
use App\Models\ScheduleSlot;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The schedule is content: editors manage it.
        $this->actingAs(User::factory()->create());
    }

    public function test_schedule_pages_render(): void
    {
        ScheduleException::factory()->create(['rescheduled_to' => '2026-11-25 10:00']);

        foreach ([ManagePrograms::class, ManageScheduleSlots::class, ManageScheduleExceptions::class, ManageSpecialBroadcasts::class] as $page) {
            Livewire::test($page)->assertOk();
        }
    }

    public function test_an_editor_can_create_a_weekly_slot(): void
    {
        $program = Program::factory()->create();

        Livewire::test(ManageScheduleSlots::class)
            ->callAction(CreateAction::class, data: [
                'program_id' => $program->id,
                'weekdays' => ['1', '3'],
                'start_time' => '21:30',
                'duration_minutes' => 45,
                'tag' => 'premiere',
                'is_enabled' => true,
            ])
            ->assertHasNoActionErrors();

        $slot = ScheduleSlot::query()->sole();
        $this->assertSame([1, 3], array_map('intval', $slot->weekdays));
        $this->assertStringStartsWith('21:30', $slot->start_time);
    }

    public function test_an_exception_must_fall_on_an_airing_date(): void
    {
        // Mondays only; 2026-11-24 is a Tuesday.
        $slot = ScheduleSlot::factory()->on([1], '21:00')->create();

        Livewire::test(ManageScheduleExceptions::class)
            ->callAction(CreateAction::class, data: [
                'schedule_slot_id' => $slot->id,
                'date' => '2026-11-24',
                'action' => 'cancel',
            ])
            ->assertHasActionErrors(['date']);
    }

    public function test_an_airing_can_only_have_one_exception(): void
    {
        $slot = ScheduleSlot::factory()->on([1], '21:00')->create();
        ScheduleException::factory()->for($slot, 'slot')->create(['date' => '2026-11-23']);

        Livewire::test(ManageScheduleExceptions::class)
            ->callAction(CreateAction::class, data: [
                'schedule_slot_id' => $slot->id,
                'date' => '2026-11-23',
                'action' => 'cancel',
            ])
            ->assertHasActionErrors(['date']);
    }

    public function test_moving_requires_a_new_time(): void
    {
        $slot = ScheduleSlot::factory()->on([1], '21:00')->create();

        Livewire::test(ManageScheduleExceptions::class)
            ->callAction(CreateAction::class, data: [
                'schedule_slot_id' => $slot->id,
                'date' => '2026-11-23',
                'action' => 'move',
            ])
            ->assertHasActionErrors(['rescheduled_to']);
    }

    public function test_switching_a_move_back_to_a_cancellation_clears_the_new_time(): void
    {
        $slot = ScheduleSlot::factory()->on([1], '21:00')->create();
        $exception = ScheduleException::factory()->for($slot, 'slot')->create([
            'date' => '2026-11-23',
            'rescheduled_to' => '2026-11-24 22:00',
        ]);

        Livewire::test(ManageScheduleExceptions::class)
            ->mountTableAction(EditAction::class, $exception)
            ->assertSchemaStateSet(['action' => 'move'])
            ->setTableActionData(['action' => 'cancel'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertTrue($exception->refresh()->isCancellation());
    }
}
