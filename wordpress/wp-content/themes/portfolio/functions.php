<?php
require_once( get_template_directory() . '/functions/custom-fields.php' );

class portfolio {
    function init () {
        
    }
}

add_action( 'init', array( 'portfolio', 'init' ) );