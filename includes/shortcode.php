<?php
function sr_reservation_form()
{
    global $wpdb;
    $is_admin = current_user_can('manage_options'); // Verifica si es admin

    echo '<script src="https://www.google.com/recaptcha/api.js?render=6LdkdZkqAAAAAN-AsD59HEG-xcGmHVsUQC2t7C8z"></script>';

    // Mensajes de éxito y error
    if (isset($_GET['form-error'])) {
        echo '<div id="error-message" style="color: red; font-weight: bold; margin-bottom: 100px; padding-left:30px;margin-top:-200px; background-color:white">';
        echo '<h3 style="color:red">' . esc_html(urldecode($_GET['form-error'])) . '</h3>';
        echo '</div>';
    }
    if (isset($_GET['success'])) {
        echo '<div id="success-message" style="color: green; font-weight: bold; margin-bottom: 100px; padding-left:30px;margin-top:-200px; background-color:white">';
        echo '<h3 style="color:green">¡Reserva completada con éxito! Le avisaremos en cuanto esté <u>confirmada</u><br>¿Quiere realizar otra reserva?</h3>';
        echo '</div>';
    }

    // Configuración de localización
    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');

    // Variables iniciales
    $start_date = isset($_GET['start_date']) ? strtotime(sanitize_text_field($_GET['start_date'])) : strtotime(date('Y-m-d'));
    $end_date = strtotime('+6 days', $start_date);

    // Consultar horarios ocupados
    $start_date_formatted = date('Y-m-d', $start_date);
    $end_date_formatted = date('Y-m-d', $end_date);
    $table_name = $wpdb->prefix . 'reservations';
    $confirmed_reservations = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT date, time FROM $table_name WHERE date BETWEEN %s AND %s",
            $start_date_formatted,
            $end_date_formatted
        ),
        ARRAY_A
    );

    // Convertir en un array de fácil uso
    $occupied_slots = [];
    foreach ($confirmed_reservations as $reservation) {
        $occupied_slots[$reservation['date']][$reservation['time']] = true;
    }

    // Contenedor de reserva
    ob_start();
    echo '<div id="sr-reservation" class="sr-reservation-container">';
    
    echo '<h3>Seleccione un horario</h3>';

    // Calendario y navegación
    echo '<div class="sr-navigation">';
    echo '<label for="date-picker"></label><br>';
    echo '<input type="date" id="date-picker" style="padding: 5px; font-size: 16px; width:120px">';
    echo '<a href="?start_date=' . date('Y-m-d', strtotime('-7 days', $start_date)) . '" class="button">Semana anterior</a>';
    echo '<a href="?start_date=' . date('Y-m-d', strtotime('+7 days', $start_date)) . '" class="button">Semana siguiente</a>';
    echo '</div>';

    // Formulario
    echo '<form id="reservation-form" action="' . admin_url('admin-post.php') . '" method="POST">';
    echo '<input type="hidden" name="action" value="sr_save_reservation">';
    echo '<input type="hidden" id="selected-datetime" name="datetime" value="">';
    echo '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">';

    // Tabla de horarios
    echo '<table class="wp-list-table widefat fixed striped">';
    $date_formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, 'EEEE, d \'de\' MMMM');
// Obtener la fecha y hora actual + 24h
$current_timestamp = time();
$limit_timestamp = strtotime('+24 hours', $current_timestamp);

    echo '<thead><tr>';
    for ($i = 0; $i < 7; $i++) {
        $current_date = (new DateTime())->setTimestamp($start_date)->modify("+$i days");
        $formatted_date = $date_formatter->format($current_date);
        echo '<th>' . ucfirst($formatted_date);

        // Botón para bloquear un día entero (solo para admins)
        if ($is_admin) {
            echo '<br><button type="button" class="block-day-button" data-date="' . date('Y-m-d', $current_date->getTimestamp()) . '">Bloquear todo el día</button>';
        }

        echo '</th>';
    }
    echo '</tr></thead>';

    for ($hour = 10; $hour <= 18; $hour += 2) {
        echo '<tr>';
        for ($i = 0; $i < 7; $i++) {
            $current_date = strtotime("+$i days", $start_date);
            $date = date('Y-m-d', $current_date);
            $time = $hour . ':00';
            $datetime = $date . ' ' . $time;
            $datetime_timestamp = strtotime($datetime);
            $limit_timestamp = time() + (24 * 3600); // 24 horas en segundos
            $is_occupied = isset($occupied_slots[$date][$time]);
            // Verifica si es menor a 24h

            $id = 'datetime-' . $date . '-' . $hour;

            echo '<td>';
            if ($is_occupied  || $datetime_timestamp < $limit_timestamp) {
                echo '<span style="color: red;">Ocupado</span>';
            } else {
                echo '<label>';
                if ($is_admin) {
                    echo '<input type="checkbox" class="block-checkbox" data-date="' . $date . '" name="blocked_times[]" value="' . $datetime . '"> ';
                } else {
                    echo '<input type="radio" id="' . $id . '" name="datetime-radio" value="' . $datetime . '"> ';
                }
                echo $time;
                echo '</label>';
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';

    // **Campos de formulario**
    echo '<label for="full_name">Nombre completo:</label>';
    echo '<input type="text" name="full_name" id="full_name" required placeholder="Tu nombre completo">';

    echo '<label for="adults">Cantidad de adultos:</label>';
    echo '<input type="number" name="adults" id="adults" min="1" value="1" required>';

    echo '<label for="children">Cantidad de niños:</label>';
    echo '<input type="number" name="children" id="children" min="0" value="0" required>';

    echo '<label for="phone">Teléfono:</label>';
    echo '<input type="tel" name="phone" id="phone" required pattern="[0-9]{9}" placeholder="123456789">';

    // Selector de métodos de pago
    echo '<div style="margin-top: 20px; margin-bottom: 10px;">';
    echo '<label for="payment_method"><strong>Método de pago:</strong></label><br>';
    echo '<input type="radio" name="payment_method" id="payment_method_cash" value="cash" required>';
    echo '<label for="payment_method_cash">Pago en efectivo</label><br>';
    echo '<input type="radio" name="payment_method" id="payment_method_bizum" value="bizum" required>';
    echo '<label for="payment_method_bizum">Bizum</label>';
    echo '</div>';

    echo '<p><strong>Precio total:</strong> <span id="total-price">0</span> €</p>';
   
    echo '<label for="terms_conditions"> He leído y acepto las condiciones y la política de privacidad.<br>Consiento recibir whatsapps para informaciones relacionadas con la reserva</label><br>';
    echo '<input type="checkbox" name="terms_conditions" id="terms_conditions" required><br>';
    // **Botón de envío**
    if ($is_admin) {
        echo '<button type="submit" class="button button-primary">Bloquear horarios</button>';
    } else {
        echo '<button type="submit" class="button button-primary">Reservar</button>';
    }

    echo '</form>';

    // **Script para bloquear días completos**
    echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".block-day-button").forEach(button => {
                button.addEventListener("click", function () {
                    let date = this.getAttribute("data-date");
                    document.querySelectorAll(".block-checkbox[data-date=\'" + date + "\']").forEach(checkbox => {
                        checkbox.checked = true;
                    });
                });
            });

        const datePicker = document.getElementById("date-picker");

        datePicker.addEventListener("change", function () {
            const selectedDate = this.value;
            if (selectedDate) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set("start_date", selectedDate);
                window.location.href = currentUrl.toString().replace("#sr-reservation", "") + "#sr-reservation";
            }
        });
    });
    grecaptcha.ready(function() {
            grecaptcha.execute("6LdkdZkqAAAAAN-AsD59HEG-xcGmHVsUQC2t7C8z", {action: "submit"}).then(function(token) {
                document.getElementById("g-recaptcha-response").value = token;
            });
        });
    </script>';
    
    return ob_get_clean();
}

add_shortcode('simple_reservation_form', 'sr_reservation_form');