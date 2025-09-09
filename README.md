# UTINNOVA-DASHBOARD

## Descripción general

Este proyecto es un sistema de gestión y seguimiento académico para bootcamps, diseñado para la plataforma UT INNOVA. Permite la administración de usuarios, cursos, asistencias, generación de reportes y constancias, entre otras funcionalidades.

## Resumen de archivos en la raíz del proyecto UTINNOVA-DASHBOARD

- **main.php**  
  Punto de entrada principal del dashboard. Controla la sesión, verifica el login y carga los componentes visuales y funcionales. Muestra alertas de perfil incompleto y la estructura base de la página.

- **login.php**  
  Página de inicio de sesión. Valida credenciales, gestiona la sesión y permite el acceso al sistema.

- **index.php**  
  Redirecciona al login si el usuario no está autenticado. Incluye cabecera y scripts base.

- **close.php**  
  Cierra la sesión del usuario y lo redirige al login.

- **profile.php**  
  Página de perfil del usuario. Permite actualizar datos personales y la contraseña.

- **editUsers.php**  
  Gestión y edición de usuarios registrados en el sistema.

- **editCourses.php**  
  Administración y edición de cursos asignados.

- **editTest.php**  
  Gestión y edición de puntajes de formularios.

- **attendance.php**  
  Registro y gestión de asistencia de usuarios.

- **attendanceGroup.php**  
  Toma de asistencia por grupos.

- **individualAttendance.php**  
  Actualización de asistencia individual.

- **attendance_tracking.php**  
  Seguimiento detallado de asistencias.

- **asistencias.php**  
  Gestión avanzada de asistencias y detalles.

- **asistenciaNivelacion.php**  
  Registro de asistencias a masterclass y nivelaciones.

- **studentsToApprove.php**  
  Listado de estudiantes pendientes por aprobación.

- **verifiedUsers.php**  
  Listado de usuarios verificados y aceptados.

- **updateDocument.php**  
  Actualización de fotos de cédula de usuarios.

- **studentContacts.php**  
  Historial de contactos realizados a un estudiante.

- **contactLogs.php**  
  Registro general de contactos realizados.

- **registrarionsContact.php**  
  Matriz general de inscripciones y contactos.

- **registration_traking.php**  
  Seguimiento de campistas y su proceso de registro.

- **preRegistrations.php**  
  Encuestas y gestión de pre-registros de empleabilidad.

- **entryAndClosing.php**  
  Encuestas de entrada y cierre de empleabilidad.

- **constanciasFinalizacion.php**  
  Generación de constancias de finalización de cursos.

- **generateCertFormat.php**  
  Generación de modelos de certificación.

- **credentials.php**  
  Generación y descarga de carnets para usuarios.

- **executor_credentials.php**  
  Generación y descarga de carnets para ejecutores.

- **codigosQR.php**  
  Generación de códigos QR para usuarios y eventos.

- **bootcamp_period.php**  
  Administración de periodos de los bootcamps.

- **course_assignments.php**  
  Asignación de cursos a estudiantes.

- **registerMoodle.php**  
  Registro de usuarios en la plataforma Moodle.

- **multipleMoodle.php**  
  Registro múltiple de usuarios en Moodle.

- **multiple_erase.php**  
  Desmatriculación múltiple de usuarios en Moodle.

- **changeMoodle.php**  
  Cambio de bootcamp para usuarios en Moodle.

- **moodleAssignments.php**  
  Asignación múltiple de cursos en Moodle.

- **moodle_team.php**  
  Asignación de profesores a cursos de Moodle.

- **projectionTable.php**  
  Tabla dinámica para proyecciones y análisis.

- **schedules.php**  
  Administración de horarios de cursos y sedes.

- **schedulesPreRegis.php**  
  Horarios de sedes para pre-registro.

- **headquarters.php**  
  Administración de sedes.

- **headquartersRegistrations.php**  
  Administración de sedes con pre-registros.

- **headquartersAttendance.php**  
  Administración de sedes con campistas activos.

- **seguimiento_pqr.php**  
  Administración y seguimiento de PQRS.

- **studentReport.php**  
  Reporte de incidencias o novedades de estudiantes.

- **gestionarReportes.php**  
  Gestión de reportes sobre campistas.

- **changePassword.php**  
  Cambio de contraseña para campistas.

- **change_history.php**  
  Historial de cambios realizados en el sistema.

- **encuestas.php**  
  Gestión y administración de encuestas.

- **pruebaCurso.php**  
  Ejemplo de consulta de información de cursos en Moodle.

- **asignarDocentes.php**  
  Asignación de docentes a grupos.

- **asignarMentores.php**  
  Asignación de mentores a grupos.

- **asignarMonitores.php**  
  Asignación de monitores a grupos.

- **tutoriales.php**  
  Guía y tutoriales de uso de la plataforma.

- **cron_obtener_notas.php**  
  Script para obtención automática de notas desde Moodle (usado por cron).

- **conexion.php**  
  Archivo de configuración y conexión a la base de datos.

- **funciones.php**  
  Funciones auxiliares y utilitarias para el sistema.

- **README.md**  
  Documentación principal del proyecto.

- **composer.json / composer.lock**  
  Archivos de dependencias PHP (librerías externas).

- **package.json / package-lock.json**  
  Archivos de dependencias JavaScript (Bootstrap, icons, etc).
- **main.php**: Archivo principal del dashboard. Controla la sesión, verifica el acceso y carga los componentes visuales y funcionales.

- **controller/header.php**: Barra superior y navegación.
- **components/sliderBar.php**: Menú lateral de navegación.
- **components/cardContadores/contadoresCards.php**: Tarjetas con contadores de inscritos, cursos y otros indicadores.
- **components/modals/userNew.php** y **components/modals/newAdvisor.php**: Modales para gestión de usuarios y asesores.

## Seguridad

- Todos los archivos principales verifican la sesión del usuario antes de mostrar contenido.
- Si el usuario no está logueado, se redirige automáticamente a la página de inicio de sesión.

## Funcionalidad destacada

- **Alerta de perfil incompleto**: Si el usuario tiene campos obligatorios sin completar, se muestra una alerta y se redirige a la página de perfil.
- **Animación de carga**: Se utiliza una animación visual para indicar el procesamiento de datos en el dashboard.
- **Integración con DataTables y Bootstrap**: Para una visualización moderna y responsiva de los datos.

## components/sliderBar.php

Este archivo implementa la barra lateral de navegación principal del dashboard. Permite el acceso rápido a las funciones clave del sistema, como gestión de usuarios, registros, certificados, seguimiento y reportes.  
Las opciones que aparecen están condicionadas por el rol del usuario, garantizando que cada perfil solo vea las funcionalidades que le corresponden.

**Características:**
- Diseño responsivo con Bootstrap Offcanvas.
- Cada botón incluye un icono, etiqueta y popover descriptivo.
- Acceso a modales y páginas según permisos.
- Créditos de desarrollo al pie de la barra.

**Roles y visibilidad:**
- Los roles como Administrador, Asesor, Académico, Monitor, etc., determinan qué opciones se muestran.
- El código PHP controla la visibilidad de cada bloque de opción.

**Personalización:**
- El estilo y la estructura permiten una experiencia de usuario clara y rápida.
- Se pueden agregar nuevas opciones fácilmente siguiendo el patrón de visibilidad por rol.

## components/sliderBarRight.php

Este archivo implementa la barra lateral derecha de opciones avanzadas del dashboard. Permite el acceso rápido a funciones administrativas como envío de correos masivos, asignación y matrícula de cursos, generación de carnets, administración de sedes y horarios, gestión de asistencias y reportes, entre otras.

**Características:**
- Diseño responsivo con Bootstrap Offcanvas.
- Cada botón incluye un icono, etiqueta y popover descriptivo.
- Acceso a páginas y acciones según permisos.
- Créditos de desarrollo al pie de la barra.

**Roles y visibilidad:**
- Los roles como Administrador, Académico, Control maestro, Monitor, Asesor, Permanencia, etc., determinan qué opciones se muestran.
- El código PHP controla la visibilidad de cada bloque de opción y permite personalizar el acceso según el perfil del usuario.

**Personalización:**
- El estilo y la estructura permiten una experiencia de usuario clara y rápida.
- Se pueden agregar nuevas opciones fácilmente siguiendo el patrón de visibilidad por rol y extra_rol.

## components/sliderBarBotton.php

Este archivo implementa la barra inferior de gestión de matriculados y asistencia en el dashboard. Permite el acceso rápido a funciones como control de asistencia individual y grupal, matrícula múltiple, edición de cursos, usuarios, horarios, puntajes y más.

**Características:**
- Diseño horizontal y responsivo con Bootstrap Offcanvas.
- Cada botón incluye un icono, etiqueta y popover descriptivo.
- Acceso a páginas y acciones según permisos y rol.
- Pensado para facilitar la gestión rápida desde la parte inferior de la interfaz.

**Roles y visibilidad:**
- Los roles como Administrador, Académico, Docente, Control maestro, Mentor, Monitor, Asesor, Supervisor, Permanencia, etc., determinan qué opciones se muestran.
- El código PHP controla la visibilidad de cada bloque de opción y permite personalizar el acceso según el perfil del usuario.

**Personalización:**
- El estilo y la estructura permiten una experiencia de usuario clara y rápida.
- Se pueden agregar nuevas opciones fácilmente siguiendo el patrón de visibilidad por rol y extra_rol.

## controller/header.php

Este archivo implementa la barra superior y navegación principal del dashboard.  
Incluye el logo, menú principal, accesos rápidos, perfil del usuario y botones flotantes.  
Las opciones del menú y los accesos dependen del rol del usuario logueado, mostrando solo las funcionalidades permitidas para cada perfil.

**Características:**
- Barra superior fija y responsiva con Bootstrap.
- Menú principal con opciones dinámicas según el rol.
- Acceso rápido a informes, PQRS, periodos, aulas y perfil.
- Botón flotante para redacción de correos.
- Integración con barra lateral derecha y componentes modales.
- Descarga de informes con control de tiempo y feedback visual.

**Roles y visibilidad:**
- Los roles como Administrador, Control maestro, Empleabilidad, Permanencia, Académico, etc., determinan qué opciones se muestran.
- El código PHP controla la visibilidad de cada bloque de opción y menú.

**Personalización:**
- El diseño y la estructura permiten una experiencia de usuario clara y rápida.
- Se pueden agregar nuevas opciones y accesos fácilmente siguiendo el patrón de visibilidad por rol.

## activeMoodle.php

Este archivo gestiona el listado y administración de usuarios activos en la plataforma Moodle.  
Permite visualizar, filtrar y realizar acciones sobre las matrículas activas de los usuarios, integrando componentes para agregar usuarios, asesores y gestionar matrículas.

**Características:**
- Verificación de sesión y rol antes de mostrar la información.
- Filtros por bootcamp, sede, programa y modalidad.
- Visualización dinámica y responsiva con DataTables.
- Integración de componentes para agregar usuarios y asesores.
- Acceso a funcionalidades avanzadas según el rol del usuario.

**Personalización:**
- El diseño y la estructura permiten una experiencia de usuario clara y rápida.
- Se pueden agregar nuevas acciones y filtros fácilmente.

## Componentes relacionados con activeMoodle.php

El módulo **activeMoodle.php** utiliza varios componentes para la gestión avanzada de usuarios activos en Moodle.  
A continuación se describen los principales archivos del directorio `components/activeMoodle`:

- **listActiveMoodle.php**  
  Muestra el listado principal de usuarios activos en Moodle.  
  Permite filtrar por sede, programa, modalidad y bootcamp.  
  Incluye opciones para exportar a Excel y para desmatricular usuarios individualmente, con confirmación por código de seguridad.

- **deleteMatricula.php**  
  Procesa la eliminación de la matrícula de un usuario en Moodle y en la base de datos local.  
  Realiza la eliminación vía API de Moodle, guarda el historial de matrícula y actualiza el estado del usuario.  
  Soporta tanto eliminación individual como masiva (usando el flag `isMultiple`).

- **multipleEraseMoodle.php**  
  Permite seleccionar múltiples usuarios para desmatriculación masiva.  
  Muestra una tabla con checkboxes, panel lateral de usuarios seleccionados y un modal de confirmación con código de seguridad.  
  Realiza la eliminación de todos los usuarios seleccionados de forma segura y muestra el progreso y resultados.

---

**Flujo de uso:**
1. El usuario accede a **activeMoodle.php** y visualiza el listado de usuarios activos (listActiveMoodle.php).
2. Puede filtrar, buscar y seleccionar usuarios para desmatriculación individual o múltiple.
3. Al confirmar la eliminación, se llama a **deleteMatricula.php** para procesar la baja en Moodle y en la base de datos.
4. El sistema registra el historial y muestra el resultado al usuario.


## asistenciaNivelacion.php

Este archivo permite registrar y visualizar la asistencia de usuarios a sesiones de masterclass y nivelaciones.  
Verifica la sesión y el rol antes de mostrar la información, integrando componentes para mostrar códigos QR, tarjetas de asistencia y filtros.

**Características:**
- Registro y visualización de asistencias a masterclass y nivelaciones.
- Verificación de sesión y rol antes de mostrar la información.
- Integración de componentes para códigos QR y tarjetas de asistencia.
- Visualización dinámica y responsiva con DataTables y Bootstrap.
- Facilita la gestión rápida de asistencias por parte de los responsables.

**Personalización:**
- El diseño y la estructura permiten una experiencia de usuario clara y rápida.
- Se pueden agregar nuevas acciones y filtros




