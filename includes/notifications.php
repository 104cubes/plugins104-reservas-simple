<?php 
function sr_send_whatsapp_meta($to, $template, $reservation) {
    $url = "https://graph.facebook.com/v21.0/466446299894041/messages";
    $access_token = "EAARZBtgFQ0YkBOZBwrr1sNjEn71in8YC4xi7RPDZATd67A9zFRooQ4vcjdDneq7abMtgVcdhN39GYTDalpxC0Daqz69n4OdV0HcwzmMaKhrEMnI4JX6d6GDKJo426BG7RE4yQkykYZACpo6p45MAMJKJa824kepr0Q5ndUJZAZC7mTXSMWeZAYSbfpZAUyZAD93NdV2HsQ5QUgZBkJDsf5Utsbm8BpKi2Lpspph24ZD";
    $parameters = [
        [
            "type" => "text",
            "text" => $reservation['full_name'] . ' - ' . $reservation['phone']
        ],
        [
            "type" => "text",
            "text" => "{$reservation['date']} a las {$reservation['time']}"
        ],
        [
            "type" => "text",
            "text" => "Adultos: {$reservation['adults']}, Niños: {$reservation['children']}"
        ]
    ];
    $data = [
        "messaging_product" => "whatsapp",
        "to" => "$to",
        "type" => "template",
        "template" => [
            "name" => "hello_world",
            "language" => [
                "code" => "en_US"
            ]
        ]
    ];
    /*$data = [
        "messaging_product" => "whatsapp",
        "to" => "$to",
        "type" => "template",
        "template" => [
            "name" => "hello_word",
            "language" => [
                "code" => "en_EN" // Asegúrate de que el código del idioma sea correcto
            ],
            "components" => [
                [
                    "type" => "body",
                    "parameters" => $parameters
                ]
            ]
        ]
    ];*/

    $headers = [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Error al enviar el mensaje de WhatsApp: $error");
        return false; // Devuelve false si hay un error
    }
    $response_data = json_decode($response, true);
    if (!isset($response_data['messages']) || empty($response_data['messages'])) {
        error_log("Error en la respuesta de la API de WhatsApp: " . print_r($response_data, true));
        return false; // Devuelve false si la respuesta no contiene mensajes válidos
    }

    return true; // Devuelve true si todo salió bien
}