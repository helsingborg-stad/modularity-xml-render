<?php

namespace ModularityXmlRender\Module;

class XmlRender extends \Modularity\Module
{
    public $slug = 'xml-render';
    public $supports = array();

    public function init()
    {
        //Define module
        $this->nameSingular = __("XML Render", 'modularity-xml-render');
        $this->namePlural = __("XML Renders", 'modularity-xml-render');
        $this->description = __("Retrives data from XML and renders it as a list.", 'modularity-xml-render');

        add_action('save_post', array($this, 'saveOptions'), 10, 3);
        add_action('admin_notices', array($this, 'validationNotice'));
        add_filter('post_updated_messages', array($this, 'updateNotices'));
    }

    public function validationNotice()
    {
        if (!$errors = get_transient('mod_xml_render_error')) {
            return;
        }

        // Clear and the transient
        delete_transient('mod_xml_render_error');

        foreach ($errors as $error) {
            $class = 'notice notice-error is-dismissible';
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($error['message']));
        }
    }

    public function updateNotices($messages)
    {
        if (!empty(get_transient('mod_xml_render_error'))) {
            $messages = array();
        }

        return $messages;
    }


    /**
     * Extracting XML Data by key - Creating new posts
     * @param $sxe
     * @param $backEndKey
     * @return mixed
     */
    public function extractXMLDataCreatePosts($xmlData, $backEndKey)
    {
        $returnArray = array();
        foreach ((array)$xmlData as $key => $value) {

            if (is_array($value) && !$this->is_assoc($value)) {
                $indies = array();
                foreach ($value as $secondkey => $secondvalue) {
                    $indies[$secondkey] = $this->extractXMLDataCreatePosts($secondvalue, $backEndKey);
                }
                $returnArray[$key] = $indies;

            } else {
                if (is_array($value)) {
                    $returnArray[$key] = $this->extractXMLDataCreatePosts($value, $backEndKey);
                } else {
                    if ($key === 'AssignmentId')
                        echo $value;
                    if ($backEndKey === $key) {
                        echo "<p>key: ".$key .' value:'. $value."</p>";

                    }
                }
            }
        }
    }

    /**
     * @param $array
     * @return bool
     */
    function is_assoc($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * @param $postId
     * @param $post
     * @param $update
     */
    public function saveOptions($postId, $post, $update)
    {
        if ($post->post_type !== 'mod-' . $this->slug) {
            return;
        }

        if (array_key_exists('mod_xml_render_url', $_POST) && array_key_exists('mod_xml_render_fieldmap', $_POST)) {
            $url = $_POST['mod_xml_render_url'];
            $view = $_POST['mod_xml_render_view'];
            $fieldMap = json_decode(html_entity_decode(stripslashes($_POST['mod_xml_render_fieldmap'])));

            if ($view === 'posttype') {
                $data = wp_remote_get($_POST['mod_xml_render_url']);
                $parseXML = json_decode(json_encode(simplexml_load_string($data['body'])), true);
                $xmlData = [];
                foreach ($fieldMap->content as $items) {
                    $refVar = (substr_count($items->item->value, '.')) ? substr($items->item->value,
                        strrpos($items->item->value, '.') + 1) : $items->item->value;
                    $xmlData[$items->item->value] = $this->extractXMLDataCreatePosts($parseXML['Assignments'], $refVar);
                }
            }

            exit;

            if ($url && $view && isset($fieldMap->heading) && !empty($fieldMap->heading)) {
                update_post_meta($postId, 'xml_url', $url);
                update_post_meta($postId, 'view', $view);
                update_post_meta($postId, 'fieldmap', $_POST['mod_xml_render_fieldmap']);

            } else {
                $this->addSettingsError();
                remove_action('save_post', array($this, 'saveOptions'));
                wp_update_post(array('ID' => $postId, 'post_status' => 'draft'));
                add_action('save_post', array($this, 'saveOptions'));
            }
        }
    }

    public function addSettingsError()
    {
        add_settings_error(
            'missing-settings-fields',
            'missing-settings-fields',
            __('Complete the data settings.', 'modularity-xml-render'),
            'error'
        );

        set_transient('mod_xml_render_error', get_settings_errors(), 30);
    }

    public function data(): array
    {
        $options = $this->getOptions($this->ID);

        $data = get_fields($this->ID);
        $data['url'] = $options['url'];
        $data['view'] = $options['view'];
        $data['fieldMap'] = $options['fieldMap'];
        $data['classes'] = implode(' ',
            apply_filters('Modularity/Module/Classes', array('box', 'box-panel'), $this->post_type, $this->args));

        return $data;
    }

    public function template(): string
    {
        return "list.blade.php";
    }

    public function script()
    {
        // Enqueue React
        class_exists('\Modularity\Helper\React') ? \Modularity\Helper\React::enqueue() : \ModularityXmlRender\Helper\React::enqueue();

        wp_enqueue_script('modularity-' . $this->slug);
        wp_localize_script('modularity-' . $this->slug, 'modXMLRender', array(
            'translation' => array(
                'somethingWentWrong' => __('Something went wrong, please try again later.', 'modularity-xml-render'),
                'noResults' => __('No results found.', 'modularity-xml-render'),
                'filterOn' => __('Filter on...', 'modularity-xml-render'),
                'next' => __('Next', 'modularity-xml-render'),
                'prev' => __('Previous', 'modularity-xml-render'),
            )
        ));
    }

    public function style()
    {

    }

    public function adminEnqueue()
    {
        global $post;
        if (!isset($post->post_type) || $post->post_type !== 'mod-' . $this->slug) {
            return;
        }

        // Enqueue React
        class_exists('\Modularity\Helper\React') ? \Modularity\Helper\React::enqueue() : \ModularityXmlRender\Helper\React::enqueue();

        wp_enqueue_script('modularity-xml-render-admin-js');
        $options = $this->getOptions($post->ID);
        wp_localize_script('modularity-xml-render-admin-js', 'modXMLRender', array(
            'options' => $options,
            'translation' => array(
                'resetSettings' => __('Reset settings', 'modularity-xml-render'),
                'validXMLUrl' => __('Enter a valid XML api url.', 'modularity-xml-render'),
                'sendRequest' => __('Send request', 'modularity-xml-render'),
                'selectItemsContainer' => __('Select where to retrieve the information', 'modularity-xml-render'),
                'infoFields' => __('Information fields', 'modularity-xml-render'),
                'title' => __('Title', 'modularity-xml-render'),
                'heading' => __('Heading', 'modularity-xml-render'),
                'headings' => __('Headings', 'modularity-xml-render'),
                'content' => __('Content', 'modularity-xml-render'),
                'select' => __('Select', 'modularity-xml-render'),
                'couldNotFetch' => __('Could not fetch data from URL.', 'modularity-xml-render'),
                'list' => __('List', 'modularity-xml-render'),
                'posttype' => __('Export to post type', 'modularity-xml-render'),
                'accordion' => __('Accordion', 'modularity-xml-render'),
                'accordiontable' => __('Accordion table', 'modularity-xml-render'),
                'table' => __('Table', 'modularity-xml-render'),
                'selectView' => __('Select view', 'modularity-xml-render'),
                'dragAndDropInfo' => __('Drag and drop fields into the areas to the right. The areas accept different amount of values depending on selected view.',
                    'modularity-xml-render'),
                'value' => __('Value', 'modularity-xml-render'),
                'prefix' => __('Prefix', 'modularity-xml-render'),
                'suffix' => __('Suffix', 'modularity-xml-render'),
                'selectDateFormat' => __('Select date format', 'modularity-xml-render'),
                'none' => __('None', 'modularity-xml-render'),
                'exportToPostType' => __('Export to post type', 'modularity-xml-render'),
                'exportChoice' => __('Export to post type', 'modularity-xml-render'),
            )
        ));

        wp_enqueue_style('modularity-' . $this->slug . '-admin'); // Enqueue styles
    }

    public function getOptions($postId)
    {
        $url = get_post_meta($postId, 'xml_url', true);
        $view = get_post_meta($postId, 'view', true);
        $fieldmap = get_post_meta($postId, 'fieldmap', true);
        $options = array(
            'url' => $url ? $url : null,
            'view' => $view ? $view : null,
            'fieldMap' => $fieldmap ? $fieldmap : null,
            //'xmlData' =>
        );

        return $options;
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
