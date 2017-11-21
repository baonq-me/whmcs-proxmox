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

add_hook('ClientEdit', 1, function(array $params) {
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
});

// Ref: https://github.com/dylanhansch/whmcs-order-management
add_hook('InvoicePaid', 1, function($vars) {
    $paidInvoiceID = $vars['invoiceid'];
    logActivity("[Addon] Proxmox: Invoice #$paidInvoiceID is paid");

    /*
     * Accept paid orders
     */

    $command = 'GetOrders';
    $values = array(
        'status' => 'Pending',
        'limitnum' => '100',
    );
    $invoiceid = $results['orders']['order'][0]['invoiceid'];

    $results = localAPI($command, $values);

    if ($results['result'] == 'success') {
        $numReturned = $results['numreturned'] - 1;
        for ($i = 0; $i <= $numReturned; $i++) {
            $invoiceID = $results['orders']['order'][$i]['invoiceid'];
            if ($invoiceID == $paidInvoiceID) {
                $orderID = $results['orders']['order'][$i]['id'];

                $command = 'AcceptOrder';
                $values = array(
                    'orderid' => $orderID,
                );

                $acceptOrderResults = localAPI($command, $values);

                if ($acceptOrderResults['result'] != 'success') {
                    logActivity("An error occured accepting order $invoiceID: " . $acceptOrderResults['result']);
                } else
                {
                  $command = 'GetInvoice';
                  $values = array(
                      'invoiceid' => $paidInvoiceID,
                  );
                  $paidInvoice = localAPI($command, $values);
                  foreach ($paidInvoice['items']['item'] as $invoice)
                  {
                      logActivity("[Addon] Proxmox: Invoice ID#$paidInvoiceID -> Item #{$invoice['id']} (relid = {$invoice['relid']}) -> Description: {$invoice['description']}");
                      $vmConfigItems = Capsule::table('tblcustomfieldsvalues')->join('tblcustomfields', 'tblcustomfieldsvalues.fieldid', '=', 'tblcustomfields.id')->where('tblcustomfieldsvalues.relid', '=', $invoice['relid'])->select('fieldname', 'value')->get();
                      logActivity("[Addon] Proxmox: ".json_encode($vmConfigItems));

                      $pyProxmoxConfig = [];
                      foreach ($vmConfigItems as $config)
                      {
                          logActivity("[Addon] Proxmox: Invoice ID#$paidInvoiceID -> Item #{$invoice['id']} (relid = {$invoice['relid']}) -> {$config->fieldname} => {$config->value}");
                          $pyProxmoxConfig[$config->fieldname] = $config->value;
                      }
                      $cmd = "cd ../modules/addons/proxmox/pyproxmox; ./proxmox.py -c --node pve --cpu {$pyProxmoxConfig['CPU']} --mem {$pyProxmoxConfig['Memory']} --storage {$pyProxmoxConfig['Hard disk']} --hostname {$pyProxmoxConfig['Hostname']} --debug > pyproxmox.log";
                      logActivity("[Addon] Proxmox: $cmd");
                      //exec($cmd);
                      logActivity(file_get_contents("/var/www/html/whmcs/modules/addons/proxmox/pyproxmox/pyproxmox.log"));

                  }
                }
            }
        }
        return true;
    } else {
        logActivity("An error occured with getting orders: " . $results['result']);
        return false;
    }
});


add_hook('AfterCronJob', 1, function($vars) {
    // $date = exec("date");
    // file_put_contents(__DIR__."/date.txt", $date);

    // $items = Capsule::table("tblinvoiceitems")->whereRaw("TIMESTAMPDIFF(MINUTE, `updated_on` , NOW()) > 5")->get();

    Capsule::table('tblinvoiceitems')
            ->where('status', 'Paid')
            ->where('notes', 'Managed by Proxmox addon')
            ->whereRaw('TIME(`updated_on`) + TIME(\'00:05\') <= TIME(NOW())')
            ->update(array('status' => 'Queued'));

    $item = Capsule::table('tblinvoiceitems')
            ->where('status', 'Queued')
            ->where('notes', 'Managed by Proxmox addon')
            ->first();

    Capsule::table('tblinvoiceitems')
            ->where('id', $item->id)
            ->update(array('status' => 'Creating'));

    sleep(20);

    Capsule::table('tblinvoiceitems')
            ->where('id', $item->id)
            ->update(array('status' => 'Created'));

    //logActivity('Hook on update item: '.json_encode($item));
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
