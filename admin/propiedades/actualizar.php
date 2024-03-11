<?php

    require '../../includes/funciones.php';
    $auth = estadoAutenticado();

    $auth = $_SESSION['login'];

    if(!$auth){
        header('Location: /bienesraices/index.php');
    }

    
     
    // Validad la URL por ID válido
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if(!$id){
        header('Location: /bienesraices/admin');
    }

    // Base de datos
    require '../../includes/config/database.php';
    $db = conectarDB();

    // Obtener los datos de la propiedad
    $consulta = "SELECT * FROM propiedades WHERE id = ${id}";
    $resultado = mysqli_query($db, $consulta);
    $propiedad = mysqli_fetch_assoc($resultado); /* Cuando no hay que Iterar resultado se ocupa mysqli_fetch_assoc */

    // Consultar BD para obtener vendedores
    $consulta = "SELECT * FROM vendedor"; //Consulto a la base de datos tal cual como la cree
    $resultado = mysqli_query($db, $consulta);

    // Arreglo con mensajes de errores
    $errores = [];

        $titulo = $propiedad['titulo'];
        $precio = $propiedad['precio'];
        $descripcion = $propiedad['descripcion'];
        $habitaciones = $propiedad['habitaciones'];
        $wc = $propiedad['wc'];
        $estacionamiento = $propiedad['estacionamiento'];
        $vendedor_id = $propiedad['vendedor_id'];
        $imagenPropiedad = $propiedad['imagen'];

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        /* Trae información del POST */
        //echo "<pre>";
        //var_dump($_POST);
        //echo"</pre>";

        /* Trae información de los archivos */
        //echo "<pre>";
        //var_dump($_FILES);
        //echo"</pre>";
        //exit;

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

            //Crear carpeta
            $carpetaImagenes = '../../imagenes/';

            if(!is_dir($carpetaImagenes)) {
               mkdir($carpetaImagenes);
            }

            $nombreImgen = '';

            /**SUBIDA DE ARCHIVOS */        

            if($imagen['name']){

                /* unlink es una funcion de php para eliminar archivos */
                unlink($carpetaImagenes .$propiedad['imagen']);

                // Generar un nombre único md5 genera un hash unico para nombre de img
                $nombreImgen = md5( uniqid( rand(), true ) ) . ".jpg";

                // Subir la imagen
                move_uploaded_file($imagen['tmp_name'], $carpetaImagenes . $nombreImgen);
            }else {
                $nombreImgen = $propiedad['imagen'];
            }

            
                // Actualizar información en la base de datos 
                $query = " UPDATE propiedades SET titulo = '${titulo}', precio = '${precio}', imagen = '{$nombreImgen}',descripcion = '${descripcion}',titulo = '${titulo}', habitaciones = ${habitaciones}, wc = ${wc}, estacionamiento = ${estacionamiento}, vendedor_id = ${vendedor_id} WHERE id = ${id}";

                //echo $query;

                $resultado = mysqli_query($db, $query);

                if($resultado){
                    
                    // Redirecionar al usuario, la redireción con funcion header siempre debe ser antes del html.
                    header('Location: /bienesraices/admin/?resultado=2');
                }
            }

            
        }    

    incluirTemplate('header');
?>

    <main class="contenedor seccion">
        <h1>Actualizar Propiedades</h1>

        <a href="/bienesraices/admin/" class="boton boton-verde">Volver</a>

        <br/>

        <!-- Imprimiendos los errores -->
        <?php foreach($errores as $error):?>
            <div class="alerta error">
                <?php echo $error; ?>
            </div>   
        <?php endforeach; ?>    


           <!--  Formulario -->
        <form class="formulario" method="POST" enctype="multipart/form-data"> <!-- Si no pones action envia al mismo archivo -->
            <fieldset>
                <legend>Información General</legend>

                <label for="titulo">Titulo:</label>
                <input type="text" id="titulo" name="titulo" placeholder="Titulo Propiedad" value="<?php echo $titulo; ?>">

                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" placeholder="Precio Propiedad" value="<?php echo $precio; ?>">

                <label for="imagen">Imagen:</label>
                <input type="file" id="imagen" accept="image/jpeg, image/png" name="imagen"> <!-- accept/jpeg es para determinar el tipo de archivo aceptar -->

                <img src="/bienesraices/imagenes/<?php echo $imagenPropiedad; ?>" class="imagen-small">

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
            <input type="submit" value="Actualizar Propiedad" class="boton boton-verde">

        </form>

    </main>

    <br/>
    
<?php
    incluirTemplate('footer');
?>