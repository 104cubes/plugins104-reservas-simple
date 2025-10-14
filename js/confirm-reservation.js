document.addEventListener('DOMContentLoaded', function () {
    // Seleccionar todos los botones "Confirmar"
    const confirmButtons = document.querySelectorAll('.confirm-button'); // Asegúrate de que los botones tienen esta clase

    confirmButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const form = document.getElementById('reservation-form');
            const errorMessage = document.createElement('div');
            errorMessage.style.color = 'red';
            errorMessage.style.fontWeight = 'bold';
            errorMessage.style.marginBottom = '20px';
            errorMessage.id = 'datetime-error';
        
            form.addEventListener('submit', function (event) {
                const selectedDatetime = document.querySelector('input[name="datetime-radio"]:checked');
                
                // Eliminar mensaje de error anterior
                const existingError = document.getElementById('datetime-error');
                if (existingError) {
                    existingError.remove();
                }
        
                if (!selectedDatetime) {
                    event.preventDefault(); // Evitar envío del formulario
                    errorMessage.textContent = 'Debe seleccionar una fecha y hora.';
                    form.prepend(errorMessage); // Mostrar mensaje sobre el formulario
                }
            });
            const paymentIntentId = this.dataset.paymentIntentId; // ID del PaymentIntent
            const reservationForm = this.closest('form'); // Encuentra el formulario asociado a esta reserva

            // Capturar el pago con Stripe
            fetch(`${sr_vars.ajaxurl}?action=sr_capture_payment_intent`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `payment_intent_id=${encodeURIComponent(paymentIntentId)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Si el pago se captura exitosamente, envía el formulario de confirmación
                        reservationForm.submit();
                    } else {
                        alert('Error al capturar el pago: ' + data.data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al realizar la solicitud.');
                });
        });
    });

   
});

