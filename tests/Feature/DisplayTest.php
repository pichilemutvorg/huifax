<?php

namespace Tests\Feature;

use Tests\TestCase;

class DisplayTest extends TestCase
{
    public function test_display_page_mounts_the_app_with_instance_locale_and_timezone(): void
    {
        config([
            'app.timezone' => 'America/Santiago',
            'huifax.display.locale' => 'es-CL',
        ]);

        $this->withoutVite()
            ->get(route('display'))
            ->assertOk()
            ->assertSee('<html lang="es-CL"', false)
            ->assertSee('id="display"', false)
            ->assertSee('data-timezone="America/Santiago"', false);
    }

    public function test_document_title_includes_tagline_when_configured(): void
    {
        config([
            'huifax.branding.title' => 'HUIFAX',
            'huifax.branding.tagline' => 'Example tagline',
        ]);

        $this->withoutVite()
            ->get(route('display'))
            ->assertSee('<title>HUIFAX - Example tagline</title>', false);
    }

    public function test_document_title_is_only_the_title_without_tagline(): void
    {
        config([
            'huifax.branding.title' => 'HUIFAX',
            'huifax.branding.tagline' => null,
        ]);

        $this->withoutVite()
            ->get(route('display'))
            ->assertSee('<title>HUIFAX</title>', false);
    }
}
