<?php

return [
    'navigation' => [
        'content' => 'Contenido',
        'display' => 'Pantalla',
        'settings' => 'Configuración',
        'view_display' => 'Ver pantalla',
    ],

    'roles' => [
        'admin' => 'Administrador',
        'editor' => 'Editor',
    ],

    'common' => [
        'is_enabled' => 'Habilitado',
        'duration' => 'Duración (segundos)',
        'duration_default' => 'Por defecto',
        'seconds' => ':seconds s',
        'source_code' => 'Código fuente',
    ],

    'notices' => [
        'label' => 'aviso',
        'plural' => 'avisos',
        'fields' => [
            'title' => 'Título',
            'body' => 'Texto',
            'starts_at' => 'Visible desde',
            'ends_at' => 'Visible hasta',
        ],
        'help' => [
            'window' => 'Déjalo vacío para que el aviso no tenga límite por ese lado.',
            'duration' => 'Déjalo vacío para usar la duración de la diapositiva de avisos.',
        ],
        'status' => [
            'label' => 'Estado',
            'active' => 'Vigente',
            'scheduled' => 'Programado',
            'expired' => 'Vencido',
            'disabled' => 'Deshabilitado',
        ],
    ],

    'emergency_contacts' => [
        'label' => 'número de emergencia',
        'plural' => 'números de emergencia',
        'fields' => [
            'name' => 'Nombre',
            'phone' => 'Número',
        ],
    ],

    'social_links' => [
        'label' => 'red social',
        'plural' => 'redes sociales',
        'fields' => [
            'platform' => 'Plataforma',
            'handle' => 'Usuario o dirección',
            'url' => 'Enlace',
        ],
        'help' => [
            'handle' => 'Lo que se muestra en pantalla, por ejemplo @canal o canal.cl.',
            'url' => 'Opcional. No se muestra en pantalla.',
        ],
    ],

    'slides' => [
        'label' => 'diapositiva',
        'plural' => 'diapositivas',
        'fields' => [
            'type' => 'Tipo',
            'name' => 'Nombre interno',
            'background' => 'Fondo propio',
            'heading' => 'Encabezado',
            'per_page' => 'Elementos por página',
        ],
        'help' => [
            'name' => 'Solo para identificarla en el panel.',
            'duration' => 'Déjalo vacío para usar la duración por defecto del tipo (:seconds s).',
            'background' => 'Reemplaza la rotación de fondos mientras esta diapositiva está al aire.',
            'heading' => 'Déjalo vacío para usar el encabezado por defecto.',
            'per_page' => 'Si hay más, se reparten en varias páginas.',
        ],
    ],

    'slide_types' => [
        'emergency_contacts' => 'Números de emergencia',
        'social_links' => 'Redes sociales',
        'notices' => 'Avisos',
    ],

    'backgrounds' => [
        'label' => 'fondo',
        'plural' => 'fondos',
        'fields' => [
            'name' => 'Nombre',
            'image' => 'Imagen',
            'is_enabled' => 'En la rotación general',
        ],
        'help' => [
            'image' => 'Fotografía horizontal, idealmente de 1920×1080 o más. Máximo 8 MB.',
            'is_enabled' => 'Si lo desactivas, el fondo solo se usa en las diapositivas que lo tengan asignado.',
        ],
    ],

    'users' => [
        'label' => 'usuario',
        'plural' => 'usuarios',
        'fields' => [
            'name' => 'Nombre',
            'email' => 'Correo electrónico',
            'role' => 'Rol',
            'password' => 'Contraseña',
        ],
        'help' => [
            'password' => 'Déjala vacía para mantener la actual.',
            'own_role' => 'No puedes cambiar tu propio rol.',
        ],
    ],

    'settings' => [
        'branding' => [
            'title' => 'Marca',
            'fields' => [
                'title' => 'Título',
                'tagline' => 'Lema',
                'logo' => 'Logo',
            ],
            'help' => [
                'title' => 'Se muestra en el encabezado cuando no hay logo.',
                'tagline' => 'Se muestra al pie de la pantalla.',
                'logo' => 'PNG o SVG con fondo transparente. Reemplaza al título en el encabezado.',
            ],
        ],
        'display' => [
            'title' => 'Pantalla',
            'fields' => [
                'poll_interval' => 'Revisar cambios cada (segundos)',
                'reload_at' => 'Recarga diaria a las',
                'background_interval' => 'Cambiar de fondo cada (segundos)',
            ],
            'help' => [
                'poll_interval' => 'Cada cuánto la pantalla busca contenido nuevo. Los cambios se aplican al pasar a la siguiente diapositiva.',
                'reload_at' => 'Recarga de seguridad, solo si el servidor responde. Déjalo vacío para desactivarla.',
            ],
        ],
        'audio' => [
            'title' => 'Audio',
            'fields' => [
                'enabled' => 'Reproducir la radio desde la pantalla',
                'stream_url' => 'URL del stream',
                'volume' => 'Volumen (%)',
            ],
            'help' => [
                'enabled' => 'Normalmente el audio lo reproduce el sistema de emisión (p. ej. CasparCG). Actívalo solo si la pantalla debe reproducirlo.',
            ],
        ],
    ],
];
