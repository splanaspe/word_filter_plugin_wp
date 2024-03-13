<?php

/*
    Plugin Name: Word Filter Plugin
    Plugin URI: salvadorplanas.com
    Description: Plugin that filters the words
    Version: 1.0
    Author: Salvador Planas
    Author URI: salvadorplanas.com
    License: GPL2
    Text Domain: wcpdomain
    Domain Path: Languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class OurWordFilterPlugin {
    // Creamos un plugin que tiene un icono, como si fuera post_type
    function __construct(){
        add_action('admin_menu', array($this, 'ourMenu'));
        if(get_option('plugin_words_to_filter')) add_action('the_content', array($this, 'filterLogic'));
        add_action('admin_init', array($this, 'ourSettings'));
    }

    function ourSettings(){
        add_settings_section('replacement-text-section', null, null, 'word-filter-options',  );
        register_setting('replacementFields', 'replacementText');
        add_settings_field('replacement-text', 'Filtered Text', array($this, 'replacementFieldHTML'), 'word-filter-options', 'replacement-text-section');
    }

    function replacementFieldHTML(){ ?>
        <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText')) ?>">
        <p class="description"> Leave Blank to simply remove the words</p>
    <?php
    }

    function filterLogic($content){
        $badWords = explode(',', get_option('plugin_words_to_filter'));
        $badWordsTrimmed = array_map('trim', $badWords);
        return str_ireplace($badWordsTrimmed, esc_html(get_option('replacementText','****')), $content);
    }

    function ourMenu(){
        // 7 parametros
        // 1 - Lo que se ve arriba en el navegador
        // 2 - Lo que se muestra en el plugin, debajo del nombre principal
        // 3 - permisos 
        // 4 - slug del setting page
        // 5 - funcion que muestra el setting page
        // 6 - icono
        // 7 - nº de plugin en el dashboard, orden 100
        
        $mainPageHook = add_menu_page(
            'Words To Filter', 
            'Word Filter', 
            'manage_options', 
            'ourwordfilter' , 
            array($this,'wordFilterPage'), 
            plugin_dir_url(__FILE__).'custom.svg', 
            100);
        
        add_submenu_page( 'ourwordfilter', 'Words To Filter','Words List', 'manage_options','ourwordfilter', array($this,'wordFilterPage'));

        // funcion para añadir submenu al plugin - 6 parametros
        // 1 - añadir el menu al que queremos añadir este submenu
        // 2 - Lo que se ve arriba en el navegador
        // 3 - Texto que se verá en la barra lateral de administracion
        // 4 - Capacidad, permisos
        // 5 - slug del submenu page
        // 6 - funcion que muestra el codigo del subpage
        add_submenu_page(
            'ourwordfilter',
            'Word Filter Options',
            'Options',
            'manage_options',
            'word-filter-options',
            array($this, 'optionsSubPage')
        );

        add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));
    }

    function mainPageAssets(){
        wp_enqueue_style('filterAdminCss', plugin_dir_url( __FILE__ ) . 'styles.css');
    }

    function handleForm(){
        if(wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')){
            update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
            <div class="updated">
                <p> Your filtered words were saved </p>
            </div>
        <?php  
        } else{ ?>
            <div class="error">
                <p> Sorry, you do not have permission to perfom that action</p>
            </div>
        <?php
        }

    }

    function wordFilterPage(){ 
        // Esto se muestra dentro de la pagina settings del plugin
        ?>

        <div class="wrap">
            <h1> Word Filter </h1>
            <?php if(isset($_POST['justsubmitted']) && $_POST['justsubmitted'] == 'true') $this->handleForm() ?>
            <form method="POST">
                <input type="hidden" name="justsubmitted" value="true">
                <?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
                <label for="plugin_words_to_filter"> Enter a comma-separated list of words that you want to remove </label>
                <div class="word-filter__flex-container">
                    <textarea name="plugin_words_to_filter" placeholder="malo, sucio, horrible"><?php echo esc_textarea( get_option('plugin_words_to_filter') ) ?></textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary">
            </form>
        </div>

    <?php 
    }

    function optionsSubPage(){
        // Esto se muestra dentro de la subpagina "options"  del plugin
        ?>
        
        <div class="wrap">
            <h1> Word Filter Options </h1>
            <form action="options.php" method="POST">
                <?php
                    settings_errors();
                    settings_fields('replacementFields');
                    do_settings_sections('word-filter-options');
                    submit_button();
                ?>
            </form>
        </div>

    <?php 
    }

}

$ourWordFilterPlugin = new OurWordFilterPlugin();

?>