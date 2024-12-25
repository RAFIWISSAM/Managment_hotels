<?php
require_once '../includes/config.php';
include '../includes/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$occupationChambres = [];
$reservationsEffectuees = [];

// Récupérer l'occupation des chambres
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT c.type_chambre, COUNT(r.id_reservation) AS nombre_reservations, SUM(DATEDIFF(r.date_depart, r.date_arrivee)) AS total_nuits FROM chambres c LEFT JOIN reservations r ON c.id_chambre = r.id_chambre GROUP BY c.type_chambre");
    $stmt->execute();
    $occupationChambres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer le total des réservations et les revenus
    $stmt = $conn->prepare("SELECT COUNT(id_reservation) AS total_reservations, SUM(prix_total) AS revenus_totaux FROM reservations");
    $stmt->execute();
    $reservationsEffectuees = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Rapports</h2>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Occupation des Chambres</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Type de Chambre</th>
                            <th>Nombre de Réservations</th>
                            <th>Total de Nuits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($occupationChambres as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['type_chambre']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_reservations']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_nuits']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Statistiques des Réservations</h5>
            </div>
            <div class="card-body">
                <p>Total des Réservations : <strong><?php echo htmlspecialchars($reservationsEffectuees['total_reservations']); ?></strong></p>
                <p>Revenus Totaux : <strong><?php echo htmlspecialchars($reservationsEffectuees['revenus_totaux']); ?> MAD</strong></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
