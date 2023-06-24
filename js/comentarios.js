// Álvaro Rodríguez Gallardo

// Esconde o despliega la sección de comentarios dando a un botón que activa el evento

function mostrarOcultarComentarios(){
    var estado = document.getElementById('visibilidad_comentarios');

    if(estado.style.right=='-500px') {
        estado.style.right = '0';
    } else {
        estado.style.right = '-500px';
    }

}

function mostrarOcultarEdicion(){
    var estado = document.getElementById('visibilidad_gestor');

    if(estado.style.right=='-500px') {
        estado.style.right = '0';
    } else {
        estado.style.right = '-500px';
    }
}
// Se gestionan posibles errores, alertando de ellos, y se introduce el comentario si todo es correcto

function enviarComentario(){
    var nombre = document.getElementById("nombre");
    var email = document.getElementById("email");
    var comentario = document.getElementById("miComentario");
    const region_comentar = document.getElementById("seccion_comentarios");
    const expresion_regular = /\S+@\S+\.\S+/;

    if (nombre.value == "") {
        alert("Rellene la casilla de nombre");
        return false;
    }

    if (email.value == "") {
        alert("Rellene la casilla del mail");
        return false;
    }

    if (comentario.value == "") {
        alert("Rellene la casilla del comentario");
        return false;
    }

    if (expresion_regular.test(email.value)) {
        const caja = document.createElement("div");
        const comentar = document.createElement("p");
        const name = document.createElement("p");
        const momento = document.createElement("p");
        name.textContent = nombre.value;
        momento.textContent = Date().toString().substring(0,24);
        comentar.textContent = comentario.value;
        caja.style.backgroundColor = 'skyblue';
        caja.appendChild(name);
        caja.appendChild(momento);
        caja.appendChild(comentar);
        region_comentar.appendChild(caja);

     //   document.getElementById("formulario").submit();
    } else {
        alert("Introduzca una dirección de email válida");
        return false;
    }

}

// Función para censurar palabras prohibidas. En el momento que la detecta, la censura

function censurarPalabra() {
    var comentario = document.getElementById('miComentario');

    // Recibir el archivo json y asignarlo a palabrasProhibidas

   //const palabrasProhibidas = <?php echo $json_datos; ?>;
   
    for (var i=0; i<palabrasProhibidas.length; i++) {   // EN vez de comprobar por palabra, aprovecho la funcionalidad disponible para que sea menor el tiempo de cómputo
        var entreEspacios = new RegExp('\\b' + palabrasProhibidas[i] + "\\b", "gi");  //Así censura la palabra cuando esté entre espacios (no censura diputación, por ejemplo)
        var sustituye = "*".repeat(palabrasProhibidas[i].length);           // flag 'g' para  búsqueda global y la flag 'i' para que no distinga mayúsculas y minúsculas
        comentario.value = comentario.value.replace(entreEspacios,sustituye);
    }

    document.getElementById('miComentario').value = comentario.value;
      
}