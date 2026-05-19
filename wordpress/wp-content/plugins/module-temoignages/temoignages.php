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
            margin-bottom: 0.5rem
