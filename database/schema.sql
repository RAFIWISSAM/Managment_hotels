-- Création de la base de données
CREATE DATABASE IF NOT EXISTS hotel_management;
USE hotel_management;

-- Table des villes
CREATE TABLE IF NOT EXISTS villes (
    id_ville INT PRIMARY KEY AUTO_INCREMENT,
    nom_ville VARCHAR(100) NOT NULL,
    pays VARCHAR(100) NOT NULL,
    region VARCHAR(100)
);

-- Table des hôtels
CREATE TABLE IF NOT EXISTS hotels (
    id_hotel INT PRIMARY KEY AUTO_INCREMENT,
    id_ville INT,
    nom_hotel VARCHAR(100) NOT NULL,
    adresse TEXT NOT NULL,
    description TEXT,
    email VARCHAR(100),
    telephone VARCHAR(20),
    site_web VARCHAR(100),
    nb_etoiles INT,
    FOREIGN KEY (id_ville) REFERENCES villes(id_ville)
);


-- Table des chambres
CREATE TABLE IF NOT EXISTS chambres (
    id_chambre INT PRIMARY KEY AUTO_INCREMENT,
    id_hotel INT,
    type_chambre VARCHAR(50) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    disponibilite BOOLEAN DEFAULT true,
    nombre_lits INT NOT NULL,
    description TEXT,
    FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel)
);

-- Table des clients
CREATE TABLE IF NOT EXISTS clients (
    id_client INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    points_fidelite INT DEFAULT 0,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des réservations
CREATE TABLE IF NOT EXISTS reservations (
    id_reservation INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    id_chambre INT,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    statut VARCHAR(20) DEFAULT 'en_attente',
    date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client),
    FOREIGN KEY (id_chambre) REFERENCES chambres(id_chambre)
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS paiements (
    id_paiement INT PRIMARY KEY AUTO_INCREMENT,
    id_reservation INT,
    montant DECIMAL(10,2) NOT NULL,
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
    methode_paiement VARCHAR(50),
    statut VARCHAR(20),
    reference_transaction VARCHAR(100),
    FOREIGN KEY (id_reservation) REFERENCES reservations(id_reservation)
);


-- Table des photos
CREATE TABLE IF NOT EXISTS photos (
    id_photo INT PRIMARY KEY AUTO_INCREMENT,
    id_hotel INT,
    id_chambre INT,
    url_photo VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel),
    FOREIGN KEY (id_chambre) REFERENCES chambres(id_chambre)
);

-- Table des avis clients
CREATE TABLE IF NOT EXISTS avis_clients (
    id_avis INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT,
    id_hotel INT,
    note INT NOT NULL,
    commentaire TEXT,
    date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client),
    FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel)
);

-- Déclencheur pour mettre à jour la disponibilité des chambres
DELIMITER //
CREATE TRIGGER after_reservation_insert 
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    UPDATE chambres 
    SET disponibilite = FALSE 
    WHERE id_chambre = NEW.id_chambre;
END//

-- Déclencheur BEFORE INSERT pour vérifier la disponibilité de la chambre
CREATE TRIGGER before_reservation_insert
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    IF NOT verifierDisponibiliteChambre(NEW.id_chambre, NEW.date_arrivee, NEW.date_depart) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Chambre non disponible pour les dates sélectionnées';
    END IF;
END//

-- Déclencheur AFTER DELETE pour rétablir la disponibilité de la chambre
CREATE TRIGGER after_reservation_delete
AFTER DELETE ON reservations
FOR EACH ROW
BEGIN
    UPDATE chambres
    SET disponibilite = TRUE
    WHERE id_chambre = OLD.id_chambre;
END//

-- Fonction pour vérifier la disponibilité d'une chambre
CREATE FUNCTION verifierDisponibiliteChambre(chambre_id INT, date_debut DATE, date_fin DATE) RETURNS BOOLEAN
BEGIN
    DECLARE disponible BOOLEAN;
    SET disponible = NOT EXISTS (
        SELECT 1 FROM reservations
        WHERE id_chambre = chambre_id
        AND statut = 'en_attente'
        AND (
            (date_arrivee <= date_debut AND date_depart > date_debut) OR
            (date_arrivee < date_fin AND date_depart >= date_fin) OR
            (date_arrivee >= date_debut AND date_depart <= date_fin)
        )
    );
    RETURN disponible;
END//

-- Fonction pour calculer le montant d'une réservation
CREATE FUNCTION calculerMontantReservation(chambre_id INT, date_debut DATE, date_fin DATE) RETURNS DECIMAL(10,2)
BEGIN
    DECLARE montant DECIMAL(10,2);
    DECLARE prix_par_nuit DECIMAL(10,2);
    DECLARE nb_nuits INT;
    SET prix_par_nuit = (SELECT prix FROM chambres WHERE id_chambre = chambre_id);
    SET nb_nuits = DATEDIFF(date_fin, date_debut);
    SET montant = prix_par_nuit * nb_nuits;
    RETURN montant;
END//

-- Fonction pour calculer le taux d'occupation
CREATE FUNCTION CalculerTauxOccupation(hotel_id INT) RETURNS DECIMAL(5,2)
BEGIN
    DECLARE taux DECIMAL(5,2);
    DECLARE total_chambres INT;
    DECLARE chambres_occupees INT;
    SELECT COUNT(*) INTO total_chambres FROM chambres WHERE id_hotel = hotel_id;
    SELECT COUNT(*) INTO chambres_occupees FROM reservations WHERE id_chambre IN (SELECT id_chambre FROM chambres WHERE id_hotel = hotel_id) AND statut = 'complete';
    SET taux = (chambres_occupees / total_chambres) * 100;
    RETURN taux;
END//

-- Procédure pour ajouter une réservation
CREATE PROCEDURE ajouterReservation(IN client_id INT, IN chambre_id INT, IN date_debut DATE, IN date_fin DATE)
BEGIN
    IF verifierDisponibiliteChambre(chambre_id, date_debut, date_fin) THEN
        INSERT INTO reservations (id_client, id_chambre, date_arrivee, date_depart, prix_total, statut)
        VALUES (client_id, chambre_id, date_debut, date_fin, calculerMontantReservation(chambre_id, date_debut, date_fin), 'en_attente');
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Chambre non disponible pour les dates sélectionnées';
    END IF;
END//

-- Procédure pour annuler une réservation
CREATE PROCEDURE annulerReservation(IN reservation_id INT)
BEGIN
    UPDATE reservations
    SET statut = 'annulee'
    WHERE id_reservation = reservation_id;

    UPDATE chambres
    SET disponibilite = TRUE
    WHERE id_chambre = (SELECT id_chambre FROM reservations WHERE id_reservation = reservation_id);
END//

-- Procédure pour générer des rapports
CREATE PROCEDURE genererRapports()
BEGIN
    SELECT h.nom_hotel, COUNT(r.id_reservation) AS total_reservations, SUM(r.prix_total) AS total_revenus
    FROM hotels h
    JOIN chambres c ON h.id_hotel = c.id_hotel
    JOIN reservations r ON c.id_chambre = r.id_chambre
    WHERE r.statut = 'complete'
    GROUP BY h.nom_hotel;
END//
DELIMITER ;
