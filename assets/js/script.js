

// Función para alternar visibilidad de la contraseña
function togglePasswordVisibility(id) {
    var input = document.getElementById(id);
    var icon = input.nextElementSibling;

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Función para validar las contraseñas
function validarContraseñas() {
    var nuevaContraseña = document.getElementById("contraseña").value;
    var verificarContraseña = document.getElementById("verificar_contraseña").value; // Asegúrate de tener este campo
    var errorMensaje = document.getElementById("errorMensaje");

    if (nuevaContraseña !== verificarContraseña) {
        errorMensaje.textContent = "Las contraseñas no coinciden.";
        return false;
    }

    return true;
}
