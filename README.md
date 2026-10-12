# Inca Rail Challenge

Plataforma interna para consultar ligas, equipos y jugadores de fútbol. La aplicación conserva localmente los datos obtenidos desde [football-data.org](https://www.football-data.org), aplica autorización por permisos y expone una SPA para usuarios administrativos y de consulta.

## Stack

- Backend: PHP 8.5, Laravel 13, Eloquent, Laravel Passport y Spatie Laravel Permission.
- Frontend: Vue 3, Vue Router, Pinia, Vuetify, TypeScript y Composition API.
- Persistencia: MySQL 8.
- Procesamiento: Laravel Queue con driver `database`.
- Entorno: Docker Compose, PHP-FPM, Nginx y Node/Vite.

## Requisitos

- Docker 29+
- Docker Compose v2+
- Un token de `football-data.org`

## Instalación

Clonar el repositorio y acceder al proyecto:

~~~bash
git clone https://github.com/josedls/inca-rail-challenge.git
cd inca-rail-challenge
~~~

Desde la raíz del repositorio:

~~~bash
cp backend/.env.example backend/.env
~~~

Completar `backend/.env` antes de continuar:

~~~dotenv
FOOTBALL_DATA_TOKEN=tu_token
ADMIN_PASSWORD=una_contraseña
VIEWER_PASSWORD=otra_contraseña
~~~

Son valores de ejemplo para desarrollo local. Las credenciales demo también pueden cambiarse mediante `ADMIN_NAME`, `ADMIN_EMAIL`, `VIEWER_NAME` y `VIEWER_EMAIL`. No se deben subir valores reales ni archivos `.env` al repositorio.

Levantar los servicios:

~~~bash
docker compose up -d --build
~~~

Inicializar la aplicación:

~~~bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
docker compose exec backend php artisan passport:keys
docker compose exec backend php artisan passport:client \
  --personal \
  --provider=users \
  --name="Inca Rail Personal Access Client"
~~~

El último comando se ejecuta una sola vez por instalación. Si Passport ya tiene un cliente personal para el proveedor `users`, no es necesario crearlo nuevamente.

URLs locales:

- Frontend: <http://localhost:5173>
- API: <http://localhost:8000>
- MySQL desde el host: `127.0.0.1:3307`

El worker de colas (proceso que ejecuta los jobs) se inicia automáticamente con Docker Compose. Para verificar los servicios:

~~~bash
docker compose ps
docker compose logs -f queue-worker
~~~

## Usuarios demo

Los seeders son idempotentes: se pueden ejecutar nuevamente sin duplicar los usuarios.

| Usuario | Rol | Acceso |
| --- | --- | --- |
| `admin@inca-rail.test` | `admin` | Administración de usuarios, roles y permisos; consulta e importación de fútbol |
| `viewer@inca-rail.test` | `viewer` | Consulta de ligas, equipos y jugadores |

Las contraseñas se definen exclusivamente en `backend/.env` mediante `ADMIN_PASSWORD` y `VIEWER_PASSWORD`.

## API principal

Todas las rutas, excepto `/login`, requieren un token Bearer de Passport y un usuario activo.

### Autenticación

| Método | Ruta | Permiso |
| --- | --- | --- |
| `POST` | `/login` | Público |
| `GET` | `/me` | Sesión válida |
| `POST` | `/logout` | Sesión válida |

### Fútbol

| Método | Ruta | Permiso | Función |
| --- | --- | --- | --- |
| `GET` | `/competitions` | `competitions.view` | Lista ligas guardadas localmente |
| `GET` | `/competitions/{id}` | `competitions.view` | Detalle y equipos guardados |
| `GET` | `/team` | `team.view` | Lista equipos guardados |
| `GET` | `/team/{id}` | `team.view` | Detalle y jugadores guardados |
| `GET` | `/players` | `players.view` | Lista jugadores guardados |
| `POST` | `/competitions/sync` | `competitions.view` | Solicita la sincronización del catálogo de ligas |
| `POST` | `/competitions/{id}/import` | `competitions.view` | Solicita la importación de una liga |

El alcance expone jugadores mediante `GET /players`; no contempla el detalle individual de un jugador. Por ello, no existe un caso de “jugador inexistente” independiente. Las respuestas `404` aplican a recursos identificables, como `GET /team/{id}`, cuando el equipo solicitado no está guardado localmente.

Los `GET` son de lectura y no generan peticiones externas. Las operaciones que consultan football-data.org son explícitas y asíncronas mediante `POST`; responden `202 Accepted` cuando el trabajo queda encolado.

### Administración

Usuarios:

- `GET /users`
- `POST /users`
- `PATCH /users/{id}`
- `PATCH /users/{id}/status`

Roles:

- `GET /roles`
- `POST /roles`
- `PATCH /roles/{id}`
- `DELETE /roles/{id}`

Permisos:

- `GET /permissions`
- `POST /permissions`
- `PATCH /permissions/{id}`
- `DELETE /permissions/{id}`

La colección de Postman está disponible en [docs/postman/inca-rail-challenge.postman_collection.json](docs/postman/inca-rail-challenge.postman_collection.json).

## Persistencia e integración externa

La base de datos local es la fuente de lectura de la API. La sincronización sigue este flujo:

1. Se registra o actualiza el catálogo de ligas.
2. La importación de una liga se divide en trabajos para la liga, sus equipos y los jugadores de cada equipo.
3. Los trabajos guardan relaciones mediante operaciones idempotentes y restricciones únicas, evitando duplicados en reintentos o ejecuciones repetidas.
4. El worker procesa los jobs con la cola `database`; los estados de progreso y los errores parciales quedan almacenados para que el frontend pueda mostrarlos.

El límite gratuito de football-data.org es de 10 peticiones por minuto. Los jobs que pueden llamar al proveedor usan el middleware `RateLimited('football-data')`, que controla cuántas peticiones se ejecutan por minuto. La regla se registra con un límite global de 10 peticiones y Laravel guarda sus contadores en el almacén de cache (cache store) `database`, respaldado por la tabla `cache`. Los jobs se reprograman dentro de una ventana de recuperación de 15 minutos (`FOOTBALL_DATA_RETRY_WINDOW_MINUTES`). Esto evita que varios usuarios o importaciones concurrentes excedan el límite. Spatie también utiliza cache para acelerar la resolución de roles y permisos. La configuración relevante es:

~~~dotenv
FOOTBALL_DATA_TIMEOUT=5
FOOTBALL_DATA_RETRY_WINDOW_MINUTES=15
~~~

Los datos ya guardados se pueden consultar aunque football-data.org esté temporalmente indisponible. Un fallo del proveedor no elimina la copia local; se registra como error de sincronización/importación y puede afectar solo a los equipos que no pudieron procesarse.

## Arquitectura

El diagrama completo está disponible en [docs/architecture.md](docs/architecture.md).

El backend separa responsabilidades en capas:

- `Application`: casos de uso y contratos (ports).
- `Domain`: reglas y objetos del dominio.
- `Infrastructure`: Eloquent, Passport, Spatie, cliente externo y colas (adapters).
- `Http`: controladores delgados, validación, middleware y respuestas API.

### Decisión de arquitectura

Consideré tres alternativas:

- **Laravel tradicional:** rápida para un CRUD, pero mezcla fácilmente controladores, Eloquent e integración externa; esto dificulta sustituir dependencias y probar los casos de uso.
- **Hexagonal/Clean Architecture completa:** ofrece un aislamiento mayor, pero introduce más capas, mappers y abstracciones de las necesarias para el alcance de la prueba.
- **Arquitectura por capas pragmática:** fue la opción que elegí. Conserva los beneficios importantes de Clean/Hexagonal sin añadir complejidad accidental.

La aplicación separa los casos de uso en `Application`, las reglas y datos del dominio en `Domain`, y los detalles técnicos en `Infrastructure`. Las interfaces funcionan como ports (contratos); Eloquent, Passport, Spatie, el cliente de football-data.org y las colas son adapters (implementaciones concretas). Los controladores solo coordinan HTTP, validación y respuestas API.

Esta decisión permite aplicar SOLID, mantener los controladores delgados, sustituir la fuente externa o la persistencia y probar la lógica con dobles de prueba. No incorporé CQRS, event sourcing ni una implementación DDD completa porque no aportan valor proporcional para este dominio y este alcance.

El frontend separa vistas, router (enrutador), stores (estado compartido) y servicios HTTP. La autorización se aplica en backend mediante permisos y se refleja en el router y la interfaz para mejorar la experiencia, sin sustituir la validación del servidor.

Decisiones relevantes:

- Los datos locales se consultan con `GET`; importar es una operación explícita `POST` para evitar efectos secundarios inesperados.
- La importación es asíncrona porque una liga puede requerir muchas consultas externas y debe respetar el límite de 10 peticiones por minuto.
- Se mantiene una copia local para desacoplar la disponibilidad de la API propia de la disponibilidad del proveedor externo.
- La desactivación de usuarios usa `is_active`: conserva el registro y sus relaciones, pero impide la autenticación del usuario.

## Pruebas y calidad

Las pruebas se ejecutan dentro de Docker:

~~~bash
docker compose exec backend php artisan test
docker compose exec backend vendor/bin/pint --test
docker compose exec frontend npm run test
docker compose exec frontend npm run build
~~~

El backend cubre autenticación, logout, usuario actual, autorización `401/403`, CRUD administrativo, errores HTTP, persistencia local, integración externa y trabajos de importación. El frontend cubre servicios, navegación y protección de rutas por permisos.

## Comandos útiles

~~~bash
# Seguir logs de la API y del worker
docker compose logs -f nginx backend queue-worker

# Ver trabajos fallidos
docker compose exec backend php artisan queue:failed

# Reintentar trabajos fallidos
docker compose exec backend php artisan queue:retry all

# Limpiar configuración en caché después de cambiar .env
docker compose exec backend php artisan config:clear

# Detener servicios conservando los datos de MySQL
docker compose down
~~~

Para reiniciar también la base de datos y los volúmenes de Docker, usar `docker compose down -v` únicamente cuando se quiera perder los datos locales.

## Anexos

- [Diagrama de arquitectura](docs/architecture.md)
- [Flujo de importación](docs/import-flow.md)
- [Modelo de datos](docs/data-model.md)
- [Colección de Postman](docs/postman/inca-rail-challenge.postman_collection.json)
