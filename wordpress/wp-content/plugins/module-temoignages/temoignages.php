<?php
/**
 * Plugin Name: Module Témoignages JOSSEL
 * Description: Gestion des avis clients avec stockage Zéro SQL (JSON).
 * Version: 1.0
 * Author: Leny (Lot 4)
 */

if (!defined('ABSPATH')) exit;

// On définit où le fichier JSON sera sauvegardé (dans le dossier intranet/data comme pour les partenaires)
define('JOSSEL_JSON_TEMOIGNAGES', '/var/www/html/SAE203/intranet/data/temoignages.json');

// =======================================================
// 1. BACK-OFFICE : CRÉATION DU MENU ET GESTION DES DONNÉES
// =======================================================

add_action('admin_menu', 'jossel_menu_temoignages');
function jossel_menu_temoignages() {
    // Ajoute un onglet "Témoignages" dans le menu gauche de WordPress
    add_menu_page('Témoignages', 'Témoignages', 'manage_options', 'jossel-temoignages', 'jossel_page_admin_temoignages', 'dashicons-testimonial', 20);
}

function jossel_page_admin_temoignages() {
    // Si le fichier JSON n'existe pas encore, on le crée avec un tableau vide
    if (!file_exists(JOSSEL_JSON_TEMOIGNAGES)) {
        file_put_contents(JOSSEL_JSON_TEMOIGNAGES, json_encode([]));
    }

    // Si le formulaire d'ajout est soumis...
    if (isset($_POST['ajouter_temoignage'])) {
        $nouveau_temoignage = [
            'id' => uniqid(), // Génère un ID unique
            'nom' => sanitize_text_field($_POST['nom']),
            'entreprise' => sanitize_text_field($_POST['entreprise']),
            'citation' => sanitize_textarea_field($_POST['citation']),
            'note' => intval($_POST['note'])
        ];
        
        // On lit le fichier JSON, on ajoute la donnée, on réécrit le fichier
        $data = json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true);
        $data[] = $nouveau_temoignage;
        file_put_contents(JOSSEL_JSON_TEMOIGNAGES, json_encode($data, JSON_PRETTY_PRINT));
        
        echo '<div class="notice notice-success"><p>Témoignage ajouté avec succès !</p></div>';
    }

    // HTML de l'interface d'administration
    ?>
    <div class="wrap">
        <h1>Gestion des Témoignages Clients</h1>
        
        <div style="background:#fff; padding:20px; border:1px solid #ccc; margin-bottom:20px;">
            <h2>Ajouter un nouvel avis</h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr><th>Nom du client</th><td><input type="text" name="nom" required style="width:100%;"></td></tr>
                    <tr><th>Entreprise</th><td><input type="text" name="entreprise" required style="width:100%;"></td></tr>
                    <tr><th>Citation</th><td><textarea name="citation" rows="3" required style="width:100%;"></textarea></td></tr>
                    <tr><th>Note (sur 5)</th><td><input type="number" name="note" min="1" max="5" required></td></tr>
                </table>
                <p><input type="submit" name="ajouter_temoignage" class="button button-primary" value="Enregistrer le témoignage"></p>
            </form>
        </div>

        <h2>Témoignages enregistrés</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Nom</th><th>Entreprise</th><th>Note</th><th>Citation</th></tr></thead>
            <tbody>
            <?php
            $temoignages = json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true);
            if (!empty($temoignages)) {
                foreach ($temoignages as $t) {
                    echo '<tr>';
                    echo '<td><strong>' . esc_html($t['nom']) . '</strong></td>';
                    echo '<td>' . esc_html($t['entreprise']) . '</td>';
                    echo '<td>' . esc_html($t['note']) . ' / 5</td>';
                    echo '<td>' . esc_html($t['citation']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4">Aucun témoignage enregistré.</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

// =======================================================
// 2. FRONT-OFFICE : SHORTCODE POUR LES VISITEURS
// =======================================================

add_shortcode('temoignages_clients', 'jossel_shortcode_temoignages');
function jossel_shortcode_temoignages() {
    if (!file_exists(JOSSEL_JSON_TEMOIGNAGES)) {
        return '<p>Les témoignages sont indisponibles pour le moment.</p>';
    }
    
    $temoignages = json_decode(file_get_contents(JOSSEL_JSON_TEMOIGNAGES), true);
    if (empty($temoignages)) {
        return '<p>Soyez le premier à laisser un avis !</p>';
    }

    // Construction de la grille Bootstrap 5
    $html = '<div class="row g-4">';
    foreach ($temoignages as $t) {
        $etoiles = str_repeat('⭐', intval($t['note'])); // Affiche le bon nombre d'étoiles
        
        $html .= '<div class="col-md-4">';
        $html .= '  <div class="card h-100 shadow-sm">';
        $html .= '    <div class="card-body text-center">';
        $html .= '      <div class="mb-2">' . $etoiles . '</div>';
        $html .= '      <p class="card-text fst-italic">"' . esc_html($t['citation']) . '"</p>';
        $html .= '      <h5 class="card-title mb-0">' . esc_html($t['nom']) . '</h5>';
        $html .= '      <small class="text-muted">' . esc_html($t['entreprise']) . '</small>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    return $html;
}
