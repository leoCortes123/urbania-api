# Urbania API

API de plataforma para administración de condominios: autenticación, autorización, propiedades,
directorio y comunicaciones. **Laravel 13 sobre PHP 8.5, con Clean Architecture y DDD en vez del
MVC estándar del framework.**

Construido entre el **19 de junio y el 2 de julio de 2026**.

## Las cuatro decisiones que definen el repositorio

Están documentadas como ADRs en [`docs/adr/`](docs/adr/), con su contexto y sus consecuencias:

1. **[ADR-001] Clean Architecture + DDD sobre MVC estándar.** El código de negocio vive en
   `src/`, no en `app/`. Cada contexto acotado —`Auth`, `Authorization`, `Tenancy`,
   `Propiedades`, `Directorio`, `Comunicaciones`— tiene sus propias capas `Domain`,
   `Application`, `Infrastructure` y `Presentation`. `app/` queda reducido a lo que Laravel
   exige.
2. **[ADR-002] RS256 sobre HS256 para firmar los JWT.** Clave asimétrica: quien valida un token
   no necesita poder emitirlo.
3. **[ADR-003] UUID v7 sobre IDs autoincrementales.** Ordenables por tiempo, sin filtrar el
   volumen de la tabla ni obligar a un viaje a la base para conocer el identificador.
4. **[ADR-004] Doble token con rotación.** Access de vida corta y refresh rotatorio con
   **detección de reutilización**: si un refresh ya consumido vuelve a aparecer, la familia
   entera se revoca.

## Qué hay implementado

**Autenticación y seguridad.** Registro, verificación de correo, recuperación y cambio de
contraseña, perfil. **MFA por TOTP** con activación, desactivación y códigos de respaldo.
Listado y revocación de sesiones activas, huella de dispositivo, *blacklist* de tokens en Redis.

**Autorización.** RBAC con roles, permisos y asignaciones; resolutor de permisos **cacheado en
Redis** y middleware `can()`.

**Dominio.** Torres, propiedades, unidades, tipos y estados como catálogos, documentos,
directorio de residentes y comunicaciones. **Impersonación de administrador** con `claims`
propios en el JWT y registro en `security_events`.

En números: **39 migraciones**, **115 rutas** registradas por módulo, y documentación de la API
generada con Scribe.

## Verificación

La calidad no es una convención de equipo: **la impone el CI**, y si algo de esto falla el
*build* se cae.

| | |
|---|---|
| **Pruebas** | 132 archivos, **717 casos** con Pest — 96 unitarias, 27 de *feature*, 6 de integración, 2 de seguridad |
| **Cobertura** | Umbral mínimo forzado en CI (`pest --coverage --min=80`) |
| **Análisis estático** | **PHPStan nivel 10** sobre `src/` y `app/` — el nivel más estricto que existe |
| **Estilo** | Laravel Pint en modo `--test` |
| **Servicios reales en CI** | PostgreSQL 18 y Redis 7 como *services* del *job*: las pruebas de integración corren contra motores de verdad, no contra dobles |

Todo junto en un solo comando: `composer ci` (`lint` + `stan` + `test`).

## Cómo levantarlo

```bash
cp .env.example .env
composer install
composer docker-up          # PostgreSQL + Redis
composer key-generate
composer generate-jwt-keys  # el par RS256 no está versionado
composer migrate
composer seed
```

Y para verificar que todo está en pie: `composer ci`.

## Estructura

```
src/                        # el negocio, por contexto acotado
  Auth/                     #   Domain · Application · Infrastructure · Presentation
  Authorization/
  Tenancy/
  Propiedades/
  Directorio/
  Comunicaciones/
  Shared/
app/                        # solo lo que Laravel exige
docs/adr/                   # las decisiones y por qué
tests/                      # Unit · Integration · Feature · Security
```

---

*Parte de la plataforma Urbania, junto a [`urbania-web`](https://github.com/leoCortes123/urbania-web),
[`urbania-plataforma`](https://github.com/leoCortes123/urbania-plataforma) y
[`urbania-docs`](https://github.com/leoCortes123/urbania-docs).*
