<?php

namespace ModularityXmlRender;

class App
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'registerFrontendAssets'));
        add_action('admin_enqueue_scripts', array($this, 'registerAdminAssets'));

        //Init module
        add_action('plugins_loaded', array($this, 'registerModule'));

        //Register meta boxes
        add_action('add_meta_boxes', array($this, 'registerMetaBoxes'));

        if (!is_admin()) {
            return false;
        }
    }



    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYXMLRENDER_PATH . 'source/php/Module/',
                'XmlRender'
            );
        }
    }


    /**
     * Register required frontend scripts
     * @return void
     */
    public function registerFrontendAssets()
    {
        if (file_exists(MODULARITYXMLRENDER_PATH . '/dist/' . Helper\CacheBust::name('js/Front/IndexFront.js'))) {
            wp_register_script('modularity-xml-render', MODULARITYXMLRENDER_URL . '/dist/' . Helper\CacheBust::name('js/Front/IndexFront.js'), array('jquery', 'react', 'react-dom'));
        }
    }

    /**
     * Register required admin scripts & styles
     * @return void
     */
    public function registerAdminAssets()
    {
        if (file_exists(MODULARITYXMLRENDER_PATH . '/dist/' . Helper\CacheBust::name('css/modularity-xml-render-admin.css'))) {
            wp_register_style('modularity-xml-render-admin', MODULARITYXMLRENDER_URL . '/dist/' . Helper\CacheBust::name('css/modularity-xml-render-admin.css'));
        }

        if (file_exists(MODULARITYXMLRENDER_PATH . '/dist/' . Helper\CacheBust::name('js/Admin/IndexAdmin.js'))) {
            wp_register_script('modularity-xml-render-admin-js', MODULARITYXMLRENDER_URL . '/dist/' . Helper\CacheBust::name('js/Admin/IndexAdmin.js'), array('jquery', 'react', 'react-dom'), false, true);
        }
    }


    /**
     * Register meta boxes
     * @return void
     */
    public function registerMetaBoxes()
    {
        add_meta_box('xml-render-fields', __('Data settings', 'modularity-xml-render'),
            function () {
                echo '<div id="modularity-xml-render"></div>';
            }, 'mod-xml-render', 'normal', 'high');
    }
}
