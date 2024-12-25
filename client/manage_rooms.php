<?php
require_once '../includes/config.php';
include '../includes/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$errors = [];

// Récupérer la liste des hôtels pour le formulaire
$stmt_hotel = $conn->prepare("SELECT id_hotel, nom_hotel FROM hotels ORDER BY id_hotel ASC");
$stmt_hotel->execute();
$hotels = $stmt_hotel->fetchAll(PDO::FETCH_ASSOC);

// Ajouter une nouvelle chambre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {
    $id_hotel = $_POST['id_hotel'];
    $type_chambre = $_POST['type_chambre'];
    $prix = $_POST['prix'];
    $nombre_lits = $_POST['nombre_lits'];
    $description = $_POST['description'];
    $disponibilite = $_POST['disponibilite']; // 1 pour disponible, 0 pour non disponible

    $stmt = $conn->prepare("INSERT INTO chambres (id_hotel, type_chambre, prix, nombre_lits, description, disponibilite) VALUES (:id_hotel, :type_chambre, :prix, :nombre_lits, :description, :disponibilite)");
    $stmt->bindParam(':id_hotel', $id_hotel);
    $stmt->bindParam(':type_chambre', $type_chambre);
    $stmt->bindParam(':prix', $prix);
    $stmt->bindParam(':nombre_lits', $nombre_lits);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':disponibilite', $disponibilite);

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
    $nombre_lits = $_POST['nombre_lits'];
    $description = $_POST['description'];
    $disponibilite = $_POST['disponibilite'];

    $stmt = $conn->prepare("UPDATE chambres SET id_hotel = :id_hotel, type_chambre = :type_chambre, prix = :prix, nombre_lits = :nombre_lits, description = :description, disponibilite = :disponibilite WHERE id_chambre = :id_chambre");
    $stmt->bindParam(':id_hotel', $id_hotel);
    $stmt->bindParam(':type_chambre', $type_chambre);
    $stmt->bindParam(':prix', $prix);
    $stmt->bindParam(':nombre_lits', $nombre_lits);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':disponibilite', $disponibilite);
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
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer les Chambres</h2>
        <?php if (isset($chambre)): ?>
            <form method="POST" action="">
                <input type="hidden" name="id_chambre" value="<?php echo $chambre['id_chambre']; ?>">
                <div class="mb-3">
                    <label for="id_hotel" class="form-label">Hôtel</label>
                    <select class="form-select" id="id_hotel" name="id_hotel" required>
                        <option value="">Sélectionnez un hôtel</option>
                        <?php foreach ($hotels as $hotel): ?>
                            <option value="<?php echo $hotel['id_hotel']; ?>" <?php if ($hotel['id_hotel'] == $chambre['id_hotel']) echo 'selected'; ?>><?php echo htmlspecialchars($hotel['nom_hotel']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="type_chambre" class="form-label">Type de Chambre</label>
                    <input type="text" class="form-control" id="type_chambre" name="type_chambre" value="<?php echo $chambre['type_chambre']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="prix" class="form-label">Prix</label>
                    <input type="number" class="form-control" id="prix" name="prix" value="<?php echo $chambre['prix']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nombre_lits" class="form-label">Nombre de Lits</label>
                    <input type="number" class="form-control" id="nombre_lits" name="nombre_lits" value="<?php echo $chambre['nombre_lits']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" required><?php echo $chambre['description']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="disponibilite" class="form-label">Disponibilité</label>
                    <select class="form-select" id="disponibilite" name="disponibilite" required>
                        <option value="1" <?php if ($chambre['disponibilite'] == 1) echo 'selected'; ?>>Disponible</option>
                        <option value="0" <?php if ($chambre['disponibilite'] == 0) echo 'selected'; ?>>Non Disponible</option>
                    </select>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Modifier Chambre</button>
            </form>
        <?php else: ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="id_hotel" class="form-label">Hôtel</label>
                    <select class="form-select" id="id_hotel" name="id_hotel" required>
                        <option value="">Sélectionnez un hôtel</option>
                        <?php foreach ($hotels as $hotel): ?>
                            <option value="<?php echo $hotel['id_hotel']; ?>"><?php echo htmlspecialchars($hotel['nom_hotel']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="type_chambre" class="form-label">Type de Chambre</label>
                    <input type="text" class="form-control" id="type_chambre" name="type_chambre" required>
                </div>
                <div class="mb-3">
                    <label for="prix" class="form-label">Prix</label>
                    <input type="number" class="form-control" id="prix" name="prix" required>
                </div>
                <div class="mb-3">
                    <label for="nombre_lits" class="form-label">Nombre de Lits</label>
                    <input type="number" class="form-control" id="nombre_lits" name="nombre_lits" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="disponibilite" class="form-label">Disponibilité</label>
                    <select class="form-select" id="disponibilite" name="disponibilite" required>
                        <option value="1">Disponible</option>
                        <option value="0">Non Disponible</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter Chambre</button>
            </form>
        <?php endif; ?>

        <h3 class="mt-5">Liste des Chambres</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hôtel</th>
                    <th>Type de Chambre</th>
                    <th>Prix</th>
                    <th>Nombre de Lits</th>
                    <th>Description</th>
                    <th>Disponibilité</th>
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
                        <td><?php echo htmlspecialchars($chambre['nombre_lits']); ?></td>
                        <td><?php echo htmlspecialchars($chambre['description']); ?></td>
                        <td><?php echo $chambre['disponibilite'] ? 'Disponible' : 'Non Disponible'; ?></td>
                        <td>
                            <a href="manage_rooms.php?edit=<?php echo $chambre['id_chambre']; ?>" class="btn btn-warning">Modifier</a>
                            <a href="manage_rooms.php?delete=<?php echo $chambre['id_chambre']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette chambre ?');">Supprimer</a>
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
