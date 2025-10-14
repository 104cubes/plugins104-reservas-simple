<?php
/*
Plugin Name: Simple Reservations
Description: Plugin para gestionar reservas con Stripe y notificaciones por WhatsApp.
Version: 1.0
Author: Luis
*/

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Incluye las funcionalidades del plugin
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-panel.php';
require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';
add_action('admin_post_sr_save_reservation', 'sr_save_reservation');
add_action('admin_post_nopriv_sr_save_reservation', 'sr_save_reservation');

function sr_save_reservation()
{
    global $wpdb;

    // Si es un admin bloqueando horarios
    if (isset($_POST['blocked_times'])) {
        $table_name = $wpdb->prefix . 'reservations';

        foreach ($_POST['blocked_times'] as $blocked_datetime) {
            $datetime_parts = explode(' ', $blocked_datetime);
            $date = $datetime_parts[0];
            $time = $datetime_parts[1];

            $wpdb->insert($table_name, [
                'date' => $date,
                'time' => $time,
                'full_name' => 'Bloqueado',
                'adults' => 0,
                'children' => 0,
                'phone' => '',
                'payment_method' => '',
                'total_price' => 0,
                'status' => 'blocked'
            ]);
        }

        wp_redirect(add_query_arg('success', 'true', wp_get_referer()));
        exit;
    }

    // Procesar reserva normal si no es admin
    $datetime = isset($_POST['datetime']) ? sanitize_text_field($_POST['datetime']) : null;
    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : null;
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 0;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : null;
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : null;
    // **💰 Cálculo del total en euros**
    $adult_price = 7;  // Precio por adulto
    $child_price = 5;  // Precio por niño
    $total_price = ($adults * $adult_price) + ($children * $child_price);  // **SUMA TOTAL**

    if (!$datetime || !$full_name || !$phone || !$payment_method) {
        wp_redirect(add_query_arg('form-error', urlencode('Faltan datos obligatorios.'), wp_get_referer()));
        exit;
    }

    $allowed_methods = ['stripe', 'cash', 'bizum'];
    if (!in_array($payment_method, $allowed_methods)) {
        wp_redirect(add_query_arg('form-error', urlencode('Método de pago no válido.'), wp_get_referer()));
        exit;
    }

    $table_name = $wpdb->prefix . 'reservations';
    $wpdb->insert($table_name, [
        'date' => explode(' ', $datetime)[0],
        'time' => explode(' ', $datetime)[1],
        'full_name' => $full_name,
        'adults' => $adults,
        'children' => $children,
        'phone' => $phone,
        'payment_method' => $payment_method,
        'total_price' => $total_price, // Calcular si es necesario
        'status' => $payment_method === 'stripe' ? 'pending_payment' : 'pending'
    ]);
    wp_mail("a.salgueirocarrera@gmail.com", "Nueva reserva pendiente", "Detalles: \nFecha:" . $datetime . "\nNombre:" . $full_name . "\nAdultos:" . $adults ."\nNiños:" . $children . "\nTeléfono:" . $phone);
    wp_redirect(add_query_arg('success', 'true', wp_get_referer()));
    exit;
}
register_activation_hook(__FILE__, 'sr_create_reservations_table');

function sr_enqueue_scripts()
{
    if (is_page()) { // Asegurarse de que solo cargue en páginas
        wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);

        // Tu archivo personalizado
        wp_enqueue_script(
            'custom-stripe-js',
            plugin_dir_url(__FILE__) . 'js/Stripe.js',
            ['stripe-js'], // Dependencia
            null,
            true
        );

        // Localizar ajaxurl
        wp_localize_script('custom-stripe-js', 'sr_vars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_script('update-hidden-js', plugin_dir_url(__FILE__) . 'js/update-hidden.js', [], null, true);
        wp_enqueue_script('calculate-price-js', plugin_dir_url(__FILE__) . 'js/calculate-price.js', [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'sr_enqueue_scripts');

function sr_create_payment_intent()
{
    $referer = wp_get_referer(); // Obtén la URL del referer
    $base_url = $referer ? strtok($referer, '?') : home_url(); // Elimina la parte del query string

    if (!isset($_POST['amount'])) {
        wp_send_json_error('El monto es obligatorio.', 400);
        
    }

    \Stripe\Stripe::setApiKey('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'); // Reemplaza con tu clave secreta de Stripe

    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => intval($_POST['amount']), // Monto en centavos (por ejemplo, 10 € -> 1000)
            'currency' => 'eur',
            'payment_method_types' => ['card'],
            'capture_method' => 'manual', // IMPORTANTE: Esto permite la captura manual
        ]);

        wp_send_json_success(['client_secret' => $paymentIntent->client_secret]);
        wp_redirect(add_query_arg('success', 'true', $base_url) . '#success-message');
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        wp_send_json_error($e->getMessage(), 500);
        wp_redirect(add_query_arg('form_error', urlencode('Error al procesar el pago'), $base_url) . '#error-message');
        exit;
    }
}

add_action('wp_ajax_sr_create_payment_intent', 'sr_create_payment_intent');
add_action('wp_ajax_nopriv_sr_create_payment_intent', 'sr_create_payment_intent');
function sr_confirm_reservation()
{
    global $wpdb;

    $reservation_id = intval($_GET['id']);
    $table_name = $wpdb->prefix . 'reservations';

    // Recuperar la reserva
    $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $reservation_id));
    if (!$reservation) {
        wp_die('Reserva no encontrada.');
    }

    // Configurar Stripe
    try {
        \Stripe\Stripe::setApiKey('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'); // Reemplaza con tu clave secreta

        // Recuperar el PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::retrieve($reservation->payment_intent_id);

        // Registrar el estado actual del PaymentIntent
        error_log('Estado antes de capturar: ' . $paymentIntent->status);

        // Intentar capturar
        $capturedPaymentIntent = $paymentIntent->capture();

        // Registrar el estado después de capturar
        error_log('Estado después de capturar: ' . $capturedPaymentIntent->status);

        if ($capturedPaymentIntent->status === 'succeeded') {
            // Actualizar la base de datos
            $wpdb->update(
                $table_name,
                ['status' => 'confirmed'],
                ['id' => $reservation_id]
            );

            wp_redirect(admin_url('admin.php?page=sr_reservations&success=true'));
            exit;
        } else {
            wp_die('Error: El PaymentIntent no se pudo capturar correctamente.');
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        wp_die('Error al capturar el pago: ' . $e->getMessage());
    }
}
add_action('admin_post_sr_confirm_reservation', 'sr_confirm_reservation');

function sr_enqueue_admin_styles() {
    wp_enqueue_style('sr-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'sr_enqueue_admin_styles');