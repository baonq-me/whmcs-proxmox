<?php
/**
 * WHMCS SDK Sample Addon Module
 *
 * An addon module allows you to add additional functionality to WHMCS. It
 * can provide both client and admin facing user interfaces, as well as
 * utilise hook functionality within WHMCS.
 *
 * This sample file demonstrates how an addon module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Addon Modules are stored in the /modules/addons/ directory. The module
 * name you choose must be unique, and should be all lowercase, containing
 * only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "addonmodule" and therefore all functions
 * begin "addonmodule_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/addon-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

//use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

use WHMCS\Module\Addon\Proxmox\Admin\AdminDispatcher;
use WHMCS\Module\Addon\Proxmox\Client\ClientDispatcher;


/**
 * Define addon module configuration parameters.
 *
 * Includes a number of required system fields including name, description,
 * author, language and version.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * Examples of each and their possible configuration parameters are provided in
 * the fields parameter below.
 *
 * @return array
 */
function proxmox_config()
{
    return array(
        'name' => 'Proxmox VE', // Display name for your module
        'description' => 'This module help intergrating Proxmox VE into WHMCS for creating VM/CT automatically.', // Description displayed within the admin interface
        'author' => '<a href="https://github.com/baonq-me/whmcs-proxmox" target="_blank">Quoc-Bao Nguyen</a>', // Module author name
        'language' => 'english', // Default language
        'version' => '1.0', // Version number
        'fields' => array(
            // a text field type allows for single line text input
            'PVE Hostname' => array(
                'FriendlyName' => 'PVE hostname',
                'Type' => 'text',
                'Size' => '25',
                'Default' => 'pve.baonq.me',
                'Description' => 'Address of Proxmox VE server. Example: pve.baonq.me',
            ),

            'PVE User' => array(
                'FriendlyName' => 'PVE User',
                'Type' => 'text',
                'Size' => '25',
                'Default' => 'root',
                'Description' => '',
            ),

            // a password field type allows for masked text input
            'PVE Password' => array(
                'FriendlyName' => 'PVE Password',
                'Type' => 'password',
                'Size' => '25',
                'Default' => '',
                'Description' => '',
            ),

            'Default Storage Bus' => array(
                'FriendlyName' => 'Default Storage Bus',
                'Type' => 'text',
                'Size' => '25',
                'Default' => 'virtio',
                'Description' => '',
            ),

            'Default Storage Engine' => array(
                'FriendlyName' => 'Default Storage Engine',
                'Type' => 'text',
                'Size' => '25',
                'Default' => 'local-zfs',
                'Description' => '',
            ),

            'Default Storage Format' => array(
                'FriendlyName' => 'Default Storage Format',
                'Type' => 'text',
                'Size' => '25',
                'Default' => 'raw',
                'Description' => '',
            ),

            'CloudInit Storage' => array(
                'FriendlyName' => 'CloudInit Storage',
                'Type' => 'text',
                'Size' => '25',
                'Default' => 'cloudinit',
                'Description' => '',
            ),
            // the yesno field type displays a single checkbox option
            /*'Checkbox Field Name' => array(
                'FriendlyName' => 'Checkbox Field Name',
                'Type' => 'yesno',
                'Description' => 'Tick to enable',
            ),
            // the dropdown field type renders a select menu of options
            'Dropdown Field Name' => array(
                'FriendlyName' => 'Dropdown Field Name',
                'Type' => 'dropdown',
                'Options' => array(
                    'option1' => 'Display Value 1',
                    'option2' => 'Second Option',
                    'option3' => 'Another Option',
                ),
                'Description' => 'Choose one',
            ),
            // the radio field type displays a series of radio button options
            'Radio Field Name' => array(
                'FriendlyName' => 'Radio Field Name',
                'Type' => 'radio',
                'Options' => 'First Option,Second Option,Third Option',
                'Description' => 'Choose your option!',
            ),
            // the textarea field type allows for multi-line text input
            'Textarea Field Name' => array(
                'FriendlyName' => 'Textarea Field Name',
                'Type' => 'textarea',
                'Rows' => '3',
                'Cols' => '60',
                'Description' => 'Freeform multi-line text input field',
            ),*/
        )
    );
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * This function is optional.
 *
 * @return array Optional success/failure message
 */

 $triggers = [
   'updateInvoiceAndOrder' => "
CREATE TRIGGER `updateInvoiceAndOrder` AFTER UPDATE ON `tblinvoices`
FOR EACH ROW
BEGIN
IF NEW.`status` = 'Paid' THEN
 UPDATE `tblinvoiceitems` SET `notes` = 'Managed by Proxmox addon' WHERE `tblinvoiceitems`.`invoiceid` = NEW.`id`;
 UPDATE `tblinvoiceitems` SET `status` = 'Paid'                    WHERE `tblinvoiceitems`.`invoiceid` = NEW.`id`;
 UPDATE `tblinvoiceitems` SET `updated_at` = NOW()                 WHERE `tblinvoiceitems`.`invoiceid` = NEW.`id`;
 UPDATE `tblorders` SET `tblorders`.`status` = 'Active'            WHERE `tblorders`.`invoiceid` = NEW.`id`;
END IF;
END;",

'activeHostingService' => "
CREATE TRIGGER `activeHostingService` AFTER UPDATE ON `tblorders`
FOR EACH ROW
BEGIN
IF NEW.`status` = 'Active' THEN
 UPDATE `tblhosting` SET `tblhosting`.`domainstatus` = 'Active' WHERE `tblhosting`.`orderid` = NEW.`id`;
END IF;
END;",
];

 $procedures = [
   'getItemsByStatus' => "
CREATE DEFINER=`root`@`localhost` PROCEDURE `getItemsByStatus`(IN `status` TEXT, IN `amount` INT)
READS SQL DATA
SELECT `tblinvoiceitems`.`id`, `tblinvoiceitems`.`userid`, CONCAT(`tblclients`.`firstname`, ' ', `tblclients`.`lastname`) as username,  `tblinvoiceitems`.`invoiceid`, `tblinvoiceitems`.`type`, `tblinvoiceitems`.`description`, DATE_FORMAT(`tblinvoiceitems`.`updated_at`, '%b %d, %Y %k:%i:%s') as updated_at, `tblinvoiceitems`.`status`
FROM `tblinvoiceitems`
JOIN `tblclients` ON `tblclients`.`id` = `tblinvoiceitems`.`userid`
WHERE `tblinvoiceitems`.`status` = status COLLATE utf8_unicode_ci
AND `tblinvoiceitems`.`notes` = 'Managed by Proxmox addon' COLLATE utf8_unicode_ci
ORDER BY `tblinvoiceitems`.`id`
LIMIT amount",

    'getItemStatus' => "
CREATE DEFINER=`root`@`localhost` PROCEDURE `getItemStatus`(IN `itemid` INT)
READS SQL DATA
SELECT `tblinvoiceitems`.`id`, `tblinvoiceitems`.`status`
FROM `tblinvoiceitems`
WHERE `tblinvoiceitems`.`id` = itemid
AND `tblinvoiceitems`.`notes` = 'Managed by Proxmox addon' COLLATE utf8_unicode_ci",

    'updateItemStatus' => "
CREATE DEFINER=`root`@`localhost` PROCEDURE `updateItemStatus`(IN `itemid` INT, IN `status` TEXT)
MODIFIES SQL DATA
UPDATE `tblinvoiceitems`
SET `tblinvoiceitems`.`status` = status COLLATE utf8_unicode_ci
WHERE `tblinvoiceitems`.`id` = itemid
AND `tblinvoiceitems`.`notes` = 'Managed by Proxmox addon' COLLATE utf8_unicode_ci",

    'updateCustomField' => "
CREATE DEFINER=`root`@`localhost` PROCEDURE `updateCustomField`(IN `itemid` INT, IN `fieldname` TEXT, IN `value` TEXT)
MODIFIES SQL DATA
UPDATE `tblcustomfieldsvalues`
SET `tblcustomfieldsvalues`.`value` = value COLLATE utf8_unicode_ci
WHERE `tblcustomfieldsvalues`.`relid` = itemid
AND `tblcustomfieldsvalues`.`fieldid` IN
(
    SELECT `tblcustomfields`.`id`
    FROM `tblhosting`
    JOIN `tblcustomfields`
    ON `tblcustomfields`.`relid` = `tblhosting`.`packageid`
    WHERE `tblhosting`.`id` = itemid
    AND `tblcustomfields`.`fieldname` = fieldname COLLATE utf8_unicode_ci
)",

    'getCustomField' => "
CREATE DEFINER=`root`@`localhost` PROCEDURE `getCustomField`(IN `itemid` INT, IN `fieldname` TEXT)
READS SQL DATA
SELECT `tblcustomfields`.`relid`, `tblcustomfields`.`fieldname`, `tblcustomfieldsvalues`.`value`
FROM `tblcustomfieldsvalues`
JOIN `tblcustomfields`
ON `tblcustomfieldsvalues`.`fieldid` = `tblcustomfields`.`id`
WHERE `tblcustomfieldsvalues`.`relid` = itemid
AND `tblcustomfields`.`fieldname` = fieldname COLLATE utf8_unicode_ci"
 ];

 $functions = [
   'order2invoice' => "
CREATE DEFINER=`root`@`localhost` FUNCTION `order2invoice`(`orderid` INT) RETURNS int(11)
 READS SQL DATA
 DETERMINISTIC
BEGIN
DECLARE invoiceid INT;
SET invoiceid = 0;
SELECT `tblorders`.`invoiceid` INTO invoiceid FROM `tblorders` WHERE `tblorders`.`id` = orderid;
RETURN invoiceid;
END",

 'invoice2order' => "
CREATE DEFINER=`root`@`localhost` FUNCTION `invoice2order`(`invoiceid` INT) RETURNS int(11)
 READS SQL DATA
BEGIN
DECLARE orderid INT;
SET orderid = 0;
SELECT `tblorders`.`id` INTO orderid FROM `tblorders` WHERE `tblorders`.`invoiceid` = invoiceid;
RETURN orderid;
END"];

$databaseObjects = ['trigger' => $triggers, 'procedure' => $procedures, 'function' => $functions];


function proxmox_activate()
{
    // Create custom tables and schema required by your module
    //$query = "CREATE TABLE `mod_proxmox` (`id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`demo` TEXT NOT NULL )";
    //full_query($query);

    /*Capsule::schema()->dropIfExists('mod_proxmox_info');
    Capsule::schema()->dropIfExists('mod_proxmox_resource');
    Capsule::schema()->dropIfExists('mod_proxmox_usage');

    Capsule::schema()->create(
      'mod_proxmox_info',
      function ($table) {
        $table->increments('id')->unique();
        $table->text('hostname');
        $table->text('username');
        $table->text('password');
        $table->text('status');
        $table->timestamp('updated_at');
        $table->string('notes');

        //$table->primary('id');
      }
    );

    Capsule::schema()->create(
      'mod_proxmox_resource',
      function ($table) {
        $table->increments('id')->unique();
        $table->integer('cpus');
        $table->integer('memory');
        $table->integer('storage');
        $table->text('status');
        $table->timestamp('updated_at');
        $table->string('notes');

        //$table->primary('id');
        $table->foreign('id')->references('id')->on('mod_proxmox_info');
      }
    );

    Capsule::schema()->create(
      'mod_proxmox_usage',
      function ($table) {
        $table->increments('id')->unique();
        $table->float('cpuload');
        $table->float('memory');
        $table->float('storage');
        $table->text('status');
        $table->timestamp('updated_at');
        $table->string('notes');

        //$table->primary('id');
        $table->foreign('id')->references('id')->on('mod_proxmox_info');
      }
    );*/



    Capsule::schema()->table('tblinvoiceitems', function($table)
    {
        $table->timestamp('updated_at');
        $table->text('status');
    });


    global $databaseObjects;

    foreach ($databaseObjects as $type => $objects)
    {
      foreach ($objects as $name => $sql)
      {
          logActivity("[Proxmox] Installing $type '$name' into database");
          Capsule::connection()->getPdo()->exec($sql);
      }
    }

//UPDATE `tblinvoiceitems` SET `updated_on` = DATE_FORMAT(NOW(), '%b %d, %Y %k:%i:%s');

    return array(
        'status' => 'success', // Supported values here include: success, error or info
        'description' => 'Addon is activated successfully. Please visit admin/addonmodules.php?module=proxmox for configurations.',
    );
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 * Use this function to undo any database and schema modifications
 * performed by your module.
 *
 * This function is optional.
 *
 * @return array Optional success/failure message
 */
function proxmox_deactivate()
{
    //Capsule::schema()->dropIfExists('mod_proxmox_info');
    //Capsule::schema()->dropIfExists('mod_proxmox_resource');
    //Capsule::schema()->dropIfExists('mod_proxmox_usage');

    global $databaseObjects;

    foreach ($databaseObjects as $type => $objects)
    {
      foreach ($objects as $name => $sql)
      {
          logActivity("[Proxmox] Removing $type '$name' from database");
          Capsule::connection()->getPdo()->exec('DROP '.strtoupper($type).' IF EXISTS `'.$name.'`');
      }
    }

    /*Capsule::connection()->getPdo()->exec('DROP FUNCTION IF EXISTS `invoice2order`');
    Capsule::connection()->getPdo()->exec('DROP FUNCTION IF EXISTS `order2invoice`');
    Capsule::connection()->getPdo()->exec('DROP PROCEDURE IF EXISTS `getItemsByStatus`');
    Capsule::connection()->getPdo()->exec('DROP TRIGGER IF EXISTS `updateInvoiceAndOrder`');
    Capsule::connection()->getPdo()->exec('DROP TRIGGER IF EXISTS `activeHostingService`');*/

    Capsule::schema()->table('tblinvoiceitems', function($table)
    {
        $table->dropColumn(['updated_at', 'status']);
    });

    Capsule::table('tblinvoiceitems')->where('notes', 'Managed by Proxmox addon')->update(array('notes' => ''));

    return array(
        'status' => 'success', // Supported values here include: success, error or info
        'description' => 'Addon is disabled. Infomation about Proxmox Cluster will be kept.',
    );
}

/**
 * Upgrade.
 *
 * Called the first time the module is accessed following an update.
 * Use this function to perform any required database and schema modifications.
 *
 * This function is optional.
 *
 * @return void
 */
function proxmox_upgrade($vars)
{
    // $currentlyInstalledVersion = $vars['version'];
    //
    // /// Perform SQL schema changes required by the upgrade to version 1.1 of your module
    // if ($currentlyInstalledVersion < 1.1) {d
    //     $query = "ALTER `mod_proxmox` ADD `demo2` TEXT NOT NULL ";
    //     full_query($query);
    // }
    //
    // /// Perform SQL schema changes required by the upgrade to version 1.2 of your module
    // if ($currentlyInstalledVersion < 1.2) {
    //     $query = "ALTER `mod_proxmox` ADD `demo3` TEXT NOT NULL ";
    //     full_query($query);
    // }
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * This function is optional.
 *
 * @see AddonModule\Admin\Controller@index
 *
 * @return string
 */
function proxmox_output($vars)
{
    $vars['smarty'] = new Smarty();
    $vars['smartybc'] = new SmartyBC();

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $dispatcher->dispatch($action, $vars);
}

/**
 * Admin Area Sidebar Output.
 *
 * Used to render output in the admin area sidebar.
 * This function is optional.
 *
 * @param array $vars
 *
 * @return string
 */
function proxmox_sidebar($vars)
{
    //$sidebar = '<p>Giờ hiện tại<br/>'.date("F j, Y, g:i a").'</p><p>Chưa biết điền cái gì vào sidebar</p>';
    //return $sidebar;
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 * Should return an array of output parameters.
 *
 * This function is optional.
 *
 * @see AddonModule\Client\Controller@index
 *
 * @return array
 */
function proxmox_clientarea($vars)
{
    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars);
}
