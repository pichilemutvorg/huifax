# Contexto

## Qué es

HUIFAX es un sistema de señalización para pantallas de TV que rota
información permanentemente. Nació para PichilemuTV.org y se diseña para que
otros canales u organizaciones lo instalen con su propia marca y contenido.
Cada instalación es una instancia independiente (ver
[decisiones.md](decisiones.md)).

## La primera instancia: PichilemuTV.org

PichilemuTV.org es un canal comunitario de Pichilemu (Región de O'Higgins,
Chile). Cuando no hay programación en vivo ni grabada, al final del día, el canal emite HUIFAX:
una página web a pantalla completa, capturada y enviada a la señal de TV, que
rota información relevante para la comunidad con una radio online de fondo.

## Cómo se ve y se usa

- Se reproduce en una **pantalla de televisión**, sin teclado ni mouse, y nadie
  interactúa con ella. Debe arrancar sola y funcionar horas o días sin
  intervención.
- Se captura como fuente web en CasparCG. La resolución de referencia es **1920×1080 (16:9)**.
- El público la ve desde lejos: textos grandes, alto contraste, poca
  información por pantalla y tiempo suficiente para leerla.
- El audio (radio online) es parte de la emisión: si se corta, el canal queda
  en silencio. Este está encolado en CasparCG y no se reproduce desde el navegador, pero esto debe ser configurable.

## Qué hace la versión actual (referencia)

Aplicación Vue 2 + Bootstrap-Vue, publicada en `tools.pichilemutv.org/huifax`,
con título "HUIFAX - El teletipo de PichilemuTV.org". Estructura:

- **Encabezado** (`OffAirHeader`): logo y fecha/hora en español
  (`dddd D [de] MMMM [del] YYYY`).
- **Fondo** (`OffAirBackground`): fotografías de Pichilemu (p. ej. Punta de Lobos).
- **Carrusel** (`OffAirContent`), con estas diapositivas:
  - `SlideClima`: pronóstico de la Dirección Meteorológica de Chile
    (JSON público de meteochile.gob.cl, con sus íconos).
  - `SlideProgramacion`: próximos programas leídos de un Google Calendar.
  - `SlideEmergencias`: números de emergencia.
  - `SlideSociales`: redes de PichilemuTV.org (Facebook, Instagram, Twitter/X,
    YouTube, WhatsApp).
  - `SlideAviso`: avisos libres.

La nueva versión debe cubrir al menos esto. Cómo mejorarlo (contenido
administrable, más fuentes, diseño, robustez) se define a medida que avance el
proyecto.
