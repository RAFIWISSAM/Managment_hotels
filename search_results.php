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
    <title>Résultats de Recherche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Résultats de Recherche pour "<?php echo htmlspecialchars($destination); ?>"</h2>
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
                                <a href="client/reservation.php?id_hotel=<?php echo $hotel['id_hotel']; ?>" class="btn btn-primary">Réserver</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer.php'; ?>
    
</body>
</html>
