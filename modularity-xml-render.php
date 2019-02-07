<?php

/**
 * Plugin Name:       Modularity XML Render
 * Plugin URI:        
 * Description:       Plugin for parse and render xml data
 * Version:           1.0.0
 * Author:            Johan Silvergrund, Jonatan Hansson
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-xml-render
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYXMLRENDER_PATH', plugin_dir_path(__FILE__));
define('MODULARITYXMLRENDER_URL', plugins_url('', __FILE__));
define('MODULARITYXMLRENDER_TEMPLATE_PATH', MODULARITYXMLRENDER_PATH . 'templates/');

load_plugin_textdomain('modularity-xml-render', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once MODULARITYXMLRENDER_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once MODULARITYXMLRENDER_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new ModularityXmlRender\Vendor\Psr4ClassLoader();
$loader->addPrefix('ModularityXmlRender', MODULARITYXMLRENDER_PATH);
$loader->addPrefix('ModularityXmlRender', MODULARITYXMLRENDER_PATH . 'source/php/');
$loader->register();

// Acf auto import and export
add_action('plugins_loaded', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-xml-render');
    $acfExportManager->setExportFolder(MODULARITYXMLRENDER_PATH . 'acf-fields/');
    $acfExportManager->autoExport(array(
        'display-settings' => 'group_5c5c16bb7409b',
    ));
    $acfExportManager->import();
});


// Start application
new ModularityXmlRender\App();
new ModularityXmlRender\XmlParser();