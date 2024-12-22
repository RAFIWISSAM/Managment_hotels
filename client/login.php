
<?php
require_once '../includes/config.php';
include '../includes/navbar.php';

// Initialisation des variables
$errors = [];

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation des données
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis.';
    }

    // Si pas d'erreurs, vérification dans la base de données
    if (empty($errors)) {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérification de l'utilisateur
            $stmt = $conn->prepare("SELECT id_client, mot_de_passe, role FROM clients WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Démarrer une session utilisateur
                session_start();
                $_SESSION['user_id'] = $user['id_client'];
                $_SESSION['role'] = $user['role']; // Stocker le rôle dans la session

                // Rediriger vers la page appropriée en fonction du rôle
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
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
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Connexion</h2>
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
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
            <a href="register.php" class="btn btn-link">Pas encore inscrit? Inscription</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
