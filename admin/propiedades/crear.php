<?php

/* No se pueden tener dos require en mismo archivo */
    require '../../includes/funciones.php';
    $auth = estadoAutenticado();

    $auth = $_SESSION['login'];

    if(!$auth){
        header('Location: /bienesraices/index.php');
    }
  

    // Base de datos

    require '../../includes/config/database.php';
    $db = conectarDB();

    // Consultar BD para obtener vendedores
    $consulta = "SELECT * FROM vendedor"; //Consulto a la base de datos tal cual como la cree
    $resultado = mysqli_query($db, $consulta);

    // Arreglo con mensajes de errores
    $errores = [];

        $titulo = '';
        $precio = '';
        $descripcion = '';
        $habitaciones = '';
        $wc = '';
        $estacionamiento = '';
        $vendedor_id = '';

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        /* Trae información del POST */
        //echo "<pre>";
        //var_dump($_POST);
        //echo"</pre>";

        /* Trae información de los archivos */
        //echo "<pre>";
        //var_dump($_FILES);
        //echo"</pre>";

      /*   mysqli_real_escape_string es una función para que no te realicen una inyección de sql */
        $titulo = mysqli_real_escape_string( $db, $_POST['titulo']);
        $precio = mysqli_real_escape_string( $db,  $_POST['precio'] );
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $habitaciones = mysqli_real_escape_string($db, $_POST['habitaciones']);
        $wc = mysqli_real_escape_string($db, $_POST['wc']);
        $estacionamiento = mysqli_real_escape_string($db, $_POST['estacionamiento']);
        $vendedor_id = mysqli_real_escape_string($db, $_POST['vendedor']);
        $creado = date('Y/m/d');

        // Asignar files hacia una variable
        $imagen = $_FILES['imagen'];

        // Validando que los campos del formulario esten vacios
        if(!$titulo){
            $errores[] = "Debes añadir un Titulo";
        }

        if(!$precio){
            $errores[] = 'El Precio es Obligatorio';
        }

        if ( strlen($descripcion) <50 ){
            $errores[] = 'La descripción es Obligatoria y debe tener al menos 50 caracteres';
        }

        if(!$habitaciones){
            $errores[] = 'El numero de Habitaciones es Obligatorio';
        }
        
        if(!$wc){
            $errores[] = 'El numero de Baños es Obligatorio';
        }
        
        if(!$estacionamiento){
            $errores[] = 'El numero de lugares de Estacionamientos es Obligatorio';
        }
        
        if(!$vendedor_id){
            $errores[] = 'Elige un Vendedor';
        }
                    /* || hace referencia a decir O  */
        if(!$imagen['name'] || $imagen['error']){
            $errores[] = 'La Imagen es Obligatoria';
        }

        // Validar por tamaño de imagen(1 mb máximo)
        $medida = 1000 * 1000;

        if($imagen['size'] > $medida ){
            $errores[] = 'La Imagen excede el peso recomendado';
        }

        //echo "<pre>";
        //var_dump($errores);
        //echo"</pre>";

        // Revisar que le array de errores este vacio
        if(empty($errores)){
            /**SUBIDA DE ARCHIVOS */        

            //Crear carpeta
            $carpetaImagenes = '../../imagenes/';

            if(!is_dir($carpetaImagenes)) {
                mkdir($carpetaImagenes);
            }

            // Generar un nombre único md5 genera un hash unico para nombre de img
            $nombreImgen = md5( uniqid( rand(), true ) ) . ".jpg";
            // Subir la imagen
            move_uploaded_file($imagen['tmp_name'], $carpetaImagenes . $nombreImgen);


                // Insertar información en la base de datos 
                $query = "INSERT INTO propiedades (titulo, precio, imagen, descripcion, habitaciones, wc, estacionamiento, creado, vendedor_id)
                VALUES ( '$titulo','$precio','$nombreImgen','$descripcion','$habitaciones','$wc','$estacionamiento','$creado','$vendedor_id')";

                //echo $query;

                $resultado = mysqli_query($db, $query);

                if($resultado){
                    
                    // Redirecionar al usuario, la redireción con funcion header siempre debe ser antes del html.
                    header('Location: /bienesraices/admin/?resultado=1');
                }
            }

            
        }    


    incluirTemplate('header');
?>

    <main class="contenedor seccion">
        <h1>Crear</h1>

        <a href="/bienesraices/admin/" class="boton boton-verde">Volver</a>

        <br/>

        <!-- Imprimiendos los errores -->
        <?php foreach($errores as $error):?>
            <div class="alerta error">
                <?php echo $error; ?>
            </div>   
        <?php endforeach; ?>    

           <!--  Formulario -->
        <form action="/bienesraices/admin/propiedades/crear.php" class="formulario" method="POST" enctype="multipart/form-data">
            <fieldset>
                <legend>Información General</legend>

                <label for="titulo">Titulo:</label>
                <input type="text" id="titulo" name="titulo" placeholder="Titulo Propiedad" value="<?php echo $titulo; ?>">

                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" placeholder="Precio Propiedad" value="<?php echo $precio; ?>>

                <label for="imagen">Imagen:</label>
                <input type="file" id="imagen" accept="image/jpeg, image/png" name="imagen"> <!-- accept/jpeg es para determinar el tipo de archivo aceptar -->

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion"><?php echo $descripcion;?></textarea>

            </fieldset>
                <br/>
            <fieldset>
                <legend>Información Propiedad</legend>

                <label for="habitaciones">Habitaciones:</label>
                <input type="number" id="habitaciones" name="habitaciones" placeholder="Ej:3" min="1" max="9" value="<?php echo $habitaciones; ?>">

                <label for="wc">Baños:</label>
                <input type="number" id="wc" name="wc" placeholder="Ej:2" min="1" max="9" value="<?php echo $wc; ?>">

                <label for="estacionamiento">Estacionamientos:</label>
                <input type="number" id="estacionamiento" name="estacionamiento" placeholder="Ej:3" min="1" max="9" value="<?php echo $estacionamiento; ?>">

            </fieldset>

            <fieldset>
                <legend>Vendedor</legend>

                <select name="vendedor">
                    <option value="">-- Seleccione --</option>
                    <?php while($vendedor = mysqli_fetch_assoc($resultado)): ?>
                        <option <?php echo $vendedor_id === $vendedor['id'] ? 'selected':''; ?>  value="<?php echo $vendedor['id'];  ?>"><?php echo $vendedor['nombre']." ". $vendedor['apellido']; ?></option>
                    <?php endwhile; ?>    
                </select>
            </fieldset>
                <br/>
            <!-- btn de envio -->
            <input type="submit" value="Crear Propiedad" class="boton boton-verde">

        </form>

    </main>

    <br/>
    
<?php
    incluirTemplate('footer');
?>