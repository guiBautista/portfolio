<?php
/*
Plugin Name: Smart Things
Plugin URI: http://peka.wordpress.com.br
Description: Easy custom post types, custom fields, custom options, custom everything... This plugin does nothing until you call the proper hook in your functions.php (or wherever you want). Refer to documentation.
Author: Ederson Peka
Version: 0.1
Author URI: http://ederson.peka.nom.br
*/


if ( !class_exists( 'smart_things' ) ) :

class smart_things {

    private $things = array();

    function __construct( $things ) {
        $this->things = $things;
        add_filter( 'smart_custom_post_types', array( $this, '_custom_post_types' ) );
        add_filter( 'smart_taxonomies', array( $this, '_taxonomies' ) );
        add_filter( 'smart_custom_fields', array( $this, '_custom_fields' ) );
        add_filter( 'smart_options', array( $this, '_options' ) );
        add_filter( 'smart_user_meta', array( $this, '_user_meta' ) );
    }
    function _filter_things( $key ) {
        $ret = array();
        if ( is_array( $this->things ) && array_key_exists( $key, $this->things ) ) {
            $ret = $this->things[ $key ];
        }
        return $ret;
    }
    function _custom_post_types( $a = array() ) {
        return array_merge( $a, $this->_filter_things( 'custom_post_types' ) );
    }
    function _taxonomies( $a = array() ) {
        return array_merge( $a, $this->_filter_things( 'taxonomies' ) );
    }
    function _custom_fields( $a = array() ) {
        return array_merge( $a, $this->_filter_things( 'custom_fields' ) );
    }
    function _options( $a = array() ) {
        return array_merge( $a, $this->_filter_things( 'options' ) );
    }
    function _user_meta( $a = array() ) {
        return array_merge( $a, $this->_filter_things( 'user_meta' ) );
    }

    function render_fields( $cf, $pid = 0, $print_table = true, $print_table_tags = true, $print_nonce = true ) {

        if ( count( $cf ) ) :

            echo '<div class="smart_things_admin">';
            if ( $print_nonce ) echo '<input type="hidden" name="smart_things_noncename" id="smart_things_noncename" value="' . wp_create_nonce( plugin_basename(__DIR__) ) . '" />';
            ?>

            <?php if ( $print_table && $print_table_tags ) : ?>
                <table class="smart_things_admin">
                    <tbody>
            <?php endif; ?>

                <?php
                $showanywplink = false;
                foreach ( $cf as $slug => $atts ) :
                    $can_view_this = true;
                    if ( array_key_exists( 'view_caps', $atts ) ) {
                        if ( !is_array( $atts[ 'view_caps' ] ) ) $atts[ 'view_caps' ] = array( $atts[ 'view_caps' ] );
                        foreach ( $atts[ 'view_caps' ] as $view_cap ) {
                            $can_view_this = ( $can_view_this && current_user_can( $view_cap ) );
                            if ( !$can_view_this ) {
                                $can_save_this = false;
                                break;
                            }
                        }
                    }
                    if ( !$can_view_this ) continue;

                    $ph = array_key_exists( 'placeholder', $atts ) ? ' title="' . esc_attr( $atts['placeholder'] ) . '"' : '';
                    $related = false;
                    if ( 'related' == $atts['type'] || 'oldrelated' == $atts['type'] ) :
                        $args = array( 'post__not_in' => array( $pid ), 'order' => 'ASC', 'orderby' => 'title' );
                        $args = array_merge( $args, (array) $atts['query_args'] );
                        $related = get_posts( $args );
                    elseif ( 'relatedtax' == $atts['type'] ) :
                        $args = array( 'taxonomies' => array( 'categoria' ), 'order' => 'ASC', 'orderby' => 'title', 'hide_empty' => false );
                        $args = array_merge( $args, (array) $atts['query_args'] );
                        $related = get_terms( $args['taxonomies'], $args );
                    endif;
                    $data_template = '';
                    if ( array_key_exists( 'template', $atts ) ) $data_template = ' data-template="' . esc_attr( $atts['template'] ) . '"';

                    if ( 'hidden' == $atts['type'] ) : ?>

                        <?php if ( $print_table_tags ) : ?>
                            <tr class="hidden">
                                <th>
                                    <div class="smart_things smart_things_<?php echo $slug; ?>">
                                        <label for="<?php echo $slug; ?>"<?php echo $ph; ?>><?php _e( $atts['label'], 'smart-things' ); ?></label>
                                    </div>
                                </th>
                                <td>
                                    <?php call_user_func_array( array( __CLASS__, 'render_field' ), array( $slug, $atts, $related, $pid ) ); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <div class="hidden"><?php call_user_func_array( array( __CLASS__, 'render_field' ), array( $slug, $atts, $related, $pid ) ); ?></div>
                        <?php endif; ?>

                    <?php elseif ( ( ( 'related' != $atts['type'] ) && ( 'oldrelated' != $atts['type'] ) ) || $related ) : ?>
                        <?php if ( $print_table_tags ) : ?>
                            <tr<?php if ( array_key_exists( 'hidden', $atts ) && $atts['hidden'] ) echo ' class="hidden"'; echo $data_template; ?>>
                                <th>
                                    <div class="smart_things smart_things_<?php echo $slug; ?>">
                                        <?php if ( 'checkbox' == $atts['type'] ) : ?>
                                            &nbsp;
                                        <?php else : ?>
                                            <label for="<?php echo $slug; ?>"<?php echo $ph; ?>><?php _e( $atts['label'], 'smart-things' ); ?></label>
                                        <?php endif; ?>
                                    </div>
                                </th>
                                <td>
                                    <?php $showwplink = call_user_func_array( array( __CLASS__, 'render_field' ), array( $slug, $atts, $related, $pid ) ); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <div class="smart_things smart_things_<?php echo $slug; ?><?php if ( array_key_exists( 'hidden', $atts ) && $atts['hidden'] ) echo ' hidden'; ?>"<?php echo $data_template; ?>>
                                <?php $showwplink = call_user_func_array( array( __CLASS__, 'render_field' ), array( $slug, $atts, $related, $pid ) ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php $showanywplink = ( $showanywplink || $showwplink ); ?>
                <?php endforeach; ?>

            <?php if ( $print_table && $print_table_tags ) : ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if ( $showanywplink ) : ?>
                <?php if ( !class_exists( '_WP_Editors' ) ) include_once( ABSPATH . WPINC . '/class-wp-editor.php' ); ?>
                <div id="smart_things_wplink" class="wplink_container">
                    <div class="wplink_content ">
                        <div class="wrap">
                            <h2><?php _e( 'Insert Link', 'smart-things' ); ?></h2>
                            <p class="description wplink_sample"></p>
                            <?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>
                            <input type="search" autocomplete="off" name="wplink_search" class="wplink_search widefat" placeholder="<?php _e( 'Search', 'smart-things' ); ?>" />
                            <div class="wplink_results">
                                <ul data-not-found="<?php echo esc_attr( __( 'Nothing found', 'smart-things' ) ); ?>">
                                    <?php $lialt = false; foreach ( _WP_Editors::wp_link_query() as $item ) : $lialt = !$lialt; ?>
                                        <li<?php if ( $lialt ) : ?> class="alternate"<?php endif; ?> title="<?php echo esc_attr( get_permalink( $item['ID'] ) ); ?>"><?php echo get_the_title( $item['ID'] ); ?> <span class="item-info"><?php echo $item['info']; ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="submitbox">
                                <a class="submitdelete deletion wplink_cancel" href="#"><?php _e( 'Cancel', 'smart-things' ); ?></a>
	                            <input type="button" class="button button-primary button-large alignright wplink_save" value="<?php _e( 'Save', 'smart-things' ); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="wplink_close">
                        <a href="#"><?php _e( 'Close', 'smart-things' ); ?></a>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            echo '</div>';
            add_thickbox();
        endif;

    }

    function render_field( $slug, $atts, $related = array(), $pid = 0, $name = '' ) {
        global $shown;
        if ( !isset( $shown ) ) $shown = array();

        $can_save_this = true;
        if ( array_key_exists( 'save_caps', $atts ) ) {
            if ( !is_array( $atts[ 'save_caps' ] ) ) $atts[ 'save_caps' ] = array( $atts[ 'save_caps' ] );
            foreach ( $atts[ 'save_caps' ] as $save_cap ) {
                $can_save_this = ( $can_save_this && current_user_can( $save_cap ) );
                if ( !$can_save_this ) break;
            }
        }

        $sh_slug = $slug;
        $conta = 0;
        while ( in_array( $sh_slug, $shown ) ) :
            $conta++;
            $sh_slug .= '-' . $conta;
        endwhile;
        $shown[] = $sh_slug;
        if ( !$name ) $name = $slug;
        $ph = array_key_exists( 'placeholder', $atts ) ? ' placeholder="' . esc_attr( $atts['placeholder'] ) . '"' : '';
        $val = array_key_exists( 'val', $atts ) ? $atts['val'] : '';
        $showval = is_string( $val ) ? stripslashes( $val ) : $val;
        $showwplink = false;
        $dis_attr = $can_save_this ? '' : ' disabled="disabled"';
        $attr = $dis_attr;
        if ( isset( $atts[ 'attr' ] ) && is_array( $atts[ 'attr' ] ) ):
            foreach ( $atts[ 'attr' ] as $key => $value ) :
                $attr .= " $key=\"$value\"";
            endforeach;
        endif;
        ?>
        <div class="smart_things smart_things_<?php echo $slug; ?>">
            <?php if ( 'textarea' == $atts['type'] ) : ?>
                <textarea name="<?php echo $name; ?>" cols="30" rows="7" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $attr; ?>><?php echo esc_attr( htmlspecialchars( $showval ) ); ?></textarea>
            <?php elseif ( 'selectset' == $atts['type'] ) :
                $arr_val = is_array( $showval ) ? $showval : explode( ',', $showval );
                $arr_val = array_filter( $arr_val );
                $def_val = array_pad( $arr_val, count( $atts['options'] ), false );
                $crow = 1;
                if ( array_key_exists( 'maxitens', $atts ) ) $def_val = array_slice( $def_val, 0, $atts['maxitens'] );
                ?>
                <ol class="smart_things_selectset">
                <?php foreach ( $def_val as $row_val ) : ?>
                    <li>
                    <select name="<?php echo $name; ?>[]"<?php echo $dis_attr; ?>>
                            <?php if ( $atts['first_item'] ) : ?>
                                <option value=""><?php echo $atts['first_item']; ?></option>
                            <?php else : ?>
                                <option value=""><?php _e( 'Undefined', 'smart-things' ); ?></option>
                            <?php endif; ?>
                            <?php foreach ( $atts['options'] as $oslug => $oname ) : ?>
                                <option value="<?php echo $oslug; ?>"<?php if ( $oslug == $row_val ) : ?> selected="selected"<?php endif; ?>><?php _e( $oname, 'smart-things' ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </li>
                <?php $crow++; endforeach; ?>
                </ol>
            <?php elseif ( 'select' == $atts['type'] ) : if ( !is_array( $showval ) ) $showval = explode( ',', $showval ); ?>
                <?php $multiple = array_key_exists( 'multiple', $atts ) && $atts['multiple']; ?>
                <select name="<?php echo $name; ?>[]" id="<?php echo $sh_slug; ?>"<?php if ( $multiple ) : ?> multiple="multiple"<?php endif; ?><?php echo $attr; ?>>
                <?php foreach ( $atts['options'] as $oslug => $oname ) : ?>
                    <option value="<?php echo $oslug; ?>"<?php if ( in_array( $oslug, $showval ) ) : ?> selected="selected"<?php endif; ?>><?php _e( $oname, 'smart-things' ); ?></option>
                <?php endforeach; ?>
                </select>
            <?php elseif ( 'richedit' == $atts['type'] ) : ?>
                <?php call_user_func_array( array( __CLASS__, 'wp_editor' ), array( $showval ? $showval : '', $sh_slug, $name ) ); ?>
            <?php elseif ( 'oldrelated' == $atts['type'] ) :
                $inptype = ( array_key_exists( 'single', $atts ) && $atts['single'] ) ? 'radio' : 'checkbox';
                ?>
                <fieldset class="relatedset sortable">
                    <?php if ( array_key_exists( 'single', $atts ) && $atts['single'] ) : ?>
                        <label for="<?php echo $sh_slug; ?>-0"><input type="<?php echo $inptype; ?>" name="<?php echo $name; ?>[]" id="<?php echo $sh_slug; ?>-0" value="0" class="inpcheckbox"<?php echo $chk; echo $dis_attr; ?> /> <?php _e( '(None)', 'smart-things' ); ?></label><br />
                    <?php
                    endif;

                    $chkval = $showval;
                    #if ( !is_array( $chkval ) ) $chkval = (array) unserialize( $chkval );
                    if ( !is_array( $chkval ) ) $chkval = call_user_func_array( array( __CLASS__, 'explode_text' ), array( $chkval ) );
                    foreach ( $related as &$zr ) :
                        $rind = array_search( $zr->ID, $chkval );
                        if ( $rind === false ) $rind = count( $chkval );
                        $zr->chkindex = $rind;
                    endforeach;
                    usort ( $related, create_function( '$a,$b', 'return bccomp( $a->chkindex, $b->chkindex );' ) );
                    foreach ( $related as $r ) :
                        $chk = in_array( $r->ID, $chkval ) ? ' checked="checked"' : '';
                        $rtitle = get_the_title( $r->ID );
                        if ( array_key_exists( 'title_callback', $atts ) && function_exists( $atts['title_callback'] ) ) $rtitle = call_user_func_array( $atts['title_callback'], array( $r->ID ) );
                    ?>
                        <label for="<?php echo $sh_slug; ?>-<?php echo $r->ID; ?>"><input type="<?php echo $inptype; ?>" name="<?php echo $name; ?>[]" id="<?php echo $sh_slug; ?>-<?php echo $r->ID; ?>" value="<?php echo $r->ID; ?>" class="inpcheckbox"<?php echo $chk; echo $dis_attr; ?> /> <?php echo $rtitle; ?></label><br />
                    <?php endforeach; ?>
                </fieldset>

            <?php elseif ( 'related' == $atts['type'] ) :
                $inptype = ( array_key_exists( 'single', $atts ) && $atts['single'] ) ? 'radio' : 'checkbox';
                $tit_cb = array_key_exists( 'title_callback', $atts ) && function_exists( $atts['title_callback'] ) ? $atts['title_callback'] : false;
                ?>
                <fieldset class="<?php /*sortable */ ?>relatedset">
                    <?php
                    $chkval = $showval;
                    #if ( !is_array( $chkval ) ) $chkval = (array) unserialize( $chkval );
                    if ( !is_array( $chkval ) ) $chkval = call_user_func_array( array( __CLASS__, 'explode_text' ), array( $chkval ) );
                    $datarel = array();
                    $saved = array();
                    foreach ( $related as $r ) :
                        $rtitle = get_the_title( $r->ID );
                        if ( $tit_cb ) $rtitle = call_user_func_array( $tit_cb, array( $r->ID ) );
                        $datarel[] = array( 'r' => $r->ID, 't' => $rtitle );
                        if ( in_array( $r->ID, $chkval ) ) $saved[] = $rtitle;
                    endforeach;
                    ?>
                        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo implode( ',', $chkval ); ?>" data-type="<?php echo $inptype; ?>" data-related="<?php echo esc_attr( json_encode( $datarel ) ); ?>" data-slug="<?php echo $sh_slug; ?>" data-none-text="<?php echo esc_attr( __( '(None)', 'smart-things' ) ); ?>"<?php echo $dis_attr; ?> />
                    <?php if ( $saved ) : ?><span class="js-remove"><?php echo implode( ', ', $saved ); ?></span><?php endif; ?>
                </fieldset>

            <?php elseif ( 'images' == $atts['type'] ) : ?>

                <?php $single = array_key_exists( 'single', $atts ) && $atts['single']; ?>
                <input type="text" name="<?php echo $name; ?>" size="30" value="<?php echo esc_attr( htmlspecialchars( $showval ) ); ?>" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $dis_attr; ?> />
                <div class="smart_things_<?php echo $sh_slug; ?>_image_thumbs gallery-thumbs" rel="waittext:<?php _e( 'Wait...', 'smart-things' ); ?>;cleantext:<?php _e( 'Remove', 'smart-things' ); ?>;confirmclean:<?php _e( $single ? 'Do you want to detach this image?' : 'Do you want to detach these images?', 'smart-things' ); ?>;">
                    <?php echo call_user_func_array( array( __CLASS__, 'gallery_thumbs' ), array( $showval ) ); ?>
                </div>
                <a href="#<?php echo $sh_slug; ?>" class="button smart_things_gallery hidden gallery-link<?php if ( $single ) : ?> select-single-image<?php endif; ?>" title="<?php _e( $single ? 'Image' : 'Images', 'smart-things' ); ?>" rel="<?php _e( 'Insert', 'smart-things' ); ?>" alt=" "<?php echo $dis_attr; ?> /> <?php _e( $single ? 'Select image' : 'Select images', 'smart-things' ); ?></a>

            <?php elseif ( 'pdfs' == $atts['type'] ) : ?>

                <?php $single = array_key_exists( 'single', $atts ) && $atts['single']; ?>
                <input type="text" name="<?php echo $name; ?>" size="30" value="<?php echo esc_attr( htmlspecialchars( $showval ) ); ?>" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $dis_attr; ?> />
                <div class="smart_things_<?php echo $sh_slug; ?>_pdf_thumbs gallery-thumbs" rel="waittext:<?php _e( 'Wait...', 'smart-things' ); ?>;cleantext:<?php _e( 'Remove', 'smart-things' ); ?>;confirmclean:<?php _e( $single ? 'Do you want to detach this file?' : 'Do you want to detach these files?', 'smart-things' ); ?>;">
                    <?php echo call_user_func_array( array( __CLASS__, 'gallery_thumbs' ), array( $showval ) ); ?>
                </div>
                <a href="#<?php echo $sh_slug; ?>" class="button smart_things_gallery hidden gallery-link gallery-pdf<?php if ( $single ) : ?> select-single-image<?php endif; ?>" title="<?php _e( $single ? 'File' : 'Files', 'smart-things' ); ?>" rel="<?php _e( 'Insert', 'smart-things' ); ?>" alt=" "<?php echo $dis_attr; ?> /> <?php _e( $single ? 'Select file' : 'Select files', 'smart-things' ); ?></a>

            <?php elseif ( 'smartrelated' == $atts['type'] ) : ?>

                <?php $single = array_key_exists( 'single', $atts ) && $atts['single']; ?>
                <?php $pt = ( array_key_exists( 'query_args', $atts ) && array_key_exists( 'post_type', $atts['query_args'] ) ) ? $atts['query_args']['post_type'] : 'post'; ?>
                <input type="hidden" name="<?php echo $name; ?>" size="30" value="<?php echo esc_attr( htmlspecialchars( $showval ) ); ?>" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $dis_attr; ?> />
                <div class="smart_things_<?php echo $sh_slug; ?>_related_thumbs related-thumbs" rel="waittext:<?php _e( 'Wait...', 'smart-things' ); ?>;cleantext:<?php _e( 'Remove', 'smart-things' ); ?>;confirmclean:<?php _e( $single ? 'Do you want to detach this item?' : 'Do you want to detach these items?', 'smart-things' ); ?>;">
                    <?php echo call_user_func_array( array( __CLASS__, 'related_thumbs' ), array( $showval ) ); ?>
                </div>
                <a href="<?php echo admin_url( 'post-new.php?post_type=' . $pt ); ?>" class="button smart_things_related_gallery hidden related-thumbs-link" title="<?php _e( $single ? 'Item' : 'Items', 'smart-things' ); ?>" rel="<?php _e( 'Insert', 'smart-things' ); ?>" target="_blank"<?php echo $dis_attr; ?> /> <?php _e( 'New item', 'smart-things' ); ?></a>
                <a href="<?php echo admin_url( 'edit.php?post_type=' . $pt ); ?>" class="button smart_things_related_gallery hidden related-thumbs-link<?php if ( $single ) : ?> select-single-item<?php endif; ?>" title="<?php _e( $single ? 'Item' : 'Items', 'smart-things' ); ?>" rel="<?php _e( 'Insert', 'smart-things' ); ?>" target="_blank"<?php echo $dis_attr; ?> /> <?php _e( $single ? 'Select item' : 'Select items', 'smart-things' ); ?></a>

            <?php elseif ( 'relatedtax' == $atts['type'] ) :
                $inptype = ( array_key_exists( 'single', $atts ) && $atts['single'] ) ? 'radio' : 'checkbox';
                ?>
                <fieldset class="relatedset sortable">
                <?php
                    $chkval = is_array( $showval ) ? $showval : explode( ',', $showval );
                    if ( is_array( $related ) ) foreach ( $related as &$r ) :
                        $rind = array_search( $r->term_id, $chkval );
                        if ( $rind === false ) $rind = count( $chkval );
                        $r->chkindex = $rind;
                    endforeach;
                    usort ( $related, create_function( '$a,$b', 'return bccomp( $a->chkindex, $b->chkindex );' ) );
                    if ( is_array( $related ) ) foreach ( $related as $c ) {
                        $chk = in_array( $c->term_id, $chkval ) ? ' checked="checked"' : '';
                ?>
                        <label for="taxonomias-<?php echo $sh_slug . '_' . $c->term_id; ?>"><input type="<?php echo $inptype; ?>" name="<?php echo $sh_slug; ?>[]" id="taxonomias-<?php echo $sh_slug . '_' . $c->term_id; ?>" value="<?php echo $c->term_id; ?>" class="inpcheckbox"<?php echo $chk; echo $dis_attr; ?> /> <?php echo $c->name; ?></label><br />
                <?php } ?>
                </fieldset>
            <?php elseif ( 'checkbox' == $atts['type'] ) : ?>
                <label><input type="checkbox" name="<?php echo $name; ?>" size="30" value="1"<?php if ( $showval ) echo ' checked="checked"'; ?> id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $attr; ?> class="inpcheckbox" /> <?php _e( $atts['label'], 'smart-things' ); ?></label>
            <?php elseif ( 'link' == $atts['type'] ) : $showwplink = true; ?>
                <input type="text" name="<?php echo $name; ?>" size="30" value="<?php echo esc_attr( htmlspecialchars( $showval ) ); ?>" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $attr; ?> class="wplink" data-search-button-text="<?php _e( 'Search', 'smart-things' ); ?>" />
            <?php elseif ( 'colors' == $atts['type'] ) : ?>
                <input type="text" class="smart_field_colors" name="<?php echo $name; ?>" size="30" value="<?php echo esc_attr( htmlspecialchars( $showval ) ); ?>" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $dis_attr; ?> />
            <?php elseif ( 'fieldset' == $atts['type'] ) :
                $sval = (array) json_decode( $val );
                if ( array_key_exists( 'subfields', $atts ) && is_array( $atts['subfields'] ) ) :
                    $single = array_key_exists( 'single', $atts ) && $atts['single'];
                    $sshowwplink = false;
                    foreach ( $atts['subfields'] as $sslug => $satts ) :
                        ?>
                        <div class="smart_things_subfield">
                            <?php if ( 'checkbox' != $satts['type'] ) : ?>
                                <label for="<?php echo $sh_slug . '_' . $sslug; ?>"><?php _e( $satts['label'], 'smart-things' ); ?></label>
                            <?php endif;
                            if ( array_key_exists( $sslug, $sval ) ) $satts['val'] = $sval[ $sslug ];
                            $srelated = false;
                            if ( ( 'related' == $satts['type'] ) || ( 'oldrelated' == $atts['type'] ) ) :
                                $args = array( 'post__not_in' => array( $pid ), 'order' => 'ASC', 'orderby' => 'title' );
                                $args = array_merge( $args, (array) $satts['query_args'] );
                                $srelated = get_posts( $args );
                            endif;
                            $sshowwplink = call_user_func_array( array( __CLASS__, 'render_field' ), array( $sh_slug . '_' . $sslug, $satts, $srelated, $pid, $sh_slug . '[' . $sslug . ']' ) ) || $sshowwplink;
                        ?>
                        </div>
                        <?php
                    endforeach;
                    $showwplink = ( $showwplink || $sshowwplink );
                endif;
                ?>
            <?php else: ?>
                <input type="<?php echo $atts['type']; ?>" name="<?php echo $name; ?>" size="30" value="<?php echo esc_attr( htmlspecialchars( $showval ) ); ?>" id="<?php echo $sh_slug; ?>"<?php echo $ph; echo $attr; ?> />
            <?php endif; ?>
            <?php if ( array_key_exists( 'description', $atts ) && $atts[ 'description' ] ) : ?>
                <p class="description"><?php echo $atts[ 'description' ]; ?></p>
            <?php endif; ?>
        </div>
        <?php
        return $showwplink;
    }

    function gallery_thumbs( $ids, $size = 'thumbnail' ) {
        if ( !$size ) $size = 'thumbnail';
        $ret = '';
        if ( $ids ) :
            $some_img = false;
            $ret .= '<ul>';
            foreach ( array_filter( explode( ',', $ids ) ) as $att_ID ) :
                $img = wp_get_attachment_image( $att_ID, $size );
                if ( !$img ) :
                    $img = '<img src="' . home_url( '/' ) . WPINC . '/images/crystal/document.png" class="icon" draggable="false">';
                    $img .= '<div class="file_metadata">';
                    $img .= '<h3>' . get_the_title( $att_ID ) . '</h3>';
    		        $img .= '<ul>';
    		        $img .= '<li><a href="' . admin_url( 'post.php?post=' . $att_ID . '&action=edit' ) . '" target="_blank" class="edit_img_metadata">' . __( 'Edit', 'smart-things' ) . '</a></li>';
    		        $img .= '<li><a href="#" class="smart_things_remove_image_link">' . __( 'Remove file', 'smart-things' ) . '</a></li>';
    		        $img .= '</ul>';
                    $img .= '</div>';
                else :
                    $some_img = true;
                    $oimg = get_post( $att_ID );
                    $img .= '<div class="img_metadata">';
    		        $img .= '<h3>' . get_the_title( $att_ID ) . '</h3>';
                    if ( $oimg->post_content ) $img .= apply_filters( 'the_content', $oimg->post_content );
    		        $img .= '<ul>';
    		        $img .= '<li><a href="' . admin_url( 'post.php?post=' . $att_ID . '&action=edit' ) . '" target="_blank" class="edit_img_metadata">' . __( 'Edit', 'smart-things' ) . '</a></li>';
    		        $img .= '<li><a href="#" class="smart_things_remove_image_link">' . __( 'Remove image', 'smart-things' ) . '</a></li>';
    		        $img .= '</ul>';
    		        $img .= '</div>';
		        endif;
                $ret .= '<li rel="'.$att_ID.'">' . $img . '</li>';
            endforeach;
            $ret .= '</ul>';
            if ( $some_img ) $ret = '<a href="#" class="link_expand_data"><span class="collapsed_text">' . __( 'Expand data', 'smart-things' ) . '</span><span class="expanded_text">' . __( 'Collapse', 'smart-things' ) . '</span></a>' . $ret;
        endif;
        return $ret;
    }
    function ajax_gallery_thumbs() {
        header( 'Content-Type: application/json' );
        $images = array_key_exists( 'images', $_GET ) ? $_GET['images'] : null;
        $size = array_key_exists( 'size', $_GET ) ? $_GET['size'] : null;
        echo json_encode( call_user_func_array( array( __CLASS__, 'gallery_thumbs' ), array( $images, $size ) ) );
        exit;
    }

    function related_thumbs( $ids, $size = 'thumbnail' ) {
        if ( !$size ) $size = 'thumbnail';
        $ret = '';
        if ( $ids ) :
            $some_img = false;
            $ret .= '<ul>';
            foreach ( array_filter( explode( ',', $ids ) ) as $att_ID ) :
                $rpost = get_post( $att_ID );
                #$img = wp_get_attachment_image( get_post_thumbnail_id( $att_ID ), $size );
                #if ( !$img ) $img = '<img src="' . home_url( '/' ) . WPINC . '/images/crystal/document.png" class="icon" draggable="false">';
                $img = '<svg title="' . __( 'Drag and Drop', 'smart-things' ) . '" style="enable-background:new 0 0 32 32;" version="1.1" viewBox="0 0 32 32" width="20px" height="20px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M31.338,14.538L27.38,10.58C26.994,10.193,26.531,10,26,10c-1.188,0-2,1.016-2,2c0,0.516,0.186,0.986,0.58,1.38L25.2,14H18  V6.8l0.62,0.62C19.014,7.814,19.484,8,20,8c0.984,0,2-0.813,2-2c0-0.531-0.193-0.994-0.58-1.38l-3.973-3.974  C17.08,0.279,16.729,0,16,0s-1.135,0.334-1.463,0.662L10.58,4.62C10.193,5.006,10,5.469,10,6c0,1.188,1.016,2,2,2  c0.516,0,0.986-0.186,1.38-0.58L14,6.8V14H6.8l0.62-0.62C7.814,12.986,8,12.516,8,12c0-0.984-0.813-2-2-2  c-0.531,0-0.994,0.193-1.38,0.58l-3.958,3.958C0.334,14.866,0,15.271,0,16s0.279,1.08,0.646,1.447L4.62,21.42  C5.006,21.807,5.469,22,6,22c1.188,0,2-1.016,2-2c0-0.516-0.186-0.986-0.58-1.38L6.8,18H14v7.2l-0.62-0.62  C12.986,24.186,12.516,24,12,24c-0.984,0-2,0.813-2,2c0,0.531,0.193,0.994,0.58,1.38l3.957,3.958C14.865,31.666,15.271,32,16,32  s1.08-0.279,1.447-0.646l3.973-3.974C21.807,26.994,22,26.531,22,26c0-1.188-1.016-2-2-2c-0.516,0-0.986,0.186-1.38,0.58L18,25.2V18  h7.2l-0.62,0.62C24.186,19.014,24,19.484,24,20c0,0.984,0.813,2,2,2c0.531,0,0.994-0.193,1.38-0.58l3.974-3.973  C31.721,17.08,32,16.729,32,16S31.666,14.866,31.338,14.538z"/></svg>';
                $ret .= '<li rel="'.$att_ID.'">' . $img;
                $ret .= '<h4>' . get_the_title( $att_ID ) . '</h4>';
                if ( $rpost->post_excerpt ) $ret .= apply_filters( 'the_content', $rpost->post_excerpt );
                $ret .= apply_filters( 'smart_things_related_admin_extra_fields', '', $rpost );
                $ret .= '<a href="' . admin_url( 'post.php?post=' . $att_ID . '&action=edit' ) . '" target="_blank" class="edit_related_metadata">' . __( 'Edit', 'smart-things' ) . '</a>';
                $ret .= '</li>';
            endforeach;
            $ret .= '</ul>';
        endif;
        return $ret;
    }
    function ajax_related_thumbs() {
        header( 'Content-Type: application/json' );
        $items = array_key_exists( 'items', $_GET ) ? $_GET['items'] : null;
        $size = array_key_exists( 'size', $_GET ) ? $_GET['size'] : null;
        echo json_encode( call_user_func_array( array( __CLASS__, 'related_thumbs' ), array( $items, $size ) ) );
        exit;
    }

    function admin_style() {
        wp_register_style( 'smart_things_admin_css', plugins_url( '/styles/admin.css' , __FILE__ ), false, '0.1' );
        wp_enqueue_style( 'smart_things_admin_css' );
        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        } else {
            wp_enqueue_script( 'media-upload' );
        }
        wp_register_script( 'smart_things_admin_js', plugins_url( '/scripts/admin.js' , __FILE__ ), array( 'jquery', 'thickbox' ), filemtime( dirname( __FILE__ ) . '/scripts/admin.js' ), true );
        wp_enqueue_script( 'smart_things_admin_js' );
        $strings = array(
            'add' => __( 'add', 'smart-things' ),
            'del' => __( 'del', 'smart-things' ),
            'select' => __( 'Select', 'smart-things' ),
            'insert' => __( 'Insert item', 'smart-things' ),
            'noitem' => __( 'No item selected', 'smart-things' ),
            'wait' => __( 'Wait...', 'smart-things' ),
            'cleangallery' => __( 'Clean gallery', 'smart-things' ),
            'confirmcleangallery' => __( 'Clean gallery?', 'smart-things' ),
            'confirmremoveimage' => __( 'Are you sure?', 'smart-things' ),
            'warneditimg' => __( 'WARNING: changes made here are reflected wherever the same image is used!', 'smart-things' ),
            'warneditpost' => __( 'WARNING: changes made here are reflected wherever the same content is used!', 'smart-things' ),
        );
        wp_localize_script( 'smart_things_admin_js', 'smart_things_strings', $strings );
    }

    function wp_editor( $text, $name, $id = '' ) {
        wp_editor( $text, $id ? $id : array_pop( explode( '[', $name ) ), array( 'media_buttons' => false, 'textarea_name' => $name, 'textarea_rows' => 7, 'teeny' => true, 'tinymce' => array( 'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,link,unlink,bullist,formatselect,|,undo,redo,|,forecolor,removeformat' ), 'quicktags' => array( 'buttons' => 'strong,em,del,link,spell,close' ) ) );
    }

    function admin_body_class( $c ) {
        if ( array_key_exists( 'no-menu', $_REQUEST ) ) :
            $c .= ' no-menu';
            add_action( 'edit_form_top', array( __CLASS__, 'admin_no_menu_form_top' ) );
        endif;
        return $c;
    }
    function init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_style' ) );
        add_action( 'wp_ajax_gallery_thumbs', array( __CLASS__, 'ajax_gallery_thumbs' ) );
        add_action( 'wp_ajax_related_thumbs', array( __CLASS__, 'ajax_related_thumbs' ) );
        load_plugin_textdomain( 'smart-things', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
        add_action( 'edit_attachment', array( __CLASS__, 'admin_no_menu_saved_post' ), 8001 );
    }
    function admin_no_menu_form_top() {
        if ( array_key_exists( 'no-menu', $_REQUEST ) ) :
            ?>
            <input type="hidden" name="no-menu" value="true" />
            <?php
        endif;
    }
    function admin_no_menu_saved_post( $pid ) {
        if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined('DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) return $pid;
        if ( array_key_exists( 'no-menu', $_REQUEST ) ) :
            ?>
            <script type="text/javascript">
            if ( top.tb_remove ) top.tb_remove();
            if ( top.smart_things_tb_callback ) top.smart_things_tb_callback();
            </script>
            <?php
            wp_die();
        endif;
    }

    function explode_text( $text, $sep = ',' ) {
        return array_unique( array_map( 'trim', array_filter( explode( $sep, trim( $text ) ) ) ) );
    }

}

add_action( 'init', array( 'smart_things', 'init' ) );

endif;

$pdir = plugin_dir_path( __FILE__ );
include_once( $pdir . 'smart-custom-fields.php' );
include_once( $pdir . 'smart-custom-post-types.php' );
include_once( $pdir . 'smart-taxonomies.php' );
include_once( $pdir . 'smart-options.php' );
include_once( $pdir . 'smart-user-meta.php' );

?>
