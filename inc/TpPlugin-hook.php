<?php

/**
 * Force le crochet de filtres comments_open à faux pour les pages de l'extension,
 * ces pages sont identifiées par l'association de la métadonnée n41_recipes
 *
 * @param bool  $open    Whether the current post is open for comments
 * @param int   $post_id The current post ID
 * @return bool false
 */
function tpPlugin_close_hook_comments_open($open, $post_id)
{
    $single = true;
    $tpPlugin = get_post_meta($post_id, 'tpPlugin', $single);
    if ($tpPlugin !== '') {
        $open = false;
    }
    return $open;
}

// Ajout de la fonction n41_recipes_close_filter_comments_open au crochet de filtres comments_open 	
add_filter('comments_open', 'tpPlugin_close_hook_comments_open', 10, 2);
