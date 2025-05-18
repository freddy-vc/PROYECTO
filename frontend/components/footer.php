        </div><!-- Cierre del container de main-content -->
    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="<?php 
                    if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                        echo "../assets/images/logo.png";
                    } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                        echo "./assets/images/logo.png";
                    } else {
                        echo "./frontend/assets/images/logo.png";
                    }
                    ?>" alt="Logo Futsala Villavicencio">
                    <span>Futsala Villavicencio</span>
                </div>
                
                <div class="footer-links">
                    <ul>
                        <li><a href="<?php 
                        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                            echo "../../index.php";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                            echo "../index.php";
                        } else {
                            echo "./index.php";
                        }
                        ?>">Inicio</a></li>
                        <li><a href="<?php 
                        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                            echo "./clasificaciones.php";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                            echo "./pages/clasificaciones.php";
                        } else {
                            echo "./frontend/pages/clasificaciones.php";
                        }
                        ?>">Clasificaciones</a></li>
                        <li><a href="<?php 
                        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                            echo "./partidos.php";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                            echo "./pages/partidos.php";
                        } else {
                            echo "./frontend/pages/partidos.php";
                        }
                        ?>">Partidos</a></li>
                        <li><a href="<?php 
                        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                            echo "./equipos.php";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                            echo "./pages/equipos.php";
                        } else {
                            echo "./frontend/pages/equipos.php";
                        }
                        ?>">Equipos</a></li>
                        <li><a href="<?php 
                        if (strpos($_SERVER['PHP_SELF'], '/frontend/pages/') !== false) {
                            echo "./jugadores.php";
                        } else if (strpos($_SERVER['PHP_SELF'], '/frontend/') !== false) {
                            echo "./pages/jugadores.php";
                        } else {
                            echo "./frontend/pages/jugadores.php";
                        }
                        ?>">Jugadores</a></li>
                    </ul>
                </div>
                
                <div class="footer-info">
                    <p>&copy; <?php echo date('Y'); ?> Campeonato de Futsala Villavicencio</p>
                    <p>Todos los derechos reservados</p>
                    <p>Proyecto académico para curso de Bases de Datos - Ingeniería de Sistemas</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 