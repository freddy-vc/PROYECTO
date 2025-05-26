# Proyecto Campeonato de Futsala - Villavicencio

Aplicación web para la gestión de un campeonato de futsala en la ciudad de Villavicencio.

## Descripción
Este proyecto permite gestionar toda la información relacionada con un campeonato de futsala, incluyendo equipos, jugadores, encuentros, estadísticas y más. La aplicación facilita la administración del torneo, el seguimiento de partidos y la visualización de estadísticas.

## Estructura del Proyecto

### Frontend

- **frontend/assets**: Recursos estáticos de la aplicación
  - **css/**: Archivos CSS para estilos de la aplicación
  - **js/**: Scripts JavaScript para funcionalidad del lado del cliente
  - **images/**: Imágenes, logos y recursos gráficos
  
- **frontend/components**: Componentes reutilizables
  - **header.php**: Cabecera común para todas las páginas
  - **footer.php**: Pie de página común
  - **nav.php**: Barra de navegación
  - **admin_styles.php**: Estilos específicos para el panel de administración
  - **notificaciones.php**: Sistema de notificaciones para el usuario

- **frontend/pages**: Páginas públicas de la aplicación
  - **clasificaciones.php**: Muestra la tabla de clasificación del torneo
  - **detalle-equipo.php**: Información detallada de un equipo
  - **detalle-jugador.php**: Perfil completo de un jugador
  - **detalle-partido.php**: Información detallada de un partido
  - **equipos.php**: Listado de equipos participantes
  - **jugadores.php**: Listado de jugadores
  - **login.php**: Formulario de inicio de sesión
  - **partidos.php**: Calendario de partidos
  - **perfil.php**: Perfil del usuario
  - **registro.php**: Formulario de registro

- **frontend/pages/admin**: Panel de administración
  - **index.php**: Dashboard principal de administración
  - **canchas.php**: Gestión de canchas (CRUD)
  - **canchas_form.php**: Formulario para crear/editar canchas
  - **ciudades.php**: Gestión de ciudades (CRUD)
  - **ciudades_form.php**: Formulario para crear/editar ciudades
  - **directores.php**: Gestión de directores técnicos (CRUD)
  - **directores_form.php**: Formulario para crear/editar directores
  - **equipos.php**: Gestión de equipos (CRUD)
  - **equipos_form.php**: Formulario para crear/editar equipos
  - **jugadores.php**: Gestión de jugadores (CRUD)
  - **jugadores_form.php**: Formulario para crear/editar jugadores
  - **partidos.php**: Gestión de partidos (CRUD)
  - **partidos_form.php**: Formulario para crear/editar partidos
  - **usuarios.php**: Gestión de usuarios (CRUD)
  - **usuarios_form.php**: Formulario para crear/editar usuarios

### Backend

- **backend/database**: Gestión de base de datos
  - **futsala.sql**: Script para crear la estructura de la base de datos
  - **trigger.sql**: Triggers para automatizar el avance del torneo
  - **connection.php**: Archivo para gestionar la conexión con PostgreSQL

- **backend/models**: Modelos de datos (POO)
  - **Cancha.php**: Gestión de canchas donde se juegan los partidos
  - **Ciudad.php**: Gestión de ciudades de los equipos
  - **Clasificacion.php**: Cálculo y gestión de la tabla de clasificación
  - **Director.php**: Gestión de directores técnicos
  - **Equipo.php**: Gestión de equipos participantes
  - **Jugador.php**: Gestión de jugadores y sus estadísticas
  - **Partido.php**: Gestión de partidos, resultados y estadísticas
  - **Usuario.php**: Gestión de usuarios del sistema

- **backend/controllers**: Controladores de la aplicación
  - **actualizar_foto.php**: Maneja la subida y actualización de imágenes
  - **clasificaciones_controller.php**: Gestiona la tabla de clasificación
  - **eliminar_foto.php**: Elimina fotos de perfiles, jugadores o equipos
  - **equipos_controller.php**: Gestiona operaciones CRUD para equipos
  - **inicio_controller.php**: Controla la página de inicio
  - **jugadores_controller.php**: Gestiona operaciones CRUD para jugadores
  - **login.php**: Controla la autenticación de usuarios
  - **logout.php**: Gestiona el cierre de sesión
  - **partidos_controller.php**: Gestiona operaciones CRUD para partidos
  - **register.php**: Controla el registro de nuevos usuarios

## Funcionalidades Principales

1. **Gestión de Equipos**: Crear, editar y eliminar equipos, asignar directores técnicos y ciudades.
2. **Gestión de Jugadores**: Administrar jugadores, asignarlos a equipos y registrar sus estadísticas.
3. **Gestión de Partidos**: Programar partidos, registrar resultados y estadísticas (goles, asistencias, faltas).
4. **Sistema de Brackets**: Estructura automática del torneo con avance de equipos ganadores.
5. **Estadísticas**: Visualización de estadísticas individuales y por equipo.
6. **Panel de Administración**: Interfaz para administradores con control total sobre el sistema.
7. **Perfiles de Usuario**: Registro e inicio de sesión para usuarios con diferentes niveles de acceso.

## Base de Datos

El sistema utiliza PostgreSQL con las siguientes tablas principales:
- **Ciudades**: Almacena las ciudades de los equipos participantes
- **Directores**: Información de los directores técnicos
- **Equipos**: Datos de los equipos, incluyendo ciudad, director y escudo
- **Jugadores**: Información de los jugadores, incluyendo posición, dorsal y equipo
- **Canchas**: Datos de las canchas donde se juegan los partidos
- **Partidos**: Registro de partidos, con equipos local y visitante, fecha, hora y estado
- **Brackets**: Estructura del torneo con fases (cuartos, semis, final)
- **Goles**: Registro de goles por partido, jugador y minuto
- **Asistencias**: Registro de asistencias por partido, jugador y minuto
- **Faltas**: Registro de faltas por partido, jugador y tipo
- **Usuarios**: Gestión de usuarios del sistema

## Características Técnicas

- **Arquitectura MVC**: Separación clara entre Modelos, Vistas y Controladores
- **POO en PHP**: Uso de programación orientada a objetos
- **Triggers en PostgreSQL**: Automatización del avance del torneo
- **Validación de Datos**: Prevención de inyección SQL y XSS
- **Manejo de Sesiones**: Control de acceso y autenticación
- **Responsive Design**: Interfaz adaptable a diferentes dispositivos

## Solución a Problemas Comunes

### Manejo de Imágenes en PostgreSQL
- Las imágenes se almacenan como BYTEA en la base de datos
- Se implementó validación de formatos (JPG, PNG, GIF)
- Tamaño máximo permitido: 2MB
- Detección automática del tipo MIME

### Formularios de Actualización
- Los formularios utilizan IDs consistentes entre HTML y JavaScript
- Se implementó validación tanto en cliente como en servidor
- Se agregó prevención de ataques XSS con htmlspecialchars()

### Modales y Elementos Interactivos
- Los modales de confirmación utilizan posicionamiento fijo y z-index adecuado
- Se implementaron estilos CSS para garantizar visibilidad correcta
- Se eliminaron estilos inline que causaban conflictos

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+ (orientado a objetos)
- **Frontend**: HTML5, CSS3, JavaScript ES6
- **Base de Datos**: PostgreSQL 12+
- **Librerías**: CSS personalizado, FontAwesome (iconos)