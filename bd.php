<?php
    function ingresarBD() {

        $mysqli = new mysqli("database","alvaro155w","alvaro","SIBW");
        if($mysqli->connect_errno) {
            echo("Fallo al conectar. Ocuree: " . $mysqli->connect_errno);
        }

        return $mysqli;
    }

    // Las dos siguientes funciones se usan para obtener la información que aparecerá en el grid-layout de index.html
    // Obtener TODOS los científicos de la base de datos junto a la primera imagen de su galería. Mostrar en index.html
    function obtenerCientificos($mysqli){
        $stmt = $mysqli->prepare("SELECT nombre,apellidos,ruta_imagen,ruta_cientifico,publicado FROM cientificos JOIN galeria ON cientificos.id=galeria.id_cientifico");
        $stmt->execute();
        $res = $stmt->get_result();

        $cientificos = array();

        if($res->num_rows>0){
            for($i=0; $i<mysqli_num_rows($res);$i++){   // Me quedo con la primera imagen
                $tupla = $res->fetch_assoc();
                $nombre = $tupla['nombre'];

                if (!isset($cientificos[$nombre])) {
                    $cientificos[$nombre] = $tupla;
                }
            }
        }

        $stmt->close();

        return $cientificos;
    }

    // Obtener el ID de un científico. La URL limpia será por su nombre, no por su ID
    function obtenerIdCientifico($mysqli,$nombre,$apellidos){
        $stmt = $mysqli->prepare("SELECT id FROM cientificos WHERE cientificos.nombre=? AND cientificos.apellidos=?");
        $stmt->bind_param("ss",$nombre,$apellidos);
        $stmt->execute();
        $res = $stmt->get_result();

        $idCi = mysqli_fetch_assoc($res);

        $stmt->close();

        return $idCi;
    }

    // Obtener toda la información de un científico con su ID. Puede no haber fallecido el científico
    function obtenerCientifico($mysqli,$idCi) {
    //    $res = $mysqli->query("SELECT nombre,apellidos,fNacimiento,fMuerte,ciudadNacimiento,biografia FROM cientificos WHERE id=" . $idCi);
        $stmt = $mysqli->prepare("SELECT nombre,apellidos,fNacimiento,fMuerte,ciudadNacimiento,biografia,ruta_cientifico_imprimir,publicado FROM cientificos WHERE cientificos.id=?");
        $stmt->bind_param("i",$idCi);
        $stmt->execute();
        $res = $stmt->get_result();

        $cientifico = array();

        if($res->num_rows > 0) {    // Si pudiese dar más de una fila, hacer bucle while($fila=$res->fetch_assoc())
            $cientifico = mysqli_fetch_assoc($res);

            if(is_null($cientifico['fMuerte'])){
                $cientifico['fMuerte'] = "No ha fallecido";
            }
        }

        $stmt->close();

        return $cientifico;
    }

    // Obtener la ruta de todas las imágenes de un científico. Solo se almacena la ruta por cuestiones de espacio
    function obtenerRutasImagenes($mysqli,$idCi) {
    //    $res = $mysqli->query("SELECT ruta_imagen FROM galeria WHERE id_cientifico=" . $idCi);
        $stmt = $mysqli->prepare("SELECT id_imagen,ruta_imagen,pie_foto FROM galeria WHERE galeria.id_cientifico=?");
        $stmt->bind_param("i",$idCi);
        $stmt->execute();
        $res = $stmt->get_result();

        $rutas=array();

        if($res->num_rows>0){
            for ($i=0; $i< mysqli_num_rows($res); $i++) {
                $rutas[$i] = mysqli_fetch_assoc($res);
            }
        }

        $stmt->close();

        return $rutas;
    }

    // Enlaces de cada científico
    function obtenerEnlacesCientifico($mysqli,$idCi) {
    //    $res = $mysqli->query("SELECT enlace FROM enlaces WHERE id_cientifico=" . $idCi);
        $stmt = $mysqli->prepare("SELECT id_enlace,enlace,comentario FROM enlaces WHERE enlaces.id_cientifico=?");
        $stmt->bind_param("i",$idCi);
        $stmt->execute();
        $res = $stmt->get_result();

        $enlaces=array();

        if($res->num_rows>0){
            for ($i=0; $i< mysqli_num_rows($res); $i++) {
                $enlaces[$i] = mysqli_fetch_assoc($res);
            }
        }

        $stmt->close();

        return $enlaces;
    }

    // Enlaces comunes en todas las páginas de la web
    function obtenerEnlacesPanelDerecho($mysqli){
        $stmt = $mysqli->prepare("SELECT area,enlace FROM enlaces_panel_derecho");
        $stmt->execute();
        $res = $stmt->get_result();

        $enlaces = array();

        if($res->num_rows>0){
            for($i = 0; $i<mysqli_num_rows($res); $i++) {
                $enlaces[$i] = mysqli_fetch_assoc($res);
            }
        }

        $stmt->close();

        return $enlaces;
    }

    // Para obtener los comentarios de UN científico
    function obtenerComentarios($mysqli,$idCi) {
    //    $res = $mysqli->query("SELECT nombre,fecha,comentario FROM comentarios WHERE id_cientifico=" . $idCi);
        $stmt = $mysqli->prepare("SELECT id_comentario,nombre_usuario,fecha,comentario FROM comentarios WHERE comentarios.id_cientifico=?");
        $stmt->bind_param("i",$idCi);
        $stmt->execute();
        $res = $stmt->get_result();

        $comentarios = array();

        if($res->num_rows>0){
            for ($i=0; $i< mysqli_num_rows($res); $i++) {
                $comentarios[$i] = mysqli_fetch_assoc($res);
            }
        }

        $stmt->close();

        return $comentarios;
    }

    function introducirComentario($mysqli,$idCi,$nombre,$email,$fecha,$comentario) {
    //    $mysqli->query("INSERT INTO comentarios(id_cientifico,nombre,fecha,comentarios) VALUES (" . $id_cientifico . "," . $nombre . "," . $fecha . "," . $comentario . ");");
        $stmt = $mysqli->prepare("INSERT INTO comentarios (id_cientifico, nombre_usuario, fecha, correo, comentario) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $idCi, $nombre,$fecha, $email, $comentario);
        $stmt->execute();

        $exito = 0;

        if(mysqli_affected_rows($mysqli)==1){
            $exito = 1;
        }

        $stmt->close();

        return $exito;
    }

    function registrarUsuario($mysqli,$nombre,$apell,$correo,$nick,$contra) {
        $stmt = $mysqli->prepare("INSERT INTO usuarios (nick,rol,nombre,apellidos,correo,contrasenia) VALUES (?,?,?,?,?,?)");
        $registrado="Registrado";
        $aux = password_hash($contra,PASSWORD_DEFAULT);
        $stmt->bind_param("ssssss",$nick,$registrado,$nombre,$apell,$correo,$aux);
        $stmt->execute();

        $exito = 0;

        if(mysqli_affected_rows($mysqli)==1){
            $exito=1;
        }

        $stmt->close();

        return $exito;
    }

    function iniciarSesion($mysqli,$nick,$contra){
        $stmt = $mysqli->prepare("SELECT contrasenia FROM usuarios WHERE nick=?");
        $stmt->bind_param("s",$nick);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();

        $exito = 0;

        if(isset($fila) && $fila!=NULL){
            if(password_verify($contra,$fila['contrasenia'])){
                $exito=1;
            }
        }

        $stmt->close();

        return $exito;
    }

    // Estas tres funciones se usan para desbloquear la funcionalidad especial de un usuario de la página
    function esModerador($mysqli,$nick){
        $stmt = $mysqli->prepare("SELECT rol FROM usuarios WHERE nick=? AND rol=?");
        $modera="Moderador";
        $stmt->bind_param("ss",$nick,$modera);
        $stmt->execute();
        $res = $stmt->get_result();

        $exito = 0;

        if($res->num_rows==1){
            $exito = 1;
        } 

        $stmt->close();

        return $exito;
    }

    function esGestor($mysqli,$nick){
        $stmt = $mysqli->prepare("SELECT rol FROM usuarios WHERE nick=? AND rol=?");
        $gestor="Gestor";
        $stmt->bind_param("ss",$nick,$gestor);
        $stmt->execute();
        $res = $stmt->get_result();

        $exito = 0;

        if($res->num_rows==1){
            $exito = 1;
        } 

        $stmt->close();

        return $exito;
    }

    function esSuperusuario($mysqli,$nick){
        $stmt = $mysqli->prepare("SELECT rol FROM usuarios WHERE nick=? AND rol=?");
        $super="Super";
        $stmt->bind_param("ss",$nick,$super);
        $stmt->execute();
        $res = $stmt->get_result();

        $exito = 0;

        if($res->num_rows==1){
            $exito = 1;
        } 

        $stmt->close();

        return $exito;
    }

    function obtenerDatosUsuario($mysqli,$el_nick){
        $stmt = $mysqli->prepare("SELECT nick,nombre,apellidos,correo,contrasenia FROM usuarios WHERE nick=?");
        $stmt->bind_param("s",$el_nick);
        $stmt->execute();
        $res = $stmt->get_result();

        $datos = array();

        if($res->num_rows>0){
            for ($i=0; $i< mysqli_num_rows($res); $i++) {
                $datos[$i] = mysqli_fetch_assoc($res);
            }
        }

        $stmt->close();

        return $datos;
    }

    function modificarUsuario($mysqli,$nombre,$apell,$correo,$nick,$contra) {
        // Tener referencia de qué usuario es el que pide el cambio, por si quiere cambiar todos los campos
        $aux_stmt = $mysqli->prepare("SELECT nick FROM usuarios WHERE nick=?");
        $aux_stmt->bind_param("s",$_SESSION['nick']);
        $aux_stmt->execute();
        $result = $aux_stmt->get_result();
        $previo_nick = mysqli_fetch_assoc($result);
        $stmt = $mysqli->prepare("UPDATE usuarios SET nick=?,nombre=?,apellidos=?,correo=?,contrasenia=? WHERE nick=?");
        $aux = password_hash($contra,PASSWORD_DEFAULT);
        $stmt->bind_param("ssssss",$nick,$nombre,$apell,$correo,$aux,$previo_nick['nick']);
        $stmt->execute();

        $exito=0;

        if(mysqli_affected_rows($mysqli)==1) {
            $exito=1;
        }

        $aux_stmt->close();
        $stmt->close();

        return $exito;
    }

    // A diferencia de la anterior, aquí se obtienen las fotos y enlaces de todos los científicos, asociando un científico con una lista de enlaces e imágenes

    function obtenerTodaInfoCientifico($mysqli){
        $stmt = $mysqli->prepare("SELECT id,nombre,apellidos,fNacimiento,fMuerte,ciudadNacimiento,biografia FROM cientificos");
        $stmt->execute();
        $res=$stmt->get_result();

        $informacion = array();
        $j=0;

        while($info=mysqli_fetch_assoc($res)){
            $imags=obtenerRutasImagenes($mysqli,$info['id']);
            $enls=obtenerEnlacesCientifico($mysqli,$info['id']);
            $informacion[] = $info;

            for($i=0;$i<count($imags);$i++){
                $imagen = array();
                $imagen['id_imagen'] = $imags[$i]['id_imagen'];
                $imagen['ruta_imagen'] = $imags[$i]['ruta_imagen'];
                $imagen['pie_foto'] = $imags[$i]['pie_foto'];
                $informacion[$j][7][] = $imagen;
            }

            for($i=0;$i<count($enls);$i++){
                $enlace = array();
                $enlace['id_enlace'] = $enls[$i]['id_enlace'];
                $enlace['enlace'] = $enls[$i]['enlace'];
                $enlace['comentario'] = $enls[$i]['comentario'];
                $informacion[$j][8][] = $enlace;
            }

            $j++;
        }

        $stmt->close();

        return $informacion;
    }

    // Obtener información de un científico por nombre filtrado (o parte de él)
    function filtrarPorNombre($mysqli,$nombre){
        $stmt=$mysqli->prepare("SELECT id FROM cientificos WHERE nombre LIKE ?");
        $aux = "%".$nombre."%";
        $stmt->bind_param("s",$aux);
        $stmt->execute();
        $res=$stmt->get_result();
        while($fila=mysqli_fetch_assoc($res)){
            $ids[]=$fila['id'];
        }
        $stmt->close();
        $informacion = array();

        $j=0;
        foreach ($ids as $id_cientifico) {
            $stmt = $mysqli->prepare("SELECT id,nombre,apellidos,fNacimiento,fMuerte,ciudadNacimiento,biografia FROM cientificos WHERE id=?");
            $stmt->bind_param("s",$id_cientifico);
            $stmt->execute();
            $resultado_cientifico = $stmt->get_result();
            $cient = mysqli_fetch_assoc($resultado_cientifico);
            $informacion[] = $cient;
            $stmt->close();

            $filas_galeria = obtenerRutasImagenes($mysqli,$id_cientifico);  
            $filas_enlaces = obtenerEnlacesCientifico($mysqli,$id_cientifico);
          
            for($i=0;$i<count($filas_galeria);$i++){
                $imagen = array();
                $imagen['id_imagen'] = $filas_galeria[$i]['id_imagen'];
                $imagen['ruta_imagen'] = $filas_galeria[$i]['ruta_imagen'];
                $imagen['pie_foto'] = $filas_galeria[$i]['pie_foto'];
                $informacion[$j][7][] = $imagen;
            }

            for($i=0;$i<count($filas_enlaces);$i++){
                $enlace = array();
                $enlace['id_enlace'] = $filas_enlaces[$i]['id_enlace'];
                $enlace['enlace'] = $filas_enlaces[$i]['enlace'];
                $enlace['comentario'] = $filas_enlaces[$i]['comentario'];
                $informacion[$j][8][] = $enlace;
            }

            $j++;
        }

    
       
        return isset($informacion) ? $informacion : NULL;
    }

    // Ídemo con biografía
    function filtrarPorBio($mysqli,$bio){
        $stmt=$mysqli->prepare("SELECT id FROM cientificos WHERE biografia LIKE ?");
        $aux = "%".$bio."%";
        $stmt->bind_param("s",$aux);
        $stmt->execute();
        $res=$stmt->get_result();
        while($fila=mysqli_fetch_assoc($res)){
            $ids[]=$fila['id'];
        }
        $stmt->close();
        $informacion = array();

        $j=0;
        foreach ($ids as $id_cientifico) {
            $stmt = $mysqli->prepare("SELECT id,nombre,apellidos,fNacimiento,fMuerte,ciudadNacimiento,biografia FROM cientificos WHERE id=?");
            $stmt->bind_param("s",$id_cientifico);
            $stmt->execute();
            $resultado_cientifico = $stmt->get_result();
            $cient = mysqli_fetch_assoc($resultado_cientifico);
            $informacion[] = $cient;
            $stmt->close();

            $filas_galeria = obtenerRutasImagenes($mysqli,$id_cientifico);  
            $filas_enlaces = obtenerEnlacesCientifico($mysqli,$id_cientifico);
          
            for($i=0;$i<count($filas_galeria);$i++){
                $imagen = array();
                $imagen['id_imagen'] = $filas_galeria[$i]['id_imagen'];
                $imagen['ruta_imagen'] = $filas_galeria[$i]['ruta_imagen'];
                $imagen['pie_foto'] = $filas_galeria[$i]['pie_foto'];
                $informacion[$j][7][] = $imagen;
            }

            for($i=0;$i<count($filas_enlaces);$i++){
                $enlace = array();
                $enlace['id_enlace'] = $filas_enlaces[$i]['id_enlace'];
                $enlace['enlace'] = $filas_enlaces[$i]['enlace'];
                $enlace['comentario'] = $filas_enlaces[$i]['comentario'];
                $informacion[$j][8][] = $enlace;
            }

            $j++;
        }

    
       
        return isset($informacion) ? $informacion : NULL;
    }

    // Se pueden esperar que ciertos campos estén a NULL, y esta información no aparecerá en la web
    function introducirCientifico($mysqli,$nombre,$apell,$ruta_imagen,$fNac='NULL',$fMu='NULL',$ciu='NULL',$bio='NULL',$enl='NULL',$nomb_enl='NULL',$pie='NULL',$hastag='NULL'){
        $stmt = $mysqli->prepare("INSERT INTO cientificos(nombre,apellidos,fNacimiento,fMuerte,ciudadNacimiento,biografia,ruta_cientifico,ruta_cientifico_imprimir,publicado) VALUES (?,?,?,?,?,?,?,?,?)");
        $aux1 = "cientifico-".$nombre."-".$apell;
        $aux2 = "cientifico-imprimir-".$nombre."-".$apell;
        $publicado = 0;
        $stmt->bind_param("ssssssssi",$nombre,$apell,$fNac,$fMu,$ciu,$bio,$aux1,$aux2,$publicado);
        $stmt->execute();

        if($stmt->errno){
            echo "Error en la consulta: " . $stmt->error;
            return 0;
        }
        if(mysqli_affected_rows($mysqli)==0){
            return 0;
        }
        
        $stmt->close();
        
        
        $stmt = $mysqli->prepare("SELECT id FROM cientificos WHERE nombre=? AND apellidos=?");
        $stmt->bind_param("ss",$nombre,$apell);
        $stmt->execute();
        $aux = $stmt->get_result();
        $new_id=mysqli_fetch_assoc($aux);
        $stmt->close();

        $aux_id=$new_id['id'];  

        $stmt = $mysqli->prepare("INSERT INTO galeria(id_cientifico,ruta_imagen,pie_foto) VALUES (?,?,?)");
        $stmt->bind_param("iss",$aux_id,$ruta_imagen,$pie);
        $stmt->execute();
        $stmt->close();

        if($enl!=''){
            $stmt = $mysqli->prepare("INSERT INTO enlaces(id_cientifico,enlace,comentario) VALUES (?,?,?)");       
            $stmt->bind_param("iss",$aux_id,$enl,$nomb_enl);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $mysqli->prepare("INSERT INTO hastags(id_cientifico,hastag) VALUES (?,?)"); // Hastag común a TODOS los científicos
        $hast = 'cientifico';
        $stmt->bind_param("ss",$aux_id,$hast);
        $stmt->execute();
        $stmt->close();

        if($hastag!=''){
            $stmt = $mysqli->prepare("INSERT INTO hastags(id_cientifico,hastag) VALUES(?,?)");
            $stmt->bind_param("is",$aux_id,$hastag);
            $stmt->execute();
            $stmt->close();
        }

        return 1;
    }

    function hacerModificaciones($mysqli,$id_modif,$nombre,$apell,$ciu,$fNac,$fMu,$bio,$enl,$nomb_enl,$ruta_foto,$pie,$id_foto_elimina,$id_enl_elimina,$id_pie_foto_mod,$pie_modif,$id_mod_nom_enlace,$nom_enlace,$hastag,$publicar){
        if($id_modif != ''){
            $aux1 = NULL;
            $aux2 = NULL;
            if($nombre!='' || $apell!=''){
                $stmt = $mysqli->prepare("SELECT nombre,apellidos FROM cientificos WHERE id=?");
                $stmt->bind_param("s",$id_modif);
                $stmt->execute();
                $res = $stmt->get_result();
                $fila = $res->fetch_assoc();
                $nombre_nuevo = $nombre!=NULL ? $nombre : $fila['nombre'];
                $apell_nuevo = $apell!=NULL ? $apell : $fila['apellidos'];
                $aux1 = "cientifico-".$nombre_nuevo."-".$apell_nuevo;
                $aux2 = "cientifico-imprimir-".$nombre_nuevo."-".$apell_nuevo;
                $stmt->close();
            }
            $stmt = $mysqli->prepare("UPDATE cientificos SET nombre = IFNULL(?,nombre), apellidos=IFNULL(?,apellidos), fNacimiento=IFNULL(?,fNacimiento), fMuerte=IFNULL(?,fMuerte), ciudadNacimiento=IFNULL(?,ciudadNacimiento), biografia=IFNULL(?,biografia), ruta_cientifico=IFNULL(?,ruta_cientifico), ruta_cientifico_imprimir=IFNULL(?,ruta_cientifico_imprimir), publicado=IFNULL(?,publicado) WHERE id=?");

            $stmt->bind_param("ssssssssss",$nombre,$apell,$fNac,$fMu,$ciu,$bio,$aux1,$aux2,$publicar,$id_modif);
            $stmt->execute();
        
            $stmt->close();

            if($enl != ''){
                $stmt = $mysqli->prepare("INSERT INTO enlaces(id_cientifico,enlace,comentario) VALUES (?,?,?)");
                $stmt->bind_param("sss",$id_modif,$enl,$nomb_enl);
                $stmt->execute();

                $stmt->close();
            } 
            
            if($ruta_foto != ''){
                $stmt = $mysqli->prepare("INSERT INTO galeria(id_cientifico,ruta_imagen,pie_foto) VALUES (?,?,IFNULL(?,'pie por defecto'))");
                $stmt->bind_param("sss",$id_modif,$ruta_foto,$pie);
                $stmt->execute();


                $stmt->close();
            }
        

            if($id_foto_elimina != ''){ 
                // Compruebo que el id del científico del que elimina foto es el correcto
                $stmt = $mysqli->prepare("SELECT id_cientifico FROM galeria WHERE id_imagen=?");
                $stmt->bind_param("s",$id_foto_elimina);
                $stmt->execute();
                $res = $stmt->get_result();
                if(isset($res) && $res!=NULL){
                    $aux = $res->fetch_assoc();
                    if($aux['id_cientifico']!=$id_modif)
                        return 0;
                } else {
                    return 0;
                }
                $stmt->close();

                $stmt = $mysqli->prepare("DELETE FROM galeria WHERE id_imagen=?");
                $stmt->bind_param("s",$id_foto_elimina);
                $stmt->execute();

                $stmt->close();
            }

            if($id_enl_elimina != ''){
                // Compruebo que el id del científico que elimina el enlace es el correcto
                $stmt = $mysqli->prepare("SELECT id_cientifico FROM enlaces WHERE id_enlace=?");
                $stmt->bind_param("s",$id_enl_elimina);
                $stmt->execute();
                $res = $stmt->get_result();
                if(isset($res) && $res!=NULL){
                    $aux = $res->fetch_assoc();
                    if($aux['id_cientifico']!=$id_modif)
                        return 0;
                } else {
                    return 0;
                }

                $stmt = $mysqli->prepare("DELETE FROM enlaces WHERE id_enlace=?");
                $stmt->bind_param("s",$id_enl_elimina);
                $stmt->execute();

                $stmt->close();
            }

            // A partir de aquí no compruebo pues no es tan importante modificar como eliminar
            if($id_pie_foto_mod != ''){
                $stmt = $mysqli->prepare("UPDATE galeria SET pie_foto=IFNULL(?,pie_foto) WHERE id_imagen=?");
                $stmt->bind_param("ss",$pie_modif,$id_pie_foto_mod);
                $stmt->execute();

                $stmt->close();
            }

            if($id_mod_nom_enlace != ''){
                $stmt = $mysqli->prepare("UPDATE enlaces SET comentario=IFNULL(?,comentario) WHERE id_enlace=?");
                $stmt->bind_param("ss",$nom_enlace,$id_mod_nom_enlace);
                $stmt->execute();
                $stmt->close();
            }

            if($hastag != '') {
                $stmt = $mysqli->prepare("INSERT INTO hastags(id_cientifico,hastag) VALUES (?,?)");
                $stmt->bind_param("ss",$id_modif,$hastag);
                $stmt->execute();
                $stmt->close();
            }
        }

        if(mysqli_affected_rows($mysqli)==0){   // De la manera que está programado, esta función se ejecuta únicamente si hay información para modificar
            return 0;
        }


        return 1;
    }

    function eliminarCientifico($mysqli,$id) {
        eliminarArchivosImagen($mysqli,$id);
        $stmt=$mysqli->prepare("DELETE FROM galeria WHERE id_cientifico=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();

        if(mysqli_affected_rows($mysqli)==0){   // Si el científico está en la BD, tendrá al menos una foto. Si no hay foto suya, entonces no está el científico

            return 0;
        }
        $stmt->close();
        $stmt = $mysqli->prepare("DELETE FROM enlaces WHERE id_cientifico=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();

        $stmt->close();
        $stmt = $mysqli->prepare("DELETE FROM comentarios WHERE id_cientifico=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();


        $stmt->close();
        $stmt = $mysqli->prepare("DELETE FROM hastags WHERE id_cientifico=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();


        $stmt->close();
        $stmt = $mysqli->prepare("DELETE FROM cientificos WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();

        $stmt->close();

        return 1;
    }

    // Si se elimina un científico, se eliminan sus imágenes almacenadas en /imgs
    function eliminarArchivosImagen($mysqli,$id) {
        $stmt = $mysqli->prepare("SELECT ruta_imagen FROM galeria WHERE id_cientifico=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $res=$stmt->get_result();
        $nombres = array();

        while($nombre=$res->fetch_assoc()){
            $nombres[] = $nombre['ruta_imagen'];
        }

        for($i=0; $i<count($nombres); $i++){
            $ruta = $nombres[$i];

            if(file_exists($ruta)){
                unlink($ruta);
            }
        }

        $stmt->close();
    }

    // Lista de usuarios
    function obtenerTodaInfoUsuarios($mysqli){
        $stmt = $mysqli->prepare("SELECT nick,rol FROM usuarios");
        $stmt->execute();
        $res = $stmt->get_result();
        $usuarios = array();

        if($res->num_rows>0){
            for ($i=0; $i< mysqli_num_rows($res); $i++) {
                $usuarios[$i] = mysqli_fetch_assoc($res);
            }
        }

        $stmt->close();

        return $usuarios;
    }

    // Usado para poder tener la restricción de como mínimo un superusuario en la web
    function cuantosSuper($mysqli) {
        $stmt = $mysqli->prepare("SELECT nick FROM usuarios WHERE rol='super'");
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        
        $cantidad_super = mysqli_num_rows($res);

        return $cantidad_super;
    }

    function hacerCambiosRol($mysqli,$nick,$rol,$cantidad_super){
        if($cantidad_super>1 || ($cantidad_super==1 && $_SESSION['super'] && $nick!=$_SESSION['nick'])){
            $stmt = $mysqli->prepare("UPDATE usuarios SET rol=? WHERE nick=?");
            $stmt->bind_param("ss",$rol,$nick);
            $stmt->execute();

            if(mysqli_affected_rows($mysqli)==0){
                return 0;
            }

            $stmt->close();
            if($rol!='super' && $nick==$_SESSION['nick']){
                $_SESSION['super'] = false;
            }
        } else {
            return 0;
        }

        return 1;
    }

    function obtenerComentario($mysqli,$id) {
        $stmt = $mysqli->prepare("SELECT comentario FROM comentarios WHERE id_comentario=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $aux = $res->fetch_assoc();
        
        return $aux['comentario'];
    }

    function modificarComentario($mysqli,$id,$nuevo) {
        $stmt = $mysqli->prepare("UPDATE comentarios SET comentario=? WHERE id_comentario=?");
        $stmt->bind_param("ss",$nuevo,$id);
        $stmt->execute();

        $stmt->close();

        return 1;
    }

    function eliminarComentario($mysqli,$id) {
        $stmt = $mysqli->prepare("DELETE FROM comentarios WHERE id_comentario=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();

        if(mysqli_affected_rows($mysqli)==0){
            return 0;
        }

        $stmt->close();

        return 1;
    }

    // Obtener todos los comentarios de todos los científicos
    function obtenerTodosComentarios($mysqli,$nombre=NULL,$apellidos=NULL) {
        if($nombre==NULL || $apellidos==NULL){  // Por defecto, o si uno de los campos no se escribe, se muestran todos los comentarios
            $stmt = $mysqli->prepare("SELECT nombre,apellidos,id_comentario,nombre_usuario,fecha,comentario FROM comentarios JOIN cientificos ON comentarios.id_cientifico=cientificos.id");
        } else {    // Obligo a que me den nombre y apellidos
            $stmt = $mysqli->prepare("SELECT nombre,apellidos,id_comentario,nombre_usuario,fecha,comentario FROM comentarios JOIN cientificos ON comentarios.id_cientifico=cientificos.id WHERE nombre=? AND apellidos=?");
            $stmt->bind_param("ss",$nombre,$apellidos);
        }

        $stmt->execute();
        $res=$stmt->get_result();
        $comentarios = array();

        while($fila=$res->fetch_assoc()){
            $comentarios[] = $fila;
        }

        $stmt->close();

        return $comentarios;
    }

    function obtenerHastagsCientifico($mysqli,$idCi) {  // Para mostrar por pantalla los hastag de un científico en cientifico.html
        $stmt = $mysqli->prepare("SELECT hastag FROM hastags WHERE id_cientifico=?");
        $stmt->bind_param("i",$idCi);
        $stmt->execute();
        $res = $stmt->get_result();

        $hastags=array();

        while($fila=$res->fetch_assoc()){
            $hastags[] = $fila;
        }
        $stmt->close();

        return $hastags;
    }

    // Todos los científicos en función del hastag seleccionado por el usuario. Tales científicos serán los únicos que se muestren en index.html
    function obtenerCientificosHastag($mysqli,$hastag) {    // Obtener científicos del hastag usado. Usar la función ya creada para un solo científico
        // Obtener id de los científicos con ese hastag
        $stmt = $mysqli->prepare("SELECT id_cientifico FROM hastags WHERE hastag=?");
        $stmt->bind_param("s",$hastag);
        $stmt->execute();
        $res = $stmt->get_result();

        $ids = array();

        while($id=$res->fetch_assoc()){
            $ids[] = $id['id_cientifico'];
        }

        $stmt->close();

        $cientificos = array();

        for($i=0; $i<count($ids); $i++){
            $cientificos[] = obtenerCientifico($mysqli,$ids[$i]);   // No me da la imagen del científico
            $stmt = $mysqli->prepare("SELECT ruta_imagen FROM galeria WHERE id_cientifico=?");
            $stmt->bind_param("i",$ids[$i]);
            $stmt->execute();
            $res = $stmt->get_result();
            $imagen = $res->fetch_assoc();
            $cientificos[$i]['ruta_imagen'] = $imagen['ruta_imagen'];
            $stmt->close();
            $stmt = $mysqli->prepare("SELECT ruta_cientifico FROM cientificos WHERE id=?");
            $stmt->bind_param("s",$ids[$i]);
            $stmt->execute();
            $res = $stmt->get_result();
            $ruta_cient = $res->fetch_assoc();
            $cientificos[$i]['ruta_cientifico'] = $ruta_cient['ruta_cientifico'];
            $stmt->close();
        }

        return $cientificos;
    }

    function obtenerCientificosFiltro($mysqli,$filtro) {
        $stmt = $mysqli->prepare("SELECT id FROM cientificos WHERE nombre LIKE ? OR apellidos LIKE ?");
        $filtro_aux = "%" . $filtro . "%";
        $stmt->bind_param("ss", $filtro_aux, $filtro_aux);
        $stmt->execute();
        $res = $stmt->get_result();

        $ids = array();

        while($id=$res->fetch_assoc()){
            $ids[] = $id['id'];
       }
       $stmt->close();

       $cientificos = array();

       for($i=0; $i<count($ids); $i++){
            $cientificos[] = obtenerCientifico($mysqli,$ids[$i]);
            $stmt = $mysqli->prepare("SELECT ruta_imagen FROM galeria WHERE id_cientifico=?");
            $stmt->bind_param("i",$ids[$i]);
            $stmt->execute();
            $res = $stmt->get_result();
            $imagen = $res->fetch_assoc();
            $cientificos[$i]['ruta_imagen'] = $imagen['ruta_imagen'];
            $stmt->close();
            $stmt = $mysqli->prepare("SELECT ruta_cientifico FROM cientificos WHERE id=?");
            $stmt->bind_param("s",$ids[$i]);
            $stmt->execute();
            $res = $stmt->get_result();
            $ruta_cient = $res->fetch_assoc();
            $cientificos[$i]['ruta_cientifico'] = $ruta_cient['ruta_cientifico'];
            $stmt->close();
        }

        return $cientificos;
    }

    function obtenerCientificoGrid($mysqli,$id) {
        $cientifico = array();
        $cientifico[] = obtenerCientifico($mysqli,$id);
        $stmt = $mysqli->prepare("SELECT ruta_imagen FROM galeria WHERE id_cientifico=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $imagen = $res->fetch_assoc();
        $cientifico[0]['ruta_imagen'] = $imagen['ruta_imagen'];
        $stmt->close();
        $stmt = $mysqli->prepare("SELECT ruta_cientifico FROM cientificos WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $ruta_cient = $res->fetch_assoc();
        $cientifico[0]['ruta_cientifico'] = $ruta_cient['ruta_cientifico'];
        $stmt->close();

        
        return $cientifico;
    }

    function obtenerCientificosFiltroJSON($mysqli,$filtro) {
        $stmt = $mysqli->prepare("SELECT nombre,apellidos,publicado FROM cientificos WHERE nombre LIKE ? OR apellidos LIKE ?");
        $aux_filtro='%'.$filtro.'%';
        $stmt->bind_param("ss",$aux_filtro,$aux_filtro);
        $stmt->execute();

        $res = $stmt->get_result();

        $cientificos = array();

        while ($row = $res->fetch_assoc()) {
            $cientifico = array(
                'nombre' => $row['nombre'],
                'apellidos' => $row['apellidos'],
                'publicado' => $row['publicado']
            );
            $cientificos[] = $cientifico;
        }

        $stmt->close();

        // Convertir el array de científicos a JSON
        $jsonData = json_encode($cientificos);

        return $jsonData;
    }

    function obtenerPalabrasProhibidas($mysqli) {
        $stmt = $mysqli->prepare("SELECT palabra FROM palabras_prohibidas");
        $stmt->execute();
        $res = $stmt->get_result();

        $palabras_prohibidas = array();

        if($res->num_rows>0){
            while($row=$res->fetch_assoc()) {
                $palabras_prohibidas[] = $row['palabra'];
            }
        }

        $stmt->close();

        return $palabras_prohibidas;
    }

    function cerrarConexion($mysqli) {
        $mysqli->close();
    }
?>