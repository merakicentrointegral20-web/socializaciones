$git = "C:\Users\xuri\AppData\Local\Programs\Git\cmd\git.exe"
$gh = "C:\Users\xuri\AppData\Local\Programs\bin\gh.exe"
$token = (& $gh auth token).Trim()
& $git add .
& $git commit -m "feat: habilitar PostgreSQL persistente, auto-deteccion de DATABASE_URL y modulo inicial de induccion en seeder"
& $git push "https://x-access-token:$token@github.com/merakicentrointegral20-web/socializaciones.git" main
