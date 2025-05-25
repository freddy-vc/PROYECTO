<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Redireccionar a la página de inicio si no es administrador
    header('Location: ../../../index.php');
    exit;
}

// Incluir el modelo de Usuario
require_once __DIR__ . '/../../models/Usuario.php';

// Verificar que se ha enviado una acción
if (!isset($_REQUEST['accion'])) {
    $_SESSION['error_usuarios'] = 'No se especificó ninguna acción';
    header('Location: ../../../frontend/pages/admin/usuarios.php');
    exit;
}

// Procesar la acción solicitada
$accion = $_REQUEST['accion'];

switch ($accion) {
    case 'crear':
        crearUsuario();
        break;
    
    case 'actualizar':
        actualizarUsuario();
        break;
    
    case 'eliminar':
        eliminarUsuario();
        break;
    
    case 'listar':
        listarUsuarios();
        break;
    
    default:
        $_SESSION['error_usuarios'] = 'Acción no reconocida';
        header('Location: ../../../frontend/pages/admin/usuarios.php');
        break;
}

/**
 * Función para crear un nuevo usuario
 */
function crearUsuario() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || 
        !isset($_POST['confirm_password']) || !isset($_POST['rol'])) {
        $_SESSION['error_usuarios'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $rol = trim($_POST['rol']);
    
    // Validar los datos
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_usuarios'] = 'Todos los campos marcados con * son obligatorios';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php');
        exit;
    }
    
    // Verificar que las contraseñas coinciden
    if ($password !== $confirm_password) {
        $_SESSION['error_usuarios'] = 'Las contraseñas no coinciden';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php');
        exit;
    }
    
    // Validar el formato del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_usuarios'] = 'El formato del email no es válido';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php');
        exit;
    }
    
    // Procesar la foto de perfil si se ha enviado
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto_perfil']['type'], $allowed_types)) {
            $_SESSION['error_usuarios'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/usuarios_form.php');
            exit;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['foto_perfil']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_usuarios'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/usuarios_form.php');
            exit;
        }
        
        // Leer el contenido del archivo
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        
        // Verificar que el contenido de la imagen no esté vacío
        if (empty($foto_perfil)) {
            $_SESSION['error_usuarios'] = 'Error al procesar la imagen: contenido vacío.';
            header('Location: ../../../frontend/pages/admin/usuarios_form.php');
            exit;
        }
    }
    
    // Crear un objeto de la clase Usuario
    $usuarioModel = new Usuario();
    
    // Registrar el nuevo usuario
    $resultado = $usuarioModel->registrarAdmin($username, $email, $password, $rol, $foto_perfil);
    
    if ($resultado['estado']) {
        // Éxito al crear el usuario
        $_SESSION['exito_usuarios'] = 'Usuario creado correctamente';
        header('Location: ../../../frontend/pages/admin/usuarios.php');
        exit;
    } else {
        // Error al crear el usuario
        $_SESSION['error_usuarios'] = 'Error al crear el usuario: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/usuarios_form.php');
        exit;
    }
}

/**
 * Función para actualizar un usuario existente
 */
function actualizarUsuario() {
    // Verificar que se han enviado los datos necesarios
    if (!isset($_POST['id']) || !isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['rol'])) {
        $_SESSION['error_usuarios'] = 'Faltan datos obligatorios';
        header('Location: ../../../frontend/pages/admin/usuarios.php');
        exit;
    }
    
    // Obtener los datos del formulario
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $rol = trim($_POST['rol']);
    
    // Validar los datos
    if (empty($username) || empty($email)) {
        $_SESSION['error_usuarios'] = 'El nombre de usuario y el email son obligatorios';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
        exit;
    }
    
    // Validar el formato del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_usuarios'] = 'El formato del email no es válido';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
        exit;
    }
    
    // Verificar que las contraseñas coinciden si se ha proporcionado una nueva contraseña
    if (!empty($password) && $password !== $confirm_password) {
        $_SESSION['error_usuarios'] = 'Las contraseñas no coinciden';
        header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
        exit;
    }
    
    // Procesar la foto de perfil si se ha enviado
    $foto_perfil = null;
    $actualizar_foto = false;
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto_perfil']['type'], $allowed_types)) {
            $_SESSION['error_usuarios'] = 'El formato de la imagen no es válido. Use JPG, PNG o GIF.';
            header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
            exit;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if ($_FILES['foto_perfil']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_usuarios'] = 'La imagen es demasiado grande. El tamaño máximo es 2MB.';
            header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
            exit;
        }
        
        // Leer el contenido del archivo
        $foto_perfil = file_get_contents($_FILES['foto_perfil']['tmp_name']);
        $actualizar_foto = true;
        
        // Verificar que el contenido de la imagen no esté vacío
        if (empty($foto_perfil)) {
            $_SESSION['error_usuarios'] = 'Error al procesar la imagen: contenido vacío.';
            header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
            exit;
        }
    }
    
    // Crear un objeto de la clase Usuario
    $usuarioModel = new Usuario();
    
    // Actualizar el usuario
    $resultado = $usuarioModel->actualizarAdmin($id, $username, $email, $password, $rol, $foto_perfil, $actualizar_foto);
    
    if ($resultado['estado']) {
        // Éxito al actualizar el usuario
        $_SESSION['exito_usuarios'] = 'Usuario actualizado correctamente';
        header('Location: ../../../frontend/pages/admin/usuarios.php');
        exit;
    } else {
        // Error al actualizar el usuario
        $_SESSION['error_usuarios'] = 'Error al actualizar el usuario: ' . $resultado['mensaje'];
        header('Location: ../../../frontend/pages/admin/usuarios_form.php?id=' . $id);
        exit;
    }
}

/**
 * Función para eliminar un usuario
 */
function eliminarUsuario() {
    // Verificar que se ha enviado el ID del usuario
    if (!isset($_POST['id'])) {
        $_SESSION['error_usuarios'] = 'No se especificó el usuario a eliminar';
        header('Location: ../../../frontend/pages/admin/usuarios.php');
        exit;
    }
    
    // Obtener el ID del usuario
    $id = intval($_POST['id']);
    
    // No se puede eliminar el usuario actual
    if ($id === $_SESSION['usuario_id']) {
        $_SESSION['error_usuarios'] = 'No puedes eliminar tu propio usuario';
        header('Location: ../../../frontend/pages/admin/usuarios.php');
        exit;
    }
    
    // Crear un objeto de la clase Usuario
    $usuarioModel = new Usuario();
    
    // Eliminar el usuario
    $resultado = $usuarioModel->eliminar($id);
    
    if ($resultado['estado']) {
        // Éxito al eliminar el usuario
        $_SESSION['exito_usuarios'] = 'Usuario eliminado correctamente';
    } else {
        // Error al eliminar el usuario
        $_SESSION['error_usuarios'] = 'Error al eliminar el usuario: ' . $resultado['mensaje'];
    }
    
    // Redireccionar a la lista de usuarios
    header('Location: ../../../frontend/pages/admin/usuarios.php');
    exit;
}

/**
 * Función para listar todos los usuarios (respuesta JSON)
 */
function listarUsuarios() {
    // Crear un objeto de la clase Usuario
    $usuarioModel = new Usuario();
    
    // Obtener todos los usuarios
    $usuarios = $usuarioModel->obtenerTodos();
    
    // Devolver los usuarios en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'estado' => true,
        'usuarios' => $usuarios
    ]);
    exit;
} 