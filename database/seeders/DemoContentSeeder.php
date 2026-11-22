<?php

namespace Database\Seeders;

use App\Enums\SocialPlatform;
use App\Models\EmergencyContact;
use App\Models\Notice;
use App\Models\Slide;
use App\Models\SocialLink;
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
    }
}
