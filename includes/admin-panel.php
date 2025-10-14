<?php

function sr_add_admin_menu() {
    add_menu_page(
        'Reservas',                 
        'Reservas',                 
        'manage_options',           
        'sr_reservations',          
        'sr_reservations_page',     
        'dashicons-calendar',       
        6                           
    );

    // Agregar submenú para reservas pasadas
    add_submenu_page(
        'sr_reservations',
        'Reservas Pasadas',
        'Reservas Pasadas',
        'manage_options',
        'sr_past_reservations',
        'sr_past_reservations_page'
    );
}

add_action('admin_menu', 'sr_add_admin_menu');

function sr_reservations_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'reservations';
    $today = date('Y-m-d');

    // Obtener reservas futuras (incluyendo bloqueadas)
    $reservations = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name WHERE date >= %s", $today)
    );

    echo '<div class="wrap">';
    echo '<h1>Reservas Futuras</h1>';
    echo '<a href="' . admin_url('admin.php?page=sr_past_reservations') . '" class="page-title-action">Ver reservas pasadas</a>';
    sr_render_reservations_table($reservations, false);
    echo '</div>';
}

function sr_past_reservations_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'reservations';
    $today = date('Y-m-d');

    // Obtener reservas pasadas (excluyendo bloqueadas)
    $reservations = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name WHERE date < %s AND status != 'blocked'", $today)
    );

    echo '<div class="wrap">';
    echo '<h1>Reservas Pasadas</h1>';
    echo '<a href="' . admin_url('admin.php?page=sr_reservations') . '" class="page-title-action">Volver a reservas futuras</a>';
    sr_render_reservations_table($reservations, true);
    echo '</div>';
}

function sr_render_reservations_table($reservations, $si) {
    if (!$reservations) {
        echo '<div class="notice notice-warning"><p>No hay reservas disponibles.</p></div>';
        return;
    }

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Adultos</th>
                <th>Niños</th>
                <th>Teléfono</th>
                <th>Forma de pago</th>
                <th>Importe</th>
                <th>Estado</th>
                <th>Enviar WhatsApp</th>
                <th>Acciones</th>
            </tr>
          </thead>';
    echo '<tbody>';
    foreach ($reservations as $reservation) {
        $statusIs = $reservation->status;
        if ($reservation->full_name == 'Bloqueado') $statusIs = 'Bloqueado';
        if ($reservation->full_name == 'Bloqueado' && $si) continue;
         echo '<tr>';
        echo '<td>' . esc_html($reservation->full_name) . '</td>';
        echo '<td>' . esc_html($reservation->date) . '</td>';
        echo '<td>' . esc_html($reservation->time) . '</td>';
        echo '<td>' . esc_html($reservation->adults) . '</td>';
        echo '<td>' . esc_html($reservation->children) . '</td>';
        echo '<td>' . esc_html($reservation->phone) . '</td>';
        echo '<td>' . esc_html($reservation->payment_method) . '</td>';
        echo '<td>' . esc_html($reservation->total_price) . '</td>';
        echo '<td>' . esc_html(ucfirst($statusIs)) . '</td>';

        // Botón de WhatsApp
        echo '<td><a href="https://web.whatsapp.com/send?phone=34'.$reservation->phone.'&text=Hola,%20Su%20reserva%20para%20el%20día%20'.$reservation->date.'%20a%20las%20'.$reservation->time.'%20ha%20sido%20confirmada.%20Para%20'.$reservation->adults.'%20adultos%20y%20'.$reservation->children.'%20niños.%0A%0A---%0AEste%20mensaje%20ha%20sido%20enviado%20por%20Granja%20El%20Horreo.%20Tratamos%20sus%20datos%20de%20acuerdo%20con%20nuestra%20política%20de%20privacidad:%20granjaelhorreo.com%2Fprivacidad.%20Si%20desea%20más%20información%20o%20ejercer%20sus%20derechos%20de%20acceso,%20rectificación,%20cancelación%20u%20oposición,%20puede%20contactar%20con%20nosotros%20en%20info%40granjaelhorreo.com." class="button button-secondary" target="_blank"><img src="https://granjaelhorreo.com/wp-content/uploads/2024/12/WhatsApp.svg_.webp" width="30"></a></td>';

        // Botón de eliminación (en ambas listas)
        echo '<td><a href="?page=sr_reservations&action=delete&id=' . $reservation->id . '" class="button button-secondary">Eliminar</a>';
        
        // Solo mostrar botón de confirmar en futuras reservas
        if ($reservation->status !== 'confirmed' && strtotime($reservation->date) >= strtotime(date('Y-m-d'))) {
            echo '<a href="?page=sr_reservations&action=confirm&id=' . $reservation->id . '" class="button button-primary">Confirmar</a>';
        }

        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}