<?php

namespace WHMCS\Module\Addon\Proxmox\Admin;

use WHMCS\Module\Addon\Proxmox\PHPProxmox\PHPProxmox;

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
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables

        // Get module configuration parameters
        $configPVEHostname = $vars['PVE Hostname'];
        $configPVEUser = $vars['PVE User'];
        //$configPVEPassword = $vars['PVE Password'];
        $configStorageBus = $vars['Default Storage Bus'];
        $configStorageEngine = $vars['Default Storage Engine'];
        $configStorageFormat = $vars['Default Storage Format'];
        $configCloudInitStorage = $vars['CloudInit Storage'];

        $proxmox = new PHPProxmox("../modules/addons/proxmox/pyproxmox");
        $proxmox->buildConfig($vars);
        $status = implode("<br/>", $proxmox->getSystemStatus());

        return <<<EOF

<h2>System Infomation</h2>
<code>{$status}</code>
<br/><br/>
<p>Proxmox VE Configurations:</p>

<blockquote>
    PVE Hostname:&#09;{$configPVEHostname}<br>
    PVE User:&#09;{$configPVEUser}<br>
    PVE Password:&#09; <code>Éo cho xem :v</code><br>
    Default Storage Bus:&#09; {$configStorageBus}<br>
    Default Storage Engine:&#09; {$configStorageEngine}<br>
    Default Storage Format:&#09; {$configStorageFormat}<br>
    CloudInit Storage:&#09; {$configCloudInitStorage}
</blockquote>

<p>
    <a href="{$modulelink}&action=show" class="btn btn-success">
        <i class="fa fa-check"></i>
        Visit valid action link
    </a>
    <a href="{$modulelink}&action=invalid" class="btn btn-default">
        <i class="fa fa-times"></i>
        Visit invalid action link
    </a>
</p>

EOF;
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

        return <<<EOF

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
