<?php

return [
    'navigation' => [
        'content' => 'Content',
        'display' => 'Display',
        'settings' => 'Settings',
        'view_display' => 'View display',
    ],

    'roles' => [
        'admin' => 'Administrator',
        'editor' => 'Editor',
    ],

    'common' => [
        'is_enabled' => 'Enabled',
        'duration' => 'Duration (seconds)',
        'duration_default' => 'Default',
        'seconds' => ':seconds s',
        'source_code' => 'Source code',
    ],

    'status' => [
        'heading' => 'Status',
        'display' => 'Display',
        'connected' => 'Connected',
        'disconnected' => 'Disconnected',
        'last_seen' => 'Last request :time',
        'never_seen' => 'Has not connected yet',
        'weather' => 'Weather · :location',
        'updated' => 'Updated :time',
        'no_data' => 'No data',
        'weather_help' => 'Refreshed every 30 minutes by the scheduler',
    ],

    'notices' => [
        'label' => 'notice',
        'plural' => 'notices',
        'fields' => [
            'title' => 'Title',
            'body' => 'Text',
            'starts_at' => 'Visible from',
            'ends_at' => 'Visible until',
        ],
        'help' => [
            'window' => 'Leave empty for no limit on that side.',
            'duration' => 'Leave empty to use the notices slide duration.',
        ],
        'status' => [
            'label' => 'Status',
            'active' => 'Active',
            'scheduled' => 'Scheduled',
            'expired' => 'Expired',
            'disabled' => 'Disabled',
        ],
    ],

    'featured_programs' => [
        'label' => 'featured program',
        'plural' => 'featured programs',
        'fields' => [
            'name' => 'Name',
            'label' => 'Label',
            'tagline' => 'Tagline',
            'schedule' => 'Schedule',
            'image' => 'Image',
        ],
        'help' => [
            'label' => 'Optional and short, e.g. Premiere or Live.',
            'schedule' => 'As it will be shown, e.g. Monday to Friday, 9 pm.',
            'image' => 'Program art or photo, ideally 1920×1080. :size MB max; optimized on upload.',
            'duration' => 'Leave empty to use the featured programs slide duration.',
        ],
    ],

    'emergency_contacts' => [
        'label' => 'emergency number',
        'plural' => 'emergency numbers',
        'fields' => [
            'name' => 'Name',
            'phone' => 'Number',
        ],
    ],

    'social_links' => [
        'label' => 'social link',
        'plural' => 'social links',
        'fields' => [
            'platform' => 'Platform',
            'handle' => 'Handle or address',
            'url' => 'Link',
        ],
        'help' => [
            'handle' => 'What is shown on screen, e.g. @channel or channel.org.',
            'url' => 'Optional. Not shown on screen.',
        ],
    ],

    'slides' => [
        'label' => 'slide',
        'plural' => 'slides',
        'fields' => [
            'type' => 'Type',
            'name' => 'Internal name',
            'background' => 'Own background',
            'heading' => 'Heading',
            'location' => 'Location',
            'per_page' => 'Items per page',
        ],
        'help' => [
            'name' => 'Only used to identify it in the panel.',
            'duration' => 'Leave empty to use the type default (:seconds s).',
            'background' => 'Replaces the background rotation while this slide is on screen.',
            'heading' => 'Leave empty to use the default heading.',
            'location' => 'Locations available from the weather data source.',
            'per_page' => 'Extra items are split across several pages.',
        ],
    ],

    'slide_types' => [
        'featured_programs' => 'Featured programs',
        'weather' => 'Weather forecast',
        'emergency_contacts' => 'Emergency numbers',
        'social_links' => 'Social links',
        'notices' => 'Notices',
    ],

    'backgrounds' => [
        'label' => 'background',
        'plural' => 'backgrounds',
        'fields' => [
            'name' => 'Name',
            'image' => 'Image',
            'is_enabled' => 'In the general rotation',
        ],
        'help' => [
            'image' => 'Landscape photo, ideally 1920×1080 or larger. :size MB max; optimized on upload.',
            'is_enabled' => 'When off, the background is only used by slides that have it assigned.',
        ],
    ],

    'users' => [
        'label' => 'user',
        'plural' => 'users',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'role' => 'Role',
            'password' => 'Password',
        ],
        'help' => [
            'password' => 'Leave empty to keep the current one.',
            'own_role' => 'You cannot change your own role.',
        ],
    ],

    'settings' => [
        'branding' => [
            'title' => 'Branding',
            'fields' => [
                'title' => 'Title',
                'tagline' => 'Tagline',
                'logo' => 'Logo',
            ],
            'help' => [
                'title' => 'Shown in the header when there is no logo.',
                'tagline' => 'Shown at the bottom of the screen.',
                'logo' => 'PNG or SVG with a transparent background. Replaces the title in the header.',
            ],
        ],
        'display' => [
            'title' => 'Display',
            'fields' => [
                'poll_interval' => 'Check for changes every (seconds)',
                'reload_at' => 'Daily reload at',
                'background_interval' => 'Change background every (seconds)',
            ],
            'help' => [
                'poll_interval' => 'How often the display looks for new content. Changes apply on the next slide.',
                'reload_at' => 'Safety reload, only if the server answers. Leave empty to disable it.',
            ],
        ],
        'audio' => [
            'title' => 'Audio',
            'fields' => [
                'enabled' => 'Play the radio from the display',
                'stream_url' => 'Stream URL',
                'volume' => 'Volume (%)',
            ],
            'help' => [
                'enabled' => 'Audio is usually played by the playout system (e.g. CasparCG). Only enable this if the display must play it.',
                'stream_url' => 'Use https when available: on an HTTPS site, an http stream may be blocked.',
            ],
        ],
    ],
];
