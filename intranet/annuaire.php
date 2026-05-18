<?php
session_start();

// 1. SÉCURITÉ : Vérification de la session
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 2. GESTION DES PERMISSIONS (Harmonisée avec clients.php et partenaires.php)
$role = strtolower($_SESSION['user']['role'] ?? $_SESSION['user']['groupe'] ?? 'salarie');
$nom_user_connecte = $_SESSION['user']['nom'] ?? $_SESSION['user']['login'] ?? 'Utilisateur';

// L'admin ET la direction ont le droit de modifier les informations
$peut_modifier = ($role === 'admin' || $role === 'administrateur' || $role === 'direction');

// Ligne de test : décommente la ligne suivante pour forcer le mode modification sur ta machine de test
// $peut_modifier = true;

// 3. LOGIQUE MÉTIER (CRUD JSON)
$fichier_json = __DIR__ . '/data/utilisateurs.json';
$message_succes = '';
$message_erreur = '';

// Traitement du formulaire de modification (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier' && $peut_modifier) {
    if (file_exists($fichier_json)) {
        $contenu_json = file_get_contents($fichier_json);
        $employes = json_decode($contenu_json, true);
        
        $id_a_modifier = intval($_POST['id_user']);
        
        foreach ($employes as $key => $emp) {
            if (isset($emp['id']) && intval($emp['id']) === $id_a_modifier) {
                // Mise à jour des champs textuels du fichier utilisateurs.json
                $employes[$key]['nom'] = trim($_POST['nom_complet']);
                $employes[$key]['poste'] = trim($_POST['poste']);
                $employes[$key]['groupe'] = trim($_POST['groupe']);
                break;
            }
        }
        
        // Sauvegarde dans le fichier JSON avec un formatage propre
        if (file_put_contents($fichier_json, json_encode($employes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $message_succes = "Les informations du collaborateur ont été mises à jour avec succès.";
        } else {
            $message_erreur = "Erreur lors de l'enregistrement dans le fichier JSON.";
        }
    }
}

// Lecture des données pour l'affichage (GET)
$employes = [];
if (file_exists($fichier_json)) {
    $contenu_json = file_get_contents($fichier_json);
    $data = json_decode($contenu_json, true);
    if (is_array($data)) {
        $employes = $data;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annuaire des Collaborateurs - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CHARTE GRAPHIQUE UNIFIÉE JOSSEL */
        :root {
            --c-fond: #FFFFFF;            
            --c-texte: #1A1A1A;           
            --c-structurant: #E0E0E0;     
            --c-action: #0056b3;          
            --c-secondaire: #4A4A4A;      
        }

        body {
            background-color: var(--c-fond);
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        /* Navbar */
        .navbar-custom { background-color: var(--c-fond); border-bottom: 1px solid var(--c-structurant); }
        .navbar-custom .navbar-brand { color: var(--c-texte); font-weight: 700; }
        .text-muted { color: var(--c-secondaire) !important; }

        /* Boutons */
        .btn-action { background-color: var(--c-action); color: var(--c-fond); border: none; font-weight: 500; }
        .btn-action:hover { background-color: #004494; color: var(--c-fond); }
        
        .btn-structurant { border: 1px solid var(--c-structurant); color: var(--c-texte); background: transparent; }
        .btn-structurant:hover { background-color: var(--c-structurant); }

        /* Badges */
        .badge-role { background-color: var(--c-texte); color: var(--c-fond); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        
        /* Badges de Groupes dans le tableau */
        .badge-groupe-admin { background-color: #dc3545; color: white; }
        .badge-groupe-manager { background-color: #ffc107; color: black; }
        .badge-groupe-direction { background-color: #17a2b8; color: white; }
        .badge-groupe-salarie { background-color: #28a745; color: white; }

        /* Tableau */
        .table-container { border: 1px solid var(--c-structurant); border-radius: 6px; background-color: var(--c-fond); overflow: hidden; }
        .table { margin-bottom: 0; }
        .table thead th { background-color: #FAFAFA; border-bottom: 1px solid var(--c-structurant); color: var(--c-secondaire); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; padding: 1rem; border-top: none; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--c-structurant); }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #FAFAFA; }

        /* Photo de profil ronde */
        .avatar-img { width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 1px solid var(--c-structurant); }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand">JOSSEL <span class="fw-light text-muted">| Annuaire Collaborateurs</span></span>
            <div class="d-flex align-items-center">
                <span class="me-4 text-muted small">
                    <?= htmlspecialchars($nom_user_connecte) ?> 
                    <span class="badge badge-role ms-2"><?= htmlspecialchars($role) ?></span>
                </span>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="fichiers.php" class="btn btn-structurant btn-sm me-2">Fichiers</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <?php if ($message_succes): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message_succes) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($message_erreur): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message_erreur) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold m-0">Équipe & Collaborateurs</h2>
                <p class="text-muted small m-0 mt-1">Liste officielle du personnel de l'entreprise (Base JSON)</p>
            </div>
            <?php if ($peut_modifier): ?>
                <span class="badge bg-danger px-3 py-2 rounded-pill">Mode Édition Activé</span>
            <?php endif; ?>
        </div>

        <div class="table-container shadow-sm">
            <table class="table">
                <thead>
                    <tr>
                        <th class="ps-4">Photo</th>
                        <th>Nom Complet</th>
                        <th>Poste / Fonction</th>
                        <th>Groupe d'accès</th>
                        <?php if ($peut_modifier): ?><th class="text-end pe-4">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employes)): ?>
                        <?php foreach ($employes as $emp): 
                            $id = htmlspecialchars($emp['id'] ?? 0);
                            $nom_complet = htmlspecialchars($emp['nom'] ?? 'Inconnu');
                            $poste = htmlspecialchars($emp['poste'] ?? 'Non défini');
                            $groupe = htmlspecialchars(strtolower($emp['groupe'] ?? 'salarie'));
                            $photo_url = htmlspecialchars($emp['photo'] ?? '');

                            $avatar_secours = "https://ui-avatars.com/api/?name=" . urlencode($nom_complet) . "&background=E0E0E0&color=1A1A1A&rounded=true";

                            $badge_class = 'badge-groupe-' . $groupe;
                            if (!in_array($groupe, ['admin', 'manager', 'direction', 'salarie'])) {
                                $badge_class = 'bg-secondary';
                            }
                        ?>
                        <tr>
                            <td class="ps-4">
                                <img src="<?= $photo_url ?>" onerror="this.onerror=null;this.src='<?= $avatar_secours ?>';" alt="Photo" class="avatar-img">
                            </td>
                            <td><strong style="color: var(--c-texte);"><?= $nom_complet ?></strong></td>
                            <td><span class="text-muted"><?= $poste ?></span></td>
                            <td>
                                <span class="badge <?= $badge_class ?> px-2 py-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <?= $groupe ?>
                                </span>
                            </td>
                            
                            <?php if ($peut_modifier): ?>
                            <td class="text-end pe-4 text-nowrap">
                                <button class="btn btn-structurant btn-sm edit-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        data-id="<?= $id ?>"
                                        data-nom="<?= $nom_complet ?>"
                                        data-poste="<?= $poste ?>"
                                        data-groupe="<?= $groupe ?>">
                                    Éditer
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-5 text-muted">Aucun collaborateur trouvé dans le fichier JSON.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($peut_modifier): ?>
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="annuaire.php">
              <input type="hidden" name="action" value="modifier">
              <input type="hidden" name="id_user" id="modal_id_user">
              
              <div class="modal-header">
                <h5 class="modal-title fw-bold">Modifier le collaborateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Nom Complet</label>
                    <input type="text" name="nom_complet" id="modal_nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Poste / Fonction</label>
                    <input type="text" name="poste" id="modal_poste" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Groupe d'accès</label>
                    <select class="form-select" name="groupe" id="modal_groupe" required>
                        <option value="salarie">Salarié</option>
                        <option value="manager">Manager</option>
                        <option value="direction">Direction</option>
                        <option value="admin">Administrateur</option>
                    </select>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modal_id_user').value = this.getAttribute('data-id');
                    document.getElementById('modal_nom').value = this.getAttribute('data-nom');
                    document.getElementById('modal_poste').value = this.getAttribute('data-poste');
                    
                    const groupe = this.getAttribute('data-groupe').toLowerCase();
                    const select = document.getElementById('modal_groupe');
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === groupe) {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                });
            });
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
