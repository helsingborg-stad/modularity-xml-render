<?php

namespace ModularityXmlRender\Module;

/**
 * Class XmlRender
 * @package ModularityXmlRender\Module
 */
class XmlRender extends \Modularity\Module
{
    public $slug = 'xml-render';
    public $supports = array();
    public $result = array();
    public $ID = null;

    public $post_author = 0;
    public $post_date = '';
    public $post_content = '';
    public $post_title = '';
    public $post_excerpt = '';
    public $post_status = 'publish';
    public $meta_input = [];
    public $postType = '';
    public $metaId = null;

    /**
     *
     */
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

    /**
     *
     */
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

    /**
     * @param $messages
     * @return array
     */
    public function updateNotices($messages)
    {
        if (!empty(get_transient('mod_xml_render_error'))) {
            $messages = array();
        }

        return $messages;
    }

    /**
     * @param $array
     * @return array|bool
     */
    public function arrayFlatten($arr)
    {
        if (!is_array($arr)) {
            return false;
        }

        $result = array();

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->arrayFlatten($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param $keys
     * @param $ref
     * @return array
     */
    public function interSectKeys($keys, $ref)
    {
        return array_intersect_key($keys, array_flip($ref));
    }

    /**
     * @param $array
     * @param $ref
     * @return array
     */
    public function extractXMLData($array, $ref)
    {
        if (!isset($result) || !is_array($result)) {
            $result = array();
        }

        foreach ($array as $item) {
            $result[] = $this->interSectKeys($this->arrayFlatten($item), $ref);
        }
        return $result;
    }

    /**
     * @param $exportData
     * @param $designations
     * @return mixed
     */
    public function mergeDataWithEqualKeys($exportData, $designations)
    {
        $data = [];
        foreach ($exportData as $index => $value) {
            foreach ($exportData[$index] as $key => $val) {
                $data[$index][$key]['Value'] = $val;
                foreach ($designations as $item => $desvalue) {
                    $designationKey = key($designations[$item]);
                    if ($key === $designationKey) {
                        if ($designations[$item][key($designations[$item])] === '') {
                            $data[$index][$key]['Designation'] = 'metadata';
                        } else {
                            $data[$index][$key]['Designation'] = $designations[$item][key($designations[$item])];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $url
     * @param $fieldMap
     */
    public function exportData($url, $fieldMap)
    {
        $data = wp_remote_get($url);
        $parseXML = (array)json_decode(json_encode(simplexml_load_string($data['body'])), true);
        $parseXML = array_pop($parseXML['Assignments']);
        $keys = [];
        $int = 0;

        foreach ($fieldMap->content as $items) {
            $keysFromBackend = (substr_count($items->item->value, '.')) ? substr($items->item->value,
                strrpos($items->item->value, '.') + 1) : $items->item->value;
            $keys[$keysFromBackend] = $keysFromBackend;
            $designations[$int][$keysFromBackend] = $items->designation;
            $int++;
        }

        $exportData = $this->extractXMLData($parseXML, $keys);
        $exportDataMergedWithDesignation = $this->mergeDataWithEqualKeys($exportData, $designations);
        $this->exportToPostype($exportDataMergedWithDesignation);
    }

    /**
     * @return array|object|null
     */
    public function getMetaData(){
        global $wpdb;
        $postIdAssigned = $wpdb->get_results( "
                    SELECT *
                    FROM $wpdb->postmeta
                    WHERE meta_key='metaId' 
                    AND meta_value = '".$this->metaId."'"
        );
        return $postIdAssigned;
    }

    /**
     *
     */
    public function updatePostType(){

        $postIdAssigned = $this->getMetaData();

        if(count($postIdAssigned) > 0) {
            $postIdAssigned = (array) $postIdAssigned[0];
        }

        $PostData = array(
            'post_title' => $this->post_title,
            'post_content' => $this->post_content,
            'post_excerpt' => $this->post_excerpt,
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => $this->postType,
        );

        if (isset($postIdAssigned['post_id']) && !empty($postIdAssigned['post_id'])) {

            $PostData['ID'] = $postIdAssigned['post_id'];
            wp_update_post($PostData);
            update_post_meta($postIdAssigned['post_id'], 'metaId', $this->metaId);

            if (count($this->meta_input) > 0 && $postIdAssigned['post_id']) {
                foreach ($this->meta_input as $metaKey => $value) {
                    update_post_meta($PostData['ID'], key($this->meta_input[$metaKey]), $value);
                }
            }
        } else {

            $postID = wp_insert_post($PostData);
            add_post_meta($postID, 'metaId', $this->metaId);

            if (count($this->meta_input) > 0 && $postID) {
                foreach ($this->meta_input as $metaKey => $value) {
                    add_post_meta($postID, key($this->meta_input[$metaKey]), $value);
                }
            }
        }
    }

    /**
     * @param $data
     */
    public function exportToPostype($data)
    {
        foreach ($data as $key => $value) {
            foreach ($data[$key] as $itemKeys => $item) {

                if ($item['Designation'] === 'metaId') {
                    $this->metaId = $item['Value'];
                }

                if ($item['Designation'] === 'posttitle') {
                    $this->post_title = $item['Value'];
                }

                if ($item['Designation'] === 'postexcerpt') {
                    $this->post_excerpt = $item['Value'];
                }

                if ($item['Designation'] === 'postcontent') {
                    $this->post_content = $item['Value'];
                }

                if ($item['Designation'] === 'metadata') {
                    array_push($this->meta_input, array($item['Designation'] => $item['Value']));
                }
            }

            if ($this->metaId){
                $this->updatePostType();
            } else {
                echo "No Assignment Id, Please go back and assign...";
                exit;
            }
        }
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
            if (!$_POST['postType']) {
                return;
            }
            $this->postType = $_POST['postType'];

            $url = $_POST['mod_xml_render_url'];
            $export = $_POST['exportToPostType'];
            $view = $_POST['mod_xml_render_view'];
            $fieldMap = json_decode(html_entity_decode(stripslashes($_POST['mod_xml_render_fieldmap'])));
            $setPostType = $_POST['setPostType'];

            if ($export === 'export') {
                $this->exportData($url, $fieldMap, $setPostType);
                update_post_meta($postId, 'xml_url', $url);
                //update_post_meta($postId, 'view', $view);
                update_post_meta($postId, 'fieldmap', $_POST['mod_xml_render_fieldmap']);
                update_post_meta($postId, 'setPostType', $setPostType);
            }

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

    /**
     *
     */
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

    /**
     * @return arra
     */
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

    /**
     * @return string
     */
    public function template(): string
    {
        return "list.blade.php";
    }

    /**
     *
     */
    public function script()
    {
        // Enqueue React
        class_exists('\Modularity\Helper\React') ? \Modularity\Helper\React::enqueue() : \ModularityXmlRender\Helper\React::enqueue();

        wp_enqueue_script('modularity-' . $this->slug);
        wp_localize_script('modularity-' . $this->slug, 'modXMLRender', array(
            'translation' => array(
                'somethingWentWrong' => __('Something went wrong, please try again later.',
                    'modularity-xml-render'),
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

    /**
     *
     */
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
            'posttypes' => get_post_types(),
            'setPostType' =>  get_post_meta($post->ID, 'setPostType', true),
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
                'exportChoice' => __('Yes, export', 'modularity-xml-render'),
                'designation' => __('Post-type designation', 'modularity-xml-render'),
                'posttypeChoose' => __('Choose post type', 'modularity-xml-render'),
                'designationChoose' => __('Choose designation', 'modularity-xml-render'),
                'designationValuesMetaData' => __('Meta data', 'modularity-xml-render'),
                'designationValuesPostTitle' => __('Post title', 'modularity-xml-render'),
                'designationValuesPostExcerpt' => __('Post excerpt', 'modularity-xml-render'),
                'designationValuesPostContent' => __('Post content', 'modularity-xml-render'),
                'designationValuesPostId' => __('Assignment Id', 'modularity-xml-render'),
                'designationValuesPostDate' => __('Post date', 'modularity-xml-render'),
            )
        ));

        wp_enqueue_style('modularity-' . $this->slug . '-admin'); // Enqueue styles
    }

    /**
     * @param $postId
     * @return array
     */
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
