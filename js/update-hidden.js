document.addEventListener("DOMContentLoaded", function () {
    const radios = document.querySelectorAll('input[name="datetime-radio"]');
    const hiddenField = document.getElementById('selected-datetime');

    radios.forEach(function (radio) {
        radio.addEventListener("change", function () {
            if (radio.checked) {
                hiddenField.value = radio.value; // Actualiza el campo hidden
                console.log('Fecha y hora seleccionada:', hiddenField.value); // Depuración
            }
        });
    });
});