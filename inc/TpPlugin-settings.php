<?php
// create custom plugin settings menu
add_action('admin_menu', 'tpPlugin_create_menu');

function tpPlugin_create_menu()
{

    //create new top-level menu
    add_menu_page('TpPlugin Settings', 'TpPlugin Settings', 'administrator', __FILE__, 'tpPlugin_setting_page');

    //call register settings function
    add_action('admin_init', 'enregistre_tpPlugin_settings');
}


function enregistre_tpPlugin_settings()
{
    //enregistre les settings
    register_setting('tpPlugin-settings-group', 'tpPlugin_settings', 'tpPlugin_sanitize_option');
}

function tpPlugin_sanitize_option($input)
{
    $input['nombre_jours']    = sanitize_text_field($input['nombre_jours']);
    $input['droit_editeur']  = sanitize_text_field($input['droit_editeur']);
    $input['droit_contributeur']  = sanitize_text_field($input['droit_contributeur']);
    $input['droit_auteur']  = sanitize_text_field($input['droit_auteur']);
    $input['visibilite_defaut'] = sanitize_text_field($input['visibilite_defaut']);
    return $input;
}

function tpPlugin_setting_page()
{
?>
    <div class="wrap">
        <h1>TpPlugin réglage</h1>
        <form method="post" action="options.php">
            <?php settings_fields('tpPlugin-settings-group'); ?>
            <?php $tpPlugin_settings = get_option('tpPlugin_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Nombre de jour durée annonce (0==INFINI)</th>
                    <td><input type="number" name="tpPlugin_settings[nombre_jours]" min="0" value="<?php if (isset($tpPlugin_settings['nombre_jours'])) echo $tpPlugin_settings['nombre_jours'] ?>" required /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Droit de créer un annonce</th>
                    <td><label for="">Éditeur</label>
                        <input type="checkbox" name="tpPlugin_settings[droit_editeur]" value="OUI" <?php checked(!isset($tpPlugin_settings['droit_editeur']) || $tpPlugin_settings['droit_editeur'] === 'OUI') ?> />
                        <br>
                        <label for="">Contributeur</label>
                        <input type="checkbox" name="tpPlugin_settings[droit_contributeur]" value="OUI" <?php checked(!isset($tpPlugin_settings['droit_contributeur']) || $tpPlugin_settings['droit_contributeur'] === 'OUI') ?> />
                        <br>
                        <label for="">Auteur</label>
                        <input type="checkbox" name="tpPlugin_settings[droit_auteur]" value="OUI" <?php checked(!isset($tpPlugin_settings['droit_auteur']) || $tpPlugin_settings['droit_auteur'] === 'OUI') ?> />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Visibilté par défaut</th>
                    <td><label for="">OUI </label><input type="radio" name="tpPlugin_settings[visibilite_defaut]" value="OUI" <?php checked(!isset($tpPlugin_settings['visibilite_defaut']) || $tpPlugin_settings['visibilite_defaut'] === 'OUI') ?> required />
                        <br>
                        <label for="">NON </label><input type="radio" name="tpPlugin_settings[visibilite_defaut]" value="NON" required <?php checked(!isset($tpPlugin_settings['visibilite_defaut']) || $tpPlugin_settings['visibilite_defaut'] === 'NON') ?> />
                    </td>

                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>
<?php } ?>