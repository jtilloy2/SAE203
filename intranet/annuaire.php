<?php
// ==========================================
// Élouan - Lot 6 : Annuaire du Personnel
// ==========================================

// 1. Sécurité : On démarre la session pour l'intranet
session_start();

// 2. Définition du chemin vers ton fichier JSON
$fichier_json = 'data/utilisateurs.json';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuaire du Personnel - Intranet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">MonEntreprise Intranet</a>
            <div class="navbar-nav">
                <a class="nav-link active" href="annuaire.php">Annuaire</a>
                <a class="nav-link" href="fichiers.php">Fichiers Partagés</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="text-primary fw-bold">Annuaire des Collaborateurs</h1>
            <p class="text-muted">Liste officielle des salariés de l'entreprise (12 profils minimum requis)</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="ps-4">Photo</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénom</th>
                                <th scope="col">Poste / Fonction</th>
                                <th scope="col" class="pe-4">Biographie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // 3. Vérification de l'existence du fichier JSON
                            if (file_exists($fichier_json)) {
                                
                                // 4. Lecture du contenu du fichier JSON
                                $contenu_json = file_get_contents($fichier_json);
                                
                                // 5. Décodage du JSON en tableau associatif PHP (true)
                                $employes = json_decode($contenu_json, true);

                                // 6. On vérifie que le décodage a fonctionné et qu'on a bien un tableau
                                if (is_array($employes)) {
                                    
                                    // 7. Boucle foreach pour parcourir chaque employé (Interdiction du SQL !)
                                    foreach ($employes as $emp) {
                                        
                                        // Sécurisation des données contre les failles XSS
                                        // (on vérifie si la clé existe avec l'opérateur ?? pour éviter les notices PHP)
                                        $nom       = htmlspecialchars($emp['nom'] ?? '');
                                        $prenom    = htmlspecialchars($emp['prenom'] ?? '');
                                        $fonction  = htmlspecialchars($emp['fonction'] ?? '');
                                        $photo_url = htmlspecialchars($emp['photo'] ?? '');
                                        $bio       = htmlspecialchars($emp['bio'] ?? '');

                                        echo "<tr>";
                                        // Affichage de la photo (this-person-does-not-exist.com) formatée proprement
                                        echo "<td class='ps-4'><img src='{$photo_url}' alt='Photo' class='rounded-circle' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                                        echo "<td class='fw-bold'>{$nom}</td>";
                                        echo "<td>{$prenom}</td>";
                                        echo "<td><span class='badge bg-secondary'>{$fonction}</span></td>";
                                        echo "<td class='pe-4 text-muted' style='max-width: 300px; font-size: 0.9rem;'>{$bio}</td>";
                                        echo "</tr>";
                                    }
                                    
                                } else {
                                    echo "<tr><td colspan='5' class='text-center text-danger py-4'>Erreur : Le format ou la structure du fichier JSON est invalide.</td></tr>";
                                }
                            } else {
                                // Message d'alerte si ton fichier n'est pas trouvé au bon endroit
                                echo "<tr><td colspan='5' class='text-center text-warning py-4'>Le fichier <code>data/utilisateurs.json</code> n'existe pas. Crée-le pour afficher les employés !</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
