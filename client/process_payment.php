<?php
// process_payment.php
require_once '../includes/config.php';
require_once 'payment_config.php';

function processPayment($amount, $currency, $token) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, PAYMENT_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount' => $amount,
        'currency' => $currency,
        'token' => $token,
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . PAYMENT_API_KEY,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        return json_decode($response, true); // Retourne le résultat du paiement
    } else {
        throw new Exception("Erreur de paiement: " . $response);
    }
}

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    $currency = 'EUR'; // Vous pouvez ajuster la devise si nécessaire
    $token = $_POST['card_token'] ?? '';

    try {
        $paymentResponse = processPayment($amount, $currency, $token);
        // Gérer la réponse du paiement ici (par exemple, enregistrer le statut du paiement dans la base de données)
        // Redirection ou message de succès
        echo "Paiement réussi: " . htmlspecialchars($paymentResponse['message']);
    } catch (Exception $e) {
        echo "Erreur: " . htmlspecialchars($e->getMessage());
    }
}
?>