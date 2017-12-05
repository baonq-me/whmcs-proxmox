<?php

namespace WHMCS\Module\Addon\Proxmox\WHMCS_Data;

use WHMCS\Database\Capsule;

class WHMCS_Data
{
  public function __construct() {
  }

  public function checkItem($item, $status)
  {
    $search = Capsule::table('tblinvoiceitems')
            ->where('id', $item)
            ->where('status', $status)
            ->where('notes', 'Managed by Proxmox addon')
            ->select('id')
            ->get();

    if (count($search) != 1)
    {
      logActivity("WHMCS_Data->checkItem(): Invalid invoice item #$item with status $status");
      return false;
    }
    else return true;
  }

  public function moveItemToQueue($item, $ipaddress)
  {
    $result = Capsule::table('tblinvoiceitems')
            ->where('id', $item)
            ->where('status', 'Paid')
            ->where('notes', 'Managed by Proxmox addon')
            ->update(['status' => 'Queued', 'ipaddress' => $ipaddress]);
    logActivity("WHMCS_Data->moveItemToQueue(): Invoice item #$item ($ipaddress) is moved to queue");
  }

  public function createItem()
  {
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
  }
}

?>
