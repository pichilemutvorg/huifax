<?php

namespace Database\Seeders;

use App\Enums\BroadcastTag;
use App\Enums\SocialPlatform;
use App\Models\EmergencyContact;
use App\Models\Notice;
use App\Models\Program;
use App\Models\ScheduleSlot;
use App\Models\Slide;
use App\Models\SocialLink;
use App\Models\SpecialBroadcast;
use Illuminate\Database\Seeder;

/**
 * Sample content to preview the display during development.
 * Not meant for production: each instance loads its own content from the panel.
 *
 *     php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Ambulancia', '131'],
            ['Bomberos', '132'],
            ['Carabineros', '133'],
            ['PDI', '134'],
            ['Rescate marítimo', '137'],
            ['Fono Drogas', '1412'],
        ] as $index => [$name, $phone]) {
            EmergencyContact::create(['name' => $name, 'phone' => $phone, 'sort_order' => $index]);
        }

        foreach ([
            [SocialPlatform::Facebook, 'canal.example'],
            [SocialPlatform::Instagram, '@canal.example'],
            [SocialPlatform::YouTube, '@canalexample'],
            [SocialPlatform::WhatsApp, '+56 9 1234 5678'],
            [SocialPlatform::Website, 'canal.example.org'],
        ] as $index => [$platform, $handle]) {
            SocialLink::create(['platform' => $platform, 'handle' => $handle, 'sort_order' => $index]);
        }

        Notice::create([
            'title' => 'Bienvenidos a HUIFAX',
            'body' => 'Este es un aviso de ejemplo. Edita o elimina los avisos desde el panel de administración.',
            'sort_order' => 0,
        ]);
        Notice::create([
            'title' => 'Corte programado de agua',
            'body' => 'El martes entre 9:00 y 14:00 h en el sector centro. Guarde agua con anticipación.',
            'sort_order' => 1,
        ]);

        foreach (['notices', 'emergency_contacts', 'social_links'] as $index => $type) {
            Slide::create(['type' => $type, 'sort_order' => $index]);
        }

        // Its forecast arrives with `php artisan huifax:weather` (or the scheduler).
        Slide::create(['type' => 'weather', 'sort_order' => 3, 'settings' => ['location' => 'stgoc']]);

        $this->seedSchedule();

        Slide::create(['type' => 'upcoming_programs', 'sort_order' => 4]);
        Slide::create(['type' => 'daily_schedule', 'sort_order' => 5]);
    }

    private function seedSchedule(): void
    {
        $weekdays = [1, 2, 3, 4, 5];
        $everyDay = [1, 2, 3, 4, 5, 6, 7];

        foreach ([
            ['Noticiero central', 'Las noticias de la comuna', [[$weekdays, '13:00', 30, BroadcastTag::Live], [$weekdays, '21:00', 45, BroadcastTag::Live]]],
            ['Buenos días', 'Magacín matinal', [[$weekdays, '08:00', 120, null]]],
            ['Conversaciones', 'Entrevistas a vecinas y vecinos', [[[2, 4], '19:00', 60, BroadcastTag::Premiere], [[6], '11:00', 60, BroadcastTag::Rerun]]],
            ['Música en vivo', 'Bandas locales en el estudio', [[[5], '22:00', 90, BroadcastTag::Live]]],
            ['Documental', null, [[$everyDay, '17:00', 60, null]]],
        ] as [$name, $description, $slots]) {
            $program = Program::create(['name' => $name, 'description' => $description]);

            foreach ($slots as [$days, $time, $minutes, $tag]) {
                ScheduleSlot::create([
                    'program_id' => $program->id,
                    'weekdays' => $days,
                    'start_time' => $time,
                    'duration_minutes' => $minutes,
                    'tag' => $tag,
                ]);
            }
        }

        SpecialBroadcast::create([
            'program_id' => Program::create(['name' => 'Festival costumbrista', 'description' => 'Transmisión desde la plaza'])->id,
            'starts_at' => now()->addDays(2)->setTime(18, 0),
            'duration_minutes' => 180,
            'tag' => BroadcastTag::Live,
        ]);
    }
}
