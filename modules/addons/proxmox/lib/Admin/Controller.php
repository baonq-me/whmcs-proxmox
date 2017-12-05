<?php

namespace WHMCS\Module\Addon\Proxmox\Admin;

use WHMCS\Module\Addon\Proxmox\PHPProxmox\PHPProxmox;
use WHMCS\Module\Addon\Proxmox\WHMCS_Data\WHMCS_Data;

//use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Database\Capsule;

//require_once("../vendor/smarty/smarty/libs/SmartyBC.class.php");

//require_once("../modules/addons/proxmox/functions.php");

/**
 * Sample Admin Area Controller
 */
class Controller {

    public function __construct() {
        $this->whmcs = new WHMCS_Data();
    }

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

        $proxmox = new PHPProxmox($vars);
        $proxmox->buildConfig();


        $status = implode(" ", $proxmox->getSystemStatus());
        $pveConnection = intval(strpos($status, 'Could not connect to server'));

        if ($pveConnection > 0)
        {
          preg_match_all('/pve cpu thread (\d+) loadavg ([\.0-9]+) ([\.0-9]+) ([\.0-9]+)/', $status, $cpu);
          // grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage "%"}'
          $smarty->assign('cpu_percent', round(100 * $cpu[2][0] / $cpu[1][0], 2));
          $smarty->assign('cpu_load1', $cpu[2][0]);
          $smarty->assign('cpu_load2', $cpu[3][0]);
          $smarty->assign('cpu_load3', $cpu[4][0]);


          preg_match_all('/pve mem ram ([\.0-9]+) \w+ ([\.0-9]+) \w+ ([\.0-9]+)%/', $status, $mem);
          $smarty->assign('mem_percent', $mem[3][0]);
          $smarty->assign('mem_used', $mem[1][0]);
          $smarty->assign('mem_total', $mem[2][0]);

          preg_match_all("/pve storage {$vars['Default Storage Engine']} ([\.0-9]+) \w+ ([\.0-9]+) \w+ ([\.0-9]+)%/", $status, $storage);
          $smarty->assign('storage_percent', $storage[3][0]);
          $smarty->assign('storage_used', $storage[1][0]);
          $smarty->assign('storage_total', $storage[2][0]);
          $smarty->assign('storage_engine', $vars['Default Storage Engine']);
        }

        unset($vars['_lang']);
        unset($vars['smarty']);
        unset($vars['smartybc']);
        unset($vars['PVE Password']);
        ksort($vars);

        $smarty->assign('configPVE', $vars);

        $paiditems_query = Capsule::table('tblinvoiceitems')
                                ->where('tblinvoiceitems.status', 'Paid')
                                ->where('tblinvoiceitems.notes', 'Managed by Proxmox addon')
                                ->join('tblclients', 'tblclients.id', '=', 'tblinvoiceitems.userid')
                                ->select(Capsule::raw('CONCAT(tblclients.firstname, \' \', tblclients.lastname) as username, tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.userid, tblinvoiceitems.type, tblinvoiceitems.description, DATE_FORMAT(`tblinvoiceitems`.`updated_at`, \'%b %d, %Y %k:%i:%s\') as updated_at, tblinvoiceitems.status'))
                                ->get();
        // Convert object to associate array
        $paiditems = json_decode(json_encode($paiditems_query), TRUE);
        $smarty->assign('paiditems', $paiditems);


        $queueditems_query = Capsule::table('tblinvoiceitems')
                                ->where('tblinvoiceitems.status', 'Queued')
                                ->orWhere('tblinvoiceitems.status', 'Creating')
                                ->where('tblinvoiceitems.notes', 'Managed by Proxmox addon')
                                ->join('tblclients', 'tblclients.id', '=', 'tblinvoiceitems.userid')
                                ->select(Capsule::raw('CONCAT(tblclients.firstname, \' \', tblclients.lastname) as username, tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.userid, tblinvoiceitems.type, tblinvoiceitems.description, DATE_FORMAT(`tblinvoiceitems`.`updated_at`, \'%b %d, %Y %k:%i:%s\') as updated_at, tblinvoiceitems.status'))
                                ->get();
        $queueditems = json_decode(json_encode($queueditems_query), TRUE);
        $smarty->assign('queueditems', $queueditems);

        $createditems_query = Capsule::table('tblinvoiceitems')
                                ->orWhere('tblinvoiceitems.status', 'Created')
                                ->where('tblinvoiceitems.notes', 'Managed by Proxmox addon')
                                ->join('tblclients', 'tblclients.id', '=', 'tblinvoiceitems.userid')
                                ->select(Capsule::raw('CONCAT(tblclients.firstname, \' \', tblclients.lastname) as username, tblinvoiceitems.id, tblinvoiceitems.invoiceid, tblinvoiceitems.userid, tblinvoiceitems.type, tblinvoiceitems.description, DATE_FORMAT(`tblinvoiceitems`.`updated_at`, \'%b %d, %Y %k:%i:%s\') as updated_at, tblinvoiceitems.status'))
                                ->get();
        // Convert object to associate array
        $createditems = json_decode(json_encode($createditems_query), TRUE);
        $smarty->assign('createditems', $createditems);

        $smarty->display(dirname(__FILE__) . '/../../templates/admin/index.tpl');

        return;
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

    public function test($vars)
    {
      $smarty = $vars['smarty'];
      $smarty->display(dirname(__FILE__) . '/../../templates/admin/test.tpl');
    }

    public function create($vars)
    {
      $id = intval($_GET['id']);
      $ipaddress = $_GET['ipaddress'];

      if (!$this->whmcs->checkItem($id, 'Paid'))
      {
        //echo json_encode(['status' => 'fail', 'reason' => 'Invalid invoice item']);
        http_response_code(404);
      } else {
        $this->whmcs->moveItemToQueue($id, $ipaddress);
        echo json_encode(['status' => 'success']);
      }

      die();
    }
}
