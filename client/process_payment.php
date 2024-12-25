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
    $currency = 'DH'; // Vous pouvez ajuster la devise si nécessaire
    $token = $_POST['card_token'] ?? '';
    $id_reservation = $_POST['id_reservation'] ?? 0; // Récupérer l'ID de la réservation

    try {
        $paymentResponse = processPayment($amount, $currency, $token);
        
        // Si le paiement est réussi, insérer les données dans la table paiements
        if ($paymentResponse['success']) { // Assurez-vous que la réponse contient un indicateur de succès
            $stmt = $conn->prepare("INSERT INTO paiements (id_reservation, montant, methode_paiement, statut, reference_transaction) VALUES (:id_reservation, :montant, :methode_paiement, :statut, :reference_transaction)");
            $stmt->bindParam(':id_reservation', $id_reservation);
            $stmt->bindParam(':montant', $amount);
            $stmt->bindParam(':methode_paiement', $paymentResponse['method']); // Assurez-vous que cette clé existe dans la réponse
            $stmt->bindParam(':statut', $paymentResponse['status']); // Assurez-vous que cette clé existe dans la réponse
            $stmt->bindParam(':reference_transaction', $paymentResponse['transaction_id']); // Assurez-vous que cette clé existe dans la réponse
            
            if ($stmt->execute()) {
                // Redirection vers la page des réservations
                header("Location: my_reservations.php");
                exit();
            } else {
                throw new Exception("Erreur lors de l'enregistrement du paiement.");
            }
        } else {
            throw new Exception("Erreur lors du traitement du paiement.");
        }
    } catch (Exception $e) {
        echo "Erreur: " . htmlspecialchars($e->getMessage());
    }
}
?>