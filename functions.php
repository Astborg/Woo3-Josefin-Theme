<?php

//add_filter( 'show_admin_bar', '__return_false' );


function custom_theme_styles() {
    wp_enqueue_style('custom-styles', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'custom_theme_styles');


function add_godispase_to_cart() {
    
    // Kontrollera att WooCommerce är aktivt och laddat
    if (class_exists('WooCommerce')) {
        if (isset($_GET['add_godispase_to_cart'])) {
            $godispase_id = intval($_GET['add_godispase_to_cart']);

            // Hämta relaterade produkter
            $related_products = get_post_meta($godispase_id, '_related_products', true);

            if (!empty($related_products) && is_array($related_products)) {
                foreach ($related_products as $product_id) {
                    $added = WC()->cart->add_to_cart($product_id);
                    if (!$added) {
                        error_log('Misslyckades med att lägga till produkt ID: ' . $product_id . ' till varukorgen.');
                    }
                }
            } else {
                error_log('Inga relaterade produkter hittades för godispåse ID: ' . $godispase_id);
            }

            // Omdirigera till varukorgen
            wp_safe_redirect(wc_get_page_permalink('cart'));
            exit;
        }
    } else {
        error_log('WooCommerce är inte laddat.');
    }
}

add_action('template_redirect', 'add_godispase_to_cart');




function mt_enqueue_styles() {
    // Registrera huvud stylesheet
    wp_enqueue_style( 'mt-style', get_stylesheet_uri() );

}

add_action( 'wp_enqueue_scripts', 'mt_enqueue_styles' );


function mt_register_my_menu() {
    register_nav_menu('header-menu', __( 'Header Menu' ));
}
add_action( 'init', 'mt_register_my_menu' );

function mt_create_form() {

    $options = '<option value="23">Lakritsbåt</option>
    <option value="25">Hallånbåt</option>';
    $products = apply_filters('create_form_products', array(0 => __('Lakritsbåt', 'mt'), 1 => __('Hallånbåt', 'mt')));
    $options = apply_filters('create_form_html_options', $options, $products);

    $fields = ' <input name="title" />
                <textarea name="description"></textarea>
                <select name="products[]" multiple>
                    '.$options.'
                </select>
                <input type="submit" />';
    
                $fields = apply_filters('create_form_html_field', $fields);


    $output = '<form method="'.apply_filters('create_form_html_form_method', 'POST').'" action="'.apply_filters('create_form_html_form_action', '').'">
               '.$fields.'
            </form>';

    $output = apply_filters('create_form_html', $output);

    echo($output);
}

add_action('create_form', 'mt_create_form');

function mt_output_create_form() {
    do_action('before_create_form');
    do_action('create_form');
    do_action('after_create_form');
}

add_action('output_create_form', 'mt_output_create_form');

function register_godispase_post_type() {
    $labels = array(
        'name' => __('Godispåsar'),
        'singular_name' => __('Godispåse'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields'),
        'taxonomies'  => array('category'), // Lägg till stöd för kategorier
        'hierarchical' => true,
        'menu_position' => 5,
        'show_in_rest' => true,
    );

    register_post_type('godispase', $args);
}
add_action('init', 'register_godispase_post_type');