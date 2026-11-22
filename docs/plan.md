# Plan de desarrollo

Plan vivo: se ajusta a medida que se toman decisiones (ver
[decisiones.md](decisiones.md)).

## Hallazgos en la versión anterior

Revisión del bundle publicado en `tools.pichilemutv.org/huifax` (2026-11-21):

- **Clave de Google expuesta.** La clave de la API de Google Calendar está en
  texto plano en `app.js`. Hay que **revocarla o restringirla** en Google Cloud.
  La nueva versión no usa Google Calendar.
- **Clima por HTTP.** Pide `http://archivos.meteochile.gob.cl/.../pronostico.json`.
  Hoy esa URL **solo responde por HTTPS**, y sin cabeceras CORS: el navegador
  no puede leerla directamente desde otro dominio. Por eso la nueva versión la
  consulta desde el backend.
- **Reloj cada 10 ms.** El reloj se actualiza con `setInterval(..., 10)`, lo
  que gasta CPU sin necesidad. Basta con actualizarlo cada segundo, alineado al
  cambio de segundo.
- **Diapositivas de 60 s** fijas para todas (`slideInterval: 6e4`).
- El JSON de meteochile trae una entrada `"indice": "pichilemu"` con
  temperaturas mín/máx, íconos por tramo del día y textos con entidades HTML
  (`mi&eacute;rcoles`). El adaptador tendrá que decodificarlas, y el índice de
  la ciudad (`pichilemu`) es configuración de la instancia.

## Arquitectura

```
┌──────────────────────── CT Proxmox ─────────────────────────┐
│ Laravel                                                     │
│  ├─ Panel Filament (/admin)  → BD: avisos, emergencias,     │
│  │                              redes, diapositivas, config │
│  ├─ Adaptadores de fuentes   → meteochile                   │
│  │   (jobs programados + caché, normalizan y toleran fallos)│
│  ├─ GET /api/display         → JSON sin sesión con ETag/304:│
│  │                              diapositivas, datos, config │
│  └─ GET /                    → Blade que monta la app Vue   │
│  BD: SQLite (WAL)                                           │
└─────────────────────────────────────────────────────────────┘
                 ▲ HTTPS
┌────────────────┴──── CasparCG 2.4 (CEF) ────────────────────┐
│ Pantalla 1920×1080 en Vue 3: carrusel, reloj, polling cada  │
│ 30 s, cambios aplicados entre diapositivas, localStorage,   │
│ recarga diaria de seguridad, audio opcional                 │
└─────────────────────────────────────────────────────────────┘
```

**Principios aplicados:**

- **Fuentes externas solo desde el backend.** Un job programado
  (`schedule:run`) consulta cada fuente con la frecuencia adecuada (clima cada
  30–60 min) y guarda el último resultado válido. Si una consulta falla, se
  conserva el anterior junto a la fecha en que se obtuvo. La pantalla nunca
  espera a una fuente externa.
- **Diapositivas como módulos.** En el backend, cada tipo de diapositiva es
  una clase con la misma interfaz (`isAvailable()`, `data()`, `duration()`).
  En la pantalla, cada tipo es un componente Vue registrado por nombre de tipo. Agregar un tipo
  nuevo no obliga a tocar los demás.
- **Cambios en vivo sin cortar la diapositiva.** El contenido nuevo queda
  pendiente y reemplaza al vigente en la siguiente transición.
- **Pantalla tolerante a fallos.** Si `/api/display` falla, la pantalla usa la
  copia en `localStorage`. Si una diapositiva lanza una excepción al
  renderizarse, se salta y el carrusel sigue.

## Fases

### Fase 0 — Base del proyecto ✔

- [x] `git init` con rama `main`, `.gitignore` y `.editorconfig`.
- [x] Proyecto Laravel (SQLite) con Filament, Vue 3, Tailwind y daisyUI
      (versiones anotadas en decisiones.md).
- [x] Formateo y lint: Pint (PHP) y Prettier (Blade/JS/CSS/Vue).
- [x] Pruebas con PHPUnit (pantalla con locale/zona de la instancia, título,
      panel protegido).
- [x] Código en inglés, `config/huifax.php`, archivos de idioma `lang/es` y
      `lang/en`.
- [x] `.env.example` sin secretos.
- [x] Licencia AGPL-3.0-or-later (`LICENSE`).
- [x] Primer commit.
- [x] CI (GitHub Actions), Dependabot y `CONTRIBUTING.md`.
- [x] Publicar en `pichilemutvorg/huifax` y proteger `main` (ruleset).

### Fase 1 — Pantalla al aire mínima (contenido estático)

- [ ] Layout fijo de 1920×1080 escalado a 16:9, con zonas seguras del 5 %.
- [ ] Encabezado: logo y fecha/hora en `America/Santiago`, formato `es-CL`
      (`Intl.DateTimeFormat`, sin moment).
- [ ] Fondo con fotografías que rotan.
- [ ] Motor de carrusel: duración por diapositiva, transición suave, tolerante
      a errores.
- [ ] Diapositivas con datos del panel: Emergencias, Redes, Avisos.
- [ ] Endpoint `/api/display` con ETag/304 y versión de contenido.
- [ ] Polling, contenido pendiente aplicado entre diapositivas y caché en
      `localStorage`.
- [ ] Recarga programada diaria (hora configurable).

### Fase 2 — Panel de administración

- [ ] Login y usuarios (Filament).
- [ ] Enlace al código fuente en el panel, con URL configurable (AGPL §13).
- [ ] Recursos: Avisos (con vigencia desde/hasta), Números de emergencia,
      Redes sociales, Diapositivas (orden, duración, habilitada), Fondos
      (subida de imágenes), Configuración general (audio, hora de recarga).

### Fase 3 — Fuentes externas

- [ ] Adaptador meteochile, con fixtures (respuesta normal, vacía, malformada,
      error de red) e íconos servidos localmente o mediante el proxy.
- [ ] Diseño y recurso de Programación en el panel (sin Google Calendar),
      con su diapositiva.
- [ ] Citar las fuentes en pantalla.

### Fase 4 — Audio y robustez

- [ ] Reproductor de radio opcional en la página, con reconexión y backoff
      (desactivado por defecto: el audio sale de CasparCG). URL configurable en el
      panel.
- [ ] Prueba de varias horas en CasparCG midiendo la memoria.
- [ ] Monitoreo básico: endpoint de salud y estado de cada fuente en el panel.

### Fase 5 — Despliegue

- [ ] Preparar el CT (servidor web, PHP, BD, cron de `schedule:run`, HTTPS).
- [ ] Script de despliegue y documentación de operación.
