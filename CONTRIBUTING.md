# Contribuir a HUIFAX

¡Gracias por tu interés! HUIFAX es un proyecto de
[PichilemuTV.org](https://pichilemutv.org) pensado para que lo use cualquier
canal u organización.

> **English:** Issues and pull requests are welcome in English or Spanish.
> Code, comments and commit messages must be in English. Please read
> [docs/guidelines.md](docs/guidelines.md) before contributing.

## Antes de empezar

Lee la documentación en [docs/](docs/README.md), en especial:

- [docs/guidelines.md](docs/guidelines.md): reglas de desarrollo (obligatorias).
- [docs/decisiones.md](docs/decisiones.md): decisiones tomadas. Si tu cambio
  implica una decisión técnica nueva, regístrala ahí.

Para cambios grandes, abre primero un issue para conversarlo.

## Convenciones

- Código, comentarios y mensajes de commit **en inglés**. Los commits van en
  modo imperativo (`Add weather slide`).
- Documentación de `docs/` **en español**.
- Ningún texto visible queda escrito directamente en el código: va en `lang/`.
- Nada propio de una instancia (nombre del canal, logo, URL, ciudad) en el
  código: va en configuración o en datos del panel.

## Flujo de trabajo

1. Crea una rama desde `main` (`feature/...`, `fix/...`, `docs/...`).
2. Antes de abrir el pull request, ejecuta:

    ```sh
    composer lint
    npm run format
    php artisan test
    ```

3. Abre el pull request contra `main`, describiendo qué cambia y por qué. Si
   hay cambios visuales, adjunta una captura a 1920×1080.

## Licencia

Al contribuir, aceptas que tu aporte se publique bajo la licencia del proyecto,
[AGPL-3.0-or-later](LICENSE).
