<?php
session_start();

// 1. SÉCURITÉ : Vérification de la session active
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 2. GESTION DES PERMISSIONS SELON LE RÔLE DE L'UTILISATEUR
$role = isset($_SESSION['user']['role']) ? strtolower($_SESSION['user']['role']) : 'salarie';
$peut_ajouter = in_array($role, ['admin', 'direction', 'manager']);
$peut_modifier = in_array($role, ['admin', 'direction', 'manager']);
$peut_supprimer = in_array($role, ['admin', 'direction']);
$afficher_actions = true; 

// 3. LOGIQUE ENTRÉES/SORTIES FICHERS (CSV)
$fichier_csv = __DIR__ . '/data/clients.csv';
$clients = [];

// Chargement complet du fichier CSV en mémoire
if (file_exists($fichier_csv) && ($handle = fopen($fichier_csv, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $clients[] = $data;
    }
    fclose($handle);
}

// TRAITEMENT DES ACTIONS REÇUES PAR URL (GET)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Action : Télécharger la fiche client (Exigence Cahier des charges)
    if ($_GET['action'] === 'download' && isset($clients[$id])) {
        $c = $clients[$id];
        $contenu = "FICHE CLIENT - JOSSEL\n--------------------\nNom : " . ($c[0] ?? "") . "\nEmail : " . ($c[1] ?? "") . "\nTéléphone : " . ($c[2] ?? "") . "\nVille : " . ($c[3] ?? "");
        
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="fiche_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', ($c[0] ?? "client")) . '.txt"');
        echo $contenu;
        exit;
    }

    // Action : Supprimer définitivement un client du CSV
    if ($_GET['action'] === 'delete' && $peut_supprimer && isset($clients[$id]) && $id !== 0) {
        unset($clients[$id]);
        $clients = array_values($clients); // Réindexation propre du tableau PHP
        
        $handle = fopen($fichier_csv, 'w');
        foreach ($clients as $ligne) { 
            fputcsv($handle, $ligne, ","); 
        }
        fclose($handle);
        header('Location: clients.php?msg=deleted');
        exit;
    }
}

// TRAITEMENT DES FORMULAIRES ENVOYÉS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Action : Ajouter un nouveau client
    if ($_POST['action'] === 'add' && $peut_ajouter) {
        $nouveau_client = [
            trim($_POST['nom']), 
            trim($_POST['email']), 
            trim($_POST['telephone']), 
            trim($_POST['ville'])
        ];
        $handle = fopen($fichier_csv, 'a');
        fputcsv($handle, $nouveau_client, ",");
        fclose($handle);
        header('Location: clients.php?msg=added');
        exit;
    }

    // Action : Modifier (Éditer) un client existant
    if ($_POST['action'] === 'edit' && $peut_modifier && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        if (isset($clients[$id])) {
            $clients[$id] = [
                trim($_POST['nom']), 
                trim($_POST['email']), 
                trim($_POST['telephone']), 
                trim($_POST['ville'])
            ];
            
            $handle = fopen($fichier_csv, 'w');
            foreach ($clients as $ligne) {
                fputcsv($handle, $ligne, ",");
            }
            fclose($handle);
            
            header('Location: clients.php?msg=edited');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annuaire Clients - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* INTÉGRATION DE LA CHARTE GRAPHIQUE SOBRE ET STATIQUE (Lot 2) */
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
            font-family: system-ui, -apple-system, sans-serif; 
        }
        
        .navbar-custom { 
            border-bottom: 1px solid var(--c-structurant); 
            background-color: var(--c-fond);
        }
        
        .btn-action { 
            background-color: var(--c-action); 
            color: var(--c-fond); 
            border: none; 
            font-weight: 500; 
        }
        .btn-action:hover { 
            background-color: #004494; 
            color: var(--c-fond); 
        }
        
        .btn-structurant { 
            border: 1px solid var(--c-structurant); 
            color: var(--c-texte); 
            background: transparent; 
            transition: 0.2s; 
        }
        .btn-structurant:hover { 
            background-color: #f8f9fa; 
        }
        
        .btn-danger-custom { 
            border: 1px solid var(--c-structurant); 
            color: var(--c-danger); 
            background: transparent; 
        }
        .btn-danger-custom:hover { 
            background-color: var(--c-danger); 
            color: var(--c-fond); 
        }
        
        /* Badge utilisateur identique à la maquette d011a4 */
        .badge-role { 
            background-color: var(--c-texte); 
            color: var(--c-fond); 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            padding: 0.4em 0.6em; 
            border-radius: 4px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .table-container { 
            border: 1px solid var(--c-structurant); 
            border-radius: 6px; 
            overflow: hidden;
        }
        .table thead th { 
            background-color: #FAFAFA; 
            border-bottom: 1px solid var(--c-structurant); 
            color: var(--c-secondaire); 
            font-size: 0.85rem; 
            padding: 1rem; 
            text-transform: uppercase;
            font-weight: 600;
        }
        .table tbody td { 
            padding: 1rem; 
            vertical-align: middle; 
            border-bottom: 1px solid var(--c-structurant); 
        }
        
        a.email-link {
            color: var(--c-action);
            text-decoration: none;
        }
        a.email-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand fw-bold">JOSSEL <span class="fw-light text-muted">| Annuaire Clients</span></span>
            <div class="d-flex align-items-center">
                
                <span class="me-4 text-lowercase" style="color: var(--c-secondaire); font-weight: 500;">
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
                <div class="alert alert-success">Le client a été ajouté avec succès dans l'annuaire.</div>
            <?php elseif ($_GET['msg'] === 'edited'): ?>
                <div class="alert alert-success">Les informations du client ont été mises à jour avec succès.</div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-danger">Le client a été retiré de l'annuaire avec succès.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold m-0">Annuaire des Clients</h2>
                <p class="text-muted small m-0 mt-1">Base de données partagée au format plat CSV (30 clients minimum)</p>
            </div>
            <?php if ($peut_ajouter): ?>
                <button class="btn btn-action btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter un client</button>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($clients) > 1) {
                        foreach ($clients as $id => $data) {
                            if ($id === 0) continue; // On ignore la ligne d'en-tête du fichier CSV
                            
                            if (!empty(trim($data[0] ?? ''))) {
                                $nom = htmlspecialchars($data[0] ?? '');
                                $email = htmlspecialchars($data[1] ?? '');
                                $tel = htmlspecialchars($data[2] ?? '');
                                $ville = htmlspecialchars($data[3] ?? '');

                                echo "<tr>";
                                echo "<td><strong>{$nom}</strong></td>";
                                echo "<td><a href='mailto:{$email}' class='email-link'>{$email}</a></td>";
                                echo "<td>{$tel}</td>";
                                echo "<td><span class='text-muted'>{$ville}</span></td>";
                                echo "<td class='text-end text-nowrap'>";
                                
                                // Action 1 : Téléchargement de la fiche (Accessible à tous)
                                echo "<a href='?action=download&id={$id}' class='btn btn-structurant btn-sm me-1'>Fiche</a>";
                                
                                // Action 2 : Édition (Soumise à droits)
                                if ($peut_modifier) { 
                                    echo "<button class='btn btn-structurant btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editModal{$id}'>Éditer</button>"; 
                                }
                                
                                // Action 3 : Suppression (Soumise à droits stricts)
                                if ($peut_supprimer) { 
                                    echo "<a href='?action=delete&id={$id}' class='btn btn-danger-custom btn-sm' onclick='return confirm(\"Voulez-vous vraiment supprimer définitivement ce client ?\");'>Supprimer</a>"; 
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                                
                                // --- ACCORDION MODAL DE MODIFICATION SÉCURISÉ POUR CHAQUE CLIENT ---
                                if ($peut_modifier) {
                                    echo "
                                    <div class='modal fade' id='editModal{$id}' tabindex='-1' aria-hidden='true'>
                                      <div class='modal-dialog'>
                                        <div class='modal-content text-start'>
                                          <form method='POST' action='clients.php'>
                                              <input type='hidden' name='action' value='edit'>
                                              <input type='hidden' name='id' value='{$id}'>
                                              <div class='modal-header'>
                                                <h5 class='modal-title fw-bold'>Modifier la fiche client</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                              </div>
                                              <div class='modal-body'>
                                                <div class='mb-3'>
                                                    <label class='form-label small text-muted fw-bold'>Nom complet</label>
                                                    <input type='text' name='nom' class='form-control' value='{$nom}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label class='form-label small text-muted fw-bold'>Adresse Email</label>
                                                    <input type='email' name='email' class='form-control' value='{$email}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label class='form-label small text-muted fw-bold'>Numéro de téléphone</label>
                                                    <input type='text' name='telephone' class='form-control' value='{$tel}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label class='form-label small text-muted fw-bold'>Ville</label>
                                                    <input type='text' name='ville' class='form-control' value='{$ville}' required>
                                                </div>
                                              </div>
                                              <div class='modal-footer'>
                                                <button type='button' class='btn btn-structurant' data-bs-dismiss='modal'>Annuler</button>
                                                <button type='submit' class='btn btn-action'>Enregistrer les modifications</button>
                                              </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>";
                                }
                            }
                        }
                    } else { 
                        echo "<tr><td colspan='5' class='text-center p-5 text-muted'>Aucune donnée client détectée dans le fichier clients.csv.</td></tr>"; 
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($peut_ajouter): ?>
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content text-start">
          <form method="POST" action="clients.php">
              <input type="hidden" name="action" value="add">
              <div class="modal-header">
                  <h5 class="modal-title fw-bold">Nouveau Profil Client</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                    <input type="text" name="nom" class="form-control" placeholder="Nom complet (ex: Jean Dupont)" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Adresse email (ex: jean@dupond.fr)" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="telephone" class="form-control" placeholder="Téléphone (ex: 0601020304)" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="ville" class="form-control" placeholder="Ville (ex: Saint-Malo)" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-structurant" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-action">Créer la fiche</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
