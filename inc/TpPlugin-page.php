<?php
/*
*Plugin Name: Tp plugin
*Author: Gui 
*
*Page s'occupant de la création des pages
*/

/**
 * Création du formulaire de saisie d'une recette
 *
 * @param none
 * @return echo html form annonce code
 */
function html_form_tpPlugin_code()
{
    $options = get_option('tpPlugin_settings');

?>
    <!-- Permet de vérifier que un utilisateur avec certaine capacitée est connecté -->
    <?php if ((current_user_can('administrator') || (current_user_can('contributor') && $options["droit_contributeur"] == "OUI") || (current_user_can('editor') && $options["droit_editeur"] == "OUI") || (current_user_can('author') && $options["droit_auteur"] == "OUI"))) :  ?>
        <form action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>" method="post" enctype="multipart/form-data">
            <label>Marque de la voiture</label>
            <input type="text" name="marque" placeholder="Veuillez entrez le marque" required>
            <label>Modèle</label>
            <input type="text" name="modele" placeholder="Veuillez entrez le modèle" required>
            <label>Couleur</label>
            <input type="text" name="couleur" placeholder="Veuillez entrez une couleur" required>
            <label>Année</label>
            <input type="text" name="annee" placeholder="Veuillez entrer l'année" required pattern="[1-9][0-9]*">
            <label>Kilometrage</label>
            <input type="text" name="kilometrage" placeholder="Veuillez entrer le kilometrage" required pattern="[1-9][0-9]*">
            <label>Prix</label>
            <input type="text" name="prix" placeholder="Veuillez entrer le prix" required pattern="[1-9][0-9]*">
            <input type="radio" name="visibilite" value="OUI" <?php checked(!isset($options['visibilite_defaut']) || $options['visibilite_defaut'] === 'OUI') ?>>
            <label for="visibilite">Visible</label><br>
            <input type="radio" name="visibilite" value="NON" <?php checked(!isset($options['visibilite_defaut']) || $options['visibilite_defaut'] === 'NON') ?>>
            <label for="visibilite">Non visible</label><br>
            <input type="submit" style="margin-top: 30px;" name="submitted" value="Envoyez">
        </form>
    <?php else :
    ?>
        <section>
            <span style="text-align:center;">Vous devez vous <a href="../wp-admin"><strong>connecter</strong></a> ou vous ne disposez pas droit suffisant</span>
        </section>
    <?php
    endif;
}

/**
 * Insertion d'une recette dans la table annonces
 *
 * @param none
 * @return none
 */
function insert_tpPlugin()
{
    global $post;
    // si le bouton submit est cliqué
    if (isset($_POST['submitted'])) {
        // assainir les valeurs du formulaire
        $marque        = sanitize_text_field($_POST["marque"]);
        $modele  = sanitize_text_field($_POST["modele"]);
        $couleur = sanitize_text_field($_POST["couleur"]);
        $annee    = sanitize_text_field($_POST["annee"]);
        $kilometrage    = sanitize_text_field($_POST["kilometrage"]);
        $prix    = sanitize_text_field($_POST["prix"]);
        $visibilite = $_POST['visibilite'];

        //Permet d'aller chercher l id de l'utilsateur
        $id_utilisateur = get_current_user_id();


        // insertion dans la table
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'annonces',
            array(
                'marque' => $marque,
                'modele' => $modele,
                'couleur' => $couleur,
                'annee' => $annee,
                'kilometrage' => $kilometrage,
                'prix' => $prix,
                'id_utilisateur' => $id_utilisateur,
                'visibilite' => $visibilite


            ),
            array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%d',
                '%s'
            )
        );
    ?>
        <p>L'annonce a été enregistrée.</p>
    <?php
    }
}

/**
 * Exécution du code court (shortcode) de saisie d'une annonce 
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function shortcode_input_form_tpPlugin()
{
    ob_start(); // temporisation de sortie
    insert_tpPlugin();
    html_form_tpPlugin_code();
    return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher et traiter le formulaire
add_shortcode('saisie_annonce', 'shortcode_input_form_tpPlugin');


/*===========================================
========Création Page des annonces==========
============================================*/

/**
 * Création de la page de liste des recettes
 *
 * @param none
 * @return echo html list recipes code
 */
function tpPlugin_html_list_code()
{

    /* Affichage d'un lien vers le formulaire de saisie d'une recette pour l'administrateur du site
	   -------------------------------------------------------------------------------------------- */
    ?>
    <section style="margin: 0 auto; width: 80%; max-width: 100%; padding: 0">

        <?php
        global $wpdb;

        //Permet d'aller chercher les options de l'extension
        $options = get_option('tpPlugin_settings');

        //Lien vers ajout d'une annonce
        if (current_user_can('administrator') || (current_user_can('contributor') && $options["droit_contributeur"] == "OUI") || (current_user_can('editor') && $options["droit_editeur"] == "OUI") || (current_user_can('author') && $options["droit_auteur"] == "OUI")) :
        ?>
            <a href="../saisie-annonce">Saisie d'une annonce</a>
        <?php
        endif;

        //Initiation des variables a null par défaut
        $annonces_search = '';
        $lastColor = "";
        $couleurChoisi = null;
        $prixChoisi = null;
        $couleurArray = array();
        $i = 0;
        $tri = "ASC";


        //Permet d'aller les critere de recherche
        if (isset($_POST['submitted'])) {
            if ($_POST["selectCouleur"] !== "null") {
                $couleurChoisi = $_POST["selectCouleur"];
            }
            if ($_POST["slider_prix"] !== "null") {
                $prixChoisi = $_POST["slider_prix"];
            }
            //Permet d<aller chercher le sense du tri
            $tri=$_POST['orde'];
        }

        if (isset($_POST['annonces_search'])) :
            $annonces_search = trim($_POST['annonces_search']);
        endif;
        /* Affichage de la liste des annonces 
	   ---------------------------------- */

        //Création de la requête sql 
        $sql  = "SELECT * FROM $wpdb->prefix" . "annonces
         WHERE marque LIKE '%s' OR modele LIKE '%s'";
        $sql .= ($couleurChoisi !== null) ? " AND couleur = '$couleurChoisi' " : "";
        $sql .= ($prixChoisi !== null) ? " HAVING prix < $prixChoisi" : "";
        $sql .= " ORDER BY prix $tri";


        //Permet de binder les parametre
        $annonces = $wpdb->get_results($wpdb->prepare($sql, '%' . $annonces_search . '%', '%' . $annonces_search . '%'));

        ?>
        <!-- FORMUALIRE DU FILTRAGE DE ANNOCE -->
        <form style="margin-top: 30px" action="<?= esc_url($_SERVER['REQUEST_URI']) ?>" method="post">
            <input type="text" style="display: inline-block; width: 500px; padding: 0 10px; line-height: 50px" name="annonces_search" placeholder="Filtrer les marques ou modèle contenant cette chaîne de caractères" value="<?= $annonces_search ?>">
            <br>
            <label for="slider_prix">Prix</label>
            <input type="range" min="1000" max="50000" value="null" name="slider_prix" id="myRange">
            <p>Prix: <span id="demo"></span></p>
            <br>
            <label for="couleur">Couleur</label>
            <select name="selectCouleur">
                <option value="null">TOUT</option>
                <!-- Permet de supprimer les doublons couleur -->
                <?php foreach ($annonces as $couleur) :
                    $couleurArray[$i] =  strtolower($couleur->couleur);
                    $i++;
                endforeach;
                $couleurArray = array_unique($couleurArray);
                // Créé la liste des couleurs
                foreach ($couleurArray as $couleur) :
                ?>
                    <option value="<?= $couleur ?>"><?= $couleur ?></option>
                <?php
                endforeach;
                ?>
            </select>
            <br>
            <input type="radio"  name="orde" value="DESC"  <?php checked(!isset($_POST['orde']) || $tri === 'DESC') ?>>
            <label >Decroissant</label><br>
            <input type="radio" name="orde" value="ASC" <?php checked(!isset($_POST['orde']) || $tri === 'ASC') ?>>
            <label >Croissant</label><br>

            <input type="submit" style="display: inline-block; margin-left: 20px; padding: 0 24px; line-height: 50px;" name="submitted" value="Envoyez">
        </form>
        <!-- SCRIPT PERMETANT DE MONTRER LA VELEUR DU SLIDER -->
        <script>
            var slider = document.getElementById("myRange");
            var output = document.getElementById("demo");
            output.innerHTML = slider.value;
            output.innerHTML = "TOUT";

            slider.oninput = function() {
                output.innerHTML = this.value + " $";
            }
        </script>
        <?php
        //JESAIS PAS SERT À QUOI TODO
        if (count($annonces) > 0) :
        ?>
            <?php
            //Boucle affichant les annonces
            foreach ($annonces as $annonce) :
                //Doit aller chercher le nombre jour ecouler depuis la publication de l'annonce
                $date = $annonce->date_creation;
                //Permet de le mettre dans le format compatible
                $date = str_replace("/", "-", $date);
                $oDateNaissance = new DateTime($date);
                $oDateInterval = $oDateNaissance->diff(new DateTime('now'));
                $intervale =  $oDateInterval->format('%d');

            ?>
                <?php if ($options["nombre_jours"] > $intervale || $options["nombre_jours"] == 0) : ?>
                    <?php if ($annonce->visibilite !== "NON" || current_user_can('administrator') || get_current_user_id() == $annonce->id_utilisateur) : ?>
                        <hr>
                        <article style="display: flex">
                            <h4 style="margin: 0; width: 300px;">
                                <?= "Annonce: " . ($annonce->id) ?>
                                <br>
                                <!-- Permet de montrer seulement pour l'admin ou le créateur de l'annonce -->
                                <?php if (current_user_can('administrator') || get_current_user_id() == $annonce->id_utilisateur) :  ?>
                                    <a href="<?php echo "annonce-modification" . '?page=' . stripslashes($annonce->marque) . '&id=' . $annonce->id
                                                ?>">Modification</a>
                                    <a href="<?php echo "annonce-suppression" . '?page=' . stripslashes($annonce->marque) . '&id=' . $annonce->id
                                                ?>">Suppression</a>
                                <?php endif; ?>

                            </h4>

                            <div>
                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Marque: </p>
                                    <p style="padding: 5px"><?= $annonce->marque ?></p>
                                </div>

                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Modele:</p>
                                    <p style="padding: 5px"><?= $annonce->modele ?></p>
                                </div>

                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Couleur:</p>
                                    <p style="padding: 5px"><?= $annonce->couleur ?></p>
                                </div>

                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Temps:</p>
                                    <p style="padding: 5px"><?= $annonce->annee ?></p>
                                </div>
                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Temps:</p>
                                    <p style="padding: 5px"><?= $annonce->kilometrage ?> KM</p>
                                </div>
                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Prix:</p>
                                    <p style="padding: 5px"><?= $annonce->prix ?> $</p>
                                </div>
                                <div style="display: flex">
                                    <p style="width:250px; padding: 5px; color: #777">Date de création</p>
                                    <p style="padding: 5px"><?= $annonce->date_creation ?></p>
                                </div>
                                <div style="display: flex">
                                    <h5>Créé par utilisateur #<?= $annonce->id_utilisateur ?></h5>
                                </div>

                            </div>
                        </article>
                    <?php endif; ?>
                <?php endif; ?>
            <?php
            endforeach;
            ?>
            </table>
        <?php
        else :
        ?>
            <p>Aucune annonces n'est enregistrée.</p>
        <?php
        endif;
        ?>
    </section>
    <?php
}
/**
 * Exécution du code court (shortcode) d'affichage de la liste des recettes
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function tpPlugin_shortcode_list()
{
    ob_start(); // temporisation de sortie
    tpPlugin_html_list_code();
    return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher la liste des recettes
add_shortcode('liste_annonces', 'tpPlugin_shortcode_list');


function tpPlugin_html_suppression()
{

    global $wpdb;
    $numRow = 0;
    //Permet d'aller chercher l'id de l'annonce
    $annonce_id = isset($_GET['id']) ? $_GET['id'] : null;

    //Permet d'aller chercher les info de l'annoncces à supprimer
    $sql = "SELECT * FROM $wpdb->prefix" . "annonces WHERE id =%d";
    $annonce = $wpdb->get_row($wpdb->prepare($sql, $annonce_id));


    //Suppression de l'annonce
    if (isset($_POST["submitted"])) {
        //Execution de la
        if ($_POST["submitted"] == "OUI") {
            $numRow =  $wpdb->delete($wpdb->prefix . 'annonces', array('ID' => $annonce_id), array('%d'));
        }
    }
    if ($numRow > 0) :

    ?>
        <h2>Suppression effectuée</h2>
        <section>
            <a href="../annonces">Liste des annonces</a>
        </section>
    <?php
    else :
    ?> <section>
            <a href="../annonces">Liste des annonces</a>
        </section>
        <?php
        if (current_user_can('administrator') || get_current_user_id() == $annonce->id_utilisateur) :
            if ($annonce !== null) :
        ?>


                <div style="display: flex">
                    <p style="width:250px; padding: 5px; color: #777">Marque:</p>
                    <p style="padding: 5px"><?= stripslashes(nl2br($annonce->marque)) ?></p>
                </div>
                <div style="display: flex">
                    <p style="width:250px; padding: 5px; color: #777">Modele:</p>
                    <p style="padding: 5px"><?= stripslashes(nl2br($annonce->modele)) ?></p>
                </div>
                <div style="display: flex">
                    <p style="width:250px; padding: 5px; color: #777">Couleur:</p>
                    <p style="padding: 5px"><?= $annonce->couleur ?> </p>
                </div>
                <div style="display: flex">
                    <p style="width:250px; padding: 5px; color: #777">Kilometrage:</p>
                    <p style="padding: 5px"><?= $annonce->kilometrage ?> KM</p>
                </div>
                <form action="<?= esc_url($_SERVER['REQUEST_URI']) ?>" method="post">
                    <label for="submitted"><strong>Souhaitez-vous supprimer cette annonce</strong></label>
                    <input type="submit" style="display: inline-block; padding: 0 24px; line-height: 50px;" name="submitted" value="OUI">
                    <input type="submit" style="display: inline-block; padding: 0 24px; line-height: 50px;" name="submitted" value="NON">
                </form>
            <?php
            else :
            ?>
                <p>Aucune annonce pour cet identifiant.</p>
            <?php
            endif;
        else :
            ?>
            <div style="display: flex">
                <p style="padding: 5px; color: #777">Vous n'avez pas la permission</p>
            </div>
            </section>
        <?php
        endif;
    endif;
}

/**
 * Exécution du code court (shortcode) d'affichage d'une recette
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function tpPlugin_shortcode_single()
{
    ob_start(); // temporisation de sortie
    tpPlugin_html_suppression();
    return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher une recette
add_shortcode('annonce_suppression', 'tpPlugin_shortcode_single');


/*===========================================
========Création Page de annonce SUPP========
============================================*/

function tpPlugin_html_modification()
{
    global $wpdb;
    $numRow = 0;

    //Permet d'aller chercher le id
    $annonce_id = isset($_GET['id']) ? $_GET['id'] : null;

    //Permet d'aller chercher les details de l'annonce à modifier
    $sql = "SELECT * FROM $wpdb->prefix" . "annonces WHERE id =%d";
    $annonce = $wpdb->get_row($wpdb->prepare($sql, $annonce_id));

    //Éxecution de la suppression de l'annonce
    if (isset($_POST["submitted"])) {
        //Permet d'aller chercher les donnnés du form
        $marque        = sanitize_text_field($_POST["marque"]);
        $modele  = sanitize_text_field($_POST["modele"]);
        $couleur = sanitize_text_field($_POST["couleur"]);
        $annee    = sanitize_text_field($_POST["annee"]);
        $kilometrage    = sanitize_text_field($_POST["kilometrage"]);
        $prix    = sanitize_text_field($_POST["prix"]);
        $visibilite = $_POST['visibilite'];


        //Methode permetant de modifier l'annonce
        $numRow = $wpdb->update(
            $wpdb->prefix . "annonces",
            //Tableau à update
            array(
                'marque' => $marque,
                'modele' => $modele,
                'couleur' => $couleur,
                'annee' => $annee,
                'kilometrage' => $kilometrage,
                'prix' => $prix,
                'visibilite' => $visibilite
            ),
            array('ID' => $annonce_id),
            array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%s'
            )
        );
    }
    //Permet de confirmer la modification
    if ($numRow > 0) :
        ?>
        <h2>Modification effectuée</h2>
    <?php
    endif;
    ?>
    <section>
        <a href="annonces">Revenir à la liste</a>
    </section>

    <?php
    if (current_user_can('administrator') || get_current_user_id() == $annonce->id_utilisateur) :
        if ($annonce !== null || $numRow > 0) :
    ?>
            <!--Code HTML...-->
            <form action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>" method="post" enctype="multipart/form-data">
                <label>Marque de la voiture</label>
                <input type="text" name="marque" placeholder="Veuillez entrez le marque" value="<?= $annonce->marque ?>" required>
                <label>Modèle</label>
                <input type="text" name="modele" placeholder="Veuillez entrez le modèle" value="<?= $annonce->modele ?>" required>
                <label>Couleur</label>
                <input type="text" name="couleur" placeholder="Veuillez entrez une couleur" value="<?= $annonce->couleur ?>" required>
                <label>Année</label>
                <input type="text" name="annee" placeholder="Veuillez entrer l'année" value="<?= $annonce->annee ?>" required pattern="[1-9][0-9]*">
                <label>Kilometrage</label>
                <input type="text" name="kilometrage" placeholder="Veuillez entrer le kilometrage" value="<?= $annonce->kilometrage ?>" required pattern="[1-9][0-9]*">
                <label>Prix</label>
                <input type="text" name="prix" placeholder="Veuillez entrer le prix" value="<?= $annonce->prix ?>" required pattern="[1-9][0-9]*\.\d{2}">
                <input type="radio" name="visibilite" value="OUI" <?php checked($annonce->visibilite == "OUI") ?>>
                <label for="male">Visible</label><br>
                <input type="radio" name="visibilite" value="NON" <?php checked($annonce->visibilite == "NON") ?>>
                <label for="female">Non visible</label><br>
                <input type="submit" style="margin-top: 30px;" name="submitted" value="Envoyez">
            </form>
        <?php
        else :
        ?>
            <p>Aucune annonce pour cet identifiant.</p>
        <?php
        endif;
    else :
        ?>
        <div style="display: flex">
            <p style="padding: 5px; color: #777">Vous n'avez pas la permission</p>
        </div>
        </section>
    <?php
    endif;
    ?>
    </section>
<?php

}

/**
 * Exécution du code court (shortcode) d'affichage d'une recette
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function tpPlugin_shortcode_modif()
{
    ob_start(); // temporisation de sortie
    tpPlugin_html_suppression();
    return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher une recette
add_shortcode('annonce_modification', 'tpPlugin_html_modification');
