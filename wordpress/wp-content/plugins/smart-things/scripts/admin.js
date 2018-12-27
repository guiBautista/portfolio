var smart_things_gallery_link;
var smart_things_tb_callback = false;

jQuery( document ).ready( function () {

    var inputmodelo = jQuery( '<input type="color" value="#ffffff" class="colorinput" />' );
    var linkmodelo = jQuery( '<a href="#" class="linkremovecolor">' + smart_things_strings.del + '</a></span>' );
    linkmodelo.click( function () {
        jQuery( this ).parents( 'span.colorinputspan' ).remove();
        jQuery( 'div.smart_things' ).trigger( 'atualizar' );
        return false;
    } );
    inputmodelo.on( 'change click focus blur', function() {
        jQuery( 'div.smart_things' ).trigger( 'atualizar' );
    } );
    jQuery( 'div.smart_things input.smart_field_colors' ).each( function () {
        jQuery( this ).parents( 'div.smart_things' ).on( 'atualizar', function () {
            var strcores = [];
            jQuery( 'input.colorinput', jQuery( this ) ).each( function () {
                strcores[strcores.length] = jQuery( this ).val();
            } );
            jQuery( 'input.smart_field_colors', jQuery( this ) ).val( strcores.join( ',' ) );
        } );
        var coresval = jQuery( this ).val() + '';
        var ascores = coresval.split( ',' );
        if ( coresval.replace( /\s/gi, '' ).length ) for ( c = 0; c < ascores.length; c++ ) {
            var ospan = jQuery( '<span class="colorinputspan"></span>' );
            ospan.append( inputmodelo.clone( true ).val( ascores[c] ).attr( 'placeholder', jQuery( this ).attr( 'placeholder' ) ) );
            ospan.append( linkmodelo.clone( true ) );
            jQuery( this ).parent().append( ospan );
        }
        jQuery( this ).hide().parent().append( '<span class="colorinputspan"><a href="#" class="linkaddcolor">' + smart_things_strings.add + '</a></span>' );
        jQuery( 'span.colorinputspan a.linkaddcolor' ).click( function () {
            var ospan = jQuery( '<span class="colorinputspan"></span>' );
            ospan.append( inputmodelo.clone( true ).attr( 'placeholder', jQuery( this ).attr( 'placeholder' ) ) );
            ospan.append( linkmodelo.clone( true ) );
            jQuery( this ).parents( 'span.colorinputspan' ).before( ospan );
            jQuery( 'div.smart_things' ).trigger( 'atualizar' );
            return false;
        } );
    } );

    var inputrotulo = jQuery( '<input />' ).attr( 'type', 'text' ).on( 'change click focus blur', function () {
        jQuery( this ).parents( 'div.gridchavevalor' ).trigger( 'atualizar' );
    } );
    var textareabloco = jQuery( '<textarea />' ).attr( 'rows', '1' ).on( 'change click focus blur', function () {
        jQuery( this ).parents( 'div.gridchavevalor' ).trigger( 'atualizar' );
    } );
    var linkremove = jQuery( '<a />' ).addClass( 'removeline' ).attr( 'href', '#' ).html( smart_things_strings.del ).click( function () {
        jQuery( this ).parents( 'div.linha' ).next( 'br' ).remove().end().remove();
        jQuery( 'div.gridchavevalor' ).trigger( 'atualizar' );
        return false;
    } );
    jQuery( 'div.customfield_especificacoes textarea[name="especificacoes"]' ).each( function () {
        var espec = jQuery( this ).val();
        espec = espec.split( /\n[(\s*\n)]+/m );
        var ediv = jQuery( '<div />' ).addClass( 'gridchavevalor' );
        for ( var e = 0; e < espec.length; e++ ) {
            var bloco = espec[e];
            bloco = bloco.split( /\n/ );
            var rotulo = bloco.shift();
            bloco = bloco.join( "\n" );
            var inprotulo = inputrotulo.clone( true ).val( rotulo );
            var txtbloco = textareabloco.clone( true ).val( bloco );
            var removelinha = linkremove.clone( true );
            var linha = jQuery( '<div />' ).addClass( 'linha' );
            linha.append( inprotulo ).append( txtbloco ).append( removelinha );
            ediv.append( linha ).append( '<br />' );
        }
        var adicionalinha = jQuery( '<a />' ).addClass( 'addline' ).attr( 'href', '#' ).html( smart_things_strings.add ).click( function () {
            var inprotulo = inputrotulo.clone( true );
            var txtbloco = textareabloco.clone( true );
            var removelinha = linkremove.clone( true );
            var linha = jQuery( '<div />' ).addClass( 'linha' );
            linha.append( inprotulo ).append( txtbloco ).append( removelinha );
            jQuery( this ).before( linha ).before( '<br />' );
            return false;
        } );
        ediv.append( adicionalinha );
        jQuery( this ).hide().after( ediv );
    } );
    jQuery( 'div.gridchavevalor' ).on( 'atualizar', function () {
        var strespec = [];
        jQuery( 'div.linha', jQuery( this ) ).each( function () {
            var rotulo = jQuery( this ).find( 'input' ).val();
            var bloco = jQuery( this ).find( 'textarea' ).val();
            strespec[strespec.length] = rotulo + "\n" + ( bloco ? bloco + "\n" : '' ) + "\n";
        } );
        jQuery( 'textarea[name="especificacoes"]', jQuery( this ).parents( 'div.customfield' ) ).val( strespec.join( "\n" ) );
    } );

    jQuery( '.gallery-link.hidden' ).click( function (e) {
      e.preventDefault();
      smart_things_gallery_link = jQuery( this );
      frame = wp.media( {
        title : jQuery( this ).attr( 'title' ) ? jQuery( this ).attr( 'title' ) : 'Galeria',
        multiple : !jQuery( this ).hasClass( 'select-single-image' ),
        library : { type : jQuery( this ).hasClass( 'gallery-pdf' ) ? 'application/pdf' : 'image'},
        button : { text : jQuery( this ).attr( 'rel' ) ? jQuery( this ).attr( 'rel' ) : smart_things_strings.select }
      } );
      frame.on( 'select', function() {
        var arrimages = [];
        var images=frame.state().get('selection');
        images.each( function (image) { if (image.id) arrimages.push(image.id) } );
        var strimages = arrimages.join(',');
        jQuery( smart_things_gallery_link ).siblings('input').first().val( strimages );
        smart_things_gallery_update( smart_things_gallery_link );
      } );
      frame.on( 'open', function () {
        var selection = frame.state().get('selection');
        ids = ( jQuery( smart_things_gallery_link ).siblings('input').first().val() + '' ).split(',');
        ids.forEach( function( id ) {
          attachment = wp.media.attachment(id);
          attachment.fetch();
          selection.add( attachment ? [ attachment ] : [] );
        } );
      } );
      frame.open();
      return false;
    } ).removeClass('hidden').siblings('label,input').hide();

    jQuery('.gallery-thumbs').each( function () {
        var clean_link = jQuery( '<a href="#" class="clean-gallery-link button" rel="confirmclean:' + smart_things_parse_rel( this, 'confirmclean', smart_things_strings.confirmcleangallery ) + ';">' + smart_things_parse_rel( this, 'cleantext', smart_things_strings.cleangallery ) + '</a>' );
        clean_link.click( function (e) {
            e.preventDefault();
            if ( confirm( smart_things_parse_rel( this, 'confirmclean', smart_things_strings.confirmcleangallery ) ) ) {
                jQuery( this ).siblings( 'input' ).first().val( '' );
                jQuery( this ).siblings( '.gallery-thumbs' ).html( '' );
                jQuery( this ).addClass( 'hidden' );
            }
            return false;
        } );
        if ( !jQuery( this ).children( 'ul' ).length ) clean_link.addClass( 'hidden' );
        jQuery( this ).after( clean_link );
    } );

    jQuery( '.related-thumbs-link.hidden' ).click( function (e) {
      e.preventDefault();
      var tbwidth = Math.ceil( jQuery( window ).width() * 0.87 );
      var tbheight = Math.ceil( jQuery( window ).height() * 0.87 );
      tb_show( smart_things_strings.warneditpost, jQuery( this ).attr( 'href' ) + '&no-menu&TB_iframe=true&width=' + tbwidth + '&height=' + tbheight );
      smart_things_gallery_link = jQuery( this );
      return false;
    } ).removeClass('hidden').siblings('label,input').hide();

    if ( jQuery( 'input.wplink' ).length ) {
        jQuery( 'input.wplink' ).each( function () {
            jQuery( this ).after( ' <a href="#smart_things_wplink" class="openlinkdialog" rel="' + jQuery( this ).attr( 'id' ) + '">' + jQuery( this ).data( 'search-button-text' ) + '</a>' );
            jQuery( '#smart_things_wplink' ).detach().appendTo( 'body' ).children( 'div.wplink_content' );
        } );
        function wplink_filterresults() {
            jQuery( 'input.wplink_search' ).each( function () {
                jQuery( this ).data( 'timeOutID', false );
                var searchval = jQuery( this ).val();
                jQuery.post( ajaxurl, { 'action': 'wp-link-ajax', 'page': 1, '_ajax_linking_nonce': jQuery( '#_ajax_linking_nonce' ).val(), 'search': searchval }, function ( data ) {
                    data = jQuery.parseJSON( data );
                    var ulres = jQuery( 'div.wplink_results ul' );
                    jQuery( 'li', ulres ).remove();
                    if ( data && data.length ) {
                        for ( var i = 0; i < data.length; i++ ) {
                            ulres.append( '<li title="' + data[i].permalink + '"' + ( i%2 ? ' class="alternate"' : '' ) + '>' + data[i].title + ' <span class="item-info">' + data[i].info + '</span></li>' );
                        }
                    } else {
                        jQuery( '<li title=""><em>' + ulres.data( 'not-found' ) + '</em></li>' ).appendTo( ulres );
                    }
                } );
            } );
        }
        jQuery( 'input.wplink_search' ).on( 'change keyup', function () {
            if ( jQuery( this ).data( 'timeOutID' ) ) clearTimeout( jQuery( this ).data( 'timeOutID' ) );
            jQuery( this ).data( 'timeOutID', setTimeout( wplink_filterresults, 500 ) );
        } );
        jQuery( 'div.wplink_results ul' ).on( 'click', function ( evt ) {
            evt = ( evt || window.event );
            var esrc = jQuery( evt.srcElement || evt.target );
            if ( esrc.get(0).tagName.toLowerCase() != 'li' ) esrc = esrc.parents( 'div.wplink_results ul li' );
            var thiscontainer = jQuery( this ).parents( 'div.wplink_container' );
            jQuery( '.wplink_sample', thiscontainer ).html( esrc.attr( 'title' ) );
            jQuery( '.wplink_search', thiscontainer ).focus().select();
        } );
        jQuery( 'a.openlinkdialog' ).on( 'click', function () {
            var lcontainer = jQuery( jQuery( this ).attr( 'href' ) ).data( 'wplinkinput', jQuery( this ).attr( 'rel' ) );
            var wh = jQuery( window ).height();
            jQuery( 'div.wplink_content', lcontainer ).height( Math.ceil( wh * .8 ) ).css( { 'margin-top': Math.ceil( wh * .08 ) + 'px', 'padding-top': Math.ceil( wh * .05 ) + 'px' } );
            jQuery( '.wplink_sample', lcontainer ).html( jQuery( '#' + jQuery( this ).attr( 'rel' ) ).val() );
            lcontainer.fadeIn( function () {
                jQuery( '.wplink_search', jQuery( this ) ).focus().select();
            } );
            jQuery( 'div.wplink_close,a.wplink_cancel', lcontainer ).on( 'click', function () {
                jQuery( this ).parents( 'div.wplink_container' ).fadeOut();
            } );
            jQuery( 'input.wplink_save', lcontainer ).on( 'click', function () {
                var thiscontainer = jQuery( this ).parents( 'div.wplink_container' );
                var linksample = jQuery( '.wplink_sample', thiscontainer ).html();
                jQuery( '#' + thiscontainer.data( 'wplinkinput' ) ).val( linksample ).focus();
                jQuery( this ).parents( 'div.wplink_container' ).fadeOut();
                if(window.linkCallback)window.linkCallback(linksample)
            } );
        } );
    }

    jQuery( 'div.postbox-container div.smart_things_admin' ).each( function () {
        if ( !jQuery( 'table.smart_things_admin tr', jQuery( this ) ).length ) {
            jQuery( this ).parents( 'div.postbox-container' ).hide();
        }
    } );

    jQuery( 'table .smart_things' ).on( 'click', '.edit_img_metadata,.edit_related_metadata', function () {
        if ( typeof( tb_show ) != 'undefined' ) {
            var tbwidth = Math.ceil( jQuery( window ).width() * 0.87 );
            var tbheight = Math.ceil( jQuery( window ).height() * 0.87 );
            var warnmsg = smart_things_strings.warneditimg;
            if ( jQuery( this ).hasClass( 'edit_related_metadata' ) ) warnmsg = smart_things_strings.warneditpost;
            tb_show( warnmsg, jQuery( this ).attr( 'href' ) + '&no-menu&TB_iframe=true&width=' + tbwidth + '&height=' + tbheight );
            smart_things_gallery_link = jQuery( '.gallery-link, .related-thumbs-link', jQuery( this ).parents( 'div.smart_things' ) );
            smart_things_tb_callback = function () {
                smart_things_gallery_update( smart_things_gallery_link );
                smart_things_tb_callback = false;
            }
            return false;
        }
    } );
    jQuery( 'table .smart_things' )
        .on( 'click', '.link_expand_data', smart_things_gallery_expand_data )
        .on( 'click', '.smart_things_remove_image_link', smart_things_remove_image );

    jQuery( 'table .smart_things .file_metadata' ).parents( '.smart_things' ).addClass( 'expanded_data' );

    smart_things_gallery_drag_and_drop();

    jQuery( '.relatedset input[type="hidden"]' ).each( function () {
        var vals = ( jQuery( this ).val() + '' ).split( ',' );
        jQuery( this ).siblings( 'span.js-remove' ).remove();
        if ( jQuery( this ).data( 'type' ) == 'radio' ) {
            jQuery( this ).before( '<label for="' + jQuery( this ).data( 'slug' ) + '-0"><input type="radio" name="' + jQuery( this ).prop( 'name' ) + '[]" id="' + jQuery( this ).data( 'slug' ) + '-0" value="0" class="inpcheckbox" /> ' + jQuery( this ).data( 'none-text' ) + '</label><br />' );
        }
        var rels = jQuery( this ).data( 'related' );
        var aux_chk = [];
        var aux_no = [];
        for ( var i = 0; i < rels.length; i++ ) {
            var robj = rels[ i ];
            if ( jQuery.inArray( robj.r.toString(), vals ) == -1 ) {
                aux_no.push( robj );
            } else {
                aux_chk.push( robj );
            }
        }
        var aux = aux_chk.concat( aux_no );
        for ( var i = 0; i < aux.length; i++ ) {
            var rchk = ( jQuery.inArray( aux[i].r.toString(), vals ) != -1 );
            jQuery( this ).before( '<label for="' + jQuery( this ).data( 'slug' ) + '-' + aux[i].r + '"><input type="' + jQuery( this ).data( 'type' ) + '" name="' + jQuery( this ).prop( 'name' ) + '[]" id="' + jQuery( this ).data( 'slug' ) + '-' + aux[i].r + '" value="' + aux[i].r + '" class="inpcheckbox"' + ( rchk ? ' checked="checked"' : '' ) + ' /> ' + aux[i].t + '</label><br />' );
        }
        jQuery( this ).parent().find('br').remove().end().sortable().end().remove();
    } );

    smart_things_smartrelated();

    //custom fields - pages
    smart_things_change_custom_field();
    jQuery( '#pageparentdiv select[name="page_template"]').change( smart_things_change_custom_field );

} );

function smart_things_gallery_drag_and_drop() {
    jQuery('.smart_things>.gallery-thumbs,.smart_things>.related-thumbs').each(function(){
      var field=jQuery(this).parent().find('>input').eq(0)
      var ul=jQuery(this).find('ul')
      var moving=false
      ul.on('mousedown','img,svg',function(){
        moving=jQuery(this).parent()
        jQuery(this).addClass('moving')
        return false
      })
      ul.on('mouseover','li',function(){
        if(moving){
          if(jQuery(this).index()>moving.index()){
            jQuery(this).after(moving)
          }else if(jQuery(this).index()<moving.index()){
            jQuery(this).before(moving)
          }
        }
        return false
      })
      jQuery('body').on('mouseup',function(){
        if(moving){
          var t=[]
          ul.children('li').each(function(){
            t.push(this.getAttribute('rel'))
          })
          t = t.filter( function ( item ) { return item; } );
          field.val(t.join(','))
        }
        moving=false
        jQuery('.moving').removeClass('moving')
      })
    })
}

function smart_things_gallery_update( _gallery_link ) {
    var strimages = jQuery( _gallery_link ).siblings('input').first().val();
    //if ( !jQuery( _gallery_link ).hasClass( 'gallery-pdf' ) ) {
        var thumbs_cont = jQuery( _gallery_link ).siblings('.gallery-thumbs').first();
        if ( thumbs_cont.length ) {
            var ajax_wait_message = smart_things_parse_rel( _gallery_link, 'waittext', smart_things_strings.wait );
            thumbs_cont.html( '<div class="ajax-wait-message">' + ajax_wait_message + '</div>' );
            if ( typeof( ajaxurl ) != 'undefined' ) {
                jQuery.get(
                    ajaxurl,
                    {
                        action : 'gallery_thumbs',
                        images : strimages,
                    },
                    function( responseHTML ) {
                        if(window.mediaGalleryCallback){
                            return window.mediaGalleryCallback(responseHTML);
                        }
                        thumbs_cont.html( responseHTML );
                        thumbs_cont.siblings( '.clean-gallery-link' ).removeClass( 'hidden' );
                        smart_things_gallery_drag_and_drop();
                    }
                );
            }
        }
    //}
}

function smart_things_parse_rel( obj, attr, default_value ) {
    var rel = jQuery( obj ).attr( 'rel' ) + '';
    var attr_match = rel.match( attr + ':([^;]+)' );
    return attr_match ? attr_match[1] : default_value;
}

function smart_things_gallery_expand_data() {
    jQuery( this ).parents( '.smart_things' ).toggleClass( 'expanded_data' );
    return false;
}

function smart_things_remove_image() {
    if ( confirm( smart_things_strings.confirmremoveimage ) ) {
        var litem = jQuery( this ).parents( 'li' );
        var t=[]
        litem.siblings( 'li' ).each( function() {
                t.push( this.getAttribute( 'rel' ) )
            } );
        litem.parents( 'div.smart_things' ).children( 'input' ).first().val( t.join( ',' ) );
        litem.remove();
    }
    return false;
}

function smart_things_change_custom_field(){
    var model = jQuery( '#pageparentdiv select[name="page_template"] option:selected' ).val();
    if ( model ){
        basemodel = model.split( '.' )[0];
        var customfields = jQuery( '#smartcf_customfields .smart_things_admin tr[data-template="' + model + '"], tr[data-template="' + basemodel + '"]' );
        jQuery( '#smartcf_customfields .smart_things_admin tr[data-template]' ).hide();
        jQuery( customfields ).show();
    }
}

function smart_things_smartrelated() {
    smart_things_smartrelated_create_remove_link();

    var qs = location.search;
    if ( qs.substr( 0, 1 ) == '?' ) qs = qs.substr( 1 );
    qs = qs.split( '#' ).pop();
    qs = qs.split( '&' );
    if ( jQuery.inArray( 'no-menu', qs ) > -1 ) {
        jQuery( 'a,form' ).each( function () {
            var attr = jQuery( this ).is( 'a' ) ? 'href' : 'action';
            var h = jQuery( this ).attr( attr );
            if ( h && ( h.substr( 0, 1 ) != '#' ) ) {
                if ( h.indexOf( '?' ) > -1 ) {
                    h += '&no-menu';
                } else {
                    h += '?no-menu';
                }
            }
            jQuery( this ).attr( attr, h );
        } );
    }

    if ( jQuery( '.updated.notice-success' ).length > 0 ) {
        var pid = jQuery( '#post_ID' ).val();
        if ( ( window.self != window.top ) && window.parent.smart_things_smartrelated_inserted ) window.parent.smart_things_smartrelated_inserted( pid );
    }
    var formpf = jQuery( 'form#posts-filter' );
    if ( formpf.length && ( window.self != window.top ) && window.parent.smart_things_smartrelated_inserted ) {
        var related_insert_button = jQuery( '<button class="button-primary">' + smart_things_strings.insert + '</button>' ).on( 'click', function () {
            var cids = [];
            jQuery( 'input[name="post[]"]:checked' ).each( function () {
                cids.push( jQuery( this ).val() );
            } );
            if ( cids.length ) {
                window.parent.smart_things_smartrelated_inserted( cids );
            } else {
                alert( smart_things_strings.noitem );
            }
        } );
        jQuery( '.bulkactions input[type="submit"]', formpf ).after( related_insert_button );
    }
}

function smart_things_smartrelated_inserted( pid ) {
    if ( smart_things_gallery_link ) {
        var oinp = jQuery( smart_things_gallery_link ).siblings('input').first();
        var pids = oinp.val();
        pids = ( pids + '' ).split( ',' );
        if ( jQuery.inArray( pid, pids ) == -1 ) pids.push( pid );
        oinp.val( pids.join( ',' ) );
        smart_things_smartrelated_update( smart_things_gallery_link );
        smart_things_gallery_link = false;
    }
    tb_remove();
}

function smart_things_smartrelated_create_remove_link() {
    jQuery( '.edit_related_metadata', '.related-thumbs' ).each( function () {
        var rlink = jQuery( '<a href="#" class="remove_related">Remove</a>' );
        rlink.on( 'click', smart_things_remove_image );
        jQuery( this ).after( rlink );
    } );
}

function smart_things_smartrelated_update( _sr_link ) {
    var strimages = jQuery( _sr_link ).siblings('input').first().val();
    var thumbs_cont = jQuery( _sr_link ).siblings('.related-thumbs').first();
    if ( thumbs_cont.length ) {
        var ajax_wait_message = smart_things_parse_rel( _sr_link, 'waittext', smart_things_strings.wait );
        thumbs_cont.html( '<div class="ajax-wait-message">' + ajax_wait_message + '</div>' );
        if ( typeof( ajaxurl ) != 'undefined' ) {
            jQuery.get(
                ajaxurl,
                {
                    action : 'related_thumbs',
                    items : strimages,
                },
                function( responseHTML ) {
                    thumbs_cont.html( responseHTML );
                    smart_things_gallery_drag_and_drop();
                    smart_things_smartrelated_create_remove_link();
                }
            );
        }
    }
}
