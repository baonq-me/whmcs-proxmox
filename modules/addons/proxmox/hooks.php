<?php
/**
 * WHMCS SDK Sample Addon Module Hooks File
 *
 * Hooks allow you to tie into events that occur within the WHMCS application.
 *
 * This allows you to execute your own code in addition to, or sometimes even
 * instead of that which WHMCS executes by default.
 *
 * @see https://developers.whmcs.com/hooks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Register a hook with WHMCS.
 *
 * This sample demonstrates triggering a service call when a change is made to
 * a client profile within WHMCS.
 *
 * For more information, please refer to https://developers.whmcs.com/hooks/
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */

//use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\Proxmox\WHMCS_Data;

add_hook('ClientEdit', 1, function(array $params) {
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
});

add_hook('AfterCronJob', 1, function($vars) {
    //WHMCS_Data::moveInvoiceItemsToQueue();
    //WHMCS_Data::processInvoiceItems();
});


/**
 * Hello World Widget.
 */

add_hook('AdminHomeWidgets', 1, function() {
    return new HelloWorldWidget();
});

class HelloWorldWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Hello World';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData()
    {
        return array();
    }

    public function generateOutput($data)
    {
        return "<div class=\"widget-content-padded\">Hello World!</div>";
    }
}
