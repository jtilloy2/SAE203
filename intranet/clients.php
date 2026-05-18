<?php
session_start();

// 1. SÉCURITÉ : Vérification de la session
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 2. GESTION DES PERMISSIONS
$role = isset($_SESSION['user']['role']) ? strtolower($_SESSION['user']['role']) : 'salarie';
$peut_ajouter = in_array($role, ['admin', 'direction', 'manager']);
$peut_modifier = in_array($role, ['admin', 'direction', 'manager']);
$peut_supprimer = in_array($role, ['admin', 'direction']);
$afficher_actions = true; // On affiche toujours la colonne pour le bouton "Télécharger"

// 3. LOGIQUE MÉTIER (CRUD CSV)
$fichier_csv = __DIR__ . '/data/clients.csv';

// Lecture de tout le CSV dans un tableau en mémoire
$clients = [];
if (file_exists($fichier_csv) && ($handle = fopen($fichier_csv, "r")) !== FALSE) {
    // Utilisation du point-virgule (;) comme séparateur standard Excel/France
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $clients[] = $data;
    }
    fclose($handle);
}

// GESTION DES TÉLÉCHARGEMENTS ET SUPPRESSIONS (GET)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Action : Télécharger la fiche client (Exigence Cahier des charges)
    if ($_GET['action'] === 'download' && isset($clients[$id])) {
        $c = array_pad($clients[$id], 5, '');
        $contenu = "FICHE CLIENT - JOSSEL\n--------------------\n";
        $contenu .= "Nom : " . $c[0] . "\nEmail : " . $c[1] . "\nTéléphone : " . $c[2] . "\nAdresse : " . $c[3] . "\nVille : " . $c[4];
        
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="fiche_client_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $c[0]) . '.txt"');
        echo $contenu;
        exit;
    }

    // Action : Supprimer
    if ($_GET['action'] === 'delete' && $peut_supprimer && isset($clients[$id]) && $id !== 0) {
        unset($clients[$id]); // On supprime la ligne
        $clients = array_values($clients); // On réindexe le tableau proprement
        
        // On réécrit tout le fichier avec le séparateur ;
        $handle = fopen($fichier_csv, 'w');
        foreach ($clients as $ligne) {
            fputcsv($handle, $ligne, ";");
        }
        fclose($handle);
        header('Location: client.php?msg=deleted');
        exit;
    }
}

// GESTION DE L'AJOUT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add' && $peut_ajouter) {
    $nouveau_client = [
        $_POST['nom'],
        $_POST['email'],
        $_POST['telephone'],
        $_POST['adresse'],
        $_POST['ville']
    ];
    
    // Écriture à la fin du fichier avec le séparateur ;
    $handle = fopen($fichier_csv, 'a');
    fputcsv($handle, $nouveau_client, ";");
    fclose($handle);
    
    header('Location: client.php?msg=added');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annuaire des Clients - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* INTÉGRATION DE LA CHARTE GRAPHIQUE */
        :root {
            --c-fond: #FFFFFF;            
            --c-texte: #1A1A1A;           
            --c-structurant: #E0E0E0;     
            --c-action: #0056b3;          
            --c-secondaire: #4A4A4A;      
            --c-danger: #dc3545;          
        }

        body {
            background-color: var(--c-fond);
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        .navbar-custom { background-color: var(--c-fond); border-bottom: 1px solid var(--c-structurant); }
        .navbar-custom .navbar-brand { color: var(--c-texte); font-weight: 700; }
        h2 { color: var(--c-texte); letter-spacing: -0.5px; }
        .text-muted { color: var(--c-secondaire) !important; }

        .btn-action { background-color: var(--c-action); color: var(--c-fond); border: none; font-weight: 500; }
        .btn-action:hover { background-color: #004494; color: var(--c-fond); }
        
        .btn-structurant { border: 1px solid var(--c-structurant); color: var(--c-texte); background: transparent; }
        .btn-structurant:hover { background-color: var(--c-structurant); }

        .btn-danger-custom { border: 1px solid var(--c-structurant); color: var(--c-danger); background: transparent; }
        .btn-danger-custom:hover { background-color: var(--c-danger); color: var(--c-fond); border-color: var(--c-danger); }

        .badge-role { background-color: var(--c-texte); color: var(--c-fond); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }

        .table-container { border: 1px solid var(--c-structurant); border-radius: 6px; background-color: var(--c-fond); overflow: hidden; }
        .table { margin-bottom: 0; }
        .table thead th { background-color: #FAFAFA; border-bottom: 1px solid var(--c-structurant); color: var(--c-secondaire); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; padding: 1rem; border-top: none; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--c-structurant); }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #FAFAFA; }

        a.email-link { color: var(--c-action); text-decoration: none; font-weight: 500; }
        a.email-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand">JOSSEL <span class="fw-light text-muted">| Annuaire Clients</span></span>
            <div class="d-flex align-items-center">
                <span class="me-4 text-muted small">
                    <?= htmlspecialchars($_SESSION['user']['nom'] ?? 'Utilisateur'); ?> 
                    <span class="badge badge-role ms-2"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Salarié'); ?></span>
                </span>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-danger">Le client a été supprimé avec succès.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success">Le client a été ajouté avec succès.</div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold m-0">Annuaire des Clients</h2>
                <p class="text-muted small m-0 mt-1">Liste et gestion de la clientèle (Base CSV - 30 clients minimum requis)</p>
            </div>
            <?php if ($peut_ajouter): ?>
                <button class="btn btn-action btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addClientModal">+ Ajouter un client</button>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // On vérifie si on a des clients au-delà de la ligne d'en-tête
                    if (count($clients) > 1) {
                        foreach ($clients as $id => $data) {
                            if ($id === 0) continue; // On ignore la ligne d'en-tête du CSV
                            
                            // Sécurité : évite les "Undefined offset" si une colonne manque dans le CSV
                            $data = array_pad($data, 5, '');
                            
                            // On n'affiche la ligne que si elle contient au moins un nom
                            if (!empty(trim($data[0]))) {
                                echo "<tr>";
                                echo "<td><strong style='color: var(--c-texte);'>" . htmlspecialchars($data[0]) . "</strong></td>";
                                echo "<td><a href='mailto:" . htmlspecialchars($data[1]) . "' class='email-link'>" . htmlspecialchars($data[1]) . "</a></td>";
                                echo "<td><span class='text-muted'>" . htmlspecialchars($data[2]) . "</span></td>";
                                echo "<td><span class='text-muted'>" . htmlspecialchars($data[3]) . "</span></td>";
                                echo "<td><span class='text-muted'>" . htmlspecialchars($data[4]) . "</span></td>";
                                
                                echo "<td class='text-end'>";
                                // Tout le monde peut télécharger la fiche
                                echo "<a href='?action=download&id={$id}' class='btn btn-structurant btn-sm me-1'>Fiche</a>";
                                
                                if ($peut_modifier) {
                                    echo "<button class='btn btn-structurant btn-sm me-1' disabled title='En cours de dev'>Éditer</button>";
                                }
                                if ($peut_supprimer) {
                                    echo "<a href='?action=delete&id={$id}' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce client ?\");' class='btn btn-danger-custom btn-sm'>Supprimer</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center p-5 text-muted'>Aucun client trouvé dans le fichier data/clients.csv ou fichier vide.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($peut_ajouter): ?>
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="client.php">
              <input type="hidden" name="action" value="add">
              <div class="modal-header">
                <h5 class="modal-title">Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-structurant" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-action">Enregistrer</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
