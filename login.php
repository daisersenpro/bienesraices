<?php
    // Requiero la conexión mediando require y luego la instancio
    require 'includes/config/database.php';
    $db = conectarDB();

    // Autenticar el usuario

    $errores = [];

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        // echo "<pre>";
        // var_dump($_POST);
        // echo "</pre>";

        /* filter_var + FILTER_VALIDATE_EMAIL son para validar que mail sea valido */
        $email = mysqli_real_escape_string($db, filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)); 

        $password = mysqli_real_escape_string($db, $_POST['password']);

        if(!$email) {
            $errores[] = "El Email es obligatorio o no es válido";
        }

        if(!$password) {
            $errores[] = "El Password es obligatorio";
        }

        if(empty($errores)) {

            // Revisar si el usuario existe.
            $query = "SELECT * FROM usuarios WHERE email = '${email}'";
            $resultado = mysqli_query($db, $query);

            //var_dump($resultado);

            // num_rows sirve para ver que existan resultado en una consulta
            if( $resultado->num_rows ) {

                // Revisar si el password es correcto
                $usuario = mysqli_fetch_assoc($resultado);

                // Verifica si el password es correcto o no con password_verify
                $auth = password_verify($password, $usuario['password']);

                if($auth) {
                    // El usuario esta autenticado
                    session_start();

                    // Llenar el arreglo de la sesión
                    $_SESSION['usuario'] = $usuario['email'];
                    $_SESSION['login'] = true;

                    header('Location: /bienesraices/admin');

                }else {
                    $errores[] = 'El password es incorrecto';
                }

            } else {
                $errores[] = "El Usuario no existe";
            } 
        }
    }

    // Incluye el header
    require 'includes/funciones.php';
    incluirTemplate('header');
?>

    <main class="contenedor seccion contenido-centrado">
        <h1>Iniciar Sesión</h1>

   <!--      Alerta de errores en caso que no lleve los datos requeridos -->
        <?php foreach($errores as $error): ?>
            <div class="alerta error">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>    

        <form method="POST" class="formulario"> 
            <fieldset>
                <legend>Email & Password</legend>

                <label for="email">E-mail</label>
                <input type="email" name="email" placeholder="Tu Email" id="email" required>

                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Tu Password" id="password" required>
            </fieldset>
                <br/>
            <input type="submit" value="Iniciar Sesión" class="boton boton-verde">
            
        </form>

    </main>

    <br/>

<?php
    incluirTemplate('footer');
?>