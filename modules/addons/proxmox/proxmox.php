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
            'PyProxmox Module' => array(
                'FriendlyName' => 'PyProxmox Module',
                'Type' => 'text',
                'Size' => '40',
                'Default' => '../modules/addons/proxmox/pyproxmox',
                'Description' => 'Path to PyProxmox module. Relative path and absolute path are both accepted. Type \'auto\' for default path.',
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
function proxmox_activate()
{
    // Create custom tables and schema required by your module
    $query = "CREATE TABLE `mod_proxmox` (`id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`demo` TEXT NOT NULL )";
    full_query($query);

    return array(
        'status' => 'success', // Supported values here include: success, error or info
        'description' => 'This is a demo module only. In a real module you might report an error/failure or instruct a user how to get started with it here.',
    );

    # Generate file proxmox.conf
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
    // Undo any database and schema modifications made by your module here
    $query = "DROP TABLE `mod_proxmox`";
    full_query($query);

    return array(
        'status' => 'success', // Supported values here include: success, error or info
        'description' => 'This is a demo module only. In a real module you might report an error/failure here.',
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
    $currentlyInstalledVersion = $vars['version'];

    /// Perform SQL schema changes required by the upgrade to version 1.1 of your module
    if ($currentlyInstalledVersion < 1.1) {
        $query = "ALTER `mod_proxmox` ADD `demo2` TEXT NOT NULL ";
        full_query($query);
    }

    /// Perform SQL schema changes required by the upgrade to version 1.2 of your module
    if ($currentlyInstalledVersion < 1.2) {
        $query = "ALTER `mod_proxmox` ADD `demo3` TEXT NOT NULL ";
        full_query($query);
    }
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
    // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    // Get module configuration parameters
    $configTextField = $vars['Text Field Name'];
    $configPasswordField = $vars['Password Field Name'];
    $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['Dropdown Field Name'];
    $configRadioField = $vars['Radio Field Name'];
    $configTextareaField = $vars['Textarea Field Name'];

    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    echo $response.'A here';
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
    // Get common module parameters
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $_lang = $vars['_lang'];

    // Get module configuration parameters
    $configTextField = $vars['Text Field Name'];
    $configPasswordField = $vars['Password Field Name'];
    $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['Dropdown Field Name'];
    $configRadioField = $vars['Radio Field Name'];
    $configTextareaField = $vars['Textarea Field Name'];

    $sidebar = '<p>Giờ hiện tại<br/>'.date("F j, Y, g:i a").'</p><p>Chưa biết điền cái gì vào sidebar</p>';
    return $sidebar;
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
    // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. index.php?m=addonmodule
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    // Get module configuration parameters
    $configTextField = $vars['Text Field Name'];
    $configPasswordField = $vars['Password Field Name'];
    $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['Dropdown Field Name'];
    $configRadioField = $vars['Radio Field Name'];
    $configTextareaField = $vars['Textarea Field Name'];

    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars);
}
