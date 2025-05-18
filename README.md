# Proyecto Campeonato de Futsala - Villavicencio

Aplicación web para la gestión de un campeonato de futsala en la ciudad de Villavicencio.

## Descripción
Este proyecto permite gestionar toda la información relacionada con un campeonato de futsala, incluyendo equipos, jugadores, encuentros, estadísticas y más.

## Estructura de Directorios

### Frontend

- **frontend/assets/css**: Archivos CSS para estilos de la aplicación.
- **frontend/assets/js**: Scripts JavaScript para funcionalidad del lado del cliente.
- **frontend/assets/images**: Imágenes, logos y recursos gráficos para la aplicación.
- **frontend/components**: Componentes reutilizables como cabeceras, pies de página, navegación, etc.
- **frontend/pages**: Páginas principales de la aplicación (acceso público).
- **frontend/admin**: Páginas de administración (acceso restringido).

### Backend

- **backend/database**: Archivos SQL y script de conexión a la base de datos.
  - `futsala.sql`: Script para crear la estructura de la base de datos.
  - `connection.php`: Archivo para gestionar la conexión con PostgreSQL.
- **backend/models**: Clases de objetos que representan las entidades de la base de datos (Equipos, Jugadores, etc.).
- **backend/controllers**: Controladores que manejan la lógica de negocio.
- **backend/includes**: Archivos de utilidades, funciones comunes y configuraciones.
- **backend/api**: Endpoints para comunicación entre frontend y backend.

## Tecnologías Utilizadas

- **Backend**: PHP (orientado a objetos)
- **Frontend**: HTML, CSS, JavaScript nativo
- **Base de Datos**: PostgreSQL