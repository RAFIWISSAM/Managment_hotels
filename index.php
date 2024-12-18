<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Réservation d'Hôtels</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- En-tête -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">HotelSystem</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="client/login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="client/register.php">rate us</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenu principal -->
    <main>
        <div class="container mt-4">
            <h1>Bienvenue sur notre système de réservation d'hôtels</h1>
            <!-- Formulaire de recherche d'hôtels -->
            <div class="search-form mt-4">
                <form action="search_results.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="destination" class="form-label">Destination</label>
                        <input type="text" class="form-control" id="destination" name="destination" placeholder="Ville ou région" required>
                    </div>
                    <div class="col-md-3">
                        <label for="checkin" class="form-label">Date d'arrivée</label>
                        <input type="date" class="form-control" id="checkin" name="checkin" required>
                    </div>
                    <div class="col-md-3">
                        <label for="checkout" class="form-label">Date de départ</label>
                        <input type="date" class="form-control" id="checkout" name="checkout" required>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p>&copy; 2024 Système de Réservation d'Hôtels. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
