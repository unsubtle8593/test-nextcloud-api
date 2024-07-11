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
 * This file is a delegate for module. Class does not extend any other class.
 *
 * All methods provided in this example are optional, but function names are
 * still reserved.
 */

namespace Box\Mod\Example;

use FOSSBilling\InformationException;

class Service
{
    protected $di;

    public function setDi(\Pimple\Container|null $di): void
    {
        $this->di = $di;
    }

    /**
     * Any module may define this function to return an array of permission keys that are related to it.
     * You may define either a `bool` or a `select` permission type.
     * Modules do not need to define this function.
     * 
     * We've included an example of how to check the permissions under the `/api/Admin.php` file and some front-end usage under `/html_admin/mod_example_index.html.twig`
     * 
     * @return array 
     */
    /*
    public function getModulePermissions(): array
    {
        return [
            'do_something' => [
                'type' => 'bool',
                'display_name' => 'Do something',
                'description' => 'Allows the staff member to do something',
            ],
            'a_select' => [
                'type' => 'select',
                'display_name' => 'A select',
                'description' => 'This is an example of the select permission type',
                'options' => [
                    'value_1' => 'Value 1',
                    'value_2' => 'Value 2',
                    'value_3' => 'Value 3',
                ]
            ],
            'manage_settings' => [], // Tells FOSSBilling that there should be a permission key to manage the module's settings (admin/extension/settings/example)
        ];
    }
    */

    /**
     * Method to install the module. In most cases you will use this
     * to create database tables for your module.
     *
     * If your module isn't very complicated then the extension_meta
     * database table might be enough.
     *
     * @return bool
     *
     * @throws InformationException
     */
    public function install(): bool
    {
        // Execute SQL script if needed
        $db = $this->di['db'];
        $db->exec('SELECT NOW()');

        // throw new InformationException("Throw exception to terminate module installation process with a message", array(), 123);
        return true;
    }

    /**
     * Method to uninstall module. In most cases you will use this
     * to remove database tables for your module.
     *
     * You also can opt to keep the data in the database if you want
     * to keep the data for future use.
     *
     * @return bool
     *
     * @throws InformationException
     */
    public function uninstall(): bool
    {
        // throw new InformationException("Throw exception to terminate module uninstallation process with a message", array(), 124);
        return true;
    }

    /**
     * Method to update module. When you release new version to
     * extensions.fossbilling.org then this method will be called
     * after the new files are placed.
     *
     * @param array $manifest - information about the new module version
     *
     * @return bool
     *
     * @throws InformationException
     */
    public function update(array $manifest): bool
    {
        // throw new InformationException("Throw exception to terminate module update process with a message", array(), 125);
        return true;
    }

    /**
     * Method is used to create search query for paginated list.
     * Usually there is one paginated list per module.
     *
     * @param array $data
     *
     * @return array() = list of 2 parameters: array($sql, $params)
     */
    public function getSearchQuery(array $data): array
    {
        $params = [];
        $sql = "SELECT meta_key, meta_value
            FROM extension_meta
            WHERE extension = 'example'";

        $client_id = $data['client_id'] ?? null;

        if (null !== $client_id) {
            $sql .= ' AND client_id = :client_id';
            $params[':client_id'] = $client_id;
        }

        $sql .= ' ORDER BY created_at DESC';

        return [$sql, $params];
    }

    /**
     * Methods is a delegate for one database row.
     *
     * @param array $row - array representing one database row
     * @param string $role - guest|client|admin who is calling this method
     * @param bool $deep - true|false deep or light version of result to return to API
     *
     * @return array
     */
    public function toApiArray(array $row, string $role = 'guest', bool $deep = true): array
    {
        return $row;
    }

    /**
     * Example event hook. Any module can hook to any FOSSBilling event and perform actions.
     *
     * Make sure extension is enabled before testing this event.
     *
     * NOTE: IF you have DEBUG mode set to TRUE then all events with params
     * are logged to data/log/hook_*.log file. Check this file to see what
     * kind of parameters are passed to event.
     *
     * In this example we are going to count how many times client failed
     * to enter correct login details
     *
     * @return void
     *
     * @throws InformationException
     */
    /*
    public static function onEventClientLoginFailed(\Box_Event $event): void
    {
        // getting Dependency Injector
        $di = $event->getDi();

        // @note almost in all cases you will need Admin API
        $api = $di['api_admin'];

        // sometimes you may need guest API
        // $api_guest = $di['api_guest'];

        $params = $event->getParameters();

        // @note To debug parameters by throwing an exception
        // throw new Exception(print_r($params, 1));

        // Use RedBean ORM in any place of FOSSBilling where API call is not enough
        // First we need to find if we already have a counter for this IP
        // We will use extension_meta table to store this data.
        $values = [
            'ext' => 'example',
            'rel_type' => 'ip',
            'rel_id' => $params['ip'],
            'meta_key' => 'counter',
        ];
        $meta = $di['db']->findOne('extension_meta', 'extension = :ext AND rel_type = :rel_type AND rel_id = :rel_id AND meta_key = :meta_key', $values);
        if (!$meta) {
            $meta = $di['db']->dispense('extension_meta');
            // $count->client_id = null; // client id is not known in this situation
            $meta->extension = 'mod_example';
            $meta->rel_type = 'ip';
            $meta->rel_id = $params['ip'];
            $meta->meta_key = 'counter';
            $meta->created_at = date('Y-m-d H:i:s');
        }
        $meta->meta_value = $meta->meta_value + 1;
        $meta->updated_at = date('Y-m-d H:i:s');
        $di['db']->store($meta);

        // Now we can perform task depending on how many times wrong details were entered

        // We can log event if it repeats for 2 time
        if ($meta->meta_value > 2) {
            $api->activity_log(['m' => 'Client failed to enter correct login details ' . $meta->meta_value . ' time(s)']);
        }

        // if client gets funky, we block him
        if ($meta->meta_value > 30) {
            throw new InformationException('You have failed to login too many times. Contact support.');
        }
    }
    */

    /**
     * This event hook is registered in example module client API call.
     */
    public static function onAfterClientCalledExampleModule(\Box_Event $event): void
    {
        // error_log('Called event from example module');

        $di = $event->getDi();
        $params = $event->getParameters();

        $meta = $di['db']->dispense('extension_meta');
        $meta->extension = 'mod_example';
        $meta->meta_key = 'event_params';
        $meta->meta_value = json_encode($params);
        $meta->created_at = date('Y-m-d H:i:s');
        $meta->updated_at = date('Y-m-d H:i:s');
        $di['db']->store($meta);
    }

    /**
     * Example event hook for public ticket and set event return value.
     */
    public static function onBeforeGuestPublicTicketOpen(\Box_Event $event)
    {
        /* Uncomment lines below in order to see this function in action */

        /*
        $data            = $event->getParameters();
        $data['status']  = 'closed';
        $data['subject'] = 'Altered subject';
        $data['message'] = 'Altered text';
        $event->setReturnValue($data);
        */
    }

    /**
     * Example email sending.
     */
    public static function onAfterClientOrderCreate(\Box_Event $event)
    {
        /* Uncomment lines below in order to see this function in action */

        /*
         $di = $event->getDi();
         $api    = $di['api_admin'];
         $params = $event->getParameters();

         $email = array();
         $email['to_client'] = $params['client_id'];
         $email['code']      = 'mod_example_email'; //@see modules/Example/html_email/mod_example_email.html.twig

         // these parameters are available in email template
         $email['order']     = $api->order_get(array('id'=>$params['id']));
         $email['other']     = 'any other value';

         $api->email_template_send($email);
        */
    }
}
