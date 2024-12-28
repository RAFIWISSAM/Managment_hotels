<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous</title>
    <link href="../assets/css/contact.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body{
            background: url('../assets/images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            
           
            
        }
        .contact-container {
            background: rgba(0, 0, 0, 0.6); /* Fond semi-transparent pour améliorer la lisibilité */
            border-radius: 10px;
            
            color: white;
            max-width: 500px; /* Largeur maximale pour limiter l'étirement */
            min-height: 30vh;
            margin: 0 auto; /* Centre horizontalement */
            margin-top: 80px;
            text-align: center;
            padding-top:20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.9); /* Ajout d'une ombre */
            transition: transform 0.3s ease-in-out;
            
        }
        .contact-container:hover {
            transform: scale(1.05);
        }
        .contact-item {
            margin-bottom: 20px;
        }
        .contact-item i {
            font-size: 24px;
            margin-right: 10px;
            color: #c4a081;
        }
        .contact-item span {
            font-size: 18px;
        }
        .contact-item a {
            text-decoration: none;
            color: inherit;
        }
        h2 {
            color: #c4a081;
        }
    </style>
</head>
<header>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
    <div class="container">
        <a class="navbar-brand" href="acceuil.php">HotelSystem</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Accueil</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contactez-nous</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user"></i> <!-- Icone de profil -->
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#"><?php echo htmlspecialchars($_SESSION['user']['nom']); ?></a></li>
                            <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Connexion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 
</header>
<body>
    
    <div class="container contact-container">
        <h2 class="mb-4">Contactez-nous</h2> <!-- Ajout de la classe Bootstrap mb-4 pour la marge -->
        <div class="contact-item">
            <a href="tel:+212123456789">
                <i class="fas fa-phone"></i>
                <span>Téléphone: +212 123 456 789</span>
            </a>
        </div>
        <div class="contact-item">
            <a href="mailto:contact@hotelsystem.com">
                <i class="fas fa-envelope"></i>
                <span>Email: contact@hotelsystem.com</span>
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <footer class="bg-dark text-white mt-auto p-4 text-center fixed-bottom" style="width: 100%; bottom: 0;">
        <p>&copy; 2024 HotelSystem. Tous droits réservés.</p>
    </footer>
</body>
</html>