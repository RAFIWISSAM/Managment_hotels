<?php
require_once '../includes/config.php';


// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
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
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<header>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
    <div class="container">
        <a class="navbar-brand" href="acceuil.php">HotelSystem</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_hotels.php">Gérer les Hôtels</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_rooms.php">Gérer les Chambres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_reservations.php">Gérer les Réservations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">Gérer les Utilisateurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">Rapports</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user"></i> <!-- Icone de profil -->
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#"><?php echo htmlspecialchars($_SESSION['user']['nom']); ?></a></li>
                            <li><a class="dropdown-item" href="../logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="client/login.php">Connexion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 
</header>
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
