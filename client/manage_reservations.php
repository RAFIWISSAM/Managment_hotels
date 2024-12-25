<?php
require_once '../includes/config.php';
include '../includes/navbar.php';


// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$errors = [];

// Ajouter une nouvelle réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {
    $id_chambre = $_POST['id_chambre'];
    $id_client = $_POST['id_client']; 
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $prix_total = $_POST['prix_total']; 
    $statut = 'active'; 

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

// Modifier une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id_reservation = $_POST['id_reservation'];
    $id_chambre = $_POST['id_chambre'];
    $id_client = $_POST['id_client'];
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $prix_total = $_POST['prix_total'];
    $statut = $_POST['statut'];

    $stmt = $conn->prepare("UPDATE reservations SET id_client = :id_client, id_chambre = :id_chambre, date_arrivee = :date_arrivee, date_depart = :date_depart, prix_total = :prix_total, statut = :statut WHERE id_reservation = :id_reservation");
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->bindParam(':id_chambre', $id_chambre);
    $stmt->bindParam(':id_client', $id_client);
    $stmt->bindParam(':date_arrivee', $date_arrivee);
    $stmt->bindParam(':date_depart', $date_depart);
    $stmt->bindParam(':prix_total', $prix_total);
    $stmt->bindParam(':statut', $statut);

    if ($stmt->execute()) {
        echo "Réservation modifiée avec succès !";
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
    } else {
        echo "Erreur lors de la suppression de la réservation.";
    }
}

// Récupérer la liste des réservations
$stmt = $conn->prepare("SELECT * FROM reservations");
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les informations d'une réservation pour modification
if (isset($_GET['edit'])) {
    $id_reservation = $_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id_reservation = :id_reservation");
    $stmt->bindParam(':id_reservation', $id_reservation);
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Récupérer la liste des chambres disponibles
$stmt_chambres = $conn->prepare("SELECT id_chambre FROM chambres WHERE disponibilite = 1");
$stmt_chambres->execute();
$chambres_disponibles = $stmt_chambres->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des clients disponibles
$stmt_clients = $conn->prepare("SELECT id_client, prenom, nom FROM clients");
$stmt_clients->execute();
$clients_disponibles = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Réservations</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer les Réservations</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="id_client" class="form-label">Client</label>
                <select class="form-select" id="id_client" name="id_client" required>
                    <option value="">Sélectionnez un client</option>
                    <?php foreach ($clients_disponibles as $client): ?>
                        <option value="<?php echo $client['id_client']; ?>" <?php if (isset($reservation) && $reservation['id_client'] == $client['id_client']) echo 'selected'; ?>><?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_chambre" class="form-label">Chambre</label>
                <select class="form-select" id="id_chambre" name="id_chambre" required>
                    <option value="">Sélectionnez une chambre</option>
                    <?php foreach ($chambres_disponibles as $chambre): ?>
                        <option value="<?php echo $chambre['id_chambre']; ?>" <?php if (isset($reservation) && $reservation['id_chambre'] == $chambre['id_chambre']) echo 'selected'; ?>><?php echo htmlspecialchars($chambre['id_chambre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="date_arrivee" class="form-label">Date d'Arrivée</label>
                <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" required value="<?php if (isset($reservation)) echo $reservation['date_arrivee']; ?>">
            </div>
            <div class="mb-3">
                <label for="date_depart" class="form-label">Date de Départ</label>
                <input type="date" class="form-control" id="date_depart" name="date_depart" required value="<?php if (isset($reservation)) echo $reservation['date_depart']; ?>">
            </div>
            <div class="mb-3">
                <label for="prix_total" class="form-label">Prix Total</label>
                <input type="number" class="form-control" id="prix_total" name="prix_total" required value="<?php if (isset($reservation)) echo $reservation['prix_total']; ?>">
            </div>
            <div class="mb-3">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut" required>
                    <option value="en_attente" <?php if (isset($reservation) && $reservation['statut'] == 'en_attente') echo 'selected'; ?>>En Attente</option>
                    <option value="active" <?php if (isset($reservation) && $reservation['statut'] == 'active') echo 'selected'; ?>>Actif</option>
                    <option value="inactive" <?php if (isset($reservation) && $reservation['statut'] == 'inactive') echo 'selected'; ?>>Inactif</option>
                </select>
            </div>
            <?php if (isset($reservation)): ?>
                <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                <button type="submit" class="btn btn-primary" name="update">Modifier Réservation</button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary">Ajouter Réservation</button>
            <?php endif; ?>
        </form>

        <h3 class="mt-5">Liste des Réservations</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Client</th>
                    <th>ID Chambre</th>
                    <th>Date d'Arrivée</th>
                    <th>Date de Départ</th>
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
                            <a href="manage_reservations.php?edit=<?php echo $reservation['id_reservation']; ?>" class="btn btn-warning">Modifier</a>
                            <a href="manage_reservations.php?delete=<?php echo $reservation['id_reservation']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>

</body>
</html>
