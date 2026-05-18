<?php
/**
 * Plugin Name: Module Témoignages JOSSEL
 * Description: Gestion interactive premium des avis clients (Zéro SQL - JSON - Largeur Optimisée).
 * Version: 2.1
 * Author: Leny (Lot 4)
 */

if (!defined('ABSPATH')) exit;

define('JOSSEL_JSON_TEMOIGNAGES', '/var/www/html/SAE203/intranet/data/temoignages.json');

// =======================================================
// 1. LOGIQUE DE TRAITEMENT ET SAUVEGARDE DES DONNÉES
// =======================================================
function jossel_traiter_soumission_avis() {
    if (isset($_POST['jossel_submit_avis'])) {
        $nom = sanitize_text_field($_POST['nom']);
        $entreprise = sanitize_text_field($_POST['entreprise']);
        $citation = sanitize_textarea_field($_POST['citation']);
        $note = isset($_POST['note']) ? intval($_POST['note']) : 5;

        if (empty($nom) || empty($citation)) {
            return 'error_empty';
        }

        $nouvel_avis = [
            'id' => uniqid(),
            'nom' => $nom,
            'entreprise' => $entreprise,
            'citation' => $citation,
            'note' => $note,
            'date' => date('d/m/Y')
        ];

        $dir = dirname(JOSSEL_JSON_TEMOIGNAGES);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        $data = [];
        if (file_exists(JOSSEL_JSON_TEMOIGNAGES)) {
            $content = file_get_contents(JOSSEL_JSON_TEMOIGNAGES);
            $data = json_decode($content, true);
            if (!is_array($data)) $data = [];
        }

        $data[] = $nouvel_avis;
        
        if (file_put_contents(JOSSEL_JSON_TEMOIGNAGES, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            return 'success';
        } else {
            return 'error_write';
        }
    }
    return false;
}

// =======================================================
// 2. INTERFACE DE MODÉRATION (BACK-OFFICE WORDPRESS)
// =======================================================
add_action('admin_menu', 'jossel_menu_temoignages');
function jossel_menu_temoignages() {
    add_menu_page('Témoignages', 'Témoignages', 'manage_options', 'jossel-temoignages', 'jossel_page_admin_temoignages', 'dashicons-testimonial', 20);
}

function jossel_page_admin_temoignages() {
    if (isset($_GET['supprimer_id'])) {
        $id = sanitize_text_field($_GET['supprimer_id']);
        if (file_exists(JOSSEL_JSON_TEMOIGNAGES)) {
            $data = json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true);
            if (is_array($data)) {
                $data = array_filter($data, function($t) use ($id) { return $t['id'] !== $id; });
                file_put_contents(JOSSEL_JSON_TEMOIGNAGES, json_encode(array_values($data), JSON_PRETTY_PRINT));
            }
        }
    }

    $temoignages = file_exists(JOSSEL_JSON_TEMOIGNAGES) ? json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true) : [];
    ?>
    <div class="wrap">
        <h1>Modération des Témoignages Clients</h1>
        <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
            <thead>
                <tr>
                    <th style="width:20%;">Auteur</th>
                    <th style="width:10%;">Note</th>
                    <th>Témoignage</th>
                    <th style="width:15%;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($temoignages)): ?>
                    <?php foreach ($temoignages as $t): ?>
                    <tr>
                        <td><strong><?php echo esc_html($t['nom']); ?></strong><br><small class="text-muted"><?php echo esc_html($t['entreprise']); ?></small></td>
                        <td><span style="color:#ffb600;"><?php echo str_repeat('★', $t['note']); ?></span></td>
                        <td><p class="description">"<?php echo esc_html($t['citation']); ?>"</p></td>
                        <td><a href="?page=jossel-temoignages&supprimer_id=<?php echo $t['id']; ?>" class="button button-link-delete" style="color:red;" onclick="return confirm('Supprimer définitivement cet avis ?');">Supprimer</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">Aucun témoignage enregistré pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// =======================================================
// 3. FORMULAIRE ET AFFICHAGE PUBLIC (SHORTCODE)
// =======================================================
add_shortcode('temoignages_clients', 'jossel_shortcode_temoignages');
function jossel_shortcode_temoignages() {
    $status = jossel_traiter_soumission_avis();
    $message = "";

    if ($status === 'success') {
        $message = '<div class="alert alert-success border-0 shadow-sm mb-4" role="alert">✨ <strong>Merci !</strong> Votre avis a bien été enregistré.</div>';
    } elseif ($status === 'error_write') {
        $message = '<div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">❌ Erreur système : Impossible d\'écrire dans le fichier JSON. Vérifiez les permissions.</div>';
    } elseif ($status === 'error_empty') {
        $message = '<div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">⚠️ Veuillez remplir les champs obligatoires.</div>';
    }

    $temoignages = file_exists(JOSSEL_JSON_TEMOIGNAGES) ? json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true) : [];

    ob_start();
    ?>
    <div class="container-fluid w-100 p-0 jossel-module-avis">
        <?php echo $message; ?>

        <div class="card border-0 shadow-sm rounded-4 mb-5 w-100" style="background: #ffffff; border-top: 5px solid #0d6efd !important;">
            <div class="card-body p-4 p-md-5">
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Partagez votre expérience</h4>
                    <p class="text-muted small mb-0">Votre avis nous aide à améliorer la qualité de nos services.</p>
                </div>
                
                <form method="post" action="">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-secondary small">Nom complet *</label>
                            <input type="text" name="nom" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" required placeholder="Ex: Jean Dupont">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-secondary small">Entreprise / Organisation</label>
                            <input type="text" name="entreprise" class="form-control form-control-lg bg-light border-0 rounded-3 fs-6" placeholder="Ex: ECPR Lannion">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small">Note globale</label>
                            <select name="note" class="form-select form-select-lg border-0 bg-light fw-bold text-warning rounded-3" style="cursor: pointer;">
                                <option value="5" selected>5 ★★★★★</option>
                                <option value="4">4 ★★★★</option>
                                <option value="3">3 ★★★</option>
                                <option value="2">2 ★★</option>
                                <option value="1">1 ★</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold text-secondary small">Votre témoignage *</label>
                            <textarea name="citation" class="form-control bg-light border-0 rounded-3 fs-6" rows="2" required placeholder="Décrivez votre expérience avec notre équipe..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="jossel_submit_avis" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold shadow-sm fs-6" style="padding: 12px 0;">
                                Envoyer mon avis
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <h3 class="fw-bold text-dark mb-4">Ce que nos clients disent de nous</h3>
        
        <div class="row g-4 w-100 m-0">
            <?php if (!empty($temoignages) && is_array($temoignages)): ?>
                <?php foreach (array_reverse($temoignages) as $t): ?>
                    <div class="col-lg-4 col-md-6 px-2">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white">
                            <div class="card-body d-flex flex-column justify-content-between p-2">
                                <div>
                                    <div class="mb-2" style="color: #ffb600; font-size: 1.1rem;">
                                        <?php echo str_repeat('★', intval($t['note'])); ?>
                                    </div>
                                    <p class="card-text text-secondary lh-base mb-4 fs-6" style="font-style: italic;">
                                        "<?php echo esc_html($t['citation']); ?>"
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6"><?php echo esc_html($t['nom']); ?></h6>
                                        <?php if(!empty($t['entreprise'])): ?>
                                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                                <?php echo esc_html($t['entreprise']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(isset($t['date'])): ?>
                                        <span class="text-muted small" style="font-size: 0.8rem;"><?php echo esc_html($t['date']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 p-0">
                    <p class="text-muted italic bg-light p-4 rounded-4 text-center">Aucun avis publié pour le moment. Soyez le premier !</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
