function comprobarInicio() {
    var nick = document.getElementById("nick");
    var contra = document.getElementById("con");

    if(nick.value==""){
        alert("Ingrese un nick");
        return false;
    }

    if(contra.value==""){
        alert("Ingrese una contraseña válida");
        return false;
    }

    return true;
}

function comprobarRegistro(){
    var nombre=document.getElementById("nombre");
    var apell = document.getElementById("apellidos");
    var correo = document.getElementById("correo");
    var nick = document.getElementById("nick");
    var contra = document.getElementById("contrasenia");

    const expresion_regular = /\S+@\S+\.\S+/;

    if(nombre.value==""){
        alert("Ingrese un nombre");
        return false;
    }
    if(apell.value==""){
        alert("Ingrese unos apellidos");
        return false;
    }
    if(correo.value==""){
        alert("Ingrese un correo");
        return false;
    }
    if(nick.value==""){
        alert("Ingrese un nick");
        return false;
    }
    if(contra.value==""){
        alert("Ingrese una contraseña");
        return false;
    }

    if(expresion_regular.test(correo.value)){
        return true;
    } else {
        alert("Ingrese una dirección de correo válida");
        return false;
    }
}

function comprobarID1(){
    var id=document.getElementById('id_modif');
    var enl = document.getElementById('aniade_enlaces_edita');
    var nomb_enl = document.getElementById('nombre_enlaces_edita');

    if(id.value==""){
        alert("Ingrese el ID del científico");
        return false;
    }

    if((enl.value=="" && nomb_enl.value!="") || (enl.value!="" && nomb_enl.value=="")){
        alert("Si ingresa uno de los dos campos del enlace, debe ingresar el otro también");
    }

    return true;
}

function comprobarID2(){
    var id=document.getElementById('ID_elimina');

    if(id.value==""){
        alert("Ingrese el ID del científico");
        return false;
    }

    return true;
}

function comprobarAniade(){
    var nombre=document.getElementById('nombre_aniade');
    var img=document.getElementById('imagen_aniade');
    var enl = document.getElementById('enlace_aniade');
    var nom_enl = document.getElementById('nombre_enlace');

    if(nombre.value==""){
        alert("Ingrese el nombre del científico");
        return false;
    }

    if(img.value==""){
        alert("Ingrese una imagen para el científico");
        return false;
    }

    if((enl.value=="" && nom_enl!="") || (enl.value!="" && nom_enl.value=="")) {
        alert("Si ingresa uno de los dos campos del enlace, debe ingresar el otro también");
    }

}

function comprobarSuperUsuarios() {
    var nick = document.getElementById('nick_permisos');

    if(nick.value==""){
        alert("Ingrese el nick de un usuario de la web");
        return false;
    }



   // if(numero_super == 1){
   //     alert("Debe haber al menos un superusuario en la web");
   //     return false;
   // }
}

function revisarFiltro(){
    var nomb = document.getElementById('nombre_buscar');
    var ap = document.getElementById('apellidos_buscar');

    if(nomb.value=="" || ap.value==""){
        alert("Ingrese nombre y apellidos del científico para filtrar");
        return false;
    }
}