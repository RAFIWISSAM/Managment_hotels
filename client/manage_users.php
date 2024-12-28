<?php
require_once '../includes/config.php';
include '../includes_admin/navbar.php';

// Vérifiez si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
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
                echo "Utilisateur ajouté avec succès !";
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

// Modifier un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id_client = $_POST['id_client'];
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
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
    if (empty($role)) {
        $errors[] = 'Le rôle est requis.';
    }

    // Si pas d'erreurs, mise à jour dans la base de données
    if (empty($errors)) {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Mise à jour dans la base de données
            $stmt = $conn->prepare("UPDATE clients SET prenom = :prenom, nom = :nom, email = :email, role = :role WHERE id_client = :id_client");
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id_client', $id_client);

            if ($stmt->execute()) {
                // Redirection vers la liste des utilisateurs après une mise à jour réussie
                echo "Utilisateur modifié avec succès !";
                header("Location: manage_users.php");
                exit();
            } else {
                $errors[] = "Erreur lors de la modification de l'utilisateur.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion : " . $e->getMessage();
        }
    }
}

// Supprimer un utilisateur
if (isset($_GET['delete'])) {
    $id_client = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM clients WHERE id_client = :id_client");
    $stmt->bindParam(':id_client', $id_client);

    if ($stmt->execute()) {
        echo "Utilisateur supprimé avec succès !";
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Erreur lors de la suppression de l'utilisateur.";
    }
}

// Récupérer la liste des utilisateurs
$stmt = $conn->prepare("SELECT * FROM clients");
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les informations d'un utilisateur pour modification
if (isset($_GET['edit'])) {
    $id_client = $_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM clients WHERE id_client = :id_client");
    $stmt->bindParam(':id_client', $id_client);
    $stmt->execute();
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Utilisateurs</title>
    <link href="../assets/css/hotels.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer les Utilisateurs</h2>
        <div class="add-user-btn">
            <a href="#addUserForm" class="btn btn-primary">Ajouter Utilisateur</a>
        </div>
        <div class="table-container">
            <h3>Liste des Utilisateurs</h3>
            <table class="table table-striped">
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
                                <a href="manage_users.php?edit=<?php echo $utilisateur['id_client']; ?>#editUserForm" class="btn btn-warning">Modifier</a>
                                <a href="manage_users.php?delete=<?php echo $utilisateur['id_client']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="editUserForm" class="mt-5">
            <?php if (isset($_GET['edit'])): ?>
                <h3>Modifier un utilisateur</h3>
                <form method="POST" action="">
                    <input type="hidden" name="id_client" value="<?php echo $utilisateur['id_client']; ?>">
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $utilisateur['prenom']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $utilisateur['nom']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $utilisateur['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="client" <?php if ($utilisateur['role'] == 'client') echo 'selected'; ?>>Client</option>
                            <option value="admin" <?php if ($utilisateur['role'] == 'admin') echo 'selected'; ?>>Administrateur</option>
                        </select>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary">Modifier Utilisateur</button>
                </form>
            <?php else: ?>
                <h3>Ajouter un utilisateur</h3>
                <form id="addUserForm" method="POST" action="">
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
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <footer class="bg-dark text-white mt-auto p-4 text-center fixed-bottom" style="width: 100%; bottom: 0;">
    <p>&copy; 2024 HotelSystem. Tous droits réservés.</p>
</footer>
</body>
</html>