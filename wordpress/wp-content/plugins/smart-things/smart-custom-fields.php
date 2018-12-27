<?php

if ( !class_exists( 'smartcf' ) ) :

class smartcf extends smart_things {

    function fields( $ptype ) {
        $cf = apply_filters( 'smart_custom_fields', array() );
        $ret = array();
        foreach ( $cf as $slug => $atts ) {
            if ( in_array( $ptype, $atts['post_types'] ) ) {
                $ret[$slug] = $atts;
            } elseif ( in_array( 'all', $atts['post_types'] ) && ( ( !array_key_exists( 'exclude_types', $atts ) ) || ( !in_array( $ptype, $atts['exclude_types'] ) ) ) ) {
                $ret[$slug] = $atts;
            }
        }
        return $ret;
    }

    function init() {
        add_action( 'do_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 2 );
        add_action( 'save_post', array( __CLASS__, 'save' ) );
        add_action( 'edit_attachment', array( __CLASS__, 'save' ), 1 );
        add_action( 'get_header', array( __CLASS__, 'redirect' ) );
        add_filter( 'post_type_link', array( __CLASS__, 'redirect_permalink' ), 10, 2 );
        add_filter( 'page_link', array( __CLASS__, 'redirect_permalink' ), 10, 2 );
        add_filter( 'post_link', array( __CLASS__, 'redirect_permalink' ), 10, 2 );
        add_action( 'the_post', array( __CLASS__, 'load_post' ) );
        add_filter( 'the_content', array( __CLASS__, 'filter_content' ) );
        add_filter( 'the_editor_content', array( __CLASS__, 'filter_content' ) );
        add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'rest_filter_content' ) );
    }
    function add_meta_box( $page ) {
        global $post;
        $cf = call_user_func_array( array( __CLASS__, 'fields' ), array( $post->post_type ) );
        if ( count( $cf ) ) add_meta_box( 'smartcf_customfields', __( 'Smart Custom Fields', 'smart-things' ), array( __CLASS__, 'admin' ), $page, 'normal', 'default', array( '__block_editor_compatible_meta_box' => true, '__back_compat_meta_box' => false ) );
    }

    function admin() {
        global $post;
        $cf = call_user_func_array( array( __CLASS__, 'fields' ), array( $post->post_type ) );
        foreach ( $cf as $slug => $atts ) :
            $cf[$slug]['val'] = call_user_func_array( array( __CLASS__, 'first_custom' ), array( $slug, $post->ID ) );
        endforeach;
        call_user_func_array( array( __CLASS__, 'render_fields' ), array( $cf, $post->ID ) );
    }

    function save( $pid ) {
        if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined('DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) return $pid;

        if ( ( !array_key_exists( 'smart_things_noncename', $_POST ) ) || ( !wp_verify_nonce( $_POST['smart_things_noncename'], plugin_basename(__DIR__) ) ) ) {
            return $pid;
        }

        if ( !current_user_can( 'edit_post', $pid ) ) return $pid;
        $cf = call_user_func_array( array( __CLASS__, 'fields' ), array( $_POST['post_type'] ) );
        $toserialize = array();
        foreach ( $cf as $slug => $atts ) {
            $can_save_this = true;
            if ( array_key_exists( 'save_caps', $atts ) ) {
                if ( !is_array( $atts[ 'save_caps' ] ) ) $atts[ 'save_caps' ] = array( $atts[ 'save_caps' ] );
                foreach ( $atts[ 'save_caps' ] as $save_cap ) {
                    $can_save_this = ( $can_save_this && current_user_can( $save_cap ) );
                    if ( !$can_save_this ) break;
                }
            }
            if ( $can_save_this ) {
                $saved = call_user_func_array( array( __CLASS__, 'save_custom' ), array( $slug, $pid ) );
                if ( array_key_exists( 'searchable', $atts ) && $atts['searchable'] )
                    $toserialize[ $slug ] = array( 'label' => $atts['label'], 'content' => $saved );
	        }
        }

        if ( count( $toserialize ) ) {
            $opost = get_post( $pid );
            $ocont = call_user_func_array( array( __CLASS__, 'filter_content' ), array( $opost->post_content ) );
            $pcontent = array();
            foreach ( $toserialize as $fs => $fc ) :
                $pcontent[] = '<div class="serialized_data custom_field_' . $fs . '"><strong>' . str_replace( '</div>', '&lt;/div&gt;', $fc['label'] ) . ':</strong> ' . str_replace( '</div>', '&lt;/div&gt;', $fc['content'] ) . '</div>';
            endforeach;
            $pcontent = apply_filters( 'smart_save_meta_to_content', $pcontent, $pid );
            $pcontent = '<hr class="serialized_data" />' . PHP_EOL . implode( PHP_EOL, $pcontent );
            $upp = array();
            $upp['ID'] = $pid;
            $upp['post_content'] = $ocont . PHP_EOL . PHP_EOL . $pcontent;
            remove_action( 'save_post', array( __CLASS__, 'save' ) );
            wp_update_post( $upp );
            add_action( 'save_post', array( __CLASS__, 'save' ) );
        }

        return $pid;
    }
    function filter_content( $content ) {
        $content = preg_replace( '|\s*<hr([^>]+)class\s*=\s*"([^"]*)serialized_data([^"]*)"([^>]*)>\s*|ims', '', $content );
        $content = preg_replace( '|\s*<div([^>]+)class\s*=\s*"([^"]*)serialized_data([^"]*)"([^>]*)>(.*?)</div>\s*|ims', '', $content );
        return $content;
    }
    function rest_filter_content( $req ) {
        if ( ( is_object( $req ) && property_exists( $req, 'data' ) ) && ( array_key_exists( 'content', $req->data ) ) && array_key_exists( 'raw', $req->data[ 'content' ] ) ) {
            $req->data[ 'content' ][ 'raw' ] = call_user_func_array( array( __CLASS__, 'filter_content' ), array( $req->data[ 'content' ][ 'raw' ] ) );
        }
        return $req;
    }

    function first_custom( $field, $pid = null, $default = '' ) {
        $ret = get_post_custom_values( '_smartcf_' . $field, $pid );
        if ( $ret && $ret[0] ):
            if ( is_admin() ):
                return $ret[0];
            else:
                return stripslashes( $ret[0] );
            endif;
        endif;
        return $default;
    }
    function load( $field, $pid = null, $default = '' ) {
        return call_user_func_array( array( __CLASS__, 'first_custom' ), array( $field, $pid, $default ) );
    }
    function explode( $field, $pid = null, $default = '' ) {
        $text = call_user_func_array( array( __CLASS__, 'first_custom' ), array( $field, $pid, $default ) );
        return call_user_func_array( array( __CLASS__, 'explode_text' ), array( $text ) );
    }

    function prepare_data_to_save( $value ) {
        if ( !is_array( $value ) ) $value = trim( get_magic_quotes_gpc() ? $value : addslashes( $value ) );
        if ( $value ) {
            if ( is_array( $value ) ) {
                $vk = array_keys( $value );
                if ( count( array_filter( $vk, 'is_numeric' ) ) == count( $vk ) ) {
                    $value = implode( ',', $value );
                } else {
                    $value = addslashes( json_encode( $value ) );
                }
            }
        }
        return $value;
    }
    function save_custom( $field, $pid, $value = null ) {
        if ( !isset( $value ) ) $value = $_POST[$field];
        $value = call_user_func_array( array( __CLASS__, 'prepare_data_to_save' ), array( $value ) );
        if ( $value ) {
            add_post_meta( $pid, '_smartcf_' . $field, $value, true ) or update_post_meta( $pid, '_smartcf_' . $field, $value );
        } else {
            delete_post_meta( $pid, '_smartcf_'.$field );
        }
        return $value;
    }

    function delete_custom( $field, $pid ) {
        call_user_func_array( array( __CLASS__, 'save_custom' ), array( $field, $pid, false ) );
    }

    function redirect() {
        if ( is_singular() ) {
            global $post;
            $urldestino = call_user_func_array( array( __CLASS__, 'first_custom' ), array( 'url', $post->ID ) );
            if ( $urldestino ) {
                header( 'Location: ' . $urldestino );
                exit();
            }
        }
    }

    function redirect_permalink( $link, $p ) {
        $urldestino = call_user_func_array( array( __CLASS__, 'first_custom' ), array( 'url', $p->ID ) );
        if ( $urldestino ) $link = $urldestino;
        return $link;
    }

    function load_post( $post ) {
        $cf = call_user_func_array( array( __CLASS__, 'fields' ), array( get_post_type( $post ) ) );
        foreach ( $cf as $field => $args ) {
            $value = call_user_func_array( array( __CLASS__, 'first_custom' ), array( $field, $post->ID ) );
            if ( !$value ) $value = ( array_key_exists( 'default', $args ) ? $args['default'] : '' );
            $propname = "smart_$field";
            $post->$propname = $value;
        }
        return $post;
    }

}

add_action( 'init', array( 'smartcf', 'init' ) );

endif;

?>
