<?php
    require_once "/usr/local/lib/php/vendor/autoload.php";
    include("bd.php");

    $mysqli = ingresarBD();

    $url = $_SERVER['REQUEST_URI'];
    $cientifico = "/^\/cientifico-(\w+)-(\w+)$/i";  // detectar URL limpia para cientifico.html
    $cientifico_imprimir = "/^\/cientifico-imprimir-(\w+)-(\w+)$/i";    //detectar URL limpia para cientifico_imprimir.html
    $registarse = "/^\/registrarse$/i";
    $iniciar = "/^\/iniciar-sesion$/i";
    $modificar = "/^\/modificar$/i";
    $cerrar_sesion = "/^\/cerrar$/";
    $gestionar = "/^\/gestionar-(\w+)$/";
    $buscar = "/^\/buscar-(\w+)$/";
    $super = "/^\/permisos$/";
    $eliminar_comentario = "/^\/eliminar-comentario-(\w+)$/";
    $modificar_comentario = "/^\/modificar-comentario-(\w+)$/"; // Qué comentario se modificar según su id en la tabla
    $listar_comentarios = "/^\/listar-comentarios$/";
    $uso_hastags = "/^\/portada-(\w+)$/";
    $portada = "/^\/portada$/";
    $uso_filtros = "/^\/portada-(\w+)-(\w+)-(\w+)$/i";


    $loader = new \Twig\Loader\FilesystemLoader('templates');
    $twig = new \Twig\Environment($loader);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $referer = $_SERVER['HTTP_REFERER'];
        $isLocalhost = strpos($referer, 'localhost') !== false;
    } else {
        $isLocalhost = False;
    }

    // Petición AJAX
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if(isset($_POST['cadena'])) {
            $cadena = $_POST['cadena'];
            $cientificos = obtenerCientificosFiltroJSON($mysqli, $cadena);
        
            // Solo enviar los datos en formato JSON como respuesta
            header('Content-Type: application/json');
            echo $cientificos;
            exit;
        }
    }
    

    if (preg_match($cientifico, $url, $matches)) {
        session_start();

        $nombreCi = $matches[1];
        $apellido = $matches[2];


        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $fecha = date('Y-m-d H:i:s');
            $comentario = $_POST['comentario'];

            $idCi = obtenerIdCientifico($mysqli,$nombreCi,$apellido);

            if($nombre!=NULL && $email !=NULL && $comentario != NULL && filter_var($email,FILTER_VALIDATE_EMAIL)){  //¡Solo se introduce si rellena todos los campos!
                if(introducirComentario($mysqli,$idCi['id'],$nombre,$email,$fecha,$comentario)==0){
                    trigger_error("HA HABIDO PROBLEMAS EN LA INSERCIÓN DEL COMENTARIO",E_USER_ERROR);
                }
            }
            
        } 
        
        if(!empty($_SESSION))
            gestionarCientifico($nombreCi,$apellido,'si',$mysqli,$twig,$_SESSION['registrado'],isset($_SESSION['moderador']) ? $_SESSION['moderador'] : false,isset($_SESSION['gestor']) ? $_SESSION['gestor'] : false,isset($_SESSION['super']) ? $_SESSION['super'] : false);
        else
            gestionarCientifico($nombreCi,$apellido,'si',$mysqli,$twig);

    } else if(preg_match($cientifico_imprimir,$url,$matches2)){
        session_start();

        $nombre = $matches2[1];
        $apellido = $matches2[2];

        if(!empty($_SESSION))
            gestionarCientificoImprimir($nombre,$apellido,$mysqli,$twig,$_SESSION['registrado'],isset($_SESSION['moderador']) ? $_SESSION['moderador'] : false,isset($_SESSION['gestor']) ? $_SESSION['gestor'] : false,isset($_SESSION['super']) ? $_SESSION['super'] : false);
        else {
            gestionarCientificoImprimir($nombre,$apellido,$mysqli,$twig);
        }
    } else if(preg_match($registarse,$url)){
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $nombre = $_POST['nombre'];
            $apell = $_POST['apellidos'];
            $correo = $_POST['correo'];
            $nick = $_POST['nick'];
            $contra = $_POST['contrasenia'];

            if($nombre!=NULL && $apell!=NULL && $correo!=NULL && $nick!=NULL && $contra!=NULL && filter_var($correo,FILTER_VALIDATE_EMAIL)){
                if(registrarUsuario($mysqli,$nombre,$apell,$correo,$nick,$contra) == 0){
                    gestionarRegistro($mysqli,$twig,true);
                } else {
                    session_start();
                    $_SESSION['registrado'] = true;
                    $_SESSION['nick'] = $nick;
                    header('Location: /');
                //    gestionarBuscador($mysqli,$twig,$_SESSION['registrado']);
                }
            } else {
                gestionarRegistro($mysqli,$twig);
            }

        } else {
            gestionarRegistro($mysqli,$twig);
        }


    } else if(preg_match($iniciar,$url)){   // Iniciar sesión
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nick = $_POST['nick'];
            $contra = $_POST['con'];

            if($nick!=NULL && $contra!=NULL){
                if(iniciarSesion($mysqli,$nick,$contra) == 0){
                    gestionarInicio($mysqli,$twig,true);
                } else {
                    session_start();
                    $_SESSION['nick'] = $nick;
                    restauraVariables($mysqli,$nick);
                    header('Location: /');
                    //gestionarBuscador($mysqli,$twig,$_SESSION['registrado'],isset($_SESSION['moderador']) ? $_SESSION['moderador'] : false,isset($_SESSION['gestor']) ? $_SESSION['gestor'] : false,isset($_SESSION['super']) ? $_SESSION['super'] : false);
                }
            } else {
                gestionarInicio($mysqli,$twig);
            }
        }  else {
            gestionarInicio($mysqli,$twig);
        }
    } else if(preg_match($modificar,$url)) {    // Gestionar la modificación de datos del usuario. Si todo va bien, se muestra la portada y el nuevo nick se almacena como variale de sesión
        session_start();
        $el_nick = $_SESSION['nick'];   // Para saber qué datos tenía el usuario, previos a la modificación

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $apell = $_POST['apellidos'];
            $correo = $_POST['correo'];
            $nick = $_POST['nick'];
            $contra = $_POST['contrasenia'];
            if($nombre!=NULL && $apell!=NULL && $correo!=NULL && $nick!=NULL && $contra!=NULL && filter_var($correo,FILTER_VALIDATE_EMAIL)){
                if(modificarUsuario($mysqli,$nombre,$apell,$correo,$nick,$contra)==0){
                    gestionarModificar($mysqli,$twig,$el_nick,true,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
                } else {
                    $_SESSION['nick'] = $nick;
                    gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
                }
        
            } else {
                gestionarModificar($mysqli,$twig,$el_nick,false,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
            }
        } else {
            gestionarModificar($mysqli,$twig,$el_nick,false,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
        }
        
    } else if(preg_match($gestionar,$url)){ // Gestionar la información que aparecerá en el formulario de gestión
        session_start();
        $palabras = explode('-',$url);
        $accion = $palabras[1];

        $aniade = false;
        $edita = false;
        $elimina = false;
        $por_nombre=false;
        $por_bio = false;

        if($accion=='aniade')
            $aniade = true;

        if($accion == 'edicion')
            $edita=true;

        if($accion == 'elimina')
            $elimina=true;

        if(isset($_POST['buscador_nombre'])){
            $por_nombre = true;
        }

        if(isset($_POST['buscador_bio'])){
            $por_bio = true;
        }

        $_SESSION['accion'] = $accion;
        $errores=false;

        if($_SERVER['REQUEST_METHOD']=='POST') {
            
            if(!$por_nombre && !$por_bio){
                var_dump($_POST);
                if($aniade){    // Añadir un científico
                    $nombre_aniade = $_POST['nombre_aniade'];
                    $apell = $_POST['apellidos_aniade'];
                    $fNac = $_POST['fec_nac_aniade'] !='' ? $_POST['fec_nac_aniade'] : NULL;
                    $fMu = $_POST['fec_mu_aniade'] != '' ? $_POST['fec_mu_aniade'] : NULL;
                    $ciu= $_POST['ciudad_aniade'] != '' ? $_POST['ciudad_aniade'] : NULL;
                    $bio= $_POST['biografia_aniade'] != '' ? $_POST['biografia_aniade'] : NULL;

                    if(($_POST['enlace_aniade']=='' && $_POST['nombre_enlace']!='') || ($_POST['enlace_aniade']!='' && $_POST['nombre_enlace']=='')){
                        $todaInfoCientifico = obtenerTodaInfoCientifico($mysqli);
                        gestionarGestion($mysqli,$twig,$todaInfoCientifico,false,$aniade,$edita,$elimina,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
                    }
                    $enl=$_POST['enlace_aniade'] != '' ? $_POST['enlace_aniade'] : NULL;
                    $nomb_enl= $_POST['nombre_enlace'] != '' ? $_POST['nombre_enlace'] : NULL;

                    if(isset($_FILES['imagen_aniade'])){
                        $file_name=$_FILES['imagen_aniade']['name'];
                        $file_size=$_FILES['imagen_aniade']['size'];
                        $file_tmp=$_FILES['imagen_aniade']['tmp_name'];
                        $aux = explode('.',$_FILES['imagen_aniade']['name']);
                        $fil_ext=strtolower(end($aux));
                        $extensions=array("jpeg","jpg","png");

                        if(!in_array($fil_ext,$extensions)){
                            $errores=true;
                        }
                        if($file_size>2097152){
                            $errores=true;
                        }
                        if(!$errores){
                            $nombre_correcto=obtenerNombreSinRepetir($file_name);
                            move_uploaded_file($file_tmp,"imgs/" . $nombre_correcto);
                        }

                    }
                    $pie= $_POST['pie_imagen_aniade'] != '' ? $_POST['pie_imagen_aniade'] : NULL;
                    $hastag = $_POST['hastag_aniade'] != '' ? $_POST['hastag_aniade'] : NULL;

                    if($nombre_aniade!=NULL && isset($_FILES['imagen_aniade']) && !$errores){
                        $ruta_imagen="imgs/" . $nombre_correcto;
                        introducirCientifico($mysqli,$nombre_aniade,$apell,$ruta_imagen,$fNac,$fMu,$ciu,$bio,$enl,$nomb_enl,$pie,$hastag);
                        
                    }
                }
                if($edita){ // Modificar la información de un científico
                    $id_modif=$_POST['id_modif'];
                    $nombre = $_POST['nombre_edita'] != '' ? $_POST['nombre_edita'] : NULL;
                    $apell = $_POST['apellidos_edita'] != '' ? $_POST['apellidos_edita'] : NULL;
                    $ciu = $_POST['ciudad_edita'] != '' ? $_POST['ciudad_edita'] : NULL;
                    $fNac = $_POST['fec_nacim_edita'] !='' ? $_POST['fec_nacim_edita'] : NULL;
                    $fMu = $_POST['fec_muerte_edita'] != '' ? $_POST['fec_muerte_edita'] : NULL;
                    $bio = $_POST['biografia_edita'] != '' ? $_POST['biografia_edita'] : NULL;

                    if(($_POST['aniade_enlaces_edita'] != '' && $_POST['nombre_enlaces_edita'] == '') || ($_POST['aniade_enlaces_edita'] == '' && $_POST['nombre_enlaces_edita'] != '')){
                        $todaInfoCientifico = obtenerTodaInfoCientifico($mysqli);
                        gestionarGestion($mysqli,$twig,$todaInfoCientifico,false,$aniade,$edita,$elimina,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
                    }
                    $enl=$_POST['aniade_enlaces_edita'];
                    $nomb_enl=$_POST['nombre_enlaces_edita'];
                    $errores=false;
                    if(isset($_FILES['aniade_fotos_edita']) && !empty($_FILES['aniade_fotos_edita']['tmp_name']) && $_FILES['aniade_fotos_edita']['error'] == 0){
                        $file_name=$_FILES['aniade_fotos_edita']['name'];
                        $file_size=$_FILES['aniade_fotos_edita']['size'];
                        $file_tmp=$_FILES['aniade_fotos_edita']['tmp_name'];
                        $aux = explode('.',$_FILES['aniade_fotos_edita']['name']);
                        $fil_ext=strtolower(end($aux));
                        $extensions=array("jpeg","jpg","png");

                        if(!in_array($fil_ext,$extensions)){
                            $errores=true;
                        }
                        if($file_size>2097152){
                            $errores=true;
                        }
                        if(!$errores){
                            $nombre_correcto=obtenerNombreSinRepetir($file_name);
                            move_uploaded_file($file_tmp,"imgs/" . $nombre_correcto);
                            $ruta_imagen='imgs/' . $nombre_correcto;
                        }

                    } else {
                        $ruta_imagen='';
                    }
                    $pie=$_POST['pie_foto_edita'] != '' ? $_POST['pie_foto_edita'] : NULL;
                    $id_foto_elimina=$_POST['foto_elimina'];
                    $id_enl_elimina=$_POST['enlace_elimina'];
                    $id_pie_foto_mod=$_POST['id_modifica_pie_foto'];
                    $pie_modif=$_POST['modifica_pie_foto'];
                    $id_mod_nom_enlace=$_POST['id_modifica_nombre_enlace'];
                    $nom_enlace=$_POST['modifica_nombre_enlace'];
                    $hastag = $_POST['hastag_aniade_modif'] != '' ? $_POST['hastag_aniade_modif'] : NULL;
                    $publicar = $_POST['publica'] != '' ? $_POST['publica'] : NULL;
                    if($id_modif!='' && !$errores){
                        if(hacerModificaciones($mysqli,$id_modif,$nombre,$apell,$ciu,$fNac,$fMu,$bio,$enl,$nomb_enl,$ruta_imagen,$pie,$id_foto_elimina,$id_enl_elimina,$id_pie_foto_mod,$pie_modif,$id_mod_nom_enlace,$nom_enlace,$hastag,$publicar)==0){
                            $errores = true;
                        }
                    }
                }

                if($elimina){   // Si se indica en la URL que quiere eliminar a un científico
                    $id_eliminar = $_POST['ID_elimina'];
                    if($id_eliminar!=''){
                        if(eliminarCientifico($mysqli,$id_eliminar)==0){
                            $errores=true;
                        }
                    }
                }
                $todaInfoCientifico = obtenerTodaInfoCientifico($mysqli);
            }
            // Si se quiere filtrar por nombre o biografía (se indica en la URL cuando es cada situación). Esto es, mostrar la información de científicos filtrada por el gestor. En otro caso, se muestan todos los científicos
            if($por_nombre && !$por_bio){
                $nombre__ = isset($_POST['buscador_nombre']) ? $_POST['buscador_nombre'] : NULL;
                $todaInfoCientifico = filtrarPorNombre($mysqli,$nombre__);
            }

            if(!$por_nombre && $por_bio){
                $bio__ = isset($_POST['buscador_bio']) ? $_POST['buscador_bio'] : NULL;
                $todaInfoCientifico = filtrarPorBio($mysqli,$bio__);
            }
        } else {
            $todaInfoCientifico = obtenerTodaInfoCientifico($mysqli);
        }
        
        gestionarGestion($mysqli,$twig,$todaInfoCientifico,$errores,$aniade,$edita,$elimina,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);

    } else if(preg_match($super,$url)){ // Gestionar la página formulario visible por el superusuario para hacer sus funciones
        session_start();
        $cantidad = cuantosSuper($mysqli);
        $errores=false;

        if($_SERVER['REQUEST_METHOD']=='POST'){
            $nick = $_POST['nick_permisos'];
            $rol = $_POST['rol_permisos'];
            if($nick!=''){
                if(hacerCambiosRol($mysqli,$nick,$rol,$cantidad)==0){   
                    $errores=true;
                }
            }

        }
        if($_SESSION['super']){
            gestionarSuperUsuario($mysqli,$twig,$errores,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
        } else {
            gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
        }
    } else  if(preg_match($eliminar_comentario,$url,$matches)) {    // Si se ha generado una URL que indica que se elimina un comentario (se indica por su ID que aparece en la URL)
        session_start();
        $palabras = explode('-',$url);
        $id = $palabras[2];

        if(eliminarComentario($mysqli,$id)==0){ // No debería entrar a este bloque
            trigger_error("ERROR AL ELIMINAR COMENTARIO");
        }

        gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);

    } else if(preg_match($modificar_comentario,$url,$matches)) {    // Si se ha generado una URL por un moderador para sobreescribir un comentario preexistente
        session_start();
        $palabras = explode('-',$url);
        $id = $palabras[2];

        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);
                                                                // Compruebo si se va a generar para editar comentario o para listar otros
        if($_SERVER['REQUEST_METHOD']=="POST"){
            $nuevo_comentario = $_POST['comentario_modif'];
            $nuevo_comentario .= "\nMensaje modificado por el moderador";

            modificarComentario($mysqli,$id,$nuevo_comentario);
            gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
        } else {
            $coment = obtenerComentario($mysqli,$id);
            echo $twig->render('formulario_moderador.html',array('modificando'=>true,'comentario'=>$coment,'enlacesPanelDerecho'=>$enlacesPanelDerecho,'esta_registrado'=>$_SESSION['registrado'],'es_moderador'=>$_SESSION['moderador'],'es_gestor'=>$_SESSION['gestor'],'es_super'=>$_SESSION['super']));
        }

    } else if(preg_match($listar_comentarios,$url)) {   // Mostrar los comentarios. Por defecto, todos los de la BD. Si se filtra (por científico) solo los de ese científico
       // obtenerTodosComentarios. Si está filtrando, (alguna variable booleana pa verlo), obtenerComentariosFiltrados
       // obtener todos enlaces
       // mostrar 'formulario_moderador.html con 'modificando' = false
       session_start();
       $nombre = NULL;
       $apellido = NULL;
       if($_SERVER['REQUEST_METHOD'] == "POST"){
            $nombre=$_POST['nombre_buscar'];
            $apellido = $_POST['apellidos_buscar'];
       }
       $comentarios = obtenerTodosComentarios($mysqli,$nombre,$apellido);
       $enls = obtenerEnlacesPanelDerecho($mysqli);
       $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);

       echo $twig->render('formulario_moderador.html',array('modificando'=>false,'lista'=>$comentarios,'enlacesPanelDerecho'=>$enlacesPanelDerecho,'esta_registrado'=>$_SESSION['registrado'],'es_moderador'=>$_SESSION['moderador'],'es_gestor'=>$_SESSION['gestor'],'es_super'=>$_SESSION['super']));
    } else if(preg_match($uso_hastags,$url,$matches)){  // Gestionar la portada en función del hastag clicado por el usuario (muestra los científicos en el grid-layout con ese hastag en cuestión)
        $palabras = explode('-',$url);
        $hastag = $palabras[1];
        session_start();
        // Mostrar los científicos con el hastag en cuestión
        if($hastag!='cientifico') {
            $elHastag = $hastag;
        } else {
            $elHastag = NULL;
        }
        if(!empty($_SESSION)){
            gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super'],$elHastag);
        } else
            gestionarPortada($mysqli,$twig,false,false,false,false,$elHastag);
    } else if(preg_match($uso_filtros,$url,$matches)){
        $palabras = explode('-',$url);
        $nombre = $palabras[1];
        $apellidos = $palabras[2];
        $remarca = $palabras[3];
        $id = obtenerIdCientifico($mysqli,$nombre,$apellidos);
        session_start();
        if(!empty($_SESSION)){
            gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super'],NULL,NULL,$id,$remarca);
        } else{
            gestionarPortada($mysqli,$twig,false,false,false,false,NULL,NULL,$id,$remarca);
            
        }
    } else if(preg_match($cerrar_sesion,$url)){ // Cierre de una posible sesión
        session_start();
        session_destroy();
        
        gestionarBuscador($mysqli,$twig);
    } else if($isLocalhost && isset($_SERVER['REQUEST_METHOD'])){  # Se muestra la portada según las coincidencias de la cadena
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            session_start();
            if(isset($_POST['busqueda_lista'])){
                $cadena = $_POST['busqueda_lista'];
                if(isset($_SESSION['registrado']) && isset($_SESSION['moderador']) && isset($_SESSION['gestor']) && isset($_SESSION['super']))
                    gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super'],NULL,$cadena);
                else
                    gestionarPortada($mysqli,$twig,False,False,False,False,NULL,$cadena);
            } else {
                if(isset($_SESSION['registrado']) && isset($_SESSION['moderador']) && isset($_SESSION['gestor']) && isset($_SESSION['super']))
                    gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
                else
                    gestionarPortada($mysqli,$twig,False,False,False,False);
            }
        } else {
            session_start();
            if(isset($_SESSION['registrado']) && isset($_SESSION['moderador']) && isset($_SESSION['gestor']) && isset($_SESSION['super']))
                gestionarPortada($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
            else
                gestionarPortada($mysqli,$twig,False,False,False,False);
        }
            
        
    } else {
        session_start();
        if(isset($_SESSION['registrado']) && isset($_SESSION['moderador']) && isset($_SESSION['gestor']) && isset($_SESSION['super']))
            gestionarBuscador($mysqli,$twig,$_SESSION['registrado'],$_SESSION['moderador'],$_SESSION['gestor'],$_SESSION['super']);
        else
            gestionarBuscador($mysqli,$twig,False,False,False,False);
    }

    

    cerrarConexion($mysqli);

    function gestionarBuscador($mysqli,$twig,$registrado=false,$moderador=false,$gestor=false,$super=false) {
        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);

        echo $twig->render('index.html',array('enlacesPanelDerecho'=>$enlacesPanelDerecho,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super));
    }

    function gestionarPortada($mysqli,$twig,$registrado=false,$moderador=false,$gestor=false,$super=false,$hastag=NULL,$filtro=NULL,$id=NULL,$remarca=NULL){

        /* Por aquí saco toda la info que vaya a haber en la plantilla padre.html */
        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);
        // Ahoras saco la info que solo aparece en index.html
        if($hastag==NULL && $filtro==NULL && $id==NULL)
            $cientificos = obtenerCientificos($mysqli);
        else if($hastag !=NULL && $filtro==NULL)
            $cientificos = obtenerCientificosHastag($mysqli,$hastag);
        else if($id==NULL){
            $cientificos = obtenerCientificosFiltro($mysqli,$filtro);
        } else{
            $cientificos = obtenerCientificoGrid($mysqli,$id['id']);
        }

        echo $twig->render('portada.html',array('cientificos'=>$cientificos,'enlacesPanelDerecho'=>$enlacesPanelDerecho,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'cadena_remarcar'=>$remarca));
    }

    function gestionarCientifico($nombre,$apellidos,$renderizar_cientifico,$mysqli,$twig,$registrado=false,$moderador=false,$gestor=false,$super=false){

        /* Por aquí saco toda la info que vaya a haber en la plantilla padre.html */

        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);

        // Saco info de cientifico.html, obteniendo primero su ID

        $idCi = obtenerIdCientifico($mysqli,$nombre,$apellidos);

        $infoCientifico = obtenerCientifico($mysqli,$idCi['id']);
        $imagenes = obtenerRutasImagenes($mysqli,$idCi['id']);
        $enlaces = obtenerEnlacesCientifico($mysqli,$idCi['id']);
        $comentarios = obtenerComentarios($mysqli,$idCi['id']);
        $hastags = obtenerHastagsCientifico($mysqli,$idCi['id']);

        if($renderizar_cientifico==='si'){
            $palabrasProhibs = obtenerPalabrasProhibidas($mysqli);

            echo $twig->render('cientifico.html',array('infoCientifico'=>$infoCientifico,'imagenes'=>$imagenes,'enlaces'=>$enlaces,'comentarios'=>$comentarios,'enlacesPanelDerecho'=>$enlacesPanelDerecho,'prohibidas'=>$palabrasProhibs,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'hastags'=>$hastags));
        } else {
            return array('infoCientifico'=>$infoCientifico,'imagenes'=>$imagenes,'enlaces'=>$enlaces,'comentarios'=>$comentarios,'enlacesPanelDerecho'=>$enlacesPanelDerecho);
        }

    }

    function gestionarCientificoImprimir($nombre,$apellidos,$mysqli,$twig,$registrado=false,$moderador=false,$gestor=false,$super=false){
        // Aquí, en vez de renderizar padre.html se renderiza cientifico.html

        $aux = gestionarCientifico($nombre,$apellidos,'no',$mysqli,$twig);

        // Saco info de cientifico.html, obteniendo primero su ID

        echo $twig->render('cientifico_imprimir.html',array('infoCientifico'=>$aux['infoCientifico'],'imagenes'=>$aux['imagenes'],'enlaces'=>$aux['enlaces'],'comentarios'=>$aux['comentarios'],'enlacesPanelDerecho'=>$aux['enlacesPanelDerecho'],'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super));
    }

    function gestionarRegistro($mysqli,$twig,$error=false,$registrado=false,$moderador=false,$gestor=false,$super=false) {

        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);

        echo $twig->render('registrar.html',array('enlacesPanelDerecho'=>$enlacesPanelDerecho,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'error'=>$error));

    }

    function gestionarInicio($mysqli,$twig,$errores=false,$registrado=false,$moderador=false,$gestor=false,$super=false) {
        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);

        echo $twig->render('iniciar_sesion.html',array('enlacesPanelDerecho'=>$enlacesPanelDerecho,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'error'=>$errores));

    }

    function gestionarModificar($mysqli,$twig,$el_nick,$errores=false,$registrado=false,$moderador=false,$gestor=false,$super=false) {
        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);

        $datos = obtenerDatosUsuario($mysqli,$el_nick);

        echo $twig->render("formulario_datos.html",array('enlacesPanelDerecho'=>$enlacesPanelDerecho,'datos'=>$datos[0],'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'error'=>$errores));
    }

    // Errores gestionados los que no puedo gestionar en JavaScript directamente sin usar PHP
    function gestionarGestion($mysqli,$twig,$todaInfoCientifico,$errores,$aniade=false,$edita=false,$elimina=false,$registrado=false,$moderador=false,$gestor=false,$super=false) {
        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);
      
        echo $twig->render("formulario_gestor.html",array('enlacesPanelDerecho'=>$enlacesPanelDerecho,'aniade'=>$aniade,'edita'=>$edita,'elimina'=>$elimina,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'todaInfo'=>$todaInfoCientifico,'error'=>$errores));
    }

    function gestionarSuperUsuario($mysqli,$twig,$errores,$registrado=false,$moderador=false,$gestor=false,$super=false){
        $usuarios = obtenerTodaInfoUsuarios($mysqli);
        $enlacesPanelDerecho = obtenerEnlacesPanelDerecho($mysqli);
        $cantidad = cuantosSuper($mysqli);


        echo $twig->render("formulario_super.html",array('enlacesPanelDerecho'=>$enlacesPanelDerecho,'usuarios'=>$usuarios,'supers'=>$cantidad,'esta_registrado'=>$registrado,'es_moderador'=>$moderador,'es_gestor'=>$gestor,'es_super'=>$super,'error'=>$errores));
    }

    // Inicia a True las variables de sesión en función del rol del usuario. Si se registra, su rol será 'registrado' por defecto
    function restauraVariables($mysqli,$nick) {
        $_SESSION['registrado'] = true;
        $_SESSION['moderador'] = false;
        $_SESSION['gestor'] = false;
        $_SESSION['super'] = false;

        if(esModerador($mysqli,$nick)==1){
            $_SESSION['moderador'] = true;
        }
        if(esGestor($mysqli,$nick)==1){
            $_SESSION['gestor'] = true;
        }
        if(esSuperusuario($mysqli,$nick)==1){
            $_SESSION['super'] = true;
            $_SESSION['moderador'] = true;
            $_SESSION['gestor'] = true;
        }
    }

    // Como voy a buscar en la carpeta 'imgs', no hace falta pedir el nombre de la carpeta. Se supone que en 'imgs' solo hay archivos imagen

    function obtenerNombreSinRepetir($file_name){
        $elementos = scandir(__DIR__ . '/imgs');    // Uso ruta relativa

        foreach($elementos as $imagen) {
            if ($imagen == '.' || $imagen == '..') {
                continue;
            }

            if($imagen==$file_name){
                $file_name = pathinfo($file_name,PATHINFO_FILENAME) . '1' . '.' . pathinfo($file_name,PATHINFO_EXTENSION);  // Así, si sucesivamente hay archivos con el mismo nombre, como el array es ordenado, los va a encontrar
            }

        }

        return $file_name;

    }

?>