<?php

if ( !class_exists( 'smarttax' ) ) :

class smarttax extends smart_things {

    public function taxes() {
        return apply_filters( 'smart_taxonomies', array() );
    }

    public function init() {

        foreach ( call_user_func( array( __CLASS__, 'taxes' ) ) as $slug => $atts ) {
            $inner_atts = array_key_exists( 'attributes', $atts ) ? $atts[ 'attributes' ] : array();
            if ( !array_key_exists( 'name', $atts ) ) :
                if ( array_key_exists( 'labels', $inner_atts ) && is_array( $inner_atts[ 'labels' ] ) && array_key_exists( 'name', $inner_atts[ 'labels' ] ) ) :
                    $atts[ 'name' ] = $inner_atts[ 'labels' ][ 'name' ];
                else :
                    $atts[ 'name' ] = $slug;
                endif;
            endif;
            if ( !array_key_exists( 'singular_name', $atts ) ) :
                if ( array_key_exists( 'labels', $inner_atts ) && is_array( $inner_atts[ 'labels' ] ) && array_key_exists( 'singular_name', $inner_atts[ 'labels' ] ) ) :
                    $atts[ 'singular_name' ] = $inner_atts[ 'labels' ][ 'singular_name' ];
                else :
                    $atts[ 'singular_name' ] = $atts[ 'name' ];
                endif;
            endif;
            $low_name = mb_strtolower( $atts[ 'name' ] );
            $low_sing_name = mb_strtolower( $atts[ 'singular_name' ] );
            $labels = array(
                'name' => __( $atts['name'], 'smart-things' ),
                'singular_name' => __( $atts['singular_name'], 'smart-things' ),
                'search_items' => sprintf( __( 'Search %s', 'smart-things' ), $low_name ),
                'all_items' => sprintf( __( 'All %s', 'smart-things' ), $low_name ),
                'popular_items' => sprintf( __( 'Popular %s', 'smart-things' ), $low_name ),
                'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'smart-things' ), $low_name ),
                'add_or_remove_items' => sprintf( __( 'Add or remove %s', 'smart-things' ), $low_name ),
                'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'smart-things' ), $low_name ),
                'not_found' => sprintf( __( 'No %s found', 'smart-things' ), $low_name ),
                'parent_item' => sprintf( __( 'Parent %s', 'smart-things' ), $low_name ),
                'parent_item_colon' => sprintf( __( 'Parent %s:', 'smart-things' ), $low_sing_name ),
                'edit_item' => sprintf( __( 'Edit %s', 'smart-things' ), $low_sing_name ),
                'view_item' => sprintf( __( 'View %s', 'smart-things' ), $low_sing_name ),
                'update_item' => sprintf( __( 'Update %s', 'smart-things' ), $low_sing_name ),
                'add_new_item' => sprintf( __( 'Add %s', 'smart-things' ), $low_sing_name ),
                'new_item_name' => sprintf( __( 'New %s Name', 'smart-things' ), $low_sing_name ),
                'menu_name' => __( $atts['name'], 'smart-things' ),

            );
            $def = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_menu'      => true,
                'query_var'         => true
            );
            if ( is_array( $atts ) && array_key_exists( 'post_types', $atts ) ) {
                if ( !taxonomy_exists( $slug ) ) :
                    $atts['attributes'] = array_replace_recursive( $def, (array) $atts['attributes'] );
                else :
                    $atts['attributes'] = array_replace_recursive( (array) get_taxonomy( $slug ), (array) $atts['attributes'] );
                endif;
                register_taxonomy( $slug, $atts['post_types'], $atts['attributes'] );
                $arr_types = $atts['post_types'];
                if ( !is_array( $arr_types ) ) $arr_types = array( $arr_types );
                foreach ( $arr_types as $arr_type ) register_taxonomy_for_object_type( $slug, $arr_type );
            }
        }

        call_user_func( array( __CLASS__, 'metadata' ) );

    }

    function metadata() {
        if ( function_exists( 'get_term_meta' ) || class_exists( 'Taxonomy_Metadata' ) ) :
            $any_custom_field = false;
            foreach ( call_user_func( array( __CLASS__, 'taxes' ) ) as $tax => $atts ) :
                if ( array_key_exists( 'custom_fields', $atts ) && count( $atts['custom_fields'] ) ) :
                    add_action( $tax . '_add_form_fields', array( __CLASS__, 'add_taxonomy_fields' ), $tax );
                    add_action( $tax . '_edit_form_fields', array( __CLASS__, 'edit_taxonomy_fields' ), $tax );
                    $any_custom_field = true;
                endif;
            endforeach;
            if ( $any_custom_field ) :
                add_action( 'edit_term', array( __CLASS__, 'save_taxonomy_fields' ) );
                add_action( 'create_term', array( __CLASS__, 'save_taxonomy_fields' ) );
            endif;
        else:
            foreach ( call_user_func( array( __CLASS__, 'taxes' ) ) as $tax => $atts ) :
                if ( array_key_exists( 'custom_fields', $atts ) && count( $atts['custom_fields'] ) ) :
                    add_action( $tax . '_add_form_fields', array( __CLASS__, 'add_missing_plugin' ), $tax );
                    add_action( $tax . '_edit_form_fields', array( __CLASS__, 'edit_missing_plugin' ), $tax );
                endif;
            endforeach;
        endif;
    }

    function add_missing_plugin( $foo = 'bar' ) {
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'thickbox' );
        echo '<div class="error"><p><em>';
        echo sprintf( __( '"Smart Things" plugin depends on <a href="%s" class="thickbox">"Taxonomy Metadata"</a> plugin\'s funcionalities to extend default taxonomies\' info. We recommend <a href="%s">installing it.</a>', 'smart-things' ), admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=taxonomy-metadata&amp;TB_iframe=true' ), admin_url( 'plugins.php' ) );
        echo '</em></p></div>';
    }
    function edit_missing_plugin( $foo = 'bar' ) {
        echo '<tr><td colspan="2">';
        call_user_func_array( array( __CLASS__, 'add_missing_plugin' ), array( $foo ) );
        echo '</td></tr>';
    }

    function add_taxonomy_fields( $tax ) {
        $taxes = call_user_func( array( __CLASS__, 'taxes' ) );
        if ( array_key_exists( $tax, $taxes ) ) :
            $cf = $taxes[ $tax ]['custom_fields'];
            if ( count( $cf ) ) :
                echo '<tr><td>';
                call_user_func_array( array( __CLASS__, 'render_fields' ), array( $cf, 0, false ) );
                echo '</td></tr>';
            endif;
        endif;
    }
    function edit_taxonomy_fields( $pcat ) {
        $tax = $pcat->taxonomy;
        $taxes = call_user_func( array( __CLASS__, 'taxes' ) );
        if ( array_key_exists( $tax, $taxes ) ) :
            $cf = $taxes[ $tax ]['custom_fields'];
            if ( count( $cf ) ) :
                foreach ( $cf as $slug => $atts ) :
                    $cf[$slug]['val'] = get_term_meta( $pcat->term_id, '_smarttax_' . $slug, true );
                endforeach;
                echo '<tr><td>';
                call_user_func_array( array( __CLASS__, 'render_fields' ), array( $cf, 0, false ) );
                echo '</td></tr>';
            endif;
        endif;
    }
    function save_taxonomy_fields( $term_id ) {
        $tax = $_POST['taxonomy'];
        $taxes = call_user_func( array( __CLASS__, 'taxes' ) );
        if ( array_key_exists( $tax, $taxes ) ) :
            $cf = $taxes[ $tax ]['custom_fields'];
            if ( count( $cf ) ) :
                foreach ( $cf as $slug => $atts ) :
                    if ( array_key_exists( $slug, $_POST ) ) :
                        $val = $_POST[$slug];
                        if ( is_string( $val ) ) $val = get_magic_quotes_gpc() ? $val : addslashes( $val );
                        update_term_meta( $term_id, '_smarttax_' . $slug, $val );
                    else :
                        delete_term_meta( $term_id, '_smarttax_' . $slug );
                    endif;
                endforeach;
            endif;
        endif;
    }
    function first_custom( $term_id, $field, $default = '' ){
        $field = '_smarttax_' . $field;
        $ret = get_term_meta( $term_id, $field, true );
        if( $ret ) return $ret;
        return $default;
    }

}

add_action( 'init', array( 'smarttax', 'init' ) );

endif;

?>
