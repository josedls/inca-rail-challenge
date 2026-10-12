# Flujo de importación de una liga

```mermaid
flowchart TD
    start(["POST /competitions/{id}/import"]) --> auth{"¿Sesión válida + permiso competitions.view?"}
    auth -- "No" --> unauthorized["401 / 403"]
    auth -- "Sí" --> request["RequestCompetitionImport"]

    request --> status{"Estado actual"}
    status -- "En proceso (processing)" --> already["202 Accepted<br/>Importación en progreso"]
    status -- "Completada (completed)" --> completed["200 OK<br/>Sin nuevo job"]
    status -- "Disponible / fallida" --> mark["Marcar competencia como processing (en curso)<br/>Limpiar error y contadores"]
    mark --> dispatch["Encolar ImportCompetitionJob<br/>202 Accepted"]

    dispatch --> competitionJob["ImportCompetitionJob"]
    competitionJob --> competitionLimit{"¿Queda cupo de peticiones?"}
    competitionLimit -- "No" --> competitionRelease["Reencolar job con espera<br/>backoff (espera progresiva) · retry window (ventana de reintento): 15 min"]
    competitionRelease --> competitionJob
    competitionLimit -- "Sí" --> competitionApi["Consultar liga<br/>football-data.org"]
    competitionApi --> competitionSave["Crear o actualizar liga<br/>Persistencia local"]
    competitionSave --> teamsDispatch["Encolar ImportCompetitionTeamsJob"]

    teamsDispatch --> teamsJob["ImportCompetitionTeamsJob"]
    teamsJob --> teamsLimit{"¿Queda cupo de peticiones?"}
    teamsLimit -- "No" --> teamsRelease["Reencolar job con espera<br/>backoff (espera progresiva) · retry window (ventana de reintento): 15 min"]
    teamsRelease --> teamsJob
    teamsLimit -- "Sí" --> teamsApi["GET /competitions/{id}/teams<br/>football-data.org"]
    teamsApi --> teamsSave["Crear o actualizar equipos<br/>Conservar relaciones existentes"]
    teamsSave --> playersDispatch["Encolar ImportTeamPlayersJob<br/>uno por equipo"]

    playersDispatch --> playersJob["ImportTeamPlayersJob × N"]
    playersJob --> playersLimit{"¿Queda cupo de peticiones?"}
    playersLimit -- "No" --> playersRelease["Reencolar job con espera<br/>backoff (espera progresiva) · retry window (ventana de reintento): 15 min"]
    playersRelease --> playersJob
    playersLimit -- "Sí" --> playersApi["GET /teams/{id}<br/>football-data.org"]
    playersApi --> playersSave["Crear o actualizar jugadores<br/>Actualizar contadores"]
    playersSave --> pending{"¿Quedan equipos pendientes?"}

    pending -- "Sí" --> processing["Mantener estado processing (en curso)<br/>Actualizar progreso"]
    pending -- "No" --> errors{"¿Hubo errores?"}
    errors -- "Sí" --> failed["Estado failed (fallido)<br/>Guardar error de importación"]
    errors -- "No" --> imported["Estado completed (completado)<br/>Guardar fecha de finalización"]

    processing --> local["GET /competitions/{id}<br/>Frontend consulta datos locales"]
    failed --> local
    imported --> local
```

## Decisiones representadas

- Cada job usa el middleware RateLimited('football-data'); si se alcanza el límite global de 10 peticiones (requests) por minuto, vuelve a la cola (queue) con backoff (espera progresiva).
- Los jobs tienen una retry window (ventana de reintento) de 15 minutos para recuperarse de límites o errores temporales del proveedor.
- Las operaciones de persistencia son idempotentes: los reintentos actualizan registros existentes en lugar de duplicarlos.
- Los errores no reintentables se registran en la competencia y actualizan sus contadores; el frontend puede mostrar progreso y errores parciales.
- Los endpoints GET consultan la copia local y no llaman directamente a football-data.org.
