<?php
// ==========================================
// Élouan - Lot 6 : Annuaire du Personnel
// ==========================================
session_start();
$fichier_json = 'data/utilisateurs.json';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuaire du Personnel - Vélomat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🚴 Vélomat Intranet</a>
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

        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="ps-4 py-3">Photo</th>
                                <th scope="col" class="py-3">Nom</th>
                                <th scope="col" class="py-3">Prénom</th>
                                <th scope="col" class="py-3">Poste / Fonction</th>
                                <th scope="col" class="pe-4 py-3">Biographie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (file_exists($fichier_json)) {
                                $contenu_json = file_get_contents($fichier_json);
                                $employes = json_decode($contenu_json, true);

                                if (is_array($employes)) {
                                    foreach ($employes as $emp) {
                                        // On récupère les infos. Si elles n'existent pas, on met un texte par défaut ('—')
                                        $nom       = htmlspecialchars($emp['nom'] ?? '—');
                                        $prenom    = htmlspecialchars($emp['prenom'] ?? '—');
                                        $fonction  = htmlspecialchars($emp['fonction'] ?? 'Non défini');
                                        $bio       = htmlspecialchars($emp['bio'] ?? 'Aucune biographie renseignée.');
                                        $photo_url = htmlspecialchars($emp['photo'] ?? '');

                                        // ASTUCE : Si la photo est vide, on génère un avatar avec les initiales
                                        if (empty($photo_url)) {
                                            $photo_url = "https://ui-avatars.com/api/?name=" . urlencode($prenom . " " . $nom) . "&background=0D8ABC&color=fff&rounded=true&size=128";
                                        }

                                        echo "<tr>";
                                        // Affichage de la photo avec une petite bordure stylée
                                        echo "<td class='ps-4'><img src='{$photo_url}' alt='Photo' class='rounded-circle border border-2 border-white shadow-sm' style='width: 55px; height: 55px; object-fit: cover;'></td>";
                                        echo "<td class='fw-bold text-uppercase'>{$nom}</td>";
                                        echo "<td>{$prenom}</td>";
                                        // Affichage de la fonction dans un joli badge bleu
                                        echo "<td><span class='badge bg-primary px-3 py-2 rounded-pill shadow-sm'>{$fonction}</span></td>";
                                        echo "<td class='pe-4 text-muted' style='max-width: 300px; font-size: 0.9rem; font-style: italic;'>« {$bio} »</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center text-danger py-4'>Erreur : Le format du fichier JSON est invalide.</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-warning py-4'>Le fichier JSON est introuvable.</td></tr>";
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
