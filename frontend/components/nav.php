<nav class="main-nav">
    <ul>
        <li><a href="<?php 
        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
            echo "../../../index.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
            echo "../../index.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
            echo "../index.php";
        } else {
            echo "./index.php";
        }
        ?>" class="<?php echo $pagina_actual == 'inicio' ? 'active' : ''; ?>">Inicio</a></li>
        <li><a href="<?php 
        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
            echo "../clasificaciones.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
            echo "./clasificaciones.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
            echo "./pages/clasificaciones.php";
        } else {
            echo "./frontend/pages/clasificaciones.php";
        }
        ?>" class="<?php echo $pagina_actual == 'clasificaciones' ? 'active' : ''; ?>">Clasificaciones</a></li>
        <li><a href="<?php 
        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
            echo "../partidos.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
            echo "./partidos.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
            echo "./pages/partidos.php";
        } else {
            echo "./frontend/pages/partidos.php";
        }
        ?>" class="<?php echo $pagina_actual == 'partidos' ? 'active' : ''; ?>">Partidos</a></li>
        <li><a href="<?php 
        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
            echo "../equipos.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
            echo "./equipos.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
            echo "./pages/equipos.php";
        } else {
            echo "./frontend/pages/equipos.php";
        }
        ?>" class="<?php echo $pagina_actual == 'equipos' ? 'active' : ''; ?>">Equipos</a></li>
        <li><a href="<?php 
        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/admin/') !== false) {
            echo "../jugadores.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
            echo "./jugadores.php";
        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
            echo "./pages/jugadores.php";
        } else {
            echo "./frontend/pages/jugadores.php";
        }
        ?>" class="<?php echo $pagina_actual == 'jugadores' ? 'active' : ''; ?>">Jugadores</a></li>
    </ul>
</nav> 