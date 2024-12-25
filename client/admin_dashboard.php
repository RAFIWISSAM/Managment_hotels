<?php

require_once '../includes/config.php';
include '../includes/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../client/index.php");
    exit();
}

// Code pour récupérer les hôtels, chambres, réservations, etc.

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau d'Administration</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Panneau d'Administration</h2>
        <nav>
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link" href="manage_hotels.php">Gérer les Hôtels</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_rooms.php">Gérer les Chambres</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_reservations.php">Gérer les Réservations</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_users.php">Gérer les Utilisateurs</a></li>
                <li class="nav-item"><a class="nav-link" href="reports.php">Rapports</a></li>
            </ul>
        </nav>
        <!-- Contenu dynamique ici -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>

</body>
</html>
