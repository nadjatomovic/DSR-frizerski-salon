<?php
//Funkcija za slanje emaila
function posaljiEmail($email, $ime, $subject, $htmlSadrzaj) {
    
    if (!isset($_ENV['BREVO_API_KEY'], $_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME'])) {
        return false; // env nije pravilno postavljen
    }

    $apiKey   = $_ENV['BREVO_API_KEY'];
    $fromMail = $_ENV['MAIL_FROM'];
    $fromName = $_ENV['MAIL_FROM_NAME'];
    
    $data = [
        'sender' => [
            'name'  => $fromName,
            'email' => $fromMail
        ],
        'to' => [
            [
                'email' => $email,
                'name'  => $ime
            ]
        ],
        'subject' => $subject,
        'htmlContent' => $htmlSadrzaj
    ];
    
    // cURL request ka API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($httpCode == 201); // 201 = uspešno poslato
}
?>
