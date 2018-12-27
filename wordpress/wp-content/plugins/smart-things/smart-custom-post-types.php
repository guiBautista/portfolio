<?php

if ( ( !class_exists( 'smartcpt' ) ) ) :

class smartcpt extends smart_things {

    public function post_types( $first = false ) {
        $cpt = apply_filters( 'smart_custom_post_types', array() );
        return $first ? array_shift( array_keys( $cpt ) ) : $cpt;
    }
    
    public function init() {

        $ct = 0;
        foreach ( call_user_func( array( __CLASS__, 'post_types' ) ) as $slug => $atts ) {
            if ( !array_key_exists( 'name', $atts ) ) $atts['name'] = $slug;
            if ( !array_key_exists( 'singular_name', $atts ) ) $atts['singular_name'] = $atts['name'];
            $def = array(
                'labels' => array(
                    'name' => __( $atts['name'], 'smart-things' ),
                    'singular_name' => __( $atts['singular_name'], 'smart-things' ),
                    'add_new' => sprintf( __( 'Insert %s', 'smart-things' ), __( $atts['singular_name'] ) ),
                    'add_new_item' => sprintf( __( 'Add %s', 'smart-things' ), __( $atts['singular_name'] ) ),
                    'edit_item' => sprintf( __( 'Edit %s', 'smart-things' ), __( $atts['singular_name'] ) ),
                    'new_item' => sprintf( __( 'New %s', 'smart-things' ), __( $atts['singular_name'] ) ),
                    'all_items' => __( $atts['name'], 'smart-things' ),
                    'view_item' => sprintf( __( 'View %s', 'smart-things' ), __( $atts['singular_name'] ) ),
                    'search_items' => sprintf( __( 'Search %s', 'smart-things' ), __( $atts['singular_name'] ) ),
                    'not_found' =>  __( 'Nothing found', 'smart-things' ),
                    'not_found_in_trash' => __( 'Nothing found in trash', 'smart-things' ), 
                    'menu_name' => __( $atts['name'], 'smart-things' ),
                ),
                'description' => __( $atts['name'], 'smart-things' ),
                'public' => true,
                'has_archive' => true,
                #'rewrite' => array( 'slug' => $slug ),
                'supports' => array( 'title', 'thumbnail', 'excerpt', 'page-attributes' ),
                #'menu_position' => 25,
            );
            $atts['attributes'] = array_merge( $def, (array) $atts['attributes'] );
            register_post_type( $slug, $atts['attributes'] );
            $ct++;
        }

    }

}

add_action( 'init', array( 'smartcpt', 'init' ) );

endif;

?>
