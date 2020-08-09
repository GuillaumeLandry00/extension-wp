<?php
/*
*Plugin Name: Tp plugin
*Author: Gui 
*
*Page s'occupant de tout les éetapes à l'installation
*/

/**
 * Vérification de la version WP
 *
 * @param none
 * @return none
 */
function tpPlugin_check_version()
{
    global $wp_version;
    if (version_compare($wp_version, '4.9', '<')) {
        wp_die('Cette extension requiert WordPress version 4.9 ou plus.');
    }
}

/**
 * Création de la table annonces
 *
 * @param none
 * @return none
 */
function tpPlugin_create_table()
{
    global $wpdb;

    $sql = "CREATE TABLE $wpdb->prefix" . "annonces (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			marque varchar(255) NOT NULL,
			modele varchar(255) NOT NULL,
			couleur varchar(255) NOT NULL,
			annee YEAR UNSIGNED NOT NULL,
            kilometrage int UNSIGNED NOT NULL,
            prix decimal(8, 2) UNSIGNED NOT NULL,
            id_utilisateur int UNSIGNED NOT NULL,
            date_creation TIMESTAMP DEFAULT NOW(),
            visibilite  varchar(255) NOT NULL,
			PRIMARY KEY(id)
			) " . $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 *Création des pages de l'extension
 *
 * @param none 
 * @return none
 */
function tpPlugin_create_page()
{
    $tpPlugin_page = array(
        'post_title'     => "Saisie d'une annonce",
        'post_name' => "saisie-annonce",
        'post_content'   => "[saisie_annonce]",
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'meta_input' => array('tpPlugin' => 'form')
    );
    wp_insert_post($tpPlugin_page);

    $tpPlugin_page = array(
        'post_title' => "Annonces",
        'post_name' => "Annonces",
        'post_content' => "[liste_annonces]",
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'meta_input' => array('tpPlugin' => 'list')
    );
    wp_insert_post($tpPlugin_page);
    $tpPlugin_page = array(
        'post_title' => "Annonce suppression",
        'post_name' => "annonce-suppression",
        'post_content' => "[annonce_suppression]",
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'meta_input' => array('tpPlugin' => 'single')
    );
    wp_insert_post($tpPlugin_page);

    $tpPlugin_page = array(
        'post_title' => "Annonce modification",
        'post_name' => "annonce-modification",
        'post_content' => "[annonce_modification]",
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'meta_input' => array('tpPlugin' => 'single')
    );
    wp_insert_post($tpPlugin_page);
}
/**
 * Inilialisation de l'option TpPlugin_settings,
 * qui regroupe un tableau de réglages pour l'affichage des rubriques sur la page de liste
 *
 * @param none
 * @return none
 */
function tpPlugin_default_settings()
{
    add_option(
        'tpPlugin_settings',
        array(
            nombre_jours  => '0',
            droit_editeur => 'OUI',
            droit_contributeur    => 'OUI',
            droit_auteur    => 'OUI',
            visibilite_defaut => 'OUI'
        )
    );
}

/**
 * Suppression des pages de l'extension
 *
 * @param none
 * @return none
 */
function tpPlugin_delete_pages()
{
    global $wpdb;
    $postmetas = $wpdb->get_results(
        "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'tpPlugin'"
    );
    $force_delete = true;
    foreach ($postmetas as $postmeta) {
        wp_delete_post($postmeta->post_id, $force_delete);
    }
}

/**
 * Suppression de la table recipes
 *
 * @param none
 * @return none
 */
function tpPlugin_drop_table()
{
    global $wpdb;
    $sql = "DROP TABLE $wpdb->prefix" . "annonces";
    $wpdb->query($sql);
}

/**
 * Suppression de l'option tpPlugin
 *
 * @param none
 * @return none
 */
function tpPlugin_delete_settings()
{
    delete_option('tpPlugin_settings');
}
