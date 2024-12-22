<?php
require_once '../includes/config.php';
require_once '../database/functions.php';
include '../includes/navbar.php';

// Récupération de l'ID de l'hôtel
$id_hotel = $_GET['id_hotel'] ?? 0;

// Initialisation des variables
$errors = [];
$hotel = null;

// Vérification de l'ID de l'hôtel
if ($id_hotel) {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des détails de l'hôtel
        $stmt = $conn->prepare("SELECT * FROM hotels WHERE id_hotel = :id_hotel");
        $stmt->bindParam(':id_hotel', $id_hotel);
        $stmt->execute();
        $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Récupération des chambres disponibles pour l'hôtel
        $stmt = $conn->prepare("SELECT * FROM chambres WHERE id_hotel = :id_hotel AND disponibilite = TRUE");
        $stmt->bindParam(':id_hotel', $id_hotel);
        $stmt->execute();
        $chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errors[] = "Erreur de connexion : " . $e->getMessage();
    }
}

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $id_chambre = $_POST['id_chambre'] ?? null; // Récupérer l'ID de la chambre sélectionnée
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $date_arrivee = $_POST['date_arrivee'] ?? '';
    $date_depart = $_POST['date_depart'] ?? ''; 

    // Validation des données
    if (empty($nom)) {
        $errors[] = 'Le nom est requis.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }
    if (empty($date_arrivee)) {
        $errors[] = 'La date d arrivée est requise.';
    }
    if (empty($date_depart)) {
        $errors[] = 'La date de départ est requise.';
    }

    // Vérification de la disponibilité de la chambre
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE id_chambre = :id_chambre AND (date_arrivee < :date_depart AND date_depart > :date_arrivee)");
    $stmt->bindParam(':id_chambre', $id_chambre);
    $stmt->bindParam(':date_arrivee', $date_arrivee);
    $stmt->bindParam(':date_depart', $date_depart);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errors[] = "Chambre non disponible pour les dates sélectionnées.";
    } else {
        // Si pas d'erreurs, enregistrement de la réservation
        try {
            $id_client = 1; // Placeholder pour l'ID du client
            $stmt = $conn->prepare("INSERT INTO reservations (id_client, id_chambre, date_arrivee, date_depart) VALUES (:id_client, :id_chambre, :date_arrivee, :date_depart)");
            $stmt->bindParam(':id_client', $id_client);
            $stmt->bindParam(':id_chambre', $id_chambre);
            $stmt->bindParam(':date_arrivee', $date_arrivee);
            $stmt->bindParam(':date_depart', $date_depart);

            if ($stmt->execute()) {
                // Récupérer le montant total de la réservation
                $montant_total = calculerMontantReservation($id_chambre, $date_arrivee, $date_depart); // Assurez-vous que cette fonction est définie

                // Redirection vers la page de paiement
                header("Location: ./payment.php?amount=" . $montant_total . "&id_reservation=" . $conn->lastInsertId());
                exit();
            } else {
                $errors[] = "Erreur lors de la réservation.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Réservation pour <?php echo htmlspecialchars($hotel['nom_hotel']); ?></h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="date_arrivee" class="form-label">Date d'arrivée</label>
                <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" required>
            </div>
            <div class="mb-3">
                <label for="date_depart" class="form-label">Date de départ</label>
                <input type="date" class="form-control" id="date_depart" name="date_depart" required>
            </div>
            <div class="mb-3">
                <label for="id_chambre" class="form-label">Choisir une Chambre</label>
                <select class="form-select" id="id_chambre" name="id_chambre" required>
                    <option value="">Sélectionnez une chambre</option>
                    <?php foreach ($chambres as $chambre): ?>
                        <option value="<?php echo htmlspecialchars($chambre['id_chambre']); ?>">
                            <?php echo htmlspecialchars($chambre['type_chambre']); ?> - <?php echo htmlspecialchars($chambre['prix']); ?> €
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Réserver</button>
            
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>