<?php
// payment.php
require_once '../includes/config.php';
include '../includes/navbar.php';

$amount = $_GET['amount'] ?? 0; // Retrieve the amount to be paid
$id_reservation = $_GET['id_reservation'] ?? 0; // Retrieve the reservation ID
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement</title>
    <script src="https://www.paypal.com/sdk/js?client-id=AQU2yQMc033W0otcGH85OYgloKX-2X9uFnkNtNXCne_BPTto1m57W23S7EpurK0-SWZZ2Ze0aibHI57P&currency=EUR"></script>
    <!-- Replace YOUR_PAYPAL_CLIENT_ID with your actual PayPal client ID -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Paiement pour la réservation</h2>
        <p>Montant à payer: <strong><?php echo htmlspecialchars($amount); ?>£</strong></p>
        <p>Réservation ID: <strong><?php echo htmlspecialchars($id_reservation); ?></strong></p>

        <!-- PayPal Button -->
        <div id="paypal-button-container"></div>
    </div>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                // Create the order with PayPal
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $amount; ?>' // Use the PHP amount variable
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                // Capture the funds
                return actions.order.capture().then(function(details) {
                    // Send payment details to the server
                    fetch('process_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            orderID: data.orderID,
                            id_reservation: '<?php echo $id_reservation; ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Paiement réussi ! Merci ' + details.payer.name.given_name);
                            window.location.href = "confirmation.php?id_reservation=<?php echo $id_reservation; ?>";
                        } else {
                            alert('Une erreur s\'est produite lors du traitement du paiement.');
                        }
                    });
                });
            },
            onError: function(err) {
                console.error('Erreur lors du paiement:', err);
                alert('Une erreur s\'est produite lors du paiement. Veuillez réessayer.');
            }
        }).render('#paypal-button-container');
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
    
</body>
</html>
