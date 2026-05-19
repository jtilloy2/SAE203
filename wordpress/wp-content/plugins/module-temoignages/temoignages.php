<?php
/**
 * Plugin Name: Module Témoignages JOSSEL
 * Description: Gestion interactive premium des avis clients (Zéro SQL - JSON - Largeur Optimisée).
 * Version: 2.3
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
        $message = '<div class="alert alert-success border-0 shadow-sm mb-5 p-4 fs-5" role="alert">✨ <strong>Merci !</strong> Votre avis a bien été enregistré.</div>';
    } elseif ($status === 'error_write') {
        $message = '<div class="alert alert-danger border-0 shadow-sm mb-5 p-4 fs-5" role="alert">❌ Erreur système : Impossible d\'écrire dans le fichier JSON. Vérifiez les permissions.</div>';
    } elseif ($status === 'error_empty') {
        $message = '<div class="alert alert-warning border-0 shadow-sm mb-5 p-4 fs-5" role="alert">⚠️ Veuillez remplir les champs obligatoires.</div>';
    }

    $temoignages = file_exists(JOSSEL_JSON_TEMOIGNAGES) ? json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true) : [];

    ob_start();
    ?>
    <style>
        /* Styles pour améliorer l'UI et la taille du formulaire */
        .jossel-form-card {
            background: #ffffff; 
            border-top: 8px solid #0d6efd !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 4rem !important;
        }
        .jossel-form-card:hover {
            box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
        }
        .jossel-custom-input {
            padding: 1.1rem 1.25rem !important;
            font-size: 1.1rem !important;
            transition: all 0.2s ease-in-out;
            border: 2px solid #e9ecef !important;
        }
        .jossel-custom-input:focus {
            background-color: #fff !important;
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.15) !important;
            outline: none;
        }
        .jossel-submit-btn {
            background: linear-gradient(135deg, #0d6efd, #0a53be);
            border: none;
            padding: 1.1rem !important;
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }
        .jossel-submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.35) !important;
        }
        .jossel-form-label {
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        /* Styles pour le défilement horizontal des avis */
        .jossel-horizontal-scroll {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 1.5rem;
            padding: 1rem 0.5rem 2rem 0.5rem;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #0d6efd #f1f1f1;
        }
        .jossel-horizontal-scroll::-webkit-scrollbar {
            height: 10px;
        }
        .jossel-horizontal-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .jossel-horizontal-scroll::-webkit-scrollbar-thumb {
            background: #0d6efd;
            border-radius: 10px;
        }
        .jossel-testimonial-item {
            flex: 0 0 auto;
            width: 350px;
            max-width: 85vw;
            scroll-snap-align: start;
        }
        .jossel-testimonial-card {
            transition: transform 0.2s ease;
            border: 1px solid #f8f9fa;
        }
        .jossel-testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
        }
    </style>

    <div class="container-fluid w-100 p-0 jossel-module-avis">
        <?php echo $message; ?>

        <div class="card border-0 rounded-4 w-100 jossel-form-card">
            <div class="card-body p-4 p-md-5">
                <div class="mb-5 text-center">
                    <h3 class="fw-bolder text-dark mb-2" style="font-size: 1.8rem;">Partagez votre expérience</h3>
                    <p class="text-muted fs-6 mb-0">Votre avis nous aide à améliorer la qualité de nos services et guide nos futurs clients.</p>
                </div>
                
                <form method="post" action="">
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-secondary jossel-form-label">Nom complet *</label>
                            <input type="text" name="nom" class="form-control bg-light rounded-3 jossel-custom-input" required placeholder="Ex: Jean Dupont">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-secondary jossel-form-label">Entreprise / Organisation</label>
                            <input type="text" name="entreprise" class="form-control bg-light rounded-3 jossel-custom-input" placeholder="Ex: ECPR Lannion">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-secondary jossel-form-label">Note globale</label>
                            <select name="note" class="form-select bg-light fw-bold text-warning rounded-3 jossel-custom-input" style="cursor: pointer;">
                                <option value="5" selected>5 ★★★★★</option>
                                <option value="4">4 ★★★★</option>
                                <option value="3">3 ★★★</option>
                                <option value="2">2 ★★</option>
                                <option value="1">1 ★</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 align-items-end">
                        <div class="col-md-12 mb-2">
                            <label class="form-label fw-bold text-secondary jossel-form-label">Votre témoignage *</label>
                            <textarea name="citation" class="form-control bg-light rounded-3 jossel-custom-input" rows="4" required placeholder="Décrivez en quelques mots votre expérience avec notre équipe..."></textarea>
                        </div>
                        <div class="col-md-12 mt-4 text-end">
                            <button type="submit" name="jossel_submit_avis" class="btn btn-primary w-100 rounded-3 shadow-sm jossel-submit-btn">
                                Publier mon avis maintenant
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-end mb-4 px-2">
            <h3 class="fw-bolder text-dark mb-0">Ce que nos clients disent de nous</h3>
        </div>
        
        <?php if (!empty($temoignages) && is_array($temoignages)): ?>
            <div class="jossel-horizontal-scroll">
                <?php foreach (array_reverse($temoignages) as $t): ?>
                    <div class="jossel-testimonial-item">
                        <div class="card h-100 shadow-sm rounded-4 p-4 bg-white jossel-testimonial-card">
                            <div class="card-body d-flex flex-column justify-content-between p-0">
                                <div>
                                    <div class="mb-3" style="color: #ffb600; font-size: 1.2rem;">
                                        <?php echo str_repeat('★', intval($t['note'])); ?>
                                    </div>
                                    <p class="card-text text-secondary lh-base mb-4 fs-6" style="font-style: italic;">
                                        "<?php echo esc_html($t['citation']); ?>"
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 fs-6"><?php echo esc_html($t['nom']); ?></h6>
                                        <?php if(!empty($t['entreprise'])): ?>
                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                                <?php echo esc_html($t['entreprise']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(isset($t['date'])): ?>
                                        <span class="text-muted fw-semibold" style="font-size: 0.8rem;"><?php echo esc_html($t['date']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 p-0">
                <div class="bg-light p-5 rounded-4 text-center border">
                    <p class="text-muted fs-5 mb-0">Aucun avis publié pour le moment. Soyez le premier !</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
