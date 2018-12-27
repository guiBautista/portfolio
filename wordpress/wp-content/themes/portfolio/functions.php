<?php
require_once( get_template_directory() . '/functions/custom-fields.php' );

class portfolio {
    function init () {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'scripts_and_styles' ) );
    }

    function scripts_and_styles () {
        $assets_path = get_template_directory_uri() . '/assets/';
        // fontawesome
        wp_enqueue_script( 'fontawesome', $assets_path . 'plugins/fontawesome/all.js' );
        // bootstrap css
        wp_enqueue_style( 'bootstrap_css', $assets_path . 'plugins/bootstrap/css/bootstrap.min.css' );
        // callendar
        wp_enqueue_style( 'calendar_css', $assets_path . 'plugins/github-calendar/dist/github-calendar.css' );
        // octions
        // wp_enqueue_style( 'octions_css', $assets_path . 'plugins/octions/octicons.min.css' );
        // activity
        wp_enqueue_style( 'activity_css', $assets_path . 'plugins/github-activity/github-activity-0.1.5.min.css' );
        // css theme
        wp_enqueue_style( 'style_css', $assets_path . '/css/styles.css' );

        // jquery js
        wp_enqueue_script( 'jquery_js', $assets_path . 'plugins/jquery-3.3.1.min.js', false, false, true );
        // popper
        wp_enqueue_script( 'pooper_js', $assets_path . 'plugins/popper.min.js', array( 'jquery' ), false, true );
        // bootstrap js
        wp_enqueue_script( 'bootstrap_js', $assets_path . 'plugins/bootstrap/js/bootstrap.min.js', array( 'jquery' ), false, true );
        // jquery-rss
        wp_enqueue_script( 'jquery_rss_js', $assets_path . 'plugins/jquery-rss/dist/jquery.rss.min.js', array( 'jquery' ), false, true );
        // calendar js
        wp_enqueue_script( 'calendar_js', $assets_path . 'plugins/github-calendar/dist/github-calendar.min.js', false, false, true );
        // mustache
        wp_enqueue_script( 'mustache_js', $assets_path . 'plugins/mustache/mustache.min.js', false, false, true );
        // activity
        wp_enqueue_script( 'activity_js', $assets_path . 'plugins/github-activity/github-activity-0.1.5.min.js', array( 'jquery' ), false, true );
        // main js
        wp_enqueue_script( 'main_js', $assets_path . 'js/main.js', false, false, true );
        
    }
}

add_action( 'init', array( 'portfolio', 'init' ) );