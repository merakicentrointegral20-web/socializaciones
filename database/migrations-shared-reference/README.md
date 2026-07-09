# No ejecutar estas migraciones

Estos 4 archivos vinieron del scaffold por defecto de Laravel/Breeze y de
`spatie/laravel-permission`, pero las tablas que crean (`users`,
`password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`,
`job_batches`, `failed_jobs`, `roles`, `permissions`, etc.) **ya existen**
en la base de datos compartida `xuri_apex`, creadas por el proyecto
xuri-apex.

Se sacaron de `database/migrations/` a propósito y NO deben volver ahí:

- Si se dejan en `database/migrations/` y alguien corre
  `migrate:fresh` o `migrate:rollback`, sus métodos `down()` harían
  `DROP TABLE` sobre tablas que pertenecen a xuri-apex (login de todo el
  sistema, colaboradores, etc.) — no solo a xr-rrhh.
- La migración de permisos (`create_permission_tables`) además tiene un
  nombre de archivo distinto al que ya corrió en xuri-apex
  (`2026_05_13_132042_create_permission_tables.php`), así que Laravel la
  trataría como "pendiente" y fallaría al intentar crear tablas que ya
  existen.

Se conservan aquí solo como referencia de qué esquema ya está en la base
compartida. `database/migrations/` debe contener únicamente las tablas
NUEVAS y propias de xr-rrhh (Modulo, Capacitacion, Pregunta,
EvaluacionIntento, Certificado, etc.).
