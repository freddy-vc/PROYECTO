<nav class="main-nav">
    <ul>
        <li><a href="<?php echo $ruta_raiz; ?>index.php" class="<?php echo $pagina_actual == 'inicio' ? 'active' : ''; ?>">Inicio</a></li>
        <li><a href="<?php echo $ruta_raiz; ?>frontend/pages/clasificaciones.php" class="<?php echo $pagina_actual == 'clasificaciones' ? 'active' : ''; ?>">Clasificaciones</a></li>
        <li><a href="<?php echo $ruta_raiz; ?>frontend/pages/partidos.php" class="<?php echo $pagina_actual == 'partidos' ? 'active' : ''; ?>">Partidos</a></li>
        <li><a href="<?php echo $ruta_raiz; ?>frontend/pages/equipos.php" class="<?php echo $pagina_actual == 'equipos' ? 'active' : ''; ?>">Equipos</a></li>
        <li><a href="<?php echo $ruta_raiz; ?>frontend/pages/jugadores.php" class="<?php echo $pagina_actual == 'jugadores' ? 'active' : ''; ?>">Jugadores</a></li>
    </ul>
</nav> 