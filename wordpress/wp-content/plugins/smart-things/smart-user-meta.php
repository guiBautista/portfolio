<?php

if ( !class_exists( 'smartum' ) ) :

class smartum extends smart_things {

    function fields( $uroles ) {
        $uf = apply_filters( 'smart_user_meta', array() );
        $ret = array();
        foreach ( $uf as $slug => $atts ) {
            if ( !array_key_exists( 'roles', $atts ) ) $atts['roles'] = 'all';
            if ( !is_array( $atts['roles'] ) ) $atts['roles'] = explode( ',', $atts['roles'] );
            if ( array_intersect( $uroles, $atts['roles'] ) ) {
                $ret[$slug] = $atts;
            } elseif ( in_array( 'all', $atts['roles'] ) && ( ( !array_key_exists( 'exclude_roles', $atts ) ) || ( !array_intersect( $uroles, $atts['exclude_roles'] ) ) ) ) {
                $ret[$slug] = $atts;
            }
        }
        return $ret;
    }

    function init() {
        add_action( 'show_user_profile', array( __CLASS__, 'show_boxes' ) );
        add_action( 'edit_user_profile', array( __CLASS__, 'show_boxes' ) );
        add_action( 'personal_options_update', array( __CLASS__, 'save_meta' ) );
        add_action( 'edit_user_profile_update', array( __CLASS__, 'save_meta' ) );
    }

    function show_boxes( $ouser ) {
        $uf = call_user_func_array( array( __CLASS__, 'fields' ), array( $ouser->roles ) );

        if ( count( $uf ) ) :
            echo '<h3>' . __( 'Custom Fields', 'smart-things' ) . '</h3>';
            echo '<table class="form-table smart-things-admin">';
            echo '<tbody>';

            foreach ( $uf as $slug => $atts ) :
                $uf[$slug]['val'] = call_user_func_array( array( __CLASS__, 'get_meta' ), array( $slug, $ouser->ID ) );
            endforeach;
            call_user_func_array( array( __CLASS__, 'render_fields' ), array( $uf, 0, false ) );

            echo '</tbody>';
            echo '</table>';
        endif;
    }

    function save_meta( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) return FALSE;
        $ouser = get_userdata( $user_id );
        $uf = call_user_func_array( array( __CLASS__, 'fields' ), array( $ouser->roles ) );
        foreach ( array_keys( $uf ) as $slug ) :
            $value = call_user_func_array( array( 'smartcf', 'prepare_data_to_save' ), array( $_POST[ $slug ] ) );
            call_user_func_array( array( __CLASS__, 'set_meta' ), array( $slug, $user_id, $value ) );
        endforeach;
    }
    function get_meta( $field, $uid ) {
        return get_the_author_meta( $field, $uid );
    }
    function set_meta( $field, $uid, $value ) {
        return update_usermeta( $uid, $field, $value );
    }

}

add_action( 'init', array( 'smartum', 'init' ) );

endif;

?>
