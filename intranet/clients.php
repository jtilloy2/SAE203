<?php
session_start();

// 1. SÉCURITÉ : Vérification stricte basée sur le code de Julien A.
// Si la session 'user' n'existe pas, on le renvoie vers la page de connexion
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annuaire des Clients - Intranet Vélomat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Intranet Vélomat</a>
            <div class="d-flex text-white align-items-center">
                <span class="me-3">
                    Connecté : <strong><?= htmlspecialchars($_SESSION['user']['nom']); ?></strong> 
                    (<?= htmlspecialchars($_SESSION['user']['role']); ?>)
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Annuaire des Clients</h2>
            <button class="btn btn-success">+ Ajouter un client</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Ville</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 5. LECTURE DU FICHIER : Le chemin exact vers le fichier CSV d'Elouan
                        $fichier_csv = __DIR__ . '/data/clients.csv';
                        
                        if (file_exists($fichier_csv) && ($handle = fopen($fichier_csv, "r")) !== FALSE) {
                            
                            // On saute la première ligne (les en-têtes : Nom,Email,Telephone,Ville)
                            fgetcsv($handle, 1000, ","); 
                            
                            // Lecture ligne par ligne du CSV
                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                // Sécurité : on s'assure que la ligne contient bien 4 colonnes avant d'afficher
                                if (count($data) >= 4) {
                                    echo "<tr>";
                                    echo "<td><strong>" . htmlspecialchars($data[0]) . "</strong></td>"; // Nom
                                    echo "<td><a href='mailto:" . htmlspecialchars($data[1]) . "'>" . htmlspecialchars($data[1]) . "</a></td>"; // Email
                                    echo "<td>" . htmlspecialchars($data[2]) . "</td>"; // Téléphone
                                    echo "<td>" . htmlspecialchars($data[3]) . "</td>"; // Ville
                                    
                                    // Boutons d'action en attente du code d'Elouan
                                    echo "<td>
                                            <button class='btn btn-sm btn-primary'>Éditer</button>
                                            <button class='btn btn-sm btn-danger'>Supprimer</button>
                                          </td>";
                                    echo "</tr>";
                                }
                            }
                            fclose($handle);
                        } else {
                            // Message d'erreur propre si le fichier est introuvable
                            echo "<tr><td colspan='5' class='text-center text-danger'>Fichier clients.csv introuvable dans le dossier data/.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
