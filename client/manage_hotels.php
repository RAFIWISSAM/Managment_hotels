<?php
require_once '../includes/config.php';

include '../includes/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$errors = [];

// Ajouter un nouvel hôtel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {
    $nom_hotel = $_POST['nom_hotel'];
    $adresse = $_POST['adresse'];
    $description = $_POST['description'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $site_web = $_POST['site_web'];
    $id_ville = $_POST['id_ville']; // Assurez-vous que cette valeur est valide

    $stmt = $conn->prepare("INSERT INTO hotels (id_ville, nom_hotel, adresse, description, email, telephone, site_web) VALUES (:id_ville, :nom_hotel, :adresse, :description, :email, :telephone, :site_web)");
    $stmt->bindParam(':id_ville', $id_ville);
    $stmt->bindParam(':nom_hotel', $nom_hotel);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':site_web', $site_web);

    if ($stmt->execute()) {
        echo "Hôtel ajouté avec succès !";
    } else {
        echo "Erreur lors de l'ajout de l'hôtel.";
    }
}

// Récupérer la liste des hôtels
$stmt = $conn->prepare("SELECT * FROM hotels");
$stmt->execute();
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si un ID d'hôtel a été passé pour la modification
if (isset($_GET['edit'])) {
    $id_hotel = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM hotels WHERE id_hotel = :id_hotel");
    $stmt->bindParam(':id_hotel', $id_hotel);
    $stmt->execute();
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement de la modification
if (isset($_POST['update'])) {
    $nom_hotel = $_POST['nom_hotel'];
    $adresse = $_POST['adresse'];
    $description = $_POST['description'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $site_web = $_POST['site_web'];
    $id_ville = $_POST['id_ville'];
    $id_hotel = $_POST['id_hotel']; // Récupérer l'ID de l'hôtel à modifier
    
    $stmt = $conn->prepare("UPDATE hotels SET nom_hotel = :nom_hotel, adresse = :adresse, description = :description, email = :email, telephone = :telephone, site_web = :site_web, id_ville = :id_ville WHERE id_hotel = :id_hotel");
    $stmt->bindParam(':nom_hotel', $nom_hotel);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':site_web', $site_web);
    $stmt->bindParam(':id_ville', $id_ville);
    $stmt->bindParam(':id_hotel', $id_hotel);

    if ($stmt->execute()) {
        echo "Hôtel modifié avec succès !";
        header("Location: manage_hotels.php"); // Rediriger après modification
        exit();
    } else {
        echo "Erreur lors de la modification de l'hôtel.";
    }
}

// Code pour supprimer un hôtel
if (isset($_GET['delete'])) {
    $id_hotel = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM hotels WHERE id_hotel = :id_hotel");
    $stmt->bindParam(':id_hotel', $id_hotel);

    if ($stmt->execute()) {
        echo "Hôtel supprimé avec succès !";
        header("Location: manage_hotels.php"); // Rediriger après suppression
        exit();
    } else {
        echo "Erreur lors de la suppression de l'hôtel.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Hôtels</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer les Hôtels</h2>
        <?php if (isset($_GET['edit'])): ?>
            <form method="POST" action="">
                <input type="hidden" name="id_hotel" value="<?php echo $hotel['id_hotel']; ?>">
                <div class="mb-3">
                    <label for="nom_hotel" class="form-label">Nom de l'Hôtel</label>
                    <input type="text" class="form-control" id="nom_hotel" name="nom_hotel" value="<?php echo $hotel['nom_hotel']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo $hotel['adresse']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" required><?php echo $hotel['description']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $hotel['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo $hotel['telephone']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="site_web" class="form-label">Site Web</label>
                    <input type="text" class="form-control" id="site_web" name="site_web" value="<?php echo $hotel['site_web']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="id_ville" class="form-label">Ville</label>
                    <select class="form-select" id="id_ville" name="id_ville" required>
                        <option value="">Sélectionnez une ville</option>
                        <option value="1" <?php if ($hotel['id_ville'] == 1) echo 'selected'; ?>>Marrakech</option>
                        <option value="2" <?php if ($hotel['id_ville'] == 2) echo 'selected'; ?>>Casablanca</option>
                        <option value="3" <?php if ($hotel['id_ville'] == 3) echo 'selected'; ?>>Fès</option>
                        <option value="4" <?php if ($hotel['id_ville'] == 4) echo 'selected'; ?>>Agadir</option>
                        <option value="5" <?php if ($hotel['id_ville'] == 5) echo 'selected'; ?>>Tanger</option>
                        <option value="6" <?php if ($hotel['id_ville'] == 6) echo 'selected'; ?>>Essaouira</option>
                        <option value="7" <?php if ($hotel['id_ville'] == 7) echo 'selected'; ?>>Rabat</option>
                        <option value="8" <?php if ($hotel['id_ville'] == 8) echo 'selected'; ?>>Ouarzazate</option>
                        <option value="9" <?php if ($hotel['id_ville'] == 9) echo 'selected'; ?>>Meknès</option>
                        <option value="10" <?php if ($hotel['id_ville'] == 10) echo 'selected'; ?>>Chefchaouen</option>
                        <!-- Ajoutez ici les options de ville -->
                    </select>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Modifier Hôtel</button>
            </form>
        <?php else: ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="nom_hotel" class="form-label">Nom de l'Hôtel</label>
                    <input type="text" class="form-control" id="nom_hotel" name="nom_hotel" required>
                </div>
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" required>
                </div>
                <div class="mb-3">
                    <label for="site_web" class="form-label">Site Web</label>
                    <input type="text" class="form-control" id="site_web" name="site_web" required>
                </div>
                <div class="mb-3">
                    <label for="id_ville" class="form-label">Ville</label>
                    <select class="form-select" id="id_ville" name="id_ville" required>
                        <option value="">Sélectionnez une ville</option>
                        <option value="1">Marrakech</option>
                        <option value="2">Casablanca</option>
                        <option value="3">Fès</option>
                        <option value="4">Agadir</option>
                        <option value="5">Tanger</option>
                        <option value="6">Essaouira</option>
                        <option value="7">Rabat</option>
                        <option value="8">Ouarzazate</option>
                        <option value="9">Meknès</option>
                        <option value="10">Chefchaouen</option>
                        <!-- Ajoutez ici les options de ville -->
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter Hôtel</button>
            </form>
        <?php endif; ?>

        <h3 class="mt-5">Liste des Hôtels</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Description</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Site Web</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hotels as $hotel): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hotel['id_hotel']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['nom_hotel']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['description']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['email']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['telephone']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['site_web']); ?></td>
                        <td>
                            <a href="manage_hotels.php?edit=<?php echo $hotel['id_hotel']; ?>" class="btn btn-warning">Modifier</a>
                            <a href="manage_hotels.php?delete=<?php echo $hotel['id_hotel']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet hôtel ?');">Supprimer</a>
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
