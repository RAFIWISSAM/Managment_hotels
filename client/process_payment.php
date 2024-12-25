<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $orderID = $input['orderID'] ?? null;
    $id_reservation = $input['id_reservation'] ?? null;

    if (!$orderID || !$id_reservation) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        exit();
    }

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders/$orderID");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . getPaypalAccessToken()
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Erreur lors de la récupération des détails du paiement PayPal.");
        }

        $paymentDetails = json_decode($response, true);

        if ($paymentDetails['status'] === 'COMPLETED') {
            $montant = $paymentDetails['purchase_units'][0]['amount']['value'];
            $reference_transaction = $paymentDetails['id'];

            $stmt = $conn->prepare("INSERT INTO paiements (id_reservation, montant, methode_paiement, statut, reference_transaction) VALUES (:id_reservation, :montant, 'PayPal', 'COMPLETED', :reference_transaction)");
            $stmt->bindParam(':id_reservation', $id_reservation);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':reference_transaction', $reference_transaction);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
                exit();
            } else {
                throw new Exception("Erreur lors de l'enregistrement du paiement.");
            }
        } else {
            throw new Exception("Paiement non complété.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getPaypalAccessToken() {
    $clientId = 'AQU2yQMc033W0otcGH85OYgloKX-2X9uFnkNtNXCne_BPTto1m57W23S7EpurK0-SWZZ2Ze0aibHI57P';
    $clientSecret = 'EMZZC4kTjCsNsIogYsCSIW5Vf1JdASpa-m5PSAY5JsUHX5DXlJnyfQCJX9h4s2BNVIJDzI0AZ-s2nXjR';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    $tokenInfo = json_decode($response, true);
    return $tokenInfo['access_token'] ?? null;
}
?>