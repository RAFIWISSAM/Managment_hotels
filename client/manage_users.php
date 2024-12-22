<?php
require_once '../includes/config.php';
include '../includes/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../client/login.php");
    exit();
}

$errors = [];

// Ajouter un nouvel utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update'])) {
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validation des données
    if (empty($prenom)) {
        $errors[] = 'Le prénom est requis.';
    }
    if (empty($nom)) {
        $errors[] = 'Le nom est requis.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis.';
    }
    if (empty($role)) {
        $errors[] = 'Le rôle est requis.';
    }

    // Si pas d'erreurs, enregistrement dans la base de données
    if (empty($errors)) {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Hachage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insertion dans la base de données
            $stmt = $conn->prepare("INSERT INTO clients (prenom, nom, email, mot_de_passe, role) VALUES (:prenom, :nom, :email, :mot_de_passe, :role)");
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mot_de_passe', $hashedPassword);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                // Redirection vers la liste des utilisateurs après une inscription réussie
                header("Location: manage_users.php");
                exit();
            } else {
                $errors[] = "Erreur lors de l'ajout de l'utilisateur.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion : " . $e->getMessage();
        }
    }
}

// Récupérer la liste des utilisateurs
$stmt = $conn->prepare("SELECT * FROM clients");
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer les Utilisateurs</h2>
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
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="client">Client</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter Utilisateur</button>
        </form>

        <h3 class="mt-5">Liste des Utilisateurs</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($utilisateur['id_client']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['nom']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['role']); ?></td>
                        <td>
                            <a href="manage_users.php?edit=<?php echo $utilisateur['id_client']; ?>" class="btn btn-warning">Modifier</a>
                            <a href="manage_users.php?delete=<?php echo $utilisateur['id_client']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
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
