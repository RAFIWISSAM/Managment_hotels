<?php
require_once '../includes/config.php';
include '../includes/navbar.php';

// Initialisation des variables
$errors = [];
$reservations = [];

// Récupération de l'ID de l'utilisateur
$id_client = 1; // Utiliser l'ID de l'utilisateur connecté pour le test

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des réservations de l'utilisateur
    $stmt = $conn->prepare("SELECT r.*, h.nom_hotel FROM reservations r JOIN chambres c ON r.id_chambre = c.id_chambre JOIN hotels h ON c.id_hotel = h.id_hotel WHERE r.id_client = :id_client");
    $stmt->bindParam(':id_client', $id_client);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Erreur de connexion : " . $e->getMessage();
}

// Annulation d'une réservation
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id_reservation = :id_reservation AND id_client = :id_client");
        $stmt->bindParam(':id_reservation', $cancel_id);
        $stmt->bindParam(':id_client', $id_client);
        if ($stmt->execute()) {
            header("Location: my_reservations.php"); // Rediriger après annulation
            exit();
        } else {
            $errors[] = "Erreur lors de l'annulation de la réservation.";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur de connexion : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Mes Réservations</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
            <p>Aucune réservation trouvée.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom de l'Hôtel</th>
                        <th>Date d'Arrivée</th>
                        <th>Date de Départ</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['nom_hotel']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date_arrivee']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date_depart']); ?></td>
                            <td>
                                <a href="?cancel_id=<?php echo $reservation['id_reservation']; ?>" class="btn btn-danger">Annuler</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>

</body>
</html>
