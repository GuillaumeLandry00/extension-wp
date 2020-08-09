<?php
/*
Plugin Name: Tp plugin
Plugin URI: https://voiturePRO.plugins.com 
Description: Gestion de voiture 
Version: 1.0 
Author: Gui 
Author URI: https://guillaumeartiste3d.ca 
*/
require_once("inc/TpPlugin-page.php");
require_once("inc/TpPlugin-install.php");
require_once("inc/TpPlugin-hook.php");
require_once("inc/TpPlugin-settings.php");

/* Section pour l'activation de l'extension
 * ========================================
 */

register_activation_hook(__FILE__, 'tpPlugin_activate');

/**
 * Traitements à l'activation de l'extension
 *
 * @param none
 * @return none
 */
function tpPlugin_activate()
{
    tpPlugin_check_version();
    tpPlugin_create_table();
    tpPlugin_create_page();
    tpPlugin_default_settings();
}

register_deactivation_hook(__FILE__, 'tpPlugin_recipes_deactivate');

/**
 * Traitements à la désactivation de l'extension
 *
 * @param none
 * @return none
 */
function tpPlugin_recipes_deactivate()
{
    tpPlugin_delete_pages();
}

/* Section pour la désinstallation de l'extension
 * ==============================================
 */

register_uninstall_hook(__FILE__, 'tpPlugin_uninstall');

/**
 * Traitements à la désinstallation de l'extension
 *
 * @param none
 * @return none
 */
function tpPlugin_uninstall()
{
    tpPlugin_drop_table();
    tpPlugin_delete_settings();
}
