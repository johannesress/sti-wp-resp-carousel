<?php
/*
Plugin Name: STI Responsive Carousels
Plugin URI: http://stinformatik.eu
Description: Fügt einen Custom Post Type speziell für Carousels hinzu. Benötigt die "Simple Page Order" Erweiterung aus dem Wordpress Plugin Repository.
Version: 1.0
Author: Johannes Reß
Author URI: http://stinformatik.eu
License: GPLv2
*/

add_action( 'init', 'create_carouselpages' );

function create_carouselpages() {
    register_post_type( 'carouselpages',
        array(
            'labels' => array(
                'name' => 'Carouselseiten',
                'singular_name' => 'Carouselseite',
                'add_new' => 'Hinzufügen',
                'add_new_item' => 'Füge eine neue Carouselseite hinzu',
                'edit' => 'Bearbeiten',
                'edit_item' => 'Bearbeite die Carouselseite',
                'new_item' => 'Neue Carouselseite',
                'view' => 'Anzeigen',
                'view_item' => 'Betrachte Carouselseite',
                'search_items' => 'Durchsuchen',
                'not_found' => 'Keine Carouselseiten gefunden',
                'not_found_in_trash' => 'Keine Carouselseiten im Papierkorb',
                'parent' => 'Übergeornete Carouselseite'
            ),
 
            'public' => true,
            'menu_position' => 5,
            'hierarchical' => true,
            'supports' => array( 'title', 'thumbnail', 'excerpt', 'custom-fields'),
            'taxonomies' => array( 'carousels' ),
            'menu_icon' => 'dashicons-images-alt2',
            'has_archive' => true
        )
    );

    /* this ads your post categories to your custom post type */
    register_taxonomy_for_object_type('carousels', 'carouselpages');
}


/***********************************************************************************************/
/* Add two standart image sizes */
/***********************************************************************************************/
add_action( 'init', 'add_image_sizes' );

function add_image_sizes() {
    if (function_exists('add_theme_support')) {
        add_theme_support('post-thumbnails');
        add_image_size('sti-carousel-big', 1200, 400, true);
        add_image_size('sti-carousel-small', 800, 400, true);
    }
}


/***********************************************************************************************/
/* Kategorisierung einfügen */
/***********************************************************************************************/
add_action( 'init', 'create_carousels', 0 );

function create_carousels() {
    register_taxonomy(
        'carousels',
        'carouselpages',
        array(
            'labels' => array(
                'name' => 'Carousels',
                'rewrite' => array( 'slug' => 'sort' ),
                'add_new_item' => 'Neues Carousel hinzufügen',
                'new_item_name' => "Neues Carousel"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true
        )
    );
}


/***********************************************************************************************/
/* Sortierung nach Carousels ermöglichen */
/***********************************************************************************************/
add_filter( 'manage_edit-carouselpages_columns', 'my_columns' );

function my_columns( $columns ) {
    $columns['carousels'] = 'Carousel';
    unset( $columns['comments'] );
    return $columns;
}

add_action( 'manage_posts_custom_column', 'populate_columns' );

function populate_columns( $column ) {
    if ( 'carousels' == $column ) {
        $playlist = the_terms( get_the_ID(), 'carousels' ,  ' ' );
        echo $playlist;
    }
}

add_filter( 'manage_edit-modelle_sortable_columns', 'sort_me' );

function sort_me( $columns ) {
    $columns['carousels'] = 'carousels';
 
    return $columns;
}

add_action( 'restrict_manage_posts', 'my_filter_list' );

function my_filter_list() {
    $screen = get_current_screen();
    global $wp_query;
    if ( $screen->post_type == 'carouselpages' ) {
        wp_dropdown_categories( array(
            'show_option_all' => 'Zeige alle Carousels',
            'taxonomy' => 'carousels',
            'name' => 'carousels',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['carousels'] ) ? $wp_query->query['carousels'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
    }
}

add_filter( 'parse_query','perform_filtering' );

function perform_filtering( $query ) {
    $qv = &$query->query_vars;
    if ( ( $qv['carousels'] ) && is_numeric( $qv['carousels'] ) ) {
        $term = get_term_by( 'id', $qv['carousels'], 'carousels' );
        $qv['carousels'] = $term->slug;
    }
}


/***********************************************************************************************/
/* Adding METABOXES for Buttons and Links */
/***********************************************************************************************/

add_action( 'add_meta_boxes', 'add_carousel_metaboxes' );

function add_carousel_metaboxes() {
    add_meta_box('sti_carousel_details', 'Details', 'sti_carousel_details', 'carouselpages', 'side', 'default');
}

function sti_carousel_details() {
    global $post;
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="carousel_noncename" id="carousel_noncename" value="' .
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';


    $button = get_post_meta($post->ID, 'sti_carousel_button', true);
    echo '<p><label for="sti_carousel_button">Button</label>';
    echo '<input type="text" name="sti_carousel_button" value="' . $button  . '" class="widefat" /></p>';

    $link = get_post_meta($post->ID, 'sti_carousel_link', true);
    echo '<p><label for="sti_carousel_link">Link</label>';
    echo '<input type="text" name="sti_carousel_link" value="' . $link  . '" class="widefat" /></p>';
}


function sti_carousel_meta_save($post_id, $post) {
    if ( !wp_verify_nonce( $_POST['carousel_noncename'], plugin_basename(__FILE__) )) {
    return $post->ID;
    }

    if ( !current_user_can( 'edit_post', $post->ID ))
        return $post->ID;

    $carousel_meta['sti_carousel_button'] = $_POST['sti_carousel_button'];
    $carousel_meta['sti_carousel_link'] = $_POST['sti_carousel_link'];

    foreach ($carousel_meta as $key => $value) { 
        if( $post->post_type == 'revision' ) return; 
        $value = implode(',', (array)$value); 
        if(get_post_meta($post->ID, $key, FALSE)) {
            update_post_meta($post->ID, $key, $value);
        } else { 
            add_post_meta($post->ID, $key, $value);
        }
        if(!$value) delete_post_meta($post->ID, $key); 
    }
}
add_action('save_post', 'sti_carousel_meta_save', 1, 2);


/***********************************************************************************************/
/* Display Carousel Functionality */
/***********************************************************************************************/
function display_sti_carousel($atts) {

    extract(shortcode_atts(array(
      'carousel_name' => 'home',
      'image_size' => 'sti-carousel-big',
      'fade' => 'false',
      'autoplay' => 'false',
      'arrows' => 'false',
      'speed' => '500',
      'autoplayspeed' => '1000',
      'slidesToShow' => '1',
      'slidesToScroll' => '1',
    ), $atts));

    $slickArguments = "<script>".
                    "window.slickAttrs = {".
                    "'fade': ". $fade . ",".
                    "'autoplay': ". $autoplay . ",".
                    "'arrows': ". $arrows . ",".
                    "'speed': ". $speed . ",".
                    "'slidesToShow': ". $slidesToShow . ",".
                    "'slidesToScroll': ". $slidesToScroll . ",".
                    "'autoplaySpeed': ". $autoplayspeed .
                    "}".
                    "</script>";

    $args = array( 'post_type' => 'carouselpages', 'carousels' => $carousel_name, 'posts_per_page' => 100, 'orderby' => 'menu_order', 'order' => 'asc' );

    $html = $slickArguments . "<div class='sti-carousel-container'><div class='sti-carousel'>";

    $myposts = get_posts( $args );

    foreach ( $myposts as $post ) : 
        setup_postdata( $post );

        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $image_size);
        $url = $thumb['0'];

        $html .= '<div class="sti-carousel-item" style="background-image: url('.$url.');">';

            $post = get_post($post->ID);
            $title = get_the_title($post->ID);
            $button = get_post_meta($post->ID, "sti_carousel_button", true);
            $link = get_post_meta($post->ID, "sti_carousel_link", true);

            if($link) :
                $html .= '<a href="'.$link.'" class="sti-carousel-link">';
            else :
                $html .= '<a class="sti-carousel-link">';
            endif;


            $html .= '<span class="sti-carousel-title">'.$title.'</span>';
            $html .= '<p class="sti-carousel-excerpt">'.$post->post_excerpt.'</p>';

            if($button) :
                $html .= '<p><span class="button sti-carousel-button">'.$button.'</span></p>';
            endif;

            $html .= '</a>';

        $html .= '</div>';
    endforeach;
    
    wp_reset_postdata();

    $html .= "</div><span class='sti-carousel-prev'><span class='icon-arrow-left2'></span></span><span class='sti-carousel-next'><span class='icon-arrow-right2'></span></span></div>";

    return $html;
}

add_shortcode('sti_carousel', 'display_sti_carousel');


/***********************************************************************************************/
/* Prepare Scripts, images and styles for Carousel */
/***********************************************************************************************/
add_action( 'wp_enqueue_scripts', 'sti_carousel_stylesheet_and_scripts' );
 
function sti_carousel_stylesheet_and_scripts() {
    wp_enqueue_style( 'prefix-style', plugins_url('slick/slick.css', __FILE__) );
    wp_enqueue_script('jquery');
    wp_enqueue_script('slick', plugins_url('slick/slick.min.js', __FILE__), array('jquery'), false, true);
}


?>