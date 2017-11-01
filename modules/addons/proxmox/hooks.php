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
                  //$results = localAPI('GetInvoice', array('invoiceid' => $invoiceID), 'admin');
                  //file_put_contents("abc.txt", "");
                  //file_put_contents("abc.txt", "GetInvoice ".json_encode($results)."\n\n", FILE_APPEND);
                  //file_put_contents("abc.txt", "InvoiceID ".$invoiceID, FILE_APPEND);
                }
            }
        }



    } else {
        logActivity("An error occured with getting orders: " . $results['result']);
    }
});
