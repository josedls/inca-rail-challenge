# Modelo de datos

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    ROLES {
        bigint id PK
        varchar name
        varchar guard_name
    }

    PERMISSIONS {
        bigint id PK
        varchar name
        varchar guard_name
    }

    MODEL_HAS_ROLES {
        bigint role_id PK, FK
        varchar model_type PK
        bigint model_id PK
    }

    MODEL_HAS_PERMISSIONS {
        bigint permission_id PK, FK
        varchar model_type PK
        bigint model_id PK
    }

    ROLE_HAS_PERMISSIONS {
        bigint permission_id PK, FK
        bigint role_id PK, FK
    }

    FOOTBALL_COMPETITIONS {
        bigint id PK
        bigint external_id UK
        varchar name
        varchar code
        varchar emblem
        varchar import_status
        text import_error
        int pending_player_imports
        int failed_player_imports
        timestamp last_synced_at
        timestamp import_started_at
        timestamp import_completed_at
    }

    FOOTBALL_TEAMS {
        bigint id PK
        bigint external_id UK
        varchar name
        varchar short_name
        varchar crest
        timestamp last_synced_at
    }

    FOOTBALL_PLAYERS {
        bigint id PK
        bigint external_id UK
        varchar name
        varchar position
        tinyint shirt_number
        date date_of_birth
        varchar nationality
        timestamp last_synced_at
    }

    COMPETITION_TEAM {
        bigint competition_id PK, FK
        bigint team_id PK, FK
    }

    TEAM_PLAYER {
        bigint team_id PK, FK
        bigint player_id PK, FK
    }

    OAUTH_CLIENTS {
        uuid id PK
        varchar owner_type
        bigint owner_id
        varchar provider
        boolean revoked
    }

    OAUTH_ACCESS_TOKENS {
        char id PK
        bigint user_id FK
        uuid client_id FK
        timestamp expires_at
        boolean revoked
    }

    JOBS {
        bigint id PK
        varchar queue
        longtext payload
        timestamp reserved_at
        timestamp available_at
    }

    CACHE {
        varchar key PK
        text value
        int expiration
    }

    USERS ||--o{ MODEL_HAS_ROLES : "tiene"
    ROLES ||--o{ MODEL_HAS_ROLES : "asigna"
    USERS ||--o{ MODEL_HAS_PERMISSIONS : "recibe"
    PERMISSIONS ||--o{ MODEL_HAS_PERMISSIONS : "asigna"
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : "incluye"
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : "pertenece"

    FOOTBALL_COMPETITIONS ||--o{ COMPETITION_TEAM : "relaciona"
    FOOTBALL_TEAMS ||--o{ COMPETITION_TEAM : "participa"
    FOOTBALL_TEAMS ||--o{ TEAM_PLAYER : "integra"
    FOOTBALL_PLAYERS ||--o{ TEAM_PLAYER : "pertenece"

    USERS ||--o{ OAUTH_CLIENTS : "owner polimórfico"
    USERS ||--o{ OAUTH_ACCESS_TOKENS : "autentica"
    OAUTH_CLIENTS ||--o{ OAUTH_ACCESS_TOKENS : "emite"
```

## Interpretación

- Una liga puede tener muchos equipos y un equipo puede aparecer en varias ligas; la relación se almacena en "competition_team".
- Un equipo puede tener muchos jugadores y un jugador puede estar asociado a varios equipos; la relación se almacena en "team_player".
- Las tablas pivote tienen una clave primaria compuesta para impedir relaciones duplicadas.
- "external_id" identifica el registro en football-data.org y es único en ligas, equipos y jugadores. Esto permite repetir una importación sin duplicar datos.
- El número de camiseta pertenece actualmente al jugador porque es el dato recibido y persistido por la integración; la relación entre jugador y equipo no tiene atributos adicionales.
- Los campos de importación viven en "football_competitions" porque representan el estado agregado de la importación de una liga.

## Autorización y soporte técnico

Las tablas de roles y permisos corresponden a Spatie Permission. Las tablas "model_has_roles" y "model_has_permissions" son polimórficas; en este proyecto el modelo principal es User.

Las tablas de Passport ("oauth_clients" y "oauth_access_tokens") soportan la autenticación Bearer. Las tablas "jobs" y "cache" son infraestructura de Laravel: "jobs" permite la importación asíncrona y "cache" coordina el rate limiting y el cache de permisos.
