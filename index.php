<?php
require_once 'includes/config.php';
include 'includes/navbar.php';

// Récupération des données du formulaire
$destination = $_GET['destination'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

// Requête pour rechercher les hôtels disponibles
$query = "SELECT h.nom_hotel, h.adresse, h.description, c.type_chambre, c.prix, h.id_hotel
          FROM hotels h
          JOIN chambres c ON h.id_hotel = c.id_hotel
          WHERE h.nom_hotel LIKE :destination
          AND c.disponibilite = TRUE
          AND c.id_chambre NOT IN (
              SELECT r.id_chambre
              FROM reservations r
              WHERE (r.date_arrivee <= :checkout AND r.date_depart >= :checkin)
          )";

$stmt = $conn->prepare($query);
$stmt->bindValue(':destination', '%' . $destination . '%');
$stmt->bindValue(':checkin', $checkin);
$stmt->bindValue(':checkout', $checkout);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Réservation d'Hôtels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- En-tête -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">HotelSystem</a>
            </div>
        </nav>
    </header>

    <!-- Formulaire de recherche d'hôtels -->
    <div class="container mt-4">
        <h1>Rechercher des Hôtels</h1>
        <form action="" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="destination" class="form-label">Destination</label>
                <input type="text" class="form-control" id="destination" name="destination" placeholder="Ville ou région" value="<?php echo htmlspecialchars($destination); ?>" required>
            </div>
            <div class="col-md-3">
                <label for="checkin" class="form-label">Date d'arrivée</label>
                <input type="date" class="form-control" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" required>
            </div>
            <div class="col-md-3">
                <label for="checkout" class="form-label">Date de départ</label>
                <input type="date" class="form-control" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
            </div>
        </form>
    </div>

    <!-- Résultats de recherche -->
    <div class="container mt-5">
        <h2>Résultats de Recherche</h2>
        <div class="row">
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $hotel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($hotel['adresse']); ?></p>
                                <p class="card-text"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                <p class="card-text">Type de chambre: <?php echo htmlspecialchars($hotel['type_chambre']); ?></p>
                                <p class="card-text">Prix: <?php echo htmlspecialchars($hotel['prix']); ?> €</p>
                                <a href="client/reservation.php?id_hotel=<?php echo $hotel['id_hotel']; ?>&checkin=<?php echo urlencode($checkin); ?>&checkout=<?php echo urlencode($checkout); ?>" class="btn btn-primary">Réserver</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun hôtel trouvé pour votre recherche.</p>
                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p>&copy; 2024 Système de Réservation d'Hôtels. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
