# 🚀 MERAKI - Plataforma de Capacitación, Inducción y Certificación

Sistema integral de gestión de talento humano para la capacitación, evaluación continua y certificación de colaboradores, diseñado con **Laravel 12**, **Livewire 3**, **TailwindCSS** y **DomPDF**.

---

## 📋 Características Principales

1. **Gestión de Módulos de Aprendizaje:**
   - Creación de módulos por categorías (Recursos Humanos, Seguridad Ocupacional, Calidad, etc.).
   - Modalidades **Presenciales** y **Virtuales**.
   - Material de lectura obligatorio y material de apoyo (PDF, diapositivas e imágenes).
   - Integración de videos secuenciales (OneDrive embebido, YouTube, Google Drive o Vimeo). Candado estricto: el colaborador no puede avanzar al siguiente video sin completar el anterior.

2. **Evaluaciones Automáticas:**
   - Banco de preguntas por módulo: **Verdadero/Falso** y **Opción múltiple**.
   - Auto-calificación instantánea sobre 10 con umbral de aprobación configurable.
   - **Regla pedagógica de 3 intentos:** Si reprueba 3 veces en el ciclo actual, el sistema bloquea el examen y exige reiniciar el módulo (releer y volver a ver los videos) para reabrir una nueva oportunidad.

3. **Certificación Digital Inmediata:**
   - Emisión automática de certificados en PDF de alta fidelidad tras aprobar el módulo.
   - Código único de verificación (UUID) y correlativo anual (`001-2026`).
   - Firma y sellos institucionales de **MERAKI**.
   - Soporte para versiones con carga horaria por períodos anuales para auditorías.
   - Descarga individual y descarga en lote (ZIP) por capacitación.

4. **Inducciones Presenciales y Actas de Asistencia:**
   - Agendamiento grupal con control anti-colisión de horarios para instructores (30 min).
   - Generación de acta de asistencia en blanco en PDF para recolección de firmas físicas.
   - Registro de asistencia con obligatoriedad de adjuntar el escaneo o fotografía del acta firmada.

5. **Administración de Colaboradores y Accesos:**
   - Alta de personal directamente desde la interfaz web (nombre, correo, cédula, cargo, celular).
   - Asignación de rol de **Instructor** (crea módulos y administra eventos) y **Solo Supervisión** (jefaturas/auditoría que no rinden exámenes).
   - Activación o desactivación de cuentas con un solo clic.
   - Recordatorios directos vía WhatsApp con un clic.

---

## 🎨 ¿Cómo cambiar el Logo y la Marca? (Súper Fácil)

Toda la identidad corporativa se controla en **un solo lugar**:

### 1. Cambiar el Logo:
Simplemente coloca tu imagen en:
```text
public/img/logo.png
```
*(Opcional: Si tu archivo tiene otro nombre o formato como `logo.svg` o `logo.jpg`, solo configúralo en tu archivo `.env`: `EMPRESA_LOGO="img/tu-logo.png"`)*.
> **Nota:** Si no subes ninguna imagen, el sistema genera automáticamente un isotipo moderno y elegante con la letra inicial de tu empresa.

### 2. Cambiar Nombre, Ciudad o Firma:
Abre tu archivo `.env` y edita estas variables:
```env
EMPRESA_NOMBRE="MERAKI"
EMPRESA_CIUDAD="Guayaquil"
EMPRESA_LOGO="img/logo.png"
EMPRESA_LOGO_WEB="img/logo.png"

# Firma para los certificados PDF:
CERTIFICADO_FIRMA="img/firma.png"
CERTIFICADO_FIRMA_NOMBRE="Dirección de Talento Humano"
CERTIFICADO_FIRMA_CARGO="Talento Humano"
```

---

## 🛠️ Instalación y Puesta en Marcha

### Prerrequisitos:
* **PHP 8.2 o superior** (con extensiones `pdo_mysql`, `gd`, `zip`, `fileinfo`, `mbstring`, `openssl`).
* **Composer**
* **Node.js** (v18+) y **NPM**
* **MySQL** o **PostgreSQL**

### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/TU_USUARIO/meraki-capacitaciones.git
cd meraki-capacitaciones
```

### Paso 2: Instalar Dependencias
```bash
composer install
npm install
```

### Paso 3: Configurar el Entorno
```bash
cp .env.example .env
php artisan key:generate
```
Configura las credenciales de tu base de datos MySQL en el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meraki_capacitaciones
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 4: Ejecutar Migraciones y Datos Iniciales
```bash
php artisan migrate --seed
```
Esto creará todas las tablas necesarias y registrará el usuario administrador inicial:
* **Usuario:** `admin@meraki.com`
* **Contraseña:** `password`

### Paso 5: Compilar Assets y Ejecutar el Servidor
```bash
npm run build
php artisan serve
```
Abre en tu navegador: `http://127.0.0.1:8000`

---

## ☁️ Opciones de Despliegue en la Nube y Firebase

### 1. Base de Datos: ¿Por qué MySQL y no Firebase Firestore?
Laravel Eloquent utiliza un modelo **relacional SQL** para calcular intentos, conteos de nómina, asistencias, banco de preguntas con opciones múltiples y permisos Spatie. Intentar usar una base NoSQL (como Firestore) rompería las relaciones y paquetes del sistema.

Para producción, puedes usar una base de datos MySQL gestionada:
* **Railway / Render:** Despliega Laravel y MySQL juntos con 1 solo clic desde GitHub.
* **Google Cloud SQL:** Si deseas toda la infraestructura dentro del ecosistema de Google / Firebase.
* **Hosting cPanel / VPS:** Funciona en cualquier servidor estándar con PHP y MySQL.

### 2. Uso Recomendado de Firebase (Firebase Storage):
Puedes conectar **Firebase Storage** (Google Cloud Storage) en Laravel para almacenar todos los certificados emitidos, actas escaneadas y presentaciones en la nube de Google usando el driver de Cloud Storage de Laravel.

---

## 📁 Estructura del Proyecto

* `app/Livewire/Colaboradores/`: Administración de nómina y altas de colaboradores.
* `app/Livewire/MisModulos/`: Portal del colaborador (lectura, videos, evaluaciones).
* `app/Livewire/Modulos/`: Editor de módulos, seguimiento de asistencias y actas.
* `app/Models/`: Modelos de datos (`Modulo`, `Pregunta`, `EvaluacionIntento`, `Certificado`, `Capacitacion`).
* `resources/views/pdf/`: Plantillas DomPDF de Certificados y Actas de Inducción.
* `config/xr_rrhh.php`: Configuración global de marca e institucional.

---

## 📄 Licencia
Este proyecto es privado y de uso exclusivo para **MERAKI**.
