<?php

require_once '../includes/config.php';
include '../includes_admin/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$errors = [];

// Ajouter une nouvelle chambre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {
    $id_hotel = $_POST['id_hotel'];
    $type_chambre = $_POST['type_chambre'];
    $prix = $_POST['prix'];
    $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;
    $nombre_lits = $_POST['nombre_lits'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits, description) VALUES (:id_hotel, :type_chambre, :prix, :disponibilite, :nombre_lits, :description)");
    $stmt->bindParam(':id_hotel', $id_hotel);
    $stmt->bindParam(':type_chambre', $type_chambre);
    $stmt->bindParam(':prix', $prix);
    $stmt->bindParam(':disponibilite', $disponibilite);
    $stmt->bindParam(':nombre_lits', $nombre_lits);
    $stmt->bindParam(':description', $description);

    if ($stmt->execute()) {
        echo "Chambre ajoutée avec succès !";
    } else {
        echo "Erreur lors de l'ajout de la chambre.";
    }
}

// Récupérer la liste des chambres
$stmt = $conn->prepare("SELECT * FROM chambres");
$stmt->execute();
$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si un ID de chambre a été passé pour la modification
if (isset($_GET['edit'])) {
    $id_chambre = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM chambres WHERE id_chambre = :id_chambre");
    $stmt->bindParam(':id_chambre', $id_chambre);
    $stmt->execute();
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement de la modification
if (isset($_POST['update'])) {
    $id_chambre = $_POST['id_chambre'];
    $id_hotel = $_POST['id_hotel'];
    $type_chambre = $_POST['type_chambre'];
    $prix = $_POST['prix'];
    $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;
    $nombre_lits = $_POST['nombre_lits'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE chambres SET id_hotel = :id_hotel, type_chambre = :type_chambre, prix = :prix, disponibilite = :disponibilite, nombre_lits = :nombre_lits, description = :description WHERE id_chambre = :id_chambre");
    $stmt->bindParam(':id_hotel', $id_hotel);
    $stmt->bindParam(':type_chambre', $type_chambre);
    $stmt->bindParam(':prix', $prix);
    $stmt->bindParam(':disponibilite', $disponibilite);
    $stmt->bindParam(':nombre_lits', $nombre_lits);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':id_chambre', $id_chambre);

    if ($stmt->execute()) {
        echo "Chambre modifiée avec succès !";
        header("Location: manage_rooms.php"); // Rediriger après modification
        exit();
    } else {
        echo "Erreur lors de la modification de la chambre.";
    }
}

// Code pour supprimer une chambre
if (isset($_GET['delete'])) {
    $id_chambre = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM chambres WHERE id_chambre = :id_chambre");
    $stmt->bindParam(':id_chambre', $id_chambre);

    if ($stmt->execute()) {
        echo "Chambre supprimée avec succès !";
        header("Location: manage_rooms.php"); // Rediriger après suppression
        exit();
    } else {
        echo "Erreur lors de la suppression de la chambre.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Chambres</title>
    <link href="../assets/css/hotels.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="container mt-5">
        <h2>Gérer les Chambres</h2>
        <div class="add-room-btn">
            <a href="#addRoomForm" class="btn btn-primary">Ajouter Chambre</a>
        </div>
        <div class="table-container">
            <h3>Liste des Chambres</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hôtel</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Disponibilité</th>
                        <th>Nombre de lits</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chambres as $chambre): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($chambre['id_chambre']); ?></td>
                            <td><?php echo htmlspecialchars($chambre['id_hotel']); ?></td>
                            <td><?php echo htmlspecialchars($chambre['type_chambre']); ?></td>
                            <td><?php echo htmlspecialchars($chambre['prix']); ?></td>
                            <td><?php echo htmlspecialchars($chambre['disponibilite'] ? 'Oui' : 'Non'); ?></td>
                            <td><?php echo htmlspecialchars($chambre['nombre_lits']); ?></td>
                            <td><?php echo htmlspecialchars($chambre['description']); ?></td>
                            <td>
                                <a href="manage_rooms.php?edit=<?php echo $chambre['id_chambre']; ?>#editRoomForm" class="btn btn-warning">Modifier</a>
                                <a href="manage_rooms.php?delete=<?php echo $chambre['id_chambre']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette chambre ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="editRoomForm" class="mt-5">
            <?php if (isset($_GET['edit'])): ?>
                <h3>Modifier une chambre</h3>
                <form method="POST" action="">
                    <input type="hidden" name="id_chambre" value="<?php echo $chambre['id_chambre']; ?>">
                    <div class="mb-3">
                        <label for="id_hotel" class="form-label">Hôtel</label>
                        <input type="text" class="form-control" id="id_hotel" name="id_hotel" value="<?php echo $chambre['id_hotel']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="type_chambre" class="form-label">Type de Chambre</label>
                        <input type="text" class="form-control" id="type_chambre" name="type_chambre" value="<?php echo $chambre['type_chambre']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="prix" class="form-label">Prix</label>
                        <input type="text" class="form-control" id="prix" name="prix" value="<?php echo $chambre['prix']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="disponibilite" class="form-label">Disponibilité</label>
                        <input type="checkbox" id="disponibilite" name="disponibilite" <?php echo $chambre['disponibilite'] ? 'checked' : ''; ?>>
                    </div>
                    <div class="mb-3">
                        <label for="nombre_lits" class="form-label">Nombre de lits</label>
                        <input type="text" class="form-control" id="nombre_lits" name="nombre_lits" value="<?php echo $chambre['nombre_lits']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" ><?php echo $chambre['description']; ?></textarea>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Modifier Chambre</button>
                </form>
            <?php else: ?>
                <h3>Ajouter une chambre</h3>
                <form id="addRoomForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="id_hotel" class="form-label">Hôtel</label>
                        <input type="text" class="form-control" id="id_hotel" name="id_hotel" required>
                    </div>
                    <div class="mb-3">
                        <label for="type_chambre" class="form-label">Type de Chambre</label>
                        <input type="text" class="form-control" id="type_chambre" name="type_chambre" required>
                    </div>
                    <div class="mb-3">
                        <label for="prix" class="form-label">Prix</label>
                        <input type="text" class="form-control" id="prix" name="prix" required>
                    </div>
                    <div class="mb-3">
                        <label for="disponibilite" class="form-label">Disponibilité</label>
                        <input type="checkbox" id="disponibilite" name="disponibilite">
                    </div>
                    <div class="mb-3">
                        <label for="nombre_lits" class="form-label">Nombre de lits</label>
                        <input type="text" class="form-control" id="nombre_lits" name="nombre_lits" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter Chambre</button>
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