# Decisiones técnicas

Registro de decisiones del proyecto. Agregar una entrada por decisión, con
fecha, qué se decidió, por qué y qué alternativas se descartaron.

## Tomadas

### 2026-11-21 — Reescritura desde cero

La nueva versión de HUIFAX se desarrolla en un repositorio nuevo, sin reutilizar
el código de la versión Vue 2 (framework sin soporte). La versión actual sirve
como referencia funcional (ver [contexto.md](contexto.md)).

### 2026-11-21 — Motor de captura: CasparCG 2.4.x o superior

La página se captura con el productor HTML de CasparCG 2.4+, que trae un CEF
(Chromium Embedded) moderno. Se puede usar JavaScript y CSS actuales sin
transpilar en exceso.

- **Consecuencia:** el target de build se fija al Chromium del CEF que trae la
  versión instalada. Las pruebas visuales y las de larga duración se hacen en
  CasparCG, no solo en un navegador de escritorio.
- **Pendiente:** anotar aquí la versión exacta de CasparCG y de su CEF.

### 2026-11-21 — Stack: Laravel + Filament + Livewire + daisyUI

- **Backend:** Laravel. Sirve la pantalla, expone la API interna, hace de proxy
  y caché de las fuentes externas (por ahora meteochile) y guarda cualquier
  clave en `.env`.
- **Panel de administración:** Filament (que funciona sobre Livewire).
- **Pantalla al aire:** Vue 3 + Tailwind CSS + daisyUI, servida desde una
  página Blade (ver "Pantalla al aire en Vue 3, sin Livewire").

**Por qué:** hace falta un backend de todas formas. meteochile no envía
cabeceras CORS, y ninguna clave debe quedar en el navegador (la versión
anterior expone la de Google en su bundle). Como además se quiere un panel
propio desde la primera versión, Filament lo resuelve sin construirlo a mano.

**Descartado:** frontend vanilla con un proxy PHP o Node mínimo. Tiene menos
dependencias, pero obligaba a construir el panel a mano.

**Nota sobre las guidelines:** esto se aparta del principio de "dependencias
mínimas" en el servidor. Ese principio se mantiene en el **JavaScript de la
pantalla al aire**: ahí nada de librerías que no sean imprescindibles.

### 2026-11-21 — Contenido administrable desde un panel propio

Avisos, números de emergencia, redes sociales, orden y duración de las
diapositivas, y la configuración de audio se editan desde un panel Filament
con login. Los datos van en la base de datos de Laravel, no en el código.

**Descartado:** un JSON en el servidor (obliga a editar archivos a mano) y
Google Sheets (suma una dependencia externa y su API).

### 2026-11-21 — Despliegue en un contenedor de Proxmox (CT)

La aplicación corre en un contenedor LXC de Proxmox administrado por el canal.

- **Pendiente:** distribución del CT, servidor web (Nginx + PHP-FPM, FrankenPHP,
  etc.), dominio/HTTPS y cómo se
  publica (git pull + script, CI, etc.).

### 2026-11-21 — Pantalla al aire en Vue 3, sin Livewire

La pantalla al aire es una aplicación Vue 3 (compilada con Vite, el mismo que
ya trae Laravel) servida desde una página Blade. Livewire queda solo para el
panel Filament.

**Por qué:** Livewire depende de la sesión y del token CSRF. Tras horas o días
al aire, la sesión expira; el servidor responde 419 y aparece el diálogo "This
page has expired" en pantalla. Vue permite que cada diapositiva sea un
componente con la misma interfaz y que la pantalla funcione sin conexión con
los últimos datos válidos.

**Descartado:** componentes Livewire con polling (por lo anterior) y JavaScript
sin framework (es más trabajo mantener los componentes de diapositivas a mano).

**Dependencias de la pantalla:** solo `vue`. Nada de librerías de fechas
(`Intl`), carruseles ni HTTP (`fetch`).

### 2026-11-21 — Cambios del panel en vivo, aplicados entre diapositivas

La pantalla refleja los cambios del panel **sin recargar la página** y **sin
alterar la diapositiva que está al aire**.

- **Detección:** la pantalla consulta cada 30 s (valor configurable) un
  endpoint JSON sin sesión (`GET /api/display`). Ese endpoint responde con un
  `ETag` calculado desde la versión del contenido; si no hubo cambios, devuelve
  `304` sin cuerpo. Toda escritura en el panel incrementa la versión.
- **Aplicación:** si llega contenido nuevo, la pantalla lo guarda como
  *pendiente* (y en `localStorage`). La diapositiva actual termina su duración
  con los datos con que empezó; al pasar a la siguiente, el contenido
  pendiente reemplaza al vigente. Si la diapositiva actual fue eliminada o
  deshabilitada, igual termina su tiempo.
- **Sin conexión:** si el endpoint falla, se sigue con el contenido vigente y
  se reintenta con backoff. Al arrancar sin conexión se usa la copia de
  `localStorage`.

**Por qué polling:** una demora de hasta 30 s es irrelevante para este
contenido, y el polling no necesita procesos extra en el servidor. Con
respuestas `304`, cada consulta cuesta muy poco.

**Descartado:** WebSockets con Laravel Reverb (un daemon más que mantener vivo
en el CT, y reconexiones que manejar igual) y Server-Sent Events (cada
pantalla mantiene ocupado un worker de PHP-FPM). Se puede reconsiderar si
alguna vez hace falta inmediatez, por ejemplo para alertas de emergencia.

### 2026-11-21 — Base de datos: SQLite

SQLite, en un solo archivo, con modo WAL. Alcanza para un panel con pocos
usuarios y una pantalla que lee mediante una caché.

**Descartado por ahora:** MariaDB/PostgreSQL. Se reconsidera solo si llega a
ser necesario.

### 2026-11-21 — Programación administrada en el panel, sin Google Calendar

La grilla de programación se administra desde el panel. No se usa Google
Calendar ni ninguna clave de Google.

- **Pendiente:** diseñar el modelo de datos (programas, horarios recurrentes,
  excepciones). No entra en la primera ronda.

### 2026-11-21 — Versiones y herramientas de la base (Fase 0)

Versiones instaladas al crear el proyecto (verificadas como compatibles):

| Componente | Versión |
|---|---|
| PHP | ^8.4 (las dependencias de Symfony 8 exigen ≥ 8.4.1; CI prueba 8.4 y 8.5) |
| Laravel | 13.43 |
| Filament | 5.11 |
| Livewire | 4.4 (lo trae Filament) |
| Vue | 3.5 |
| Vite | 8 |
| Tailwind CSS | 4 |
| daisyUI | 5.8 |

- **Configuración regional:** `APP_TIMEZONE` (por defecto
  `America/Santiago`), `APP_LOCALE=es`, `APP_FAKER_LOCALE=es_CL` y
  `HUIFAX_DISPLAY_LOCALE=es-CL` (locale BCP 47 que usa la pantalla con `Intl`).
  La pantalla recibe la zona horaria y el locale desde el servidor, nunca del
  navegador.
- **SQLite:** `journal_mode=wal`, `synchronous=normal`, `busy_timeout=5000`.
- **Pantalla al aire:** entrada Vite `resources/js/display/main.js` +
  `resources/css/display.css`, vista `display.blade.php` en `/`. daisyUI
  solo con el tema `dark`. Build con target `chrome110`; ajustarlo al anotar el
  CEF real de CasparCG.
- **Panel:** Filament en `/admin`, con sus propios estilos (no usa
  `display.css`).
- **Formateo y lint:** Pint con el preset `laravel` (`composer lint`,
  `composer lint:check`) y Prettier con los plugins de Blade y Tailwind
  (`npm run format`, `npm run format:check`).
- **Pruebas:** PHPUnit (Laravel trae la 12; se actualizó a la 13, ver la
  entrada del 2026-11-22). Pest queda descartado por ahora para no sumar
  dependencias.
- **Descartado:** el `CLAUDE.md`/`AGENTS.md` genérico del scaffold, que pide
  instalar Laravel Boost. Si se quiere Boost, se decide aparte.

### 2026-11-22 — HUIFAX como producto reutilizable, una instalación por instancia

HUIFAX es el nombre del **producto**. Lo podrá usar cualquier canal u
organización que necesite información rotando en pantalla; PichilemuTV.org es
su primera instancia.

- **Despliegue:** una instalación por instancia, cada una con su `.env`, su
  base SQLite y su marca. El modelo de datos no separa por canal ni por
  pantalla (no es multi-tenant).
- **Qué es configuración y qué es dato:**
  - **`.env` / `config/huifax.php` (despliegue):** zona horaria, idioma y
    locale de la pantalla, y claves de servicios.
  - **Panel (datos):** marca (título, lema, logo, colores), contenido,
    diapositivas, radio y fuentes locales (p. ej. la ciudad del pronóstico).
    Hasta que exista la página de ajustes del panel, la marca se lee de
    `config/huifax.php` con valores genéricos.
- **Idiomas:** el código, los comentarios y los commits van en inglés. La
  documentación (`docs/`) va en español. La interfaz es traducible con los
  archivos de `lang/` de Laravel, y `es` es el idioma por defecto.
- **Pantalla al aire = `display`:** es el nombre en el código (rutas, vistas,
  entradas de Vite, `/api/display`). Las diapositivas son `slides`.

**Descartado:** una instalación con varias pantallas o canales, y
multi-tenant. Suman complejidad que hoy no hace falta. Si alguna vez se
necesita, la separación por pantalla se puede agregar más adelante.

**Pendiente:** cómo recibe la app Vue los textos traducidos (en el payload de
`/api/display` o en un JSON propio por idioma). Se decide en la Fase 1.

### 2026-11-22 — Licencia: AGPL-3.0-or-later

HUIFAX se publica bajo la **GNU Affero General Public License v3.0 o
posterior** (`AGPL-3.0-or-later`). El texto oficial está en `LICENSE`, y el
identificador SPDX en `composer.json` y `package.json`.

**Por qué AGPL:** HUIFAX se usa como servicio web. Con la GPL, alguien podría
modificarlo y ofrecerlo por red sin compartir los cambios. La AGPL obliga a
quien ofrezca una versión modificada a usuarios que interactúan con ella por
red a darles acceso a su código fuente.

**Consecuencias:**
- Cualquier dependencia que se agregue debe ser compatible con la AGPL (MIT,
  BSD, Apache-2.0, LGPL y GPL-3.0 lo son). Las actuales lo son.
- Para cumplir la sección 13 sin esfuerzo, el panel mostrará un enlace al
  código fuente; su URL será configurable para que las instancias con cambios
  apunten a su propio repositorio.
- Se eligió "o posterior" (la fórmula recomendada por la FSF) en vez de
  `AGPL-3.0-only`.

**Descartado:** MIT/Apache-2.0 (permiten forks cerrados) y GPL-3.0 (no cubre
el uso por red).

### 2026-11-22 — Repositorio público en GitHub: `pichilemutvorg/huifax`

El código se publica en <https://github.com/pichilemutvorg/huifax>, dentro de
la organización de PichilemuTV.org.

- **Metadatos:** el paquete se llama `pichilemutvorg/huifax`, con `homepage`
  y `support` en `composer.json` y `repository` en `package.json`.
  `HUIFAX_SOURCE_URL` apunta por defecto al repositorio (AGPL §13).
- **CI (GitHub Actions, `.github/workflows/ci.yml`):** corre Pint y Prettier
  en modo verificación, compila los assets y ejecuta las pruebas con PHP 8.4
  y 8.5.
- **Dependabot:** revisa semanalmente las dependencias de Composer y npm
  (agrupando las versiones menores y los parches) y mensualmente las acciones.
- **Contribuciones:** `CONTRIBUTING.md` en español con un resumen en inglés.
  Se aceptan issues y pull requests en ambos idiomas.
- **Por ser público:** ninguna clave, credencial, IP interna ni dato del
  despliegue (CT, dominios internos) se sube al repositorio. Lo propio del
  despliegue de PichilemuTV va en su `.env`, fuera de git.
- **Pendiente:** decidir si se agrega `SECURITY.md` (canal privado para
  reportar vulnerabilidades).

### 2026-11-22 — Protección de `main` y flujo de pull requests

`main` está protegida con un ruleset de GitHub (no con la protección de ramas
clásica):

- No se puede borrar ni recibir force push.
- Todo cambio entra por pull request, con 0 aprobaciones requeridas mientras
  haya un solo mantenedor (subirlo cuando haya más personas revisando). Las
  aprobaciones se invalidan con commits nuevos, y las conversaciones deben
  quedar resueltas antes de fusionar.
- Para fusionar deben pasar los checks `Lint and format`, `Tests (PHP 8.4)` y
  `Tests (PHP 8.5)`, con la rama al día respecto de `main`.
- Solo se permite *squash merge* (un commit por PR) y las ramas se borran al
  fusionar.

**Importante:** si se renombran los jobs de `ci.yml` o cambia la matriz de
PHP, hay que actualizar los checks requeridos del ruleset; si no, los PR
quedan esperando un check que nunca llega.

### 2026-11-22 — PHPUnit 13

Se aceptó el PR #1 de Dependabot, que actualiza PHPUnit de 12.5 a 13.4,
aunque el scaffold de Laravel 13 trae la 12. El CI pasó con PHP 8.4 y 8.5.
Se prefiere estar en la versión mayor vigente a quedar atrás desde el inicio.
Si alguna integración de Laravel falla con PHPUnit 13, se vuelve a la 12 y se
registra aquí.

## Pendientes

- **Radio:** la URL del stream está por llegar. Debe ser configurable desde el
  panel (URL, activar/desactivar la reproducción en la página, volumen). Por
  defecto el audio lo reproduce CasparCG.
- **Diseño de la programación:** ver la entrada correspondiente.
- **CasparCG:** anotar la versión exacta y la de su CEF.
- **Despliegue en el CT:** distribución, servidor web, HTTPS y cómo se publica.
- **Tipografía y diseño visual** de la pantalla al aire (con colores y
  tipografía personalizables por instancia).
