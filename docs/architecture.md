# Arquitectura

```mermaid
flowchart LR
    browser["Navegador"] --> frontend["Vue 3 SPA<br/>Router · Pinia · Vuetify"]
    frontend --> nginx["Nginx"]
    nginx --> api["Laravel API<br/>PHP-FPM"]

    subgraph backend["Backend por capas"]
        http["HTTP<br/>Controllers · Requests · Resources"]
        application["Application<br/>Use cases · Contracts"]
        domain["Domain<br/>Rules · Data"]
        ports["Ports<br/>Application contracts"]
        infrastructure["Infrastructure<br/>Adapters"]

        http --> application
        application --> domain
        application --> ports
        infrastructure -.->|implements| ports
    end

    api --> http
    infrastructure --> passport["Passport<br/>Tokens"]
    infrastructure --> spatie["Authorization<br/>RBAC · Spatie Permission"]
    infrastructure --> mysql
    infrastructure -->|dispatches jobs| queue
    passport --> mysql
    infrastructure --> football["football-data.org API"]

    subgraph persistence["MySQL 8"]
        mysql[("Application data")]
        queue[("jobs table")]
        cache[("cache table")]
    end

    spatie --> cache

    queue -->|reads and reserves jobs| worker["Queue worker"]
    worker --> application
    worker -->|rate limiter| cache
    worker -->|writes imported data| mysql
```

## Lectura del diagrama

- El frontend consume la API Laravel mediante HTTP.
- Los controladores solo coordinan la entrada HTTP y delegan en casos de uso.
- `Application` y `Domain` mantienen la lógica aislada de Eloquent y del proveedor externo.
- La infraestructura conecta la aplicación con MySQL, Passport, Spatie, cache, colas y football-data.org.
- La sincronización e importación se ejecutan de forma asíncrona mediante jobs en la cola `database`.
- El rate limit global de football-data.org usa el cache store `database`; la tabla `cache` coordina el límite entre jobs.
- Los `GET` consultan la copia local; solo los jobs de sincronización e importación llaman al proveedor externo.
