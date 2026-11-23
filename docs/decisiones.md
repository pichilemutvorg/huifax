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

- **Detección:** la pantalla consulta cada 30 s (`HUIFAX_POLL_INTERVAL`) el
  endpoint JSON sin sesión `GET /api/display`. Su `ETag` es el hash del
  contenido serializado; si no cambió, responde `304` sin cuerpo. No hay un
  contador de versión: cualquier cambio (incluido que un aviso entre o salga
  de su vigencia) cambia el hash sin tener que acordarse de incrementar nada.
  Por eso el payload no puede incluir valores que cambien en cada petición,
  como marcas de tiempo.
- **Aplicación:** si llega contenido nuevo, la pantalla lo guarda como
  *pendiente*. La diapositiva actual termina su duración con los datos con
  que empezó; al pasar a la siguiente, el contenido pendiente reemplaza al
  vigente y la rotación sigue desde la misma posición. Si la diapositiva
  actual fue eliminada, igual termina su tiempo.
- **Sin conexión:** si el endpoint falla, se sigue con el contenido vigente y
  se reintenta con backoff exponencial (hasta 5 min). El primer contenido va
  embebido en la página, así que la pantalla no espera a la API para mostrar
  el primer cuadro.
- **Arranque sin servidor:** si el servidor no responde cuando la página se
  carga, el navegador no tiene nada que mostrar. `localStorage` no resuelve
  esto (sin la página no hay JavaScript), así que se descartó. La solución es
  un Service Worker que sirva la página y el último contenido desde caché;
  queda para la Fase 4. Mientras tanto, la recarga diaria solo ocurre si
  `/up` responde.

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

- **Actualización:** HUIFAX no administra la grilla; ver "HUIFAX no administra
  la grilla de programación".

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

### 2026-11-22 — Arquitectura de la pantalla al aire (Fase 1)

**Modelo de datos.** Una tabla `slides` (tipo, nombre, orden, duración,
habilitada, `settings` JSON) define la rotación. El contenido en listas vive en
tablas propias: `emergency_contacts`, `social_links` y `notices`. Todas usan
`is_enabled` y `sort_order` (trait `HasDisplayOrder`). Puede haber varias
diapositivas del mismo tipo, cada una con sus ajustes (p. ej. `heading` o
`per_page`).

**Tipos de diapositiva.** Cada tipo es una clase que extiende
`App\Display\SlideTypes\SlideType` (`key()`, `defaultDuration()`, `pages()`),
registrada en `config/huifax.php`, más un componente Vue con la misma clave en
`resources/js/display/slides/index.js`. Una diapositiva produce cero o más
*páginas*: cero significa "no disponible ahora" y se salta; los avisos producen
una página por aviso vigente y las listas largas se dividen en páginas. La
duración se resuelve en este orden: página → diapositiva → valor por defecto
del tipo. Si un tipo lanza una excepción, se reporta y se omite; el resto de la
pantalla sigue.

**Textos traducidos.** Se resuelven en el servidor. Los encabezados de las
diapositivas viajan ya traducidos dentro de sus datos, y los pocos textos
propios de la interfaz de la pantalla van en `strings` (`lang/*/display.php`,
clave `ui`). Así la app Vue no necesita una librería de i18n, y un cambio de
idioma se aplica en vivo como cualquier otro contenido.

**Robustez en el navegador.**
- Las transiciones usan una animación CSS de entrada (`@keyframes`) al montar
  cada diapositiva, no `<Transition>` de Vue. Al probar en Chromium headless,
  `<Transition>` dejó diapositivas ocultas cuando no llegaban cuadros de
  animación ni eventos `transitionend`. Una animación CSS no depende de
  JavaScript ni de eventos para terminar.
- Una diapositiva que falla al renderizarse se salta (`onErrorCaptured`) y no
  vuelve a mostrarse hasta que cambie el contenido. Sin diapositivas
  disponibles se muestra una pantalla de reposo con el título o el logo.
- Recarga diaria de seguridad (`HUIFAX_RELOAD_AT`, por defecto 05:00 en la zona
  de la instancia), solo si `/up` responde y la página lleva más de una hora
  cargada.
- Lienzo fijo de 1920×1080 escalado a la ventana con `transform: scale`, con
  márgenes de zona segura (96 px horizontales, 56 px verticales).

**Dependencias nuevas:**
- `simple-icons` (CC0-1.0): íconos de marcas para las redes sociales. Solo se
  empaquetan los íconos importados.
- `vitest` (MIT, solo desarrollo): pruebas de la lógica de la pantalla
  (rotación, validación del payload, polling con backoff, recarga). Corre en
  el job de pruebas del CI.

**Diseño base.** Tema daisyUI propio `huifax`: fondo azul profundo, títulos en
turquesa (`primary`) y cifras destacadas en amarillo arena (`accent`), alto
contraste y bordes redondeados. Tipografía del sistema por ahora. Es un punto
de partida para iterar; la personalización por instancia vendrá desde el
panel.

### 2026-11-22 — Panel de administración (Fase 2)

**Ajustes editables: `spatie/laravel-settings`** (MIT), con el plugin oficial
de Filament para sus páginas. Hay tres clases en `app/Settings`:
- `BrandingSettings`: título, lema y logo.
- `DisplaySettings`: intervalo de consulta, hora de recarga e intervalo de
  fondos.
- `AudioSettings`: activar la radio en la página, URL del stream y volumen.
  El reproductor llega en la Fase 4.

Sus migraciones (`database/settings`) toman los valores iniciales del `.env`
(`HUIFAX_TITLE`, `HUIFAX_POLL_INTERVAL`, etc.); desde ahí en adelante se editan
en el panel. Lo que es propio del despliegue (zona horaria, locale y URL del
código fuente) sigue en `.env`.
**Descartado:** una tabla clave-valor propia, porque había que escribir a mano
los tipos, la caché y las páginas.

**Roles: un enum `UserRole` en la columna `users.role`**, sin un paquete de
permisos. Hay dos roles fijos:
- **administrador:** todo.
- **editor:** solo avisos, números de emergencia y redes sociales (desde el
  2026-11-23, también los programas destacados).

Las restricciones se aplican con policies de Laravel (`SlidePolicy`,
`BackgroundPolicy`, `UserPolicy`), que Filament respeta, y con `canAccess()`
en las páginas de ajustes. Los usuarios nuevos son editores, por mínimo
privilegio; `php artisan huifax:admin {email}` los promueve. Nadie puede
borrar su propia cuenta ni cambiar su propio rol, así que siempre queda al
menos un administrador.
**Descartado:** `spatie/laravel-permission`, que es excesivo para dos roles
fijos.

**Fondos: una biblioteca de imágenes (`backgrounds`) más una referencia
opcional en cada diapositiva (`slides.background_id`).**
- Los fondos habilitados ("en la rotación general") rotan detrás de todo con
  su propio intervalo, independiente de las diapositivas.
- Una diapositiva con fondo propio lo muestra mientras está al aire, esté o
  no ese fondo en la rotación.
- Al reemplazar o borrar una imagen se elimina el archivo anterior (eventos
  del modelo). Por eso en fondos no hay borrado masivo.
- En la pantalla, cada fondo nuevo se precarga antes de mostrarse: una imagen
  rota o lenta nunca reemplaza a la que está al aire. Luego aparece con un
  fundido sobre el anterior, y nunca hay más de dos capas en el DOM. Encima va
  una capa oscura con degradado que mantiene el contraste del texto.
- Las imágenes van en el disco `public`, así que cada instalación necesita
  `php artisan storage:link`.

**Convenciones del panel:**
- Los recursos son de tipo *simple* (una página, edición en modales), y las
  listas se ordenan arrastrando filas (`sort_order`). Los registros nuevos
  quedan al final (trait `HasDisplayOrder`).
- Todos los textos están en `lang/{es,en}/admin.php`, y los títulos llevan
  mayúscula solo al inicio, como corresponde en español (trait
  `HasSentenceCaseLabels`).
- Cada tipo de diapositiva declara sus propios campos de ajustes
  (`SlideType::settingsFields()`). Un tipo nuevo trae su formulario sin tocar
  el recurso.
- El pie del panel enlaza al código fuente (`HUIFAX_SOURCE_URL`), como exige
  la AGPL (§13). El menú incluye "Ver pantalla".

### 2026-11-22 — Clima (Fase 3)

**Proveedores intercambiables.** Cada fuente implementa
`App\Weather\WeatherProvider` (`locations()`, `forecasts()`, `iconUrl()`,
`attribution()`), y se elige con `huifax.weather.provider`. La primera es
`MeteochileProvider` (Dirección Meteorológica de Chile); una instancia de otro
país puede agregar la suya sin tocar el resto. Todo se normaliza a `Forecast`:
días con mínima y máxima, y cuatro tramos (`dawn`, `morning`, `afternoon`,
`night`), o un `summary` para el "resto del día".

**Formato de meteochile** (verificado contra el JSON real y el código de su
portal):
- `fechasql` es la fecha del primer día; los días siguientes son
  consecutivos.
- `temperatura` viene como `"mín/máx"`.
- `icono` y `texto` traen cuatro tramos por día: madrugada, mañana, tarde y
  noche.
- `*_resto_dia` describe el resto del día anterior a `fechasql`.
- Los textos traen entidades HTML (`mi&eacute;rcoles`).

Fixture real recortado en `tests/Fixtures/meteochile/`.

**Actualización y caché:**
- `php artisan huifax:weather` consulta la fuente para las localidades que
  usan las diapositivas de clima, y el scheduler lo ejecuta cada 30 minutos.
  Requiere `schedule:run` en el cron del servidor.
- Además, guardar una diapositiva de clima dispara una actualización después
  de responder, para no esperar al siguiente ciclo.
- Se guarda el último pronóstico válido por localidad (`weather_forecasts`).
  Un fallo de red, un HTTP de error o un JSON inválido nunca lo sobrescriben.
- Los íconos se descargan una vez al disco `public`: la pantalla nunca pide
  nada a la fuente externa. Se aceptan solo nombres de archivo simples
  (`/^[a-z0-9_]+\.png$/`), con tipo `image/*` y un máximo de 512 KB.

**Qué se muestra:**
- Solo los días desde hoy, en la zona de la instancia. Un pronóstico obtenido
  hace más de 72 horas no se muestra, y la diapositiva se salta (nunca
  aparece información vieja ni un error).
- "Hoy" muestra el tramo que corresponde a la hora: madrugada de 0 a 6,
  mañana de 6 a 12, tarde de 12 a 18 y noche de 18 a 24. El tramo se fija
  cuando la diapositiva sale al aire y no cambia mientras está en pantalla.
  Los días siguientes muestran la tarde.
- Siempre se cita la fuente ("Información proporcionada por la Dirección
  Meteorológica de Chile").
- La localidad es un ajuste de cada diapositiva. Se puede tener una por
  ciudad.

**Pruebas:** `Http::preventStrayRequests()` en el `TestCase` base, para que
ninguna prueba salga a la red.

### 2026-11-22 — Subidas y optimización de fotografías

**Problema:** las subidas fallaban con fotos de más de 2 MB. PHP traía
`upload_max_filesize = 2M` y `post_max_size = 8M`, mientras que el panel
prometía hasta 8 MB, y Livewire valida aparte sus subidas temporales (12 MB
por defecto).

**Solución:**
- Un solo límite: `huifax.uploads.max_size` (`HUIFAX_UPLOAD_MAX_SIZE`, 20 MB
  por defecto). Lo usan los campos de foto y la validación de Livewire.
- `public/.user.ini` sube los límites de PHP (24 MB por archivo, 32 MB por
  petición). Lo leen PHP-FPM y CGI, y queda versionado con el código. El
  servidor web también debe aceptar el tamaño (`client_max_body_size` en
  nginx); eso va en el despliegue.

**Optimización:** el campo `PhotoUpload` guarda una copia optimizada en vez
del original (`App\Media\PhotoOptimizer`, con intervention/image 4):
- La foto se reduce (nunca se agranda) hasta cubrir justo 1920×1080, sin
  cambiar su proporción. La pantalla la recorta con `object-cover`.
- Se guarda en WebP con calidad 82 (`HUIFAX_PHOTO_QUALITY`), que no muestra
  pérdida visible a 1080p. WebP lo soporta el CEF de CasparCG (Chromium 110).
- Se aplica la orientación EXIF y se eliminan los metadatos, incluida la
  ubicación GPS de las fotos de teléfono.
- Usa Imagick si está disponible y, si no, GD. Para GD sube el
  `memory_limit` a 512 MB mientras procesa, porque decodificar una foto de
  24 MP ocupa unos 100 MB.
- Los logos no se tocan: son PNG o SVG con transparencia y pesan poco.

Los fondos subidos antes de este cambio quedan tal cual.

### 2026-11-22 — Herramientas de revisión en la pantalla

- Las **flechas del teclado** pasan a la diapositiva siguiente (→ o ↓) o a la
  anterior (← o ↑). La diapositiva elegida dura su tiempo completo, y el
  contenido pendiente se aplica igual que en una transición normal.
- La tecla **`G`** muestra u oculta las áreas seguras (SMPTE ST 2046-1):
  *action safe* 93 % y *title safe* 90 %, más una cruz central. Con `/?guides`
  aparecen desde el inicio. Los márgenes del contenido coinciden con el
  *title safe*.
- Están siempre activas, sin necesidad de un modo de depuración: al aire
  nadie tiene teclado, así que no interfieren con la rotación.

### 2026-11-22 — HUIFAX no administra la grilla de programación

Se implementó una grilla propia (programas, horarios semanales con temporada,
cancelaciones y traslados puntuales, y emisiones especiales). Quedó en la rama
`feature/schedule` como referencia, sin combinar.

**Por qué se descartó:**
- La programación pertenece a un sistema de planificación dedicado (la
  versión completa de *overtime*, o el generador de grillas para RedCast
  OnTime mientras tanto).
- Mantenerla también en HUIFAX duplicaría la carga de datos, y tarde o
  temprano las dos versiones dejarían de coincidir.
- Para quien la edita, el modelo resultó enrevesado.

**En su lugar:**
- **Programas destacados:** anuncios tipo *billboard* de los programas
  insignia, con imagen, nombre, frase, horario en texto libre y vigencia
  opcional. Es contenido promocional y editorial.
- **Grilla externa, más adelante:** cuando exista el sistema de
  planificación, HUIFAX leerá la grilla de un feed externo, con un proveedor
  intercambiable como el del clima. El contrato (JSON o iCalendar) se define
  junto con ese sistema. La forma en que la rama de referencia envía cada
  emisión puede servir de punto de partida: programa, descripción, inicio y
  fin en ISO 8601 con desfase, y etiqueta.

### 2026-11-23 — Programas destacados

Reemplazan a la grilla propia (ver la entrada anterior).

**Contenido** (`featured_programs`, en el grupo *Contenido* del panel; lo
administran también los editores):
- Nombre, frase opcional y etiqueta opcional de texto libre ("Estreno", "En
  vivo"; hasta 20 caracteres).
- Horario en texto libre, tal como se mostrará ("Martes y jueves, 19:00"),
  sin lógica de recurrencia.
- Imagen obligatoria, optimizada al subirla igual que los fondos.
- Vigencia opcional, duración propia opcional, orden y habilitado, como los
  avisos.

**En pantalla:** la diapositiva `featured_programs` muestra cada destacado
vigente como su propia página. A la izquierda va la imagen en 16:9 y a la
derecha la etiqueta, el nombre, la frase y el horario. La imagen va dentro de
la diapositiva y no como fondo, para que el texto se lea bien sea cual sea la
imagen. La rotación precarga la imagen de la diapositiva siguiente.

**Código compartido:** la vigencia (`statusAt()` y el scope `activeAt`) pasó
al trait `HasPublicationWindow`, que usan los avisos y los destacados. La
limpieza de archivos reemplazados o borrados pasó a `HasStoredImage`, que
usan los fondos y los destacados.

## Pendientes

- **Radio:** la URL del stream está por llegar. Debe ser configurable desde el
  panel (URL, activar/desactivar la reproducción en la página, volumen). Por
  defecto el audio lo reproduce CasparCG.
- **Diseño de la programación:** ver la entrada correspondiente.
- **CasparCG:** anotar la versión exacta y la de su CEF.
- **Despliegue en el CT:** distribución, servidor web, HTTPS y cómo se publica.
- **Tipografía y diseño visual** de la pantalla al aire (con colores y
  tipografía personalizables por instancia).
