<?php

namespace ModularityXmlRender;

/**
 * Class XmlParser
 * @package ModularityXmlRender
 */
class XmlParser
{
    /**
     * XmlParser constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestRoutes'));
    }

    /**
     * Registers all rest routes for login / logout
     *
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route(
            "ModularityXmlParser/v1",
            "Get",
            array(
                'methods' => \WP_REST_Server::READABLE,
                'callback' => array($this, 'getXmlData')
            )
        );
    }

    /**
     * @param $request
     * @return json from xml data
     */
    public function getXmlData($request)
    {
        $data  = wp_remote_get($request->get_param('url'));
        $parseXML = simplexml_load_string($data['body']);

        return wp_send_json(
            array(
                'result' => $parseXML
            )
        );
    }
}