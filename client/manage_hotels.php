<?php

require_once '../includes/config.php';
include '../includes/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../client/login.php");
    exit();
}

$errors = [];

// Ajouter un nouvel hôtel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Hôtels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer les Hôtels</h2>
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
                    <option value="">Rabat</option>
                    <option value="">Marrakech</option>
                    <option value="">Ouarzazate</option>
                    <option value="">Casablanca</option>
                    <option value="">Fès</option>
                    <option value="">Agadir</option>
                    <option value="">Tanger</option>
                    <option value="">Essaouira</option>
                    <option value="">Meknès</option>
                    <option value="">Chefchaouen</option>
                    <!-- Ajoutez ici les options de ville -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter Hôtel</button>
        </form>

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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
