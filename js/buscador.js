// Generar petición POST al servidor para que reciba los parámetros de búsqueda

/*document.getElementById('busqueda_lista').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar el envío del formulario

    var form = event.target;
    var formData = new FormData(form);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Redireccionar a la URL deseada
                window.location.href = '/portada';
            } else {
                console.error('Error al enviar la solicitud.');
            }
        }
    };

    xhr.open('POST', form.action, true);
    xhr.send(formData);
});
*/

// Usar AJAX+jquery para enviar la cadena introducida por el usuario en la barra de búsqueda y devolver coincidencias

$(document).ready(function(){

    $("#busqueda_lista").keyup(function(){    // Cada vez que escriba una letra
        // Código AJAX+jquery
        cadena = $("#busqueda_lista").val();

        // Resaltar la subcadena en negrita en el elemento <p>

        $.ajax({
            data: {cadena: cadena},
            url: 'index.php',
            type: 'post',
            success: function(respuesta){
                if(cadena!="")
                    procesarAJAX(respuesta, cadena);
                else 
                    $("#lista_nombres").empty();
            }
        })
    });
});

function procesarAJAX(respuesta, cadena) {
    // Limpiar la lista actual
    $("#lista_nombres").empty();

    for (var i = 0; i < respuesta.length; i++) {
        var nombre = respuesta[i].nombre;
        var apellidos = respuesta[i].apellidos;
    
        var nombreResaltado = nombre.replace(new RegExp(cadena, 'gi'), '<strong>$&</strong>');
        var apellidosResaltados = apellidos.replace(new RegExp(cadena, 'gi'), '<strong>$&</strong>');
    
        // Construir el elemento de lista con los nombres resaltados
        var listItem = $('<li></li>').html(nombreResaltado + ' ' + apellidosResaltados);
        listItem.css({
          'position': 'relative',
          'background-color': 'white',
          'list-style-type': 'none',
          'left': '-40px',
          'top': '-28px',
          'width': '210px',
          'cursor': 'pointer'
        });
    
        // Obtener la URL correspondiente a este resultado (puedes ajustar la lógica según tus necesidades)
        var url = "portada-" + nombre+"-"+apellidos+"-"+encodeURIComponent(cadena);
    
        // Agregar el controlador de eventos de clic al elemento generado. Hacer la espera
        (function(nombre, apellidos, url) {
            listItem.on('click', function() {
            var nombreCientifico = nombre + " " + apellidos;
            $('#busqueda_lista').val(nombreCientifico);
            window.location.href = url;
            });
        })(nombre, apellidos, url);

        if(respuesta[i].publicado === 1){
            $("#lista_nombres").append(listItem);
        } else if(es_gestor_comp===true){
            $("#lista_nombres").append(listItem);
        }
    
      }
}


document.addEventListener("DOMContentLoaded", (event) => {
    // Pongo class porque puede haber más de un científico (en teoría)
    elementosRemarcar = document.getElementsByClassName('resaltar');

    for (i = 0; i < elementosRemarcar.length; i++) {
        elemento = elementosRemarcar[i];
        contenido = elemento.innerHTML;  
        // Crear una expresión regular para buscar todas las ocurrencias de la subcadena
        regex = new RegExp(remarcar, 'gi');
    
        // Remarcar subcadena
        nuevoContenido = contenido.replace(regex, '<strong>$&</strong>');
    
        elemento.innerHTML = nuevoContenido;  
    }
});