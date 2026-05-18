<?php
session_start();

// 1. SÉCURITÉ : Vérification
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 2. GESTION DES PERMISSIONS
$role = isset($_SESSION['user']['role']) ? strtolower($_SESSION['user']['role']) : 'salarie';
$peut_ajouter = in_array($role, ['admin', 'direction', 'manager']);
$peut_modifier = in_array($role, ['admin', 'direction', 'manager']);
$peut_supprimer = in_array($role, ['admin', 'direction']);
$afficher_actions = true; 

// 3. LOGIQUE CSV : LECTURE
$fichier_csv = __DIR__ . '/data/clients.csv';
$clients = [];
if (file_exists($fichier_csv) && ($handle = fopen($fichier_csv, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $clients[] = $data;
    }
    fclose($handle);
}

// ACTIONS GET (TÉLÉCHARGEMENT ET SUPPRESSION)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($_GET['action'] === 'download' && isset($clients[$id])) {
        $c = $clients[$id];
        $contenu = "FICHE CLIENT - JOSSEL\n--------------------\nNom : " . ($c[0] ?? "") . "\nEmail : " . ($c[1] ?? "") . "\nTéléphone : " . ($c[2] ?? "") . "\nVille : " . ($c[3] ?? "");
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="fiche_' . ($c[0] ?? "client") . '.txt"');
        echo $contenu;
        exit;
    }

    if ($_GET['action'] === 'delete' && $peut_supprimer && isset($clients[$id]) && $id !== 0) {
        unset($clients[$id]);
        $clients = array_values($clients);
        $handle = fopen($fichier_csv, 'w');
        foreach ($clients as $ligne) { fputcsv($handle, $ligne, ","); }
        fclose($handle);
        header('Location: client.php?msg=deleted');
        exit;
    }
}

// ACTIONS POST (AJOUT ET MODIFICATION)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Si c'est un ajout
    if ($_POST['action'] === 'add' && $peut_ajouter) {
        $nouveau_client = [$_POST['nom'], $_POST['email'], $_POST['telephone'], $_POST['ville']];
        $handle = fopen($fichier_csv, 'a');
        fputcsv($handle, $nouveau_client, ",");
        fclose($handle);
        header('Location: client.php?msg=added');
        exit;
    }

    // Si c'est une modification
    if ($_POST['action'] === 'edit' && $peut_modifier && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        // On vérifie que le client existe bien
        if (isset($clients[$id])) {
            // On remplace la ligne existante par les nouvelles données
            $clients[$id] = [$_POST['nom'], $_POST['email'], $_POST['telephone'], $_POST['ville']];
            
            // On réécrit tout le fichier
            $handle = fopen($fichier_csv, 'w');
            foreach ($clients as $ligne) {
                fputcsv($handle, $ligne, ",");
            }
            fclose($handle);
            
            header('Location: client.php?msg=edited');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annuaire Clients - JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --c-fond: #FFFFFF; --c-texte: #1A1A1A; --c-structurant: #E0E0E0; --c-action: #0056b3; --c-secondaire: #4A4A4A; --c-danger: #dc3545; }
        body { background-color: var(--c-fond); color: var(--c-texte); font-family: system-ui, sans-serif; }
        .navbar-custom { border-bottom: 1px solid var(--c-structurant); }
        .btn-action { background-color: var(--c-action); color: var(--c-fond); border: none; font-weight: 500; }
        .btn-action:hover { background-color: #004494; color: var(--c-fond); }
        .btn-structurant { border: 1px solid var(--c-structurant); color: var(--c-texte); background: transparent; transition: 0.2s; }
        .btn-structurant:hover { background-color: #f8f9fa; }
        .btn-danger-custom { border: 1px solid var(--c-structurant); color: var(--c-danger); background: transparent; }
        .btn-danger-custom:hover { background-color: var(--c-danger); color: var(--c-fond); }
        
        .badge-role { background-color: var(--c-texte); color: var(--c-fond); font-size: 0.75rem; text-transform: uppercase; padding: 0.4em 0.6em; border-radius: 4px;}
        
        .table-container { border: 1px solid var(--c-structurant); border-radius: 6px; }
        .table thead th { background-color: #FAFAFA; border-bottom: 1px solid var(--c-structurant); color: var(--c-secondaire); font-size: 0.85rem; padding: 1rem; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--c-structurant); }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand fw-bold">JOSSEL <span class="fw-light text-muted">| Annuaire Clients</span></span>
            <div class="d-flex align-items-center">
                <span class="me-4" style="color: var(--c-secondaire);">
                    <?= htmlspecialchars($_SESSION['user']['login'] ?? 'utilisateur'); ?> 
                    <span class="badge badge-role ms-1"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'salarie'); ?></span>
                </span>
                <a href="../wordpress" class="btn btn-structurant btn-sm me-2" target="_blank">Voir le site</a>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'added'): ?>
                <div class="alert alert-success">Le client a été ajouté avec succès.</div>
            <?php elseif ($_GET['msg'] === 'edited'): ?>
                <div class="alert alert-success">Les informations du client ont été mises à jour.</div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-danger">Le client a été supprimé.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h2 class="fw-bold m-0">Annuaire des Clients</h2>
            <?php if ($peut_ajouter): ?>
                <button class="btn btn-action btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter un client</button>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table class="table mb-0">
                <thead>
                    <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Ville</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php
                    if (count($clients) > 1) {
                        foreach ($clients as $id => $data) {
                            if ($id === 0) continue;
                            if (!empty(trim($data[0]))) {
                                $nom = htmlspecialchars($data[0] ?? '');
                                $email = htmlspecialchars($data[1] ?? '');
                                $tel = htmlspecialchars($data[2] ?? '');
                                $ville = htmlspecialchars($data[3] ?? '');

                                echo "<tr>";
                                echo "<td><strong>{$nom}</strong></td><td><a href='mailto:{$email}'>{$email}</a></td><td>{$tel}</td><td>{$ville}</td>";
                                echo "<td class='text-end text-nowrap'>";
                                
                                // Bouton Fiche
                                echo "<a href='?action=download&id={$id}' class='btn btn-structurant btn-sm me-1'>Fiche</a>";
                                
                                // Bouton Éditer (qui ouvre le modal correspondant)
                                if ($peut_modifier) { 
                                    echo "<button class='btn btn-structurant btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editModal{$id}'>Éditer</button>"; 
                                }
                                
                                // Bouton Supprimer
                                if ($peut_supprimer) { 
                                    echo "<a href='?action=delete&id={$id}' class='btn btn-danger-custom btn-sm' onclick='return confirm(\"Supprimer ce client ?\");'>Supprimer</a>"; 
                                }
                                
                                echo "</td></tr>";
                                
                                // --- MODAL DE MODIFICATION POUR CHAQUE CLIENT ---
                                if ($peut_modifier) {
                                    echo "
                                    <div class='modal fade' id='editModal{$id}' tabindex='-1'>
                                      <div class='modal-dialog'>
                                        <div class='modal-content text-start'>
                                          <form method='POST' action='client.php'>
                                              <input type='hidden' name='action' value='edit'>
                                              <input type='hidden' name='id' value='{$id}'>
                                              <div class='modal-header'>
                                                <h5 class='modal-title'>Modifier le Client</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                              </div>
                                              <div class='modal-body'>
                                                <label class='form-label small text-muted'>Nom complet</label>
                                                <input type='text' name='nom' class='form-control mb-3' value='{$nom}' required>
                                                
                                                <label class='form-label small text-muted'>Email</label>
                                                <input type='email' name='email' class='form-control mb-3' value='{$email}' required>
                                                
                                                <label class='form-label small text-muted'>Téléphone</label>
                                                <input type='text' name='telephone' class='form-control mb-3' value='{$tel}' required>
                                                
                                                <label class='form-label small text-muted'>Ville</label>
                                                <input type='text' name='ville' class='form-control' value='{$ville}' required>
                                              </div>
                                              <div class='modal-footer'>
                                                <button type='button' class='btn btn-structurant' data-bs-dismiss='modal'>Annuler</button>
                                                <button type='submit' class='btn btn-action'>Enregistrer</button>
                                              </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>";
                                }
                            }
                        }
                    } else { echo "<tr><td colspan='5' class='text-center p-5'>Aucun client.</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($peut_ajouter): ?>
    <div class="modal fade" id="addModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content text-start">
          <form method="POST" action="client.php">
              <input type="hidden" name="action" value="add">
              <div class="modal-header">
                  <h5 class="modal-title">Nouveau Client</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="text" name="nom" class="form-control mb-3" placeholder="Nom complet" required>
                <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                <input type="text" name="telephone" class="form-control mb-3" placeholder="Téléphone" required>
                <input type="text" name="ville" class="form-control" placeholder="Ville" required>
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
