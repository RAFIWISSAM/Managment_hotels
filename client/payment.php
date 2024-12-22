<?php
// payment.php
require_once '../includes/config.php';

$amount = $_GET['amount'] ?? 0;
$id_reservation = $_GET['id_reservation'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Paiement pour la réservation</h2>
        <p>Montant à payer: <?php echo htmlspecialchars($amount); ?> Dh</p>
        <form method="POST" action="process_payment.php">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
            <input type="hidden" name="id_reservation" value="<?php echo htmlspecialchars($id_reservation); ?>">
            <div class="mb-3">
                <label for="card_token" class="form-label">Token de carte de crédit</label>
                <input type="text" class="form-control" id="card_token" name="card_token" required>
            </div>
            <button type="submit" class="btn btn-primary">Payer</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>