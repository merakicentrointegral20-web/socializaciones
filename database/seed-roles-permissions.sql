-- Roles y permisos propios de xr-rrhh, sembrados en las tablas de Spatie
-- compartidas con xuri-apex. Se corre UNA VEZ con una cuenta con más
-- privilegios que el usuario de runtime (ver database/mysql-app-user.sql
-- sobre por qué xr_rrhh_app no tiene INSERT/DELETE en roles/permissions/
-- role_has_permissions: crear o borrar roles es una acción de
-- configuración, no de operación diaria).
--
-- Historial:
--  2026-07-08: se sembraron 3 roles (rrhh-director, medico-ocupacional,
--    seguridad-industrial). Se revirtió: en la práctica son 3 personas
--    puntuales, no 3 perfiles distintos, y la categoría del módulo ya es
--    un dato del módulo mismo. Se reemplazó por un único rol "instructor".
--  2026-07-08 (mismo día, corrección): rol único "instructor" +
--    permiso "colaboradores.gestionar" otorgado directo a la persona
--    encargada de RRHH (no vía rol), porque es una sola persona.

INSERT IGNORE INTO roles (name, guard_name, created_at, updated_at) VALUES
  ('instructor', 'web', NOW(), NOW());

INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
  ('modulos.crear', 'web', NOW(), NOW()),
  ('capacitaciones.gestionar', 'web', NOW(), NOW()),
  ('colaboradores.gestionar', 'web', NOW(), NOW());

-- El rol instructor puede crear módulos (en cualquier categoría, la
-- categoría es un campo del módulo) y gestionar capacitaciones/asistencia.
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id FROM permissions p, roles r
WHERE p.name IN ('modulos.crear', 'capacitaciones.gestionar')
  AND r.name = 'instructor';

-- colaboradores.gestionar (pantalla de acceso/roles) NO se asigna aquí a
-- ningún rol -- se otorga directo a la persona de RRHH con:
--   $user->givePermissionTo('colaboradores.gestionar');
-- (ver ejemplo real aplicado a stdev@xurisa.com en local).
