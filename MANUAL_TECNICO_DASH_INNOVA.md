# MANUAL DE USUARIO - SYGNIA - INNOVA Región 8 Lotes 1 y 2

**Sistema de Gestion Academica y Administrativa**

---

## Tabla de Contenido

1. [Requisitos del Sistema](#1-requisitos-del-sistema)
2. [Stack Tecnologico](#2-stack-tecnologico)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Arquitectura General](#4-arquitectura-general)
5. [Base de Datos](#5-base-de-datos)
6. [Integracion con Moodle](#6-integracion-con-moodle)
7. [Control de Acceso y Roles](#7-control-de-acceso-y-roles)
8. [Modulos: Funcionamiento Interno](#8-modulos-funcionamiento-interno)
9. [APIs y Endpoints](#9-apis-y-endpoints)
10. [Librerias y Dependencias](#10-librerias-y-dependencias)

---

## 1. Requisitos del Sistema

### 1.1 Servidor de Produccion

| Componente | Especificacion |
| --- | --- |
| **Servidor Web** | Apache 2.4+ o Nginx con soporte PHP-FPM |
| **PHP** | 8.3.31 |
| **Base de Datos** | MariaDB 10.6.27 (compatible con MySQL 5.7+) |
| **Sistema Operativo** | Ubuntu 22.04 LTS (produccion), Windows/Linux (desarrollo) |
| **Memoria RAM** | Minimo 512 MB para PHP, recomendado 1 GB |
| **Almacenamiento** | Depende del volumen de archivos subidos (cedulas, diplomas, fotos). Minimo 20 GB recomendado. |

### 1.3 Extensiones PHP Requeridas

- `mysqli` - Conexion a base de datos MySQL/MariaDB
- `gd` o `imagick` - Procesamiento de imagenes (Intervention Image)
- `curl` - Peticiones a la API de Moodle
- `mbstring` - Soporte multibyte para UTF-8
- `fileinfo` - Deteccion de tipo MIME en archivos subidos
- `zip` - Generacion de archivos ZIP (exportacion de cedulas)
- `xml` - Requerido por PhpSpreadsheet
- `dom` - Requerido por DomPDF

### 1.4 Dependencias del Sistema Operativo

- **Tesseract OCR** - Motor de reconocimiento optico de caracteres. Es la unica dependencia que se instala a nivel de sistema operativo, NO como libreria PHP.

  **Instalacion en Ubuntu/Debian:**
  ```bash
  sudo apt update
  sudo apt install tesseract-ocr tesseract-ocr-spa
  ```
  El paquete `tesseract-ocr-spa` instala los datos de idioma español, necesarios para reconocer cedulas colombianas.

  **Verificacion de instalacion:**
  ```bash
  which tesseract
  # Debe retornar: /usr/bin/tesseract
  
  tesseract --version
  # Debe mostrar la version instalada
  ```

  **En Windows (desarrollo local):**
  - Descargar el instalador desde: https://github.com/UB-Mannheim/tesseract/wiki
  - Instalar en `C:\Program Files\Tesseract-OCR\`
  - Agregar la ruta al PATH del sistema
  - Incluir los datos de idioma español durante la instalacion

  **¿Que pasa si Tesseract no esta instalado?** El modulo de verificacion de documentos (`document_verification`) no podra realizar el OCR de las cedulas. Los demas modulos del sistema funcionaran con normalidad.

### 1.5 Configuracion PHP Recomendada

```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 512M
max_input_vars = 10000
default_charset = "UTF-8"
date.timezone = "America/Bogota"
```

---

## 2. Stack Tecnologico

### 2.1 Backend

- **Lenguaje:** PHP 8.3 (procedural, sin framework)
- **Servidor de Base de Datos:** MariaDB 10.6.27
- **Motor de Plantillas:** PHP puro con includes
- **Manejo de Sesiones:** `$_SESSION` nativo de PHP

### 2.2 Frontend

| Dependencia | Version | Uso |
| --- | --- | --- |
| Bootstrap | 5.3.3 | Framework CSS y componentes UI |
| Bootstrap Icons | 1.11.3 | Iconografia |
| SortableJS | 1.15.6 | Listas arrastrables |
| jQuery | 3.6.0 | Manipulacion DOM y AJAX |
| DataTables | 1.13.6 | Tablas interactivas con busqueda y paginacion |
| Select2 | 4.1.0 | Selectores con busqueda |
| Chart.js | 4.x | Graficas del Dashboard (barras, donas, circulares) |
| ECharts | 5.x | Graficas de anillo (registro por departamentos, registros vs matriculados) |
| Summernote | 0.8.18 | Editor de texto enriquecido (correos) |
| SweetAlert2 | 11.7.0 | Ventanas emergentes y notificaciones |
| Font Awesome | 6.0.0 | Iconos adicionales |

### 2.3 Librerias PHP (Composer)

| Dependencia | Version | Uso |
| --- | --- | --- |
| `phpoffice/phpspreadsheet` | 3.9 | Generacion de archivos Excel (exportacion de informes) |
| `dompdf/dompdf` | 3.1 | Generacion de PDF (certificados, carnets, facturas) |
| `thiagoalessio/tesseract_ocr` | 2.13 | OCR para verificacion de documentos de identidad |
| `intervention/image` | 3.11 | Procesamiento y redimension de imagenes |

### 2.4 Base de Datos

- **Motor:** MariaDB 10.6.27 (compatible MySQL)
- **Charset:** `utf8mb4` con collation `utf8mb4_general_ci`
- **Timezone:** Configurado a `-05:00` (America/Bogota)
- **Conexion:** `mysqli` nativo, sin ORM
- **Nombre de la Base de Datos:** `dashboard`

---

## 3. Estructura del Proyecto

```
DASBOARD-ADMIN-MINTICS/
├── index.php                          # Login del sistema
├── main.php                           # Dashboard principal (pagina de inicio)
├── close.php                          # Cierre de sesion
├── profile.php                        # Perfil de usuario
├── conexion.php                       # Conexion a BD (raiz)
├── package.json                       # Dependencias npm
├── composer.json                      # Dependencias PHP
│
├── controller/
│   ├── header.php                     # Barra de navegacion superior
│   ├── footer.php                     # Pie de pagina
│   ├── conexion.php                   # Conexion a BD (global)
│   ├── botonFlotanteDerecho.php       # Boton flotante
│   ├── generar_zip_cedulas.php        # Endpoint: generacion de ZIP de cedulas
│   ├── noAprobadosMasivo.php          # Endpoint: marcar no aprobados masivo
│   ├── pasar_no_aprobado.php          # Endpoint: cambio masivo de estado
│   ├── update_directed_base.php       # Endpoint: actualizar base dirigida
│   ├── update_payment_number.php      # Endpoint: actualizar numero de pago
│   └── ...
│
├── components/
│   ├── sliderBar.php                  # Panel lateral izquierdo
│   ├── sliderBarRight.php             # Panel lateral derecho
│   ├── sliderBarBotton.php            # Panel inferior
│   │
│   ├── modals/
│   │   ├── userNew.php                # Modal: nuevo usuario (form + AJAX)
│   │   ├── newAdvisor.php             # Modal: nuevo asesor (form + POST)
│   │   ├── register_course.php        # Modal: registrar curso (form + AJAX)
│   │   ├── check_course_assignments.php # Verificar asignaciones previas
│   │   ├── get_courses.php            # Datos: cursos de Moodle
│   │   ├── get_users.php              # Datos: usuarios del sistema
│   │   ├── save_course.php            # Guardar curso
│   │   └── processUser.php            # Procesar nuevo usuario
│   │
│   ├── cardContadores/
│   │   ├── contadoresCards.php        # Contenedor de tarjetas y graficas (incluye page1 y page2)
│   │   ├── page1.php                  # Pagina 1 del Dashboard
│   │   ├── page2.php                  # Pagina 2 del Dashboard
│   │   └── actualizarContadores.php   # Endpoint AJAX: datos para contadores
│   │
│   ├── graphics/
│   │   ├── registerDeparments.php     # Grafica: registro por departamentos (ECharts)
│   │   ├── registerVsEnrolled.php     # Grafica: registros vs matriculados (ECharts)
│   │   ├── enrolledVsGraduated.php    # Grafica: matriculados vs formados (ECharts)
│   │   ├── certificadosVsFormados.php # Grafica: certificados vs formados (ECharts)
│   │   ├── ageRanges.php              # Grafica: rangos de edad (ECharts)
│   │   ├── registerDeparmentsQuery.php # Consulta SQL: departamentos
│   │   ├── registeVsEnrollerQuery.php  # Consulta SQL: registros vs matriculas
│   │   ├── ageRangesQuery.php          # Consulta SQL: rangos de edad
│   │   └── certificadosSobreFormados.php # Consulta SQL: certificados/formados
│   │
│   ├── attendance/
│   │   ├── trackingTable.php          # Tabla de seguimiento con pestañas
│   │   ├── statisticalPanel.php       # Panel estadistico (graficas de asistencia)
│   │   ├── observationsChart.php      # Grafico de observaciones
│   │   ├── getStudents.php            # Endpoint: obtener estudiantes por curso
│   │   ├── getAttendanceStats.php     # Endpoint: estadisticas de asistencia
│   │   ├── getAttendanceManagement.php # Endpoint: gestion de asistencia
│   │   ├── saveObservation.php        # Endpoint: guardar observacion
│   │   ├── saveAttendanceManagement.php # Endpoint: guardar gestion
│   │   ├── exportAttendance.php       # Exportar seguimiento completo
│   │   └── exportAttendanceSimple.php # Exportar asistencias simple
│   │
│   ├── attendanceGraphics/
│   │   ├── getCourseStatistics.php    # Endpoint: estadisticas de cumplimiento
│   │   ├── getCourseAttendanceStats.php # Endpoint: asistencias por tipo
│   │   ├── getClassesStatistics.php   # Endpoint: estadisticas por clase
│   │   └── exportar_clase.php         # Exportar asistencia de una clase
│   │
│   ├── multipleEmail/
│   │   ├── float_email.php            # Ventana flotante de correo
│   │   ├── send_email.php             # Endpoint: enviar correo individual
│   │   ├── save_history.php           # Endpoint: guardar historial de envio
│   │   ├── email_templates.php        # CRUD de plantillas de correo
│   │   └── get_user_by_numberid.php   # Endpoint: buscar usuario por cedula
│   │
│   ├── pqr/
│   │   ├── pqrButton.php              # Boton de notificaciones PQRS
│   │   └── sounds/notification.mp3    # Sonido de notificacion
│   │
│   ├── bootcampPeriods/
│   │   └── periods_button.php         # Boton de notificacion de periodos
│   │
│   ├── qrMentorias/
│   │   ├── mentoriaButton.php         # Boton de notificacion QR mentorias
│   │   └── factura_mentorias.php      # Endpoint: generar factura PDF
│   │
│   ├── infoWeek/                      # Exportacion de informes semanales
│   │   ├── exportAll.php
│   │   ├── exportAll_non_registered.php
│   │   ├── exportAll_base_adicionales.php
│   │   ├── semanal_todos.php
│   │   ├── exportHoursEL.php
│   │   ├── exportAbsence.php
│   │   ├── export_E_29.php
│   │   ├── export_E_29_specific.php
│   │   ├── export_E_19_VF_contra.php
│   │   ├── export_observations.php
│   │   ├── exportAll_from_file.php
│   │   ├── upload_informe.php
│   │   └── asistenciasComprobantes.php
│   │
│   └── filters/
│       └── takeUser.php               # Funcion: obtenerInformacionUsuario()
│
├── css/                               # Hojas de estilo
├── js/                                # Scripts JavaScript
├── img/                               # Imagenes y logos
├── uploads/                           # Archivos subidos
│   └── dashboard_bd.sql               # Backup de la base de datos
│
├── APIS/
│   └── conexion.php                   # Conexion alternativa a BD
│
└── vendor/                            # Dependencias Composer
```

---

## 4. Arquitectura General

### 4.1 Patron de Diseno

El sistema sigue una arquitectura **procedural con includes modulares**, sin uso de framework MVC. Cada pagina PHP incluye componentes reutilizables mediante `include()` y `require_once()`.

### 4.2 Flujo de Ejecucion

```
index.php (Login)
  └─> Validacion de credenciales contra tabla `users`
      └─> main.php (Dashboard)
          ├─ include("controller/header.php")
          │   ├─ include("components/sliderBarRight.php")
          │   ├─ include("components/multipleEmail/float_email.php")
          │   └─ include("components/pqr/pqrButton.php") [si rol aplica]
          ├─ include("components/sliderBar.php")
          │   └─ require_once("components/modals/register_course.php")
          ├─ include("components/modals/userNew.php")
          ├─ include("components/modals/newAdvisor.php")
          ├─ include("components/cardContadores/contadoresCards.php")
          │   ├─ include("page1.php")
          │   │   ├─ include("components/graphics/registerDeparments.php")
          │   │   ├─ include("components/graphics/registerVsEnrolled.php")
          │   │   ├─ include("components/graphics/enrolledVsGraduated.php")
          │   │   ├─ include("components/graphics/certificadosVsFormados.php")
          │   │   └─ include("components/graphics/ageRanges.php")
          │   └─ include("page2.php")
          ├─ include("controller/footer.php")
          ├─ include("controller/botonFlotanteDerecho.php")
          └─ include("components/sliderBarBotton.php")
```

### 4.3 Variables Globales de Sesion

| Variable | Tipo | Descripcion |
| --- | --- | --- |
| `$_SESSION['loggedin']` | bool | Indica si el usuario esta autenticado |
| `$_SESSION['username']` | string | Numero de documento del usuario (PK de `users`) |
| `$_SESSION['campos_incompletos']` | bool | Flag para alerta de perfil incompleto |
| `$_SESSION['campos_faltantes']` | array | Lista de campos pendientes en el perfil |

### 4.4 Funcion Principal de Usuario

La funcion `obtenerInformacionUsuario()` definida en `components/filters/takeUser.php` consulta la tabla `users` usando `$_SESSION['username']` y retorna un array asociativo con:

```php
$infoUsuario = [
    'username'  => int,      // Numero de documento (PK)
    'nombre'    => string,   // Nombre completo
    'rol'       => string,   // Nombre del rol (ej. "Administrador")
    'extra_rol' => string,   // Rol adicional (ej. "Extra Administrador")
    'foto'      => string,   // Ruta de la foto de perfil
    'email'     => string,
    'genero'    => string,
    'telefono'  => string,
    'direccion' => string,
    'edad'      => int
];
```

### 4.5 Mecanismo de Roles

La tabla `users` maneja el control de acceso mediante el campo `rol` (INT). La funcion `obtenerInformacionUsuario()` convierte el valor numerico a texto:

| ID | Rol (texto) | Descripcion |
| --- | --- | --- |
| 1 | Administrador | Gestion completa del sistema |
| 2 | Editor | Edicion de contenidos |
| 3 | Asesor | Contacto y seguimiento de campistas |
| 4 | Visualizador | Solo consulta (acceso limitado a SENATICS, tutoriales e informes basicos) |
| 5 | Docente | Registro de asistencias |
| 6 | Academico | Gestion de formacion |
| 7 | Monitor | Apoyo y seguimiento |
| 8 | Mentor | Mentorias |
| 9 | Permanencia | Control de permanencia y seguimiento |
| 10 | Empleabilidad | Encuestas y empleabilidad |
| 11 | Triangulo | Acceso a consultas individuales y pre-registros para triangulacion |
| 12 | Control maestro | Acceso total al sistema |
| 13 | Interventoria | Supervision y auditoria |
| 14 | Supervisor | Administracion de puntajes y horarios |

El campo `extra_rol` permite asignar permisos adicionales sin cambiar el rol principal. Cuando `extra_rol = 'Extra Administrador'`, el usuario accede a modulos restringidos como "Por aprobar" e "Inscritos SenaTICS".

---

## 5. Base de Datos

### 5.1 Datos de Conexion

```php
// Archivo: controller/conexion.php
$server   = "db";           // Hostname del contenedor MySQL
$username = "root";         // Usuario
$password = "root";         // Contraseña
$bd       = "dashboard";    // Nombre de la BD
$conn     = mysqli_connect($server, $username, $password, $bd);
mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET time_zone = '-05:00'");
```

### 5.2 Tabla `users` (Usuarios del Sistema)

```sql
CREATE TABLE `users` (
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `username`        int(11) NOT NULL,              -- PK funcional (cedula)
  `password`        varchar(255) NOT NULL,          -- Hash de contraseña
  `nombre`          mediumtext NOT NULL,            -- Nombre completo
  `rol`             int(2) NOT NULL,                -- ID del rol
  `rol_informativo` int(11) DEFAULT NULL,           -- Rol informativo adicional
  `extra_rol`       int(2) NOT NULL DEFAULT 0,      -- Permisos extra
  `foto`            varchar(255) NOT NULL,          -- Ruta de foto
  `orden`           int(11) NOT NULL,               -- Orden de visualizacion
  `fechaCreacionUser` varchar(15) NOT NULL,         -- Fecha de creacion
  `email`           varchar(255) NOT NULL DEFAULT '',
  `genero`          mediumtext NOT NULL DEFAULT '',
  `telefono`        varchar(20) NOT NULL DEFAULT '',
  `direccion`       varchar(255) NOT NULL DEFAULT '',
  `edad`            int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`username`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 5.3 Tabla `user_register` (Campistas Registrados)

Tabla principal de registros de campistas. Es la mas extensa del sistema con 50+ columnas:

| Campo | Tipo | Descripcion |
| --- | --- | --- |
| `id` | int(11) PK | Auto-increment |
| `typeID` | varchar(30) | Tipo de documento (CC, TI, CE) |
| `number_id` | bigint(20) UN | Numero de identificacion |
| `first_name` | varchar(50) | Primer nombre |
| `second_name` | varchar(50) | Segundo nombre |
| `first_last` | varchar(50) | Primer apellido |
| `second_last` | varchar(50) | Segundo apellido |
| `birthdate` | date | Fecha de nacimiento |
| `gender` | mediumtext | Genero |
| `email` | varchar(255) | Correo personal |
| `first_phone` | varchar(15) | Telefono principal |
| `department` | varchar(50) | Departamento |
| `municipality` | varchar(50) | Municipio |
| `headquarters` | varchar(255) | Sede seleccionada |
| `program` | varchar(50) | Programa de interes |
| `mode` | varchar(50) | Modalidad (Presencial/Virtual) |
| `schedules` | varchar(255) | Horario preferido |
| `status` | int(1) | Estado del formulario |
| `statusAdmin` | int(11) DEFAULT 0 | Estado administrativo (0=inicial, 7=inactivo, 11=no valido, 12=no aprobado) |
| `payment_number` | int(11) | Numero de pago asignado |
| `directed_base` | int(11) | Tipo de base (0=normal, 1=adicionales) |
| `lote` | int(11) | Lote de procesamiento (1 = Lote 1, 2 = Lote 2). Es el campo clave que separa los dos convenios independientes gestionados por el sistema. |
| `idCourse` | int(11) | Curso asignado |
| `contactMedium` | mediumtext | Medio de contacto |
| `institution` | varchar(255) | Institucion de referencia |
| `file_front_id` | varchar(255) | Ruta del documento frontal |
| `file_back_id` | varchar(255) | Ruta del documento posterior |
| `creationDate` | datetime | Fecha de registro |
| `dayUpdate` | datetime | Ultima actualizacion |

Indices relevantes:
```sql
KEY `idx_user_register_lote_number_headquarters` (`lote`, `number_id`, `headquarters`)
KEY `idx_user_register_institution` (`institution`)
```

### 5.4 Tabla `courses` (Cursos Registrados)

```sql
CREATE TABLE `courses` (
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `code`            int(11) NOT NULL,            -- ID del curso en Moodle
  `name`            varchar(100) NOT NULL,        -- Nombre del curso
  `real_hours`      int(3) NOT NULL,             -- Horas reales totales
  `teacher`         int(11) NOT NULL,            -- Username del profesor
  `mentor`          int(11) NOT NULL,            -- Username del mentor
  `monitor`         int(11) NOT NULL,            -- Username del monitor
  `status`          varchar(20) NOT NULL,         -- 0=Inactivo, 1=Activo
  `cohort`          int(11) NOT NULL DEFAULT 0,  -- Numero de cohorte
  `start_date`      date NOT NULL,               -- Fecha de inicio
  `end_date`        date NOT NULL,               -- Fecha de finalizacion
  `monday_hours`    int(2) NOT NULL,             -- Horas lunes (0-8)
  `tuesday_hours`   int(2) NOT NULL,
  `wednesday_hours` int(2) NOT NULL,
  `thursday_hours`  int(2) NOT NULL,
  `friday_hours`    int(2) NOT NULL,
  `saturday_hours`  int(2) NOT NULL,
  `sunday_hours`    int(2) NOT NULL,
  `notes_limit`     date DEFAULT NULL,           -- Fecha limite de notas
  `creation_date`   datetime DEFAULT current_timestamp(),
  `update_date`     datetime DEFAULT current_timestamp() ON UPDATE,
  `user_update`     varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_courses_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 5.5 Tablas de Asistencia y Seguimiento

**`attendance_records`** - Registro individual de asistencias por clase:
```sql
CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id`      int(11) NOT NULL,       -- ID del profesor que registro
  `student_id`      varchar(255) NOT NULL,  -- Documento del estudiante
  `course_id`       int(11) NOT NULL,       -- ID del curso en Moodle
  `modality`        varchar(50) NOT NULL,   -- Modalidad (Presencial/Virtual)
  `sede`            varchar(50) NOT NULL,   -- Sede
  `class_date`      date NOT NULL,          -- Fecha de la clase
  `recorded_hours`  int(2) NOT NULL,        -- Horas registradas
  `attendance_status` varchar(20) NOT NULL, -- Estado: present, absent, late
  `created_at`      timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`student_id`,`course_id`,`modality`,`sede`,`class_date`),
  KEY `idx_attendance_student_course_date_status` (`student_id`,`course_id`,`class_date`,`attendance_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**`class_observations`** - Observaciones por clase:
```sql
CREATE TABLE `class_observations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id`       varchar(255) NOT NULL,
  `course_id`        int(11) NOT NULL,
  `class_date`       date NOT NULL,
  `observation_type` varchar(50) NOT NULL,
  `observation_text` text DEFAULT NULL,
  `created_by`       varchar(100) DEFAULT NULL,
  `created_at`       timestamp DEFAULT current_timestamp(),
  `updated_at`       timestamp DEFAULT current_timestamp() ON UPDATE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_observation` (`student_id`,`course_id`,`class_date`),
  KEY `idx_course_date` (`course_id`,`class_date`),
  KEY `idx_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**`student_attendance_management`** - Gestion de intervencion:
```sql
CREATE TABLE `student_attendance_management` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id`                  varchar(255) NOT NULL,
  `course_id`                   int(11) NOT NULL,
  `requires_intervention`       varchar(2) DEFAULT NULL,    -- Si/No
  `responsible_username`        varchar(100) DEFAULT NULL,
  `intervention_observation`    text DEFAULT NULL,
  `is_resolved`                 varchar(2) DEFAULT NULL,    -- Si/No
  `requires_additional_strategy` varchar(2) DEFAULT NULL,   -- Si/No
  `strategy_observation`        text DEFAULT NULL,
  `strategy_fulfilled`          varchar(2) DEFAULT NULL,    -- Si/No
  `withdrawal_reason`           varchar(255) DEFAULT NULL,
  `withdrawal_date`             date DEFAULT NULL,
  `created_at`                  timestamp DEFAULT current_timestamp(),
  `updated_at`                  timestamp DEFAULT current_timestamp() ON UPDATE,
  PRIMARY KEY (`id`),
  KEY `idx_student_course` (`student_id`,`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 5.6 Tablas de Correo

**`email_history`** - Historial de envios:
```sql
CREATE TABLE `email_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject`          varchar(255) NOT NULL,
  `content`          mediumtext NOT NULL,       -- HTML del correo
  `recipients_count` int(11) NOT NULL,          -- Total destinatarios
  `successful_count` int(11) NOT NULL,          -- Envios exitosos
  `failed_count`     int(11) NOT NULL,          -- Envios fallidos
  `sent_by`          varchar(100) NOT NULL,     -- Usuario remitente
  `sent_from`        varchar(20) NOT NULL,      -- Origen: 'float' o 'mass'
  `created_at`       timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**`email_templates`** - Plantillas guardadas:
```sql
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name`       varchar(100) NOT NULL,
  `subject`    varchar(255) NOT NULL,
  `content`    mediumtext NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 5.7 Tablas de PQRS, Mentorias y QR

**`pqr`** - Peticiones, Quejas, Reclamos, Sugerencias:
```sql
CREATE TABLE `pqr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Peticion','Queja','Reclamo','Sugerencia') NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `cedula` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono1` varchar(20) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,       -- 1 = nuevo/pendiente
  `respuesta` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_resolucion` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,     -- Username del admin que respondio
  `numero_radicado` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**`qr_mentorias`** - Codigos QR de mentorias:
```sql
CREATE TABLE `qr_mentorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `date` datetime DEFAULT NULL,
  `url` text NOT NULL,
  `image_filename` varchar(255) NOT NULL,
  `clases_equivalentes` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `authorized` tinyint(1) DEFAULT 0,     -- 0=pendiente, 1=autorizada
  `authorized_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
```

### 5.8 Tablas Complementarias

| Tabla | Proposito | Campos Clave |
| --- | --- | --- |
| `advisors` | Registro de asesores | `idAdvisor` (documento), `email` (UNIQUE), `role` (enum) |
| `contact_log` | Bitacora de contactos a campistas | `idAdvisor`, `number_id`, `contact_established`, `details` |
| `groups` | Campistas matriculados en Moodle | `number_id`, `id_bootcamp`, `id_leveling_english`, `id_english_code`, `id_skills`, `cohort` |
| `enrollment_history` | Historico de desmatriculas | `number_id`, `unenrollment_date`, `unenrolled_by` |
| `course_assignments` | Pre-asignaciones de cursos | `student_id`, `bootcamp_id`, `leveling_english_id`, `english_code_id`, `skills_id` |
| `course_periods` | Periodos de bootcamps | `cohort`, `start_date`, `end_date`, `bootcamp_code`, `leveling_english_code` |
| `course_grade_config` | Configuracion de porcentajes de notas | `course_code`, `percentage1`, `percentage2`, `percentage3` |
| `student_grades` | Notas de estudiantes | `student_number_id`, `course_code` (UNIQUE), `grade1`, `grade2`, `grade3`, `final_grade` |
| `certificates` | Certificados emitidos | `number_id`, `link` (ruta PDF) |
| `certificados_emitidos` | Diplomas emitidos | `number_id`, `serie_certificado` (UNIQUE), `emitido_por` |
| `cedulas_pdf` | PDFs de cedulas | `number_id` (UNIQUE), `pdf_path` |
| `document_verification` | Resultados de verificacion OCR | `number_id`, `overall_match_percentage` |
| `employability_close` | Encuestas de cierre de empleabilidad | `number_id`, `current_employment_status`, `income_level` |
| `headquarters` | Catalogo de sedes | `name`, `mode` (Presencial/Virtual) |
| `headquarters_attendance` | Sedes habilitadas para asistencia | `name`, `mode` |
| `schedules` | Horarios de cursos | `schedule`, `program`, `headquarters`, `department` |
| `change_history` | Historial de cambios en campistas | `student_id`, `user_change`, `change_made`, `date` |
| `company` | Datos de la empresa/institucion | `nombre`, `nit`, `logo`, `email`, `ciudad` |
| `smtpConfig` | Configuracion de correo SMTP | `host`, `email`, `password`, `port`, `Subject` |
| `departamentos` | Catalogo de departamentos | `departamento` |
| `municipios` | Catalogo de municipios | `municipio`, `departamento_id` (FK) |
| `cohorts` | Cohortes configuradas | `cohort_number`, `start_date`, `finish_date`, `state` |
| `estados` | Catalogo de estados | `nombre` |
| `formularios` | Catalogo de formularios/test | `formulario` |
| `user_register` | Formularios de registro de campistas | 50+ columnas con datos demograficos y academicos |
| `users_administrative` | Registro de personal administrativo | Similar a user_register pero para administrativos |
| `executor_headquarters` | Relacion usuario-sede | `username`, `headquarter` |
| `team_assignments` | Asignaciones de equipo a cursos | `code`, `teacher`, `mentor`, `monitor` |
| `teachers`, `mentors`, `monitors` | Registro de equipo por curso | `number_id`, `name`, `course_id` |
| `carnet_records` | Registro de carnets generados | `number_id`, `file_path`, `generated_by` |
| `useres_cohort_one` | Datos de cohortes anteriores | `number_id`, `course`, `certification` |
| `asistencias_masterclass` | Asistencia a masterclass | `number_id`, `code` (QR), `fecha` |
| `asistencias_mentorias` | Asistencia a mentorias | `number_id`, `code` (QR), `fecha` |
| `asistencia_empleabilidad` | Asistencia a talleres de empleabilidad | `full_name`, `cedula`, `activity_type` |
| `encuestas_laborales` | Encuestas laborales | `cedula`, `situacionLaboral`, `rangoSalarial` |
| `notas_estudiantes` | Notas por estudiante | `number_id` (UNIQUE), `code`, `nota1`, `nota2` |
| `plantillas_correos` | Plantillas de correo (legacy) | `nombre`, `asunto`, `mensaje` |
| `historial_correos` | Historial de correos (legacy) | `destinatario`, `asunto`, `estado` |
| `qr_codes` | Codigos QR genericos | `title`, `url`, `image_filename` |
| `qr_masterclass` | QR de masterclass | `title`, `url`, `clases_equivalentes` |
| `participantes` | Participantes en formularios | `numero_documento` (UNIQUE) |
| `preguntas` / `opciones` / `respuestas` | Sistema de evaluaciones/test | FK: `id_formulario`, `id_pregunta` |
| `usuarios` | Usuarios de formularios | `cedula`, `id_formulario`, `nivel` |
| `user_program_topics` | Temas de programa por usuario | `number_id`, `program`, `topic` |

---

## 6. Integracion con Moodle

### 6.1 Configuracion de la API

El sistema se comunica con Moodle a traves de su API REST (Web Service). La configuracion se encuentra embebida en los archivos que la utilizan:

```php
// Configuracion en componentes como trackingTable.php
$api_url = "https://<dominio-moodle>/webservice/rest/server.php";
$token   = "<token-de-webservice-moodle>";
$format  = "json";
```

### 6.2 Funciones de Moodle Utilizadas

| Web Service | Uso en SYGNIA |
| --- | --- |
| `core_course_get_courses` | Obtener lista de cursos disponibles para matricula y seguimiento |
| `core_enrol_get_users_courses` | Obtener cursos de un usuario especifico |
| `enrol_manual_enrol_users` | Matricular campistas en cursos |
| `enrol_manual_unenrol_users` | Desmatricular campistas de cursos |
| `core_user_get_users` | Obtener informacion de usuarios de Moodle |

### 6.3 Filtrado de Cursos

Solo se muestran cursos cuyas categorias en Moodle corresponden a las permitidas:

| Categoria Moodle | Tipo de Curso |
| --- | --- |
| 14, 11, 10, 7, 6, 5 | Cursos Tecnicos |
| 4 | Ingles Nivelatorio |
| 12 | English Code |
| 13 | Habilidades de Poder |

Ademas, solo se muestran cursos cuyo `code` (id de Moodle) existe en la tabla local `courses`.

### 6.4 Patron de Llamada a la API

```php
function getCourses() {
    global $api_url, $token, $format;
    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_course_get_courses',
        'moodlewsrestformat' => $format
    ];
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
```

---

## 7. Control de Acceso y Roles

### 7.1 Verificacion de Sesion

Cada pagina del sistema inicia con:

```php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
```

### 7.2 Variables de Rol Disponibles

En cada pagina que incluye `components/filters/takeUser.php`, estan disponibles:

```php
$infoUsuario = obtenerInformacionUsuario();
$rol      = $infoUsuario['rol'];       // Ej: "Administrador"
$extraRol = $infoUsuario['extra_rol']; // Ej: "Extra Administrador" o ""
```

### 7.3 Patron de Control de Acceso en Componentes

Cada modulo (tarjeta) en los paneles laterales verifica el rol antes de renderizarse:

```php
<?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
    <!-- HTML del modulo -->
<?php endif; ?>
```

Algunos modulos requieren `$extraRol === 'Extra Administrador'` ademas del rol principal.

---

## 8. Modulos: Funcionamiento Interno

### 8.1 Dashboard (main.php)

**Incluye:** `contadoresCards.php` > `page1.php` + `page2.php`

**Mecanismo de actualizacion de contadores:**
- Todos los contadores se cargan via AJAX desde `components/cardContadores/actualizarContadores.php`
- El endpoint retorna un JSON con todos los datos necesarios (totales, porcentajes, distribuciones, generos, instituciones)
- La funcion `window.actualizarContadoresManual()` se expone globalmente para ser llamada desde el boton "Actualizar"
- Las graficas se actualizan mediante funciones globales: `window.actualizarGraficoMatriculadosVsFormados()` y `window.actualizarGraficoCertificadosVsFormados()`

**Graficas incluidas:**
- `registerDeparments.php` - ECharts donut (fetch a `registerDeparmentsQuery.php`)
- `registerVsEnrolled.php` - ECharts donut (fetch a `registeVsEnrollerQuery.php`)
- `enrolledVsGraduated.php` - ECharts donut (fetch a `actualizarContadores.php`)
- `certificadosVsFormados.php` - ECharts donut (fetch a `certificadosSobreFormados.php`)
- `ageRanges.php` - ECharts pie (fetch a `ageRangesQuery.php`)

### 8.2 Barra de Navegacion (controller/header.php)

**Incluye:**
- `components/sliderBarRight.php` - Panel derecho
- `components/multipleEmail/float_email.php` - Ventana de correo
- `components/pqr/pqrButton.php` (condicional) - Boton PQRS
- `components/bootcampPeriods/periods_button.php` (condicional) - Boton periodos
- `components/qrMentorias/mentoriaButton.php` (condicional) - Boton mentorias
- `components/classrooms/classroom_button.php` (condicional) - Boton aulas
- `components/studentsReports/reportsButton.php` (condicional) - Boton reportes
- `components/modals/cohortes.php` - Modal de cohortes

**Estructura de menus de informes (doble lote):**
El sistema gestiona dos lotes de convenio independientes (**Lote 1** y **Lote 2**). La barra de navegacion refleja esta separacion mediante tres menus desplegables:

1. **"Informes Lote 1"** (`#navbarDropdownInformesLote1`): Informes especificos para el Lote 1
   - `exportAll.php` - Informe semanal Lote 1
   - `exportAll_non_registered.php` - Contrapartida Lote 1
   - `export_E_29.php` - Formato E29 Lote 1 - Formados
   - `export_E_29_specific.php` - E29 especifico L1 (via `abrirSwalInformeE29()`)
   - `semanal_especificoL1.php` - Semanal especifico L1 (via `abrirSwalSemanalEspecificoL1()`)
   - Control maestro exclusivo: `export_E20.php`, `export_E_21.php`, `export_E_19_VF.php`, `export_E_19_VF_contra.php`

2. **"Informes Lote 2"** (`#navbarDropdownInformesLote2`): Informes especificos para el Lote 2
   - `exportAll_lote2.php` - Informe semanal Lote 2
   - `exportAll_non_registered_l2.php` - Contrapartida Lote 2
   - `export_E29_L2.php` - Formato E29 Lote 2 - Formados
   - `export_E29_specific_L2.php` - E29 especifico L2 (via `abrirSwalInformeE29_L2()`)
   - `semanal_especificoL2.php` - Semanal especifico L2 (via `abrirSwalSemanalEspecificoL2()`)
   - Control maestro exclusivo: `export_E20_L2.php`, `export_E_21_L2.php`, `export_E_19_VF_L2.php`, `export_E_19_VF_contra_l2.php`

3. **"Otros"** (`#navbarDropdownOtros`): Informes generales sin distincion de lote
   - `export_to_excel.php` - Inscritos general
   - `export_to_excel_Inst.php` - Inscritos SenaTICS (Extra Administrador/Control maestro)
   - `proyecciones.php` - Proyecciones
   - `metasDePagos.php` - Metas y pagos
   - `generar_zip_cedulas.php` - ZIP de cedulas (via `abrirSwalCedulas()`)
   - `semanal_todos.php` - Informe mensual TODOS
   - `exportHoursEL.php` - Notas y asistencia
   - `exportAbsence.php` - Registros de ausencia
   - `export_observations.php` - Observaciones
   - `asistenciasComprobantes.php` - Comprobantes presencial
   - `upload_informe.php` - Subir informe semanal (via modal `#modalSubirInforme`)

4. **"Cambio multiple"** (`#navbarDropdownCambioMultiple`): Menu exclusivo de Control maestro
   - `update_directed_base.php` - Cambiar base (via `abrirSwalDocumentos()`)
   - `pasar_no_aprobado.php` - Cambio masivo No validos/Inactivos (via `abrirSwalNoAprobado()`)
   - `noAprobadosMasivo.php` - Marcar como No Aprobados (via `abrirSwalNoAprobadosMasivo()`)

**Botones de notificacion (actualizacion en tiempo real):**
- PQRS: polling cada 1 segundo via `pqrButton.php?action=count`
- Periodos: polling cada 10 minutos via `periods_button.php?action=count`
- Mentorias: polling cada 5 segundos via `mentoriaButton.php?action=count`

**Funciones JavaScript globales definidas en header.php:**
- `descargarInforme(url, tipo)` - Descarga de informes con contador de 5 minutos
- `abrirSwalDocumentos()` - Cambio de base dirigida (POST a `update_directed_base.php`)
- `abrirSwalCedulas()` - Generacion de ZIP de cedulas (POST a `generar_zip_cedulas.php`)
- `abrirSwalNoAprobado()` - Cambio masivo de estado (POST a `pasar_no_aprobado.php`)
- `abrirSwalNoAprobadosMasivo()` - Marcar no aprobados (POST a `noAprobadosMasivo.php`)
- `abrirSwalInformeE29()` - Informe E29 especifico L1 (POST a `export_E_29_specific.php`)
- `abrirSwalInformeE29_L2()` - Informe E29 especifico L2 (POST a `export_E29_specific_L2.php`)
- `abrirSwalSemanalEspecificoL1()` - Informe semanal especifico L1
- `abrirSwalSemanalEspecificoL2()` - Informe semanal especifico L2
- `descargarInformeE29Especifico(documentos)` - Descarga E29 especifico L1
- `descargarInformeE29EspecificoL2(documentos)` - Descarga E29 especifico L2
- `descargarSemanalEspecificoL1(documentos)` - Descarga semanal especifico L1
- `descargarSemanalEspecificoL2(documentos)` - Descarga semanal especifico L2
- `generarFacturaMentorias()` - Factura PDF de mentorias (POST a `factura_mentorias.php`)

### 8.3 Registro de Cursos (Modal)

**Archivos involucrados:**
- `components/modals/register_course.php` - Modal HTML + JS
- `components/modals/get_courses.php` - Endpoint: lista de cursos de Moodle
- `components/modals/get_users.php` - Endpoint: lista de usuarios del sistema
- `components/modals/check_course_assignments.php` - Verificar asignaciones previas
- `components/modals/save_course.php` - Guardar nuevo curso

**Flujo de datos:**
1. Al abrir el modal, `loadRegisterCourseData()` hace dos fetch en paralelo (cursos + usuarios)
2. Los selectores usan un componente personalizado que reemplaza el `<select>` nativo con buscador
3. Al seleccionar un curso, se verifica via AJAX si ya tiene asignaciones (`check_course_assignments.php`)
4. Al guardar, se envia un objeto JSON con 20+ campos a `save_course.php`

### 8.4 Seguimiento de Asistencias (attendance_tracking.php)

**Archivos involucrados:**
- `components/attendance/trackingTable.php` - Tabla principal con sistema de pestañas
- `components/attendance/statisticalPanel.php` - Panel de graficas
- `components/attendance/observationsChart.php` - Grafico de observaciones
- `components/attendance/getStudents.php` - Endpoint: estudiantes por curso
- `components/attendanceGraphics/getCourseStatistics.php` - Estadisticas de cumplimiento
- `components/attendanceGraphics/getCourseAttendanceStats.php` - Asistencias por tipo
- `components/attendanceGraphics/getClassesStatistics.php` - Estadisticas por clase

**Flujo de carga:**
1. Seleccion de curso en Select2 -> `loadStudentsData(courseId, courseCode)`
2. AJAX a `getStudents.php` retorna estudiantes organizados por tipo (tecnico, ingles, english_code, habilidades) + clases
3. Las tablas se pueblan dinamicamente con columnas de clase
4. El panel estadistico carga 3 endpoints en paralelo (`Promise.all`)

### 8.5 Correo Electronico (float_email.php)

**Mecanismo de carga de dependencias:**
- El script verifica si jQuery, SweetAlert2 y Summernote ya estan cargados
- Si no, los carga dinamicamente desde CDN
- Usa un IIFE para encapsular todo el codigo

**Proceso de envio:**
- Envio secuencial (async/await) a `send_email.php`, un destinatario a la vez
- Progreso en tiempo real mostrando procesados, exitos y errores
- Al finalizar, guarda historial en `save_history.php`

### 8.6 Exportacion de Informes (Excel)

Todos los informes usan `PhpSpreadsheet` para generar archivos `.xlsx`. El patron es:

1. El frontend llama a `descargarInforme(url, tipo)` que muestra un contador de 5 minutos
2. `fetch()` con `AbortController` para timeout a los 310 segundos
3. Al recibir el blob, se descarga automaticamente con nombre `informe_{tipo}_{fecha}.xlsx`

---

## 9. APIs y Endpoints

### 9.1 Endpoints AJAX del Dashboard

| Endpoint | Metodo | Parametros | Retorno |
| --- | --- | --- | --- |
| `components/cardContadores/actualizarContadores.php` | GET | `?date=YYYY-MM-DD` (opcional) | JSON con todos los contadores |
| `components/graphics/registerDeparmentsQuery.php` | GET | `?json=1` | JSON `{labels, data}` |
| `components/graphics/registeVsEnrollerQuery.php` | GET | `?json=1` | JSON `{labels, data}` |
| `components/graphics/certificadosSobreFormados.php` | GET | - | JSON `{total_formados, total_certificados}` |
| `components/graphics/ageRangesQuery.php` | GET | `?json=1` | JSON `{labels, data}` |

### 9.2 Endpoints de Usuarios y Asesores

| Endpoint | Metodo | Datos enviados |
| --- | --- | --- |
| `components/modals/processUser.php` | POST | FormData (nombre, usuario, password, rol, foto) |
| `components/modals/newAdvisor.php` | POST | Form tradicional (identificacion, name, phone, email, role, notes) |
| `components/modals/get_courses.php` | GET | - |
| `components/modals/get_users.php` | GET | - |
| `components/modals/check_course_assignments.php` | GET | `?courseCode=X` |
| `components/modals/save_course.php` | POST | JSON con datos del curso |

### 9.3 Endpoints de Asistencia

| Endpoint | Metodo | Parametros |
| --- | --- | --- |
| `components/attendance/getStudents.php` | POST | `courseId`, `courseCode` |
| `components/attendance/getAttendanceStats.php` | POST | `student_id`, `course_id` |
| `components/attendance/getAttendanceManagement.php` | POST | `student_id`, `course_id` |
| `components/attendance/saveObservation.php` | POST | FormData del modal de observacion |
| `components/attendance/saveAttendanceManagement.php` | POST | FormData del formulario de gestion |
| `components/attendance/exportAttendance.php` | POST | `courseCode`, `data` (JSON), `classes` (JSON) |
| `components/attendance/exportAttendanceSimple.php` | POST | `courseCode`, `data` (JSON), `classes` (JSON) |
| `components/attendanceGraphics/getCourseStatistics.php` | POST | `courseCode` |
| `components/attendanceGraphics/getCourseAttendanceStats.php` | POST | `courseCode` |
| `components/attendanceGraphics/getClassesStatistics.php` | POST | `courseCode`, `courseType` |

### 9.4 Endpoints de Correo

| Endpoint | Metodo | Parametros |
| --- | --- | --- |
| `components/multipleEmail/send_email.php` | POST | `email`, `name`, `subject`, `content` |
| `components/multipleEmail/save_history.php` | POST | `subject`, `content`, `recipients_count`, `successful_count`, `failed_count`, `sent_from`, `recipients` (JSON), `errors` (JSON) |
| `components/multipleEmail/email_templates.php` | POST | `action` (save/list/load), `name`, `subject`, `content`, `id` |
| `components/multipleEmail/get_user_by_numberid.php` | GET | `?number_id=X` |

### 9.5 Endpoints de Notificaciones

| Endpoint | Metodo | Parametros | Retorno |
| --- | --- | --- | --- |
| `components/pqr/pqrButton.php` | GET | `?action=count` | JSON `{count}` |
| `components/pqr/pqrButton.php` | GET | `?action=list` | JSON array de PQRs |
| `components/bootcampPeriods/periods_button.php` | GET | `?action=count` | JSON `{count}` |
| `components/bootcampPeriods/periods_button.php` | GET | `?action=list` | JSON array de grupos sin periodo |
| `components/qrMentorias/mentoriaButton.php` | GET | `?action=count` | JSON `{count}` |
| `components/qrMentorias/mentoriaButton.php` | GET | `?action=list` | JSON array de QR pendientes |

### 9.6 Endpoints de Acciones Masivas

| Endpoint | Metodo | Body |
| --- | --- | --- |
| `controller/update_directed_base.php` | POST | JSON `{documentos: [], baseValue: "0" |
| `controller/update_payment_number.php` | POST | JSON `{documentos: [], paymentNumber: "1"-"6"}` |
| `controller/pasar_no_aprobado.php` | POST | JSON `{documentos: [], estado_final: int}` |
| `controller/noAprobadosMasivo.php` | POST | JSON `{documentos: []}` |
| `controller/generar_zip_cedulas.php` | POST | JSON `{number_ids: []}` |
| `components/qrMentorias/factura_mentorias.php` | POST | JSON `{month: "YYYY-MM"}` |

### 9.7 Endpoints de Exportacion (Informes)

Estos endpoints son llamados via `descargarInforme()` desde el frontend. Retornan un blob (archivo Excel) para descarga directa. Todos usan `?action=export` como parametro GET. Los informes estan separados por lote (**Lote 1** y **Lote 2**) para gestion independiente.

**Informes Lote 1:**

| Endpoint |
| --- |
| `components/infoWeek/exportAll.php` |
| `components/infoWeek/exportAll_non_registered.php` |
| `components/infoWeek/export_E_29.php` |
| `components/infoWeek/export_E_29_specific.php` |
| `components/infoWeek/semanal_especificoL1.php` |
| `components/infoWeek/export_E20.php` |
| `components/infoWeek/export_E_21.php` |
| `components/infoWeek/export_E_19_VF.php` |
| `components/infoWeek/export_E_19_VF_contra.php` |

**Informes Lote 2:**

| Endpoint |
| --- |
| `components/infoWeek/exportAll_lote2.php` |
| `components/infoWeek/exportAll_non_registered_l2.php` |
| `components/infoWeek/export_E29_L2.php` |
| `components/infoWeek/export_E29_specific_L2.php` |
| `components/infoWeek/semanal_especificoL2.php` |
| `components/infoWeek/export_E20_L2.php` |
| `components/infoWeek/export_E_21_L2.php` |
| `components/infoWeek/export_E_19_VF_L2.php` |
| `components/infoWeek/export_E_19_VF_contra_l2.php` |

**Informes generales (sin distincion de lote):**

| Endpoint |
| --- |
| `components/registrationsContact/export_to_excel.php` |
| `components/registrationsContact/export_to_excel_Inst.php` |
| `components/infoWeek/semanal_todos.php` |
| `components/infoWeek/exportHoursEL.php` |
| `components/infoWeek/exportAbsence.php` |
| `components/infoWeek/export_observations.php` |
| `components/infoWeek/asistenciasComprobantes.php` |
| `components/infoWeek/exportAll_from_file.php` |
| `components/infoWeek/upload_informe.php` |

---

## 10. Librerias y Dependencias

### 10.1 Dependencias NPM (Frontend)

```json
{
  "dependencies": {
    "bootstrap": "^5.3.3",
    "bootstrap-icons": "^1.11.3",
    "sortablejs": "^1.15.6"
  }
}
```

### 10.2 Dependencias Composer (Backend PHP)

```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^3.9",
        "dompdf/dompdf": "^3.1",
        "thiagoalessio/tesseract_ocr": "^2.13",
        "intervention/image": "^3.11"
    }
}
```

### 10.3 CDNs Utilizados

| Libreria | URL CDN |
| --- | --- |
| jQuery 3.6.0 | `https://code.jquery.com/jquery-3.6.0.min.js` |
| Bootstrap 5.3.3 CSS/JS | `https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/...` |
| DataTables 1.13.6 | `https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/...` |
| SweetAlert2 11.7.0 | `https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/...` |
| Chart.js | `https://cdn.jsdelivr.net/npm/chart.js` |
| ECharts | `https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js` |
| Summernote 0.8.18 | `https://cdn.jsdelivr.net/npm/summernote@0.8.18/...` |
| Font Awesome 6.0.0 | `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/...` |
| Bootstrap Icons 1.11.3 | `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/...` |
| Animate.css 4.1.1 | `https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/...` |
| Select2 4.1.0 | `https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/...` |

### 10.4 Comandos de Instalacion

```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias frontend
npm install

# Instalar Tesseract OCR en el sistema (Ubuntu)
sudo apt install tesseract-ocr

# Restaurar base de datos (si es necesario)
mysql -u root -p dashboard < uploads/dashboard_bd.sql
```

---

* Documento elaborado por Eagle Software - SYGNIA. Version 1.0 - Manual Tecnico.*
