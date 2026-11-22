# Guidelines de desarrollo

HUIFAX es un **producto reutilizable**: debe servir a otros canales de TV y a
cualquier situación que requiera información rotando permanentemente en
pantalla. PichilemuTV.org es la primera instancia, no el producto. La
mantenibilidad es prioritaria: seguir las convenciones del ecosistema
(Laravel, Filament, Vue) antes que inventar las propias. Ante una duda de
convención o de diseño, consultar.

## Principios del producto

1. **Nunca se cae en pantalla.** Si una fuente de datos falla, la diapositiva
   muestra el último dato válido o se salta; jamás un error, un spinner eterno
   o una pantalla en blanco. Una excepción en un componente no debe detener el
   carrusel.
2. **El audio no se detiene.** La radio se reconecta sola ante cortes o
   bloqueos del stream, con reintentos espaciados (backoff). Contemplar las
   políticas de autoplay del navegador usado en la emisión, cuando se escoja usar el audio de la página en vez de la reproducción mediante el playout.
3. **Funciona días seguidos.** Sin fugas de memoria: limpiar intervalos,
   timers y listeners; no acumular nodos en el DOM. Considerar una recarga
   programada (p. ej. diaria, en la madrugada) como red de seguridad, para los casos en los que se reproduzca por días completos.
4. **Pensado para TV.** Diseño fijo a 1920×1080, escalable a otras
   resoluciones 16:9. Respetar zonas seguras de TV (dejar ~5 % de margen por
   lado para texto). Tipografía grande, alto contraste, evitar líneas finas y
   patrones que parpadeen. Animaciones suaves y a 60 fps.
5. **Legible desde el sillón.** Poco texto por diapositiva y duración
   suficiente para leerlo completo.
6. **Hora e idioma de la instancia.** La zona horaria y el idioma se
   configuran por instancia y nunca se toman de la máquina ni del navegador.
   Valores por defecto: `America/Santiago` y `es-CL`.
7. **Nada propio de una instancia en el código.** Nombre del canal, logo,
   textos, colores, URL de la radio, fuentes de datos locales (p. ej. la
   ciudad del pronóstico): todo es configuración o datos del panel.

## Código

- **Idioma del código: inglés.** Nombres de clases, variables, funciones,
  rutas, tablas, columnas, claves de configuración, comentarios y mensajes de
  commit, como en cualquier proyecto de código abierto.
- **Idioma de la documentación: español** (`docs/`). El `README.md` incluye
  además un resumen en inglés.
- **Interfaz traducible.** Ningún texto visible queda escrito directamente en
  el código: se usan los archivos de idioma de Laravel (`lang/`), con `es`
  como idioma por defecto. Esto vale para la pantalla y para el panel.
- **Convenciones:** las de Laravel (nombres, estructura de carpetas, PSR-12
  vía Pint), las de Filament para el panel, y el estilo oficial de Vue
  (Composition API con `<script setup>`, componentes en PascalCase).
- Cada diapositiva es un módulo independiente con la misma interfaz
  (cargar datos, renderizar, duración, si está disponible). Agregar una nueva
  no debe requerir tocar el resto.
- Separar la **obtención de datos** (adaptadores por fuente: meteochile,
  calendario, etc.) de la **presentación**. Los adaptadores normalizan los
  datos a un formato propio y manejan sus errores.
- Contenido editable (avisos, números de emergencia, redes, duración de
  diapositivas, URL de la radio) en **configuración o datos**, no en el código.
- Dependencias mínimas. Preferir APIs del navegador antes que librerías.
- Formateo y lint automáticos desde el primer commit.

## Secretos y datos externos

- **Nunca** subir claves de API, tokens ni credenciales al repositorio. Usar
  variables de entorno o un archivo local ignorado por git, con un
  `.example` versionado.
- Una clave que termine en el JavaScript del navegador es pública: restringirla
  por dominio/referer en su proveedor, o mover la llamada a un proxy/backend.
  (La versión actual expone una clave de Google en el bundle; no repetirlo.)
- Respetar los límites de uso de las fuentes externas: cachear y consultar con
  una frecuencia razonable (p. ej. clima cada 30–60 min).
- Usar HTTPS. Si una fuente solo ofrece HTTP, resolverlo con un proxy propio
  para evitar contenido mixto.
- Citar la fuente cuando corresponda (p. ej. "Información proporcionada por la
  Dirección Meteorológica de Chile").

## Pruebas y verificación

- Probar los adaptadores de datos con respuestas guardadas (fixtures),
  incluyendo respuestas vacías, malformadas y errores de red.
- Antes de dar por terminado un cambio visual, revisarlo a 1920×1080 en el
  mismo navegador/motor que se usa en la emisión.
- Probar al menos una vez una ejecución larga (varias horas) observando el
  consumo de memoria.

## Git

- Commits pequeños con mensajes descriptivos en inglés, en modo imperativo
  (p. ej. `Add display API endpoint`).
- `main` siempre desplegable; trabajo en ramas.
- Documentar en [decisiones.md](decisiones.md) toda decisión técnica relevante
  (qué se eligió, por qué y qué se descartó).
