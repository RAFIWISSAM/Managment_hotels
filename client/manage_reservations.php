<?php
require_once '../includes/config.php';
include '../includes_admin/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$errors = [];

// Ajouter une nouvelle réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {
    $id_client = $_POST['id_client'];
    $id_chambre = $_POST['id_chambre'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $prix_total = $_POST['prix_total'];
    $statut = $_POST['statut'];

    $stmt = $conn->prepare("INSERT INTO reservations (id_client, id_chambre, date_arrivee, date_depart, prix_total, statut) VALUES (:id_client, :id_chambre, :date_arrivee, :date_depart, :prix_total, :statut)");
    $stmt->bindParam(':id_client', $id_client);
    $stmt->bindParam(':id_chambre', $id_chambre);
    $stmt->bindParam(':date_arrivee', $date_arrivee);
    $stmt->bindParam(':date_depart', $date_depart);
    $stmt->bindParam(':prix_total', $prix_total);
    $stmt->bindParam(':statut', $statut);

    if ($stmt->execute()) {
        echo "Réservation ajoutée avec succès !";
    } else {
        echo "Erreur lors de l'ajout de la réservation.";
    }
}

// Récupérer la liste des réservations
$stmt = $conn->prepare("SELECT * FROM reservations");
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si un ID de réservation a été passé pour la modification
if (isset($_GET['edit'])) {
    $id_reservation = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id_reservation = :id_reservation");
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement de la modification
if (isset($_POST['update'])) {
    $id_reservation = $_POST['id_reservation'];
    $id_client = $_POST['id_client'];
    $id_chambre = $_POST['id_chambre'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $prix_total = $_POST['prix_total'];
    $statut = $_POST['statut'];

    $stmt = $conn->prepare("UPDATE reservations SET id_client = :id_client, id_chambre = :id_chambre, date_arrivee = :date_arrivee, date_depart = :date_depart, prix_total = :prix_total, statut = :statut WHERE id_reservation = :id_reservation");
    $stmt->bindParam(':id_client', $id_client);
    $stmt->bindParam(':id_chambre', $id_chambre);
    $stmt->bindParam(':date_arrivee', $date_arrivee);
    $stmt->bindParam(':date_depart', $date_depart);
    $stmt->bindParam(':prix_total', $prix_total);
    $stmt->bindParam(':statut', $statut);
    $stmt->bindParam(':id_reservation', $id_reservation);

    if ($stmt->execute()) {
        echo "Réservation modifiée avec succès !";
        header("Location: manage_reservations.php"); // Rediriger après modification
        exit();
    } else {
        echo "Erreur lors de la modification de la réservation.";
    }
}

// Supprimer une réservation
if (isset($_GET['delete'])) {
    $id_reservation = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id_reservation = :id_reservation");
    $stmt->bindParam(':id_reservation', $id_reservation);

    if ($stmt->execute()) {
        echo "Réservation supprimée avec succès !";
        header("Location: manage_reservations.php"); // Rediriger après suppression
        exit();
    } else {
        echo "Erreur lors de la suppression de la réservation.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Réservations</title>
    <link href="../assets/css/hotels.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="container mt-5">
        <h2>Gérer les Réservations</h2>
        <div class="add-reservation-btn">
            <a href="#addReservationForm" class="btn btn-primary">Ajouter Réservation</a>
        </div>
        <div class="table-container">
            <h3>Liste des Réservations</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Chambre</th>
                        <th>Date d'arrivée</th>
                        <th>Date de départ</th>
                        <th>Prix Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['id_reservation']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['id_client']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['id_chambre']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date_arrivee']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date_depart']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['prix_total']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['statut']); ?></td>
                            <td>
                                <a href="manage_reservations.php?edit=<?php echo $reservation['id_reservation']; ?>#editReservationForm" class="btn btn-warning">Modifier</a>
                                <a href="manage_reservations.php?delete=<?php echo $reservation['id_reservation']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="editReservationForm" class="mt-5">
            <?php if (isset($_GET['edit'])): ?>
                <h3>Modifier une réservation</h3>
                <form method="POST" action="">
                    <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                    <div class="mb-3">
                        <label for="id_client" class="form-label">Client</label>
                        <input type="text" class="form-control" id="id_client" name="id_client" value="<?php echo $reservation['id_client']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_chambre" class="form-label">Chambre</label>
                        <input type="text" class="form-control" id="id_chambre" name="id_chambre" value="<?php echo $reservation['id_chambre']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_arrivee" class="form-label">Date d'arrivée</label>
                        <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" value="<?php echo $reservation['date_arrivee']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_depart" class="form-label">Date de départ</label>
                        <input type="date" class="form-control" id="date_depart" name="date_depart" value="<?php echo $reservation['date_depart']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="prix_total" class="form-label">Prix Total</label>
                        <input type="text" class="form-control" id="prix_total" name="prix_total" value="<?php echo $reservation['prix_total']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut</label>
                        <input type="text" class="form-control" id="statut" name="statut" value="<?php echo $reservation['statut']; ?>" required>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Modifier Réservation</button>
                </form>
            <?php else: ?>
                <h3>Ajouter une réservation</h3>
                <form id="addReservationForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="id_client" class="form-label">Client</label>
                        <input type="text" class="form-control" id="id_client" name="id_client" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_chambre" class="form-label">Chambre</label>
                        <input type="text" class="form-control" id="id_chambre" name="id_chambre" required>
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
                        <label for="prix_total" class="form-label">Prix Total</label>
                        <input type="text" class="form-control" id="prix_total" name="prix_total" required>
                    </div>
                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut</label>
                        <input type="text" class="form-control" id="statut" name="statut" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter Réservation</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <footer class="bg-dark text-white mt-auto p-4 text-center fixed-bottom" style="width: 100%; bottom: 0;">
    <p>&copy; 2024 HotelSystem. Tous droits réservés.</p>
</footer>
</body>
</html>