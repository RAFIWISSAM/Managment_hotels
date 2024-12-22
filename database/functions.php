<?php
function calculerMontantReservation($id_chambre, $date_arrivee, $date_depart) {
    global $conn; // Assurez-vous que la connexion à la base de données est accessible

    // Récupérer le prix par nuit de la chambre
    $stmt = $conn->prepare("SELECT prix FROM chambres WHERE id_chambre = :id_chambre");
    $stmt->bindParam(':id_chambre', $id_chambre);
    $stmt->execute();
    $prix_par_nuit = $stmt->fetchColumn();

    // Calculer le nombre de nuits
    $nb_nuits = (strtotime($date_depart) - strtotime($date_arrivee)) / (60 * 60 * 24);

    // Calculer le montant total
    $montant_total = $prix_par_nuit * $nb_nuits;

    return $montant_total;
}
?>