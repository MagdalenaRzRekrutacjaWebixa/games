<?php
/**
 * Plugin Name: Games
 * Description: Wtyczka do tworzenia nowego typu postu "games" oraz taksonomii "tags".  Wysyła request do webhooka Discorda, gdy dodany zostanie nowy post typu "games".
 * Version: 1.0
 * Author: Magdalena Rzyska
 * License: GPL2
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

function register_games_post_type() {
    $args = array(
        'label'               => 'Games',
        'public'              => true,
        'show_ui'             => true,
        'supports'            => array('title', 'editor', 'author', 'custom-fields'),
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'games'),
        'show_in_rest'        => true,
    );

    register_post_type( 'games', $args );
}
add_action( 'init', 'register_games_post_type' );


function register_games_taxonomy() {
    $args = array(
        'hierarchical' => false,
        'label' => 'Tags',
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'game-tag' ),
    );

    register_taxonomy( 'tags', 'games', $args );
}
add_action( 'init', 'register_games_taxonomy' );


// Funkcja wysyłająca dane do webhooka Discorda
function send_data_to_discord($post_id) {

    if (get_post_type($post_id) !== 'games') {
        return;
    }

    $post_title = get_the_title($post_id);
    
    $tags = wp_get_post_terms($post_id, 'tags');
    $tags_list = [];
    foreach ($tags as $tag) {
        $tags_list[] = $tag->name;
    }

    $message = [
        'content' => "Nowy post typu 'games' został dodany:\n\n",
        'embeds' => [
            [
                'title' => $post_title,
                'description' => 'ID posta: ' . $post_id,
                'fields' => [
                    [
                        'name' => 'Tagi: ',
                        'value' => implode(', ', $tags_list),
                    ],
                    [
                        'name' => 'Wykonawca: ',
                        'value' => 'Magdalena Rzyska',
                    ],
                ],
            ],
        ],
    ];

    $webhook_url = 'https://discord.com/api/webhooks/1169628746588897280/8-vg6Kb0p0vtF81WiQHvogH50AN9XvWYhLSsRUHTns4eUgqj8Vcv3UNFhjcrkcQzEXVq';

    $args = array(
        'method'    => 'POST',
        'body'      => json_encode($message),
        'headers'   => array('Content-Type' => 'application/json'),
    );
    wp_remote_post($webhook_url, $args);

}

add_action('publish_games', 'send_data_to_discord');
