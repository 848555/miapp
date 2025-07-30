document.addEventListener('DOMContentLoaded', function () {
  // --- Configuración dropdown ---
  const configLink = document.getElementById('configLink');
  const configDropdown = document.getElementById('configDropdown');
  const eliminarCuentaLink = document.getElementById('eliminarCuentaLink');
  const politicasLink = document.getElementById('politicasLink');
  const eliminarCuentaModal = document.getElementById('eliminarCuentaModal');
  const politicasModal = document.getElementById('politicasModal');
  const closeButtons = document.querySelectorAll('.modal .close');

  if (configLink && configDropdown) {
    configLink.addEventListener('click', function (e) {
      e.preventDefault();
      configDropdown.style.display = configDropdown.style.display === 'block' ? 'none' : 'block';
    });
  }

  if (eliminarCuentaLink && eliminarCuentaModal) {
    eliminarCuentaLink.addEventListener('click', function (e) {
      e.preventDefault();
      eliminarCuentaModal.style.display = 'block';
      if (configDropdown) configDropdown.style.display = 'none';
    });
  }

  if (politicasLink && politicasModal) {
    politicasLink.addEventListener('click', function (e) {
      e.preventDefault();
      politicasModal.style.display = 'block';
      if (configDropdown) configDropdown.style.display = 'none';
    });
  }

  closeButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      btn.closest('.modal').style.display = 'none';
    });
  });

  window.addEventListener('click', function (e) {
    if (e.target === eliminarCuentaModal) {
      eliminarCuentaModal.style.display = 'none';
    }
    if (e.target === politicasModal) {
      politicasModal.style.display = 'none';
    }
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });

  // --- Modal de mensajes ---
  var modal = document.getElementById('mensajeModal');
  var openModalBtn = document.getElementById('openModalBtn');
  var closeModal = document.getElementsByClassName('close')[0];
  var mensajesContainer = document.getElementById('mensajesContainer');

  if (openModalBtn) {
    openModalBtn.onclick = function () {
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
          if (xhr.status === 200) {
            mensajesContainer.innerHTML = xhr.responseText;
            modal.style.display = 'block';
            marcarMensajesLeidos();
          } else {
            alert('Hubo un problema al cargar los mensajes.');
          }
        }
      };
      xhr.open('GET', '/app/include/mostrar_mensajes.php', true);
      xhr.send();
    };
  }

  if (closeModal) {
    closeModal.onclick = function () {
      modal.style.display = 'none';
    };
  }

  // --- Ocultar mensaje después de 5 segundos ---
  var mensajeDiv = document.getElementById('mensaje-solicitante');
  if (mensajeDiv) {
    setTimeout(function () {
      mensajeDiv.style.display = 'none';
    }, 5000);
  }

  var errorMessage = document.getElementById('error-message');
  if (errorMessage) {
    setTimeout(function () {
      errorMessage.style.display = 'none';
    }, 5000);
  }

  // --- Estado mototaxistas (consultar y toggle) ---
  const estadoTexto = document.getElementById('estadoTexto');
  const toggleOnlineBtn = document.getElementById('toggleOnlineBtn');

  if (estadoTexto && toggleOnlineBtn) {
    fetch('/app/include/consultar_estado.php')
      .then(response => response.json())
      .then(data => {
        estadoTexto.textContent = "Estado: " + (data.en_linea ? "Conectado" : "Desconectado");
        toggleOnlineBtn.textContent = data.en_linea ? "Desconectarse" : "Conectarse";
      });

    toggleOnlineBtn.addEventListener('click', () => {
      fetch('/app/include/toggle_estado.php', {
        method: 'POST'
      })
        .then(response => response.json())
        .then(data => {
          estadoTexto.textContent = "Estado: " + (data.en_linea ? "Conectado" : "Desconectado");
          toggleOnlineBtn.textContent = data.en_linea ? "Desconectarse" : "Conectarse";
        });
    });
  }
});

// --- Funciones fuera del DOMContentLoaded ---

function marcarMensajesLeidos() {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/app/include/marcar_mensajes_leidos.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.send();
}

function mostrarAlerta(event) {
  event.preventDefault();

  console.log('Iniciando función mostrarAlerta');

  if (!userId) {
    console.log('Error: userId no está definido');
    return;
  }

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "/app/include/verificar_documentos.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    console.log('Estado readyState:', xhr.readyState);
    console.log('Estado de la solicitud:', xhr.status);

    if (xhr.readyState == 4 && xhr.status == 200) {
      var respuesta = xhr.responseText.trim();

      console.log('Respuesta del servidor:', respuesta);

      if (respuesta === "Ya has subido tus documentos") {
        console.log('Redirigiendo a la página de aceptar solicitudes');
        window.location.href = '/app/pages/sermototaxista.php';
      } else if (respuesta === "No has subido tus documentos") {
        console.log('Mostrando confirmación para subir documentos');
        var confirmacion = confirm("Es importante llenar el formulario con los documentos de tu vehículo para prestar el servicio de mototaxi. ¿Deseas continuar?");
        if (confirmacion) {
          window.location.href = '/app/pages/registro_de_documentos.php';
        }
      } else {
        console.log('Respuesta inesperada:', respuesta);
      }
    }
  };

  xhr.send("userId=" + encodeURIComponent(userId));
}