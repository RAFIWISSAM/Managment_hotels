<?php
require_once '../includes/config.php';
include '../includes/navbar.php';

// Initialisation des variables
$errors = [];
$message = '';

// Récupérer l'ID de l'hôtel depuis l'URL
$id_hotel = null;

// Vérifiez si l'ID de l'hôtel est valide


// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = 1; // Remplacez par l'ID réel du client connecté
    $note = $_POST['note'] ?? null;
    $commentaire = $_POST['commentaire'] ?? '';

    // Validation des données
    if (empty($note)) {
        $errors[] = "La note est requise.";
    }
    if (empty($commentaire)) {
        $errors[] = "Le commentaire est requis.";
    }

    // Si pas d'erreurs, enregistrement de l'avis
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO avis_clients (id_client, id_hotel, note, commentaire) VALUES (:id_client, :id_hotel, :note, :commentaire)");
            $stmt->bindParam(':id_client', $id_client);
            $stmt->bindParam(':id_hotel', $id_hotel);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':commentaire', $commentaire);

            if ($stmt->execute()) {
                $message = "Merci pour votre avis!";
            } else {
                $errors[] = "Erreur lors de l'enregistrement de l'avis.";
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
    <title>Laisser un Avis</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .star {
            font-size: 2rem;
            color: #ccc;
            cursor: pointer;
        }
        .star.selected {
            color: gold;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Laisser un Avis</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="id_hotel" value="<?php echo htmlspecialchars($id_hotel); ?>">
            <div class="mb-3">
                <label class="form-label">Note</label>
                <div id="star-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" name="note" id="note" required>
            </div>
            <div class="mb-3">
                <label for="commentaire" class="form-label">Commentaire</label>
                <textarea class="form-control" id="commentaire" name="commentaire" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const stars = document.querySelectorAll('.star');
        const noteInput = document.getElementById('note');

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const value = star.getAttribute('data-value');
                noteInput.value = value;

                stars.forEach(s => {
                    s.classList.remove('selected');
                });
                for (let i = 0; i < value; i++) {
                    stars[i].classList.add('selected');
                }
            });
        });
    </script>
    <?php include '../includes/footer.php'; ?>

</body>
</html>