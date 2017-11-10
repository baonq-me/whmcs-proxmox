<?php

namespace WHMCS\Module\Addon\Proxmox\Admin;

use WHMCS\Module\Addon\Proxmox\PHPProxmox\PHPProxmox;


//require_once("../vendor/smarty/smarty/libs/SmartyBC.class.php");

//require_once("../modules/addons/proxmox/functions.php");

/**
 * Sample Admin Area Controller
 */
class Controller {

    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function index($vars)
    {
        $smarty = $vars['smarty'];

        //$version = $vars['version']; // eg. 1.0
        //$LANG = $vars['_lang']; // an array of the currently loaded language variables

        $proxmox = new PHPProxmox($vars);
        $proxmox->buildConfig();
        $status = implode("<br/>", $proxmox->getSystemStatus());

        $smarty->assign('status', $status);
        $smarty->assign('modulelink', $vars['modulelink']);
        $smarty->assign('configPVEHostname', $vars['PVE Hostname']);
        $smarty->assign('configPVEUser', $vars['PVE User']);
        //$smarty->assign('configPVEPassword', $vars['PVE Password']);
        $smarty->assign('configStorageBus', $vars['Default Storage Bus']);
        $smarty->assign('configStorageEngine', $vars['Default Storage Engine']);
        $smarty->assign('configStorageFormat', $vars['Default Storage Format']);
        $smarty->assign('configCloudInitStorage', $vars['CloudInit Storage']);

        $smarty->display(dirname(__FILE__) . '/../../templates/admin/index.tpl');

        return '';
    }

    /**
     * Show action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function show($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables

        // Get module configuration parameters
        $configTextField = $vars['Text Field Name'];
        $configPasswordField = $vars['Password Field Name'];
        $configCheckboxField = $vars['Checkbox Field Name'];
        $configDropdownField = $vars['Dropdown Field Name'];
        $configRadioField = $vars['Radio Field Name'];
        $configTextareaField = $vars['Textarea Field Name'];

        echo <<<EOF

<h2>Show</h2>

<p>This is the <em>show</em> action output of the sample addon module.</p>

<p>The currently installed version is: <strong>{$version}</strong></p>

<p>
    <a href="{$modulelink}" class="btn btn-info">
        <i class="fa fa-arrow-left"></i>
        Back to home
    </a>
</p>

EOF;
    }
}
