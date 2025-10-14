<?php
/*
Plugin Name: Simple Reservations
Description: Plugin para gestionar reservas con notificaciones por WhatsApp.
Version: 1.0
Author: 104 CUBES
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

    $allowed_methods = ['manual', 'cash', 'bizum'];
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
        'status' => $payment_method === 'manual' ? 'pending_payment' : 'pending'
    ]);
    // wp_mail(...) removido por privacidad. Añade envío de 
    // email al administrador cuando se crea una reserva.
    wp_redirect(add_query_arg('success', 'true', wp_get_referer()));
    exit;
}
register_activation_hook(__FILE__, 'sr_create_reservations_table');

function sr_enqueue_scripts()
{
    if (is_page()) { // Asegurarse de que solo cargue en páginas
       

        // Localizar ajaxurl
       
        wp_enqueue_script('update-hidden-js', plugin_dir_url(__FILE__) . 'js/update-hidden.js', [], null, true);
        wp_enqueue_script('calculate-price-js', plugin_dir_url(__FILE__) . 'js/calculate-price.js', [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'sr_enqueue_scripts');


add_action('admin_post_sr_confirm_reservation', 'sr_confirm_reservation');

function sr_enqueue_admin_styles() {
    wp_enqueue_style('sr-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'sr_enqueue_admin_styles');
