<?php

if ( !class_exists( 'smartopt' ) ) :

class smartopt extends smart_things {

    function settings() {
        return apply_filters( 'smart_options', array() );
    }

    function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_options_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'options_init' ) );
    }

    function options_init() {
        $sections = call_user_func( array( __CLASS__, 'settings' ) );
        foreach ( $sections as $s_slug => $s_args ) :
            add_settings_section( $s_slug, __( $s_args['title'], 'smart-things' ), array( __CLASS__, 'section_description' ), 'smart_things_options' );
            foreach ( $s_args['options'] as $o_slug => $o_args ) :
                add_settings_field( 'smart_things_option_id_' . $o_slug, $o_args['title'], array( __CLASS__, 'option_field' ), 'smart_things_options', $s_slug, array_merge( array( 'slug' => $o_slug, 'label_for' => $o_slug ), $o_args ) );
                register_setting( 'smartopt', $o_slug );
            endforeach;
        endforeach;
    }

    function section_description( $s ) {
        echo call_user_func( array( __CLASS__, 'get_section_description' ), $s );
    }
    function get_section_description( $s ) {
        $ret = '';
        $sections = call_user_func( array( __CLASS__, 'settings' ) );
        foreach ( $sections as $s_slug => $s_args ) :
            if ( array_key_exists( 'description', $s_args ) && $s_args['description'] && $s_slug == $s['id'] ) :
                $ret = '<p class="settings_section_description">' . __( $s_args['description'], 'smart-things' ) . '</p>';
                break;
            endif;
        endforeach;
        return $ret;
    }

    function option_field( $o ) {
        call_user_func( array( __CLASS__, 'get_option_field' ), $o );
    }
    function get_option_field( $atts ) {
        $atts['label'] = $atts['title'];
        $atts['val'] = get_option( $atts['slug'] );
        $ph = ( array_key_exists( 'placeholder', $atts ) && $atts['placeholder'] ) ? ' placeholder="' . esc_attr( $atts['placeholder'] ) . '" ' : '';
        
        $fields = array( $atts['slug'] => $atts );
        call_user_func_array( array( __CLASS__, 'render_fields' ), array( $fields, 0, false, false, false ) );
    }

    function add_options_page() {
        $sections = call_user_func( array( __CLASS__, 'settings' ) );
	    if ( count( $sections ) ) add_submenu_page( apply_filters( 'smart_options_parent', 'themes.php' ), __( 'Theme Options', 'smart-things' ), __( 'Theme Options', 'smart-things' ), 'edit_theme_options', 'smart_things_options', array( __CLASS__, 'theme_options' ) );
    }

    function theme_options() {
        ?>
        <div class="wrap smart_things_options">

        <div id="icon-options-general" class="icon32"><br /></div>

        <h2><?php _e( 'Theme Options', 'smart-things' ) ;?></h2>

        <?php if ( array_key_exists( 'settings-updated', $_GET ) && $_GET['settings-updated'] == 'true' ) : ?><div id="message" class="updated"><p><?php _e( 'Options successfully saved.', 'smart-things' ); ?></p></div><?php endif; ?>

        <form method="post" action="options.php">
        <?php
        settings_fields( 'smartopt' );
        do_settings_sections( 'smart_things_options' );
        submit_button();
        ?>
        </form>

        </div>
        <?php
    }

}

add_action( 'init', array( 'smartopt', 'init' ) );

endif;

?>
