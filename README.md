# HUIFAX

[![CI](https://github.com/pichilemutvorg/huifax/actions/workflows/ci.yml/badge.svg)](https://github.com/pichilemutvorg/huifax/actions/workflows/ci.yml)

Sistema de señalización para pantallas de TV: rota información de forma
permanente (clima, programación, avisos, emergencias, redes) con un panel de
administración. Nació como la pantalla fuera de aire de
[PichilemuTV.org](https://pichilemutv.org) y está
pensado para que cualquier canal u organización lo instale con su propia marca
y contenido.

La documentación (en español) está en [docs/](docs/README.md).

> **English:** HUIFAX is a TV signage system that permanently rotates
> information slides (weather, schedule, notices, emergency numbers, social
> media), managed from an admin panel. Built with Laravel, Filament and Vue.
> Each installation is a single, self-branded instance. Code, comments and
> commits are in English; project documentation is in Spanish.

## Desarrollo

Requisitos: PHP ≥ 8.4 con `pdo_sqlite`, `intl` y `gd` (o `imagick`), Composer y
Node.

```sh
composer setup                   # instala dependencias, crea .env, migra y compila
php artisan storage:link         # publica las imágenes subidas desde el panel
php artisan make:filament-user   # crea un usuario para /admin (rol editor)
php artisan huifax:admin correo@ejemplo.org     # lo convierte en administrador
php artisan db:seed --class=DemoContentSeeder   # contenido de ejemplo (solo desarrollo)
php artisan huifax:weather       # trae el pronóstico de las diapositivas de clima
composer dev                     # servidor + Vite en modo desarrollo
```

En producción, el pronóstico se actualiza cada 30 minutos con el scheduler de
Laravel. Hay que agregar al cron del servidor:

```cron
* * * * * cd /ruta/a/huifax && php artisan schedule:run >> /dev/null 2>&1
```

- Pantalla al aire: `/` (con `/?guides` muestra las áreas seguras)
- Panel de administración: `/admin`

La pantalla tiene varios temas visuales (predeterminado, Metro y otros
inspirados en Apple, Material, Vercel, shadcn/ui, la BBC y Anthropic). Se elige
en *Configuración → Pantalla*. Con `/?theme=metro` se prueba un tema solo en esa
pantalla. *Pantalla → Vista previa* muestra la rotación completa en miniaturas,
en orden y con sus tiempos, en el tema que elijas.

En la pantalla, las flechas del teclado pasan a la diapositiva siguiente o a la
anterior, y la tecla `G` muestra u oculta las áreas seguras (*action safe* 93 %
y *title safe* 90 %).

La radio de la página está desactivada por defecto. En CasparCG conviene
reproducir el stream en una capa propia del productor FFmpeg, debajo de HUIFAX:
el audio del productor HTML no llega al canal en CasparCG 2.5.1 para Linux.

El arranque sin conexión (Service Worker) solo funciona sobre HTTPS o en
`localhost`.

Las subidas aceptan hasta 20 MB (`HUIFAX_UPLOAD_MAX_SIZE`, en KB). PHP debe
aceptar al menos eso: `public/.user.ini` lo ajusta para PHP-FPM, y el servidor
web también debe permitirlo (por ejemplo, `client_max_body_size 32m;` en
nginx). Las fotografías se guardan optimizadas en WebP.

La zona horaria y el locale de la instancia se configuran en `.env` (ver
`.env.example` y `config/huifax.php`). La marca, los intervalos, la recarga
diaria y el audio se editan en el panel, en *Configuración*.

El panel tiene dos roles: **administrador** (todo) y **editor** (avisos,
programas destacados, números de emergencia y redes sociales).

Antes de un commit: `composer lint`, `npm run format`, `php artisan test` y
`npm test`.
Para contribuir, lee [CONTRIBUTING.md](CONTRIBUTING.md).

## Licencia

[GNU Affero General Public License v3.0 o posterior](LICENSE)
(`AGPL-3.0-or-later`). Si ofreces una versión modificada de HUIFAX por red,
debes poner su código fuente a disposición de quienes la usen.
