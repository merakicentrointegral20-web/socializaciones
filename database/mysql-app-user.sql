-- Usuario de MySQL dedicado para xr-rrhh, con permisos mínimos.
-- NO usar el usuario root/todopoderoso de Xuri Apex para esta app.
--
-- Reemplazar :password por una contraseña generada (no reusar la de local).
-- Ejecutar como root/administrador de la base de datos.
--
-- Este script documenta lo que ya se aplicó en local (2026-07-08) para que
-- se pueda reproducir igual en producción (Siteground) cuando llegue ese
-- paso del plan, y para que quede registrado por qué cada tabla tiene el
-- nivel de acceso que tiene.

CREATE USER IF NOT EXISTS 'xr_rrhh_app'@'127.0.0.1' IDENTIFIED BY ':password';
CREATE USER IF NOT EXISTS 'xr_rrhh_app'@'localhost' IDENTIFIED BY ':password';

-- ─── Datos de identidad compartidos con xuri-apex: mayormente SOLO LECTURA ─
-- xr-rrhh no debe tocar filas existentes aquí. La única excepción es el
-- flujo de alta de acceso (paso 5, regla de identidad única), que necesita
-- crear la cuenta nueva y enlazar el colaborador — por eso los permisos de
-- escritura de abajo están recortados a columna, no a la tabla completa.
GRANT SELECT ON xuri_apex.users TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT ON xuri_apex.collaborators TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT ON xuri_apex.roles TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT ON xuri_apex.permissions TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT ON xuri_apex.model_has_roles TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT ON xuri_apex.model_has_permissions TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT ON xuri_apex.role_has_permissions TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- ─── Alta de acceso (paso 5): excepción puntual y acotada por columna ─────
-- Crear la cuenta (solo name/email/password, nunca 'role' ni otra columna),
-- enlazar el colaborador (solo user_id, nunca id_card/función/etc.), y
-- asignar/quitar el rol de capacitación ya existente (nunca crear roles
-- nuevos en runtime — eso se hace con una cuenta con más privilegios, ver
-- database/seeders si existiera uno para roles/permissions).
-- created_at/updated_at incluidos porque Eloquent los escribe solo en
-- cada create()/update(), aunque el modelo no los mencione explícitamente.
-- email_verified_at incluido (2026-07-10): las cuentas que crea RRHH desde
-- acá ya quedan validadas por la persona que las da de alta a mano (con
-- contraseña temporal mostrada en pantalla, no por correo) -- no dependen
-- de que el SMTP de producción funcione para poder entrar.
GRANT INSERT (name, email, password, email_verified_at, created_at, updated_at) ON xuri_apex.users TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT UPDATE (user_id, updated_at) ON xuri_apex.collaborators TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT INSERT, DELETE ON xuri_apex.model_has_roles TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT INSERT, DELETE ON xuri_apex.model_has_permissions TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- Alta de personal NUEVO de producción/bodega-CEDI desde xr-rrhh (2026-07-10):
-- RRHH puede dar de alta directamente a alguien de producción y que quede
-- reflejado como colaborador en xuri-apex (GrantCollaboratorAccess::forNuevoIngreso).
-- Acotado a las columnas que esa fila necesita; nunca birth_date (no la
-- pedimos) y jamás UPDATE/DELETE arbitrario sobre filas ya existentes de
-- xuri-apex -- eso lo sigue haciendo solo la UPDATE de arriba (user_id).
GRANT INSERT (code, first_name, last_name, id_card, `function`, email, user_id, created_at, updated_at)
  ON xuri_apex.collaborators TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- "Recordarme" en el login: Laravel escribe/rota users.remember_token en
-- cada login y logout (SessionGuard::cycleRememberToken). Sin esto, tildar
-- "Recordarme" o cerrar sesión con un remember_token activo tira un 500
-- (detectado 2026-07-08, log: "UPDATE command denied ... for table users").
GRANT UPDATE (remember_token) ON xuri_apex.users TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- ─── Infraestructura de Laravel: lectura y escritura ───────────────────────
-- Sesiones, cache, colas y tokens de reset de password son necesarios para
-- que la app funcione (SESSION_DRIVER/CACHE_STORE/QUEUE_CONNECTION=database
-- en .env). No son datos de negocio de xuri-apex, por eso sí llevan CRUD.
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.sessions TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.cache TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.cache_locks TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.jobs TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.job_batches TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.failed_jobs TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.password_reset_tokens TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- ─── Tablas propias de xr-rrhh: CRUD completo ──────────────────────────────
-- Este usuario de runtime NUNCA recibe CREATE/ALTER/DROP a nivel de base de
-- datos -- eso abriría la puerta a modificar la estructura de tablas de
-- xuri-apex por error. En su lugar: las migraciones de xr-rrhh se corren
-- con una cuenta con más privilegios (ej. root en local):
--
--   DB_USERNAME=root DB_PASSWORD= php artisan migrate
--   DB_USERNAME=root DB_PASSWORD= php artisan migrate:status
--
-- (xr_rrhh_app tampoco tiene SELECT sobre la tabla `migrations` a
-- propósito -- correr esos comandos con las credenciales normales del
-- .env da "Migration table not found", es esperado, no un bug).
--
-- Justo después de migrar se agrega aquí el GRANT explícito por tabla.
-- Plantilla para la próxima tabla nueva:
--
-- GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.<tabla_nueva>
--   TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- Paso 6 (2026-07-08): modelo de datos de capacitación.
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.modulos TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.capacitaciones TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.capacitacion_asistentes TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.preguntas TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.evaluacion_intentos TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.certificados TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.modulo_lecturas TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- Diapositivas/PDF adjuntos a un módulo (2026-07-08).
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.modulo_archivos TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- Preguntas de opción múltiple, además de verdadero/falso (2026-07-09).
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.pregunta_opciones TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

-- Celular y cargo (administración) de cada persona, dato que xuri-apex no
-- tiene en ningún lado (2026-07-10).
GRANT SELECT, INSERT, UPDATE, DELETE ON xuri_apex.colaborador_contactos TO 'xr_rrhh_app'@'127.0.0.1', 'xr_rrhh_app'@'localhost';

FLUSH PRIVILEGES;
