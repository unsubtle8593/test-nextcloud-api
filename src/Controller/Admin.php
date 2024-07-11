<?php

/**
 * FOSSBilling.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license   Apache-2.0
 *
 * Copyright FOSSBilling 2022
 * This software may contain code previously used in the BoxBilling project.
 * Copyright BoxBilling, Inc 2011-2021
 *
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */

/**
 * This file connects FOSSBilling admin area interface and API
 * Class does not extend any other class.
 */

namespace Box\Mod\Example\Controller;

class Admin implements \FOSSBilling\InjectionAwareInterface
{
    protected $di;

    public function setDi(\Pimple\Container|null $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    /**
     * This method registers menu items in admin area navigation block
     * This navigation is cached in data/cache/{hash}. To see changes please
     * remove the file.
     *
     * @return array
     */
    public function fetchNavigation(): array
    {
        return [
            'group' => [
                'index' => 1500,                // menu sort order
                'location' => 'nextcloud',          // menu group identifier for subitems
                'label' => __trans('Nextcloud module'),    // menu group title
                'class' => 'example',           // used for css styling menu item
            ],
            'subpages' => [
                [
                    'location' => 'nextcloud', // place this module in extensions group
                    'label' => __trans('Nextcloud module configure'),
                    'index' => 1500,
                    'uri' => $this->di['url']->adminLink('nextcloud'),
                    'class' => '',
                ],
            ],
        ];
    }

    /**
     * Methods maps admin areas urls to corresponding methods
     * Always use your module prefix to avoid conflicts with other modules
     * in future.
     *
     * @example $app->get('/example/test',      'get_test', null, get_class($this)); // calls get_test method on this class
     * @example $app->get('/example/:id',        'get_index', array('id'=>'[0-9]+'), get_class($this));
     */
    public function register(\Box_App &$app): void
    {
        $app->get('/example', 'get_index', [], static::class);
        $app->get('/example/test', 'get_test', [], static::class);
        $app->get('/example/user/:id', 'get_user', ['id' => '[0-9]+'], static::class);
        $app->get('/example/api', 'get_api', [], static::class);
    }

    public function get_index(\Box_App $app)
    {
        // always call this method to validate if admin is logged in
        $this->di['is_admin_logged'];

        return $app->render('mod_example_index');
    }

    public function get_test(\Box_App $app)
    {
        // always call this method to validate if admin is logged in
        $this->di['is_admin_logged'];

        $params = [];
        $params['youparamname'] = 'yourparamvalue';

        return $app->render('mod_example_index', $params);
    }

    public function get_user(\Box_App $app, $id)
    {
        // always call this method to validate if admin is logged in
        $this->di['is_admin_logged'];

        $params = [];
        $params['userid'] = $id;

        return $app->render('mod_example_index', $params);
    }

    public function get_api(\Box_App $app, $id = null)
    {
        // always call this method to validate if admin is logged in
        $api = $this->di['api_admin'];
        $list_from_controller = $api->example_get_something();

        $params = [];
        $params['api_example'] = true;
        $params['list_from_controller'] = $list_from_controller;

        return $app->render('mod_example_index', $params);
    }
}
