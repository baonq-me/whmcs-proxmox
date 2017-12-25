<?php

namespace WHMCS\Module\Addon\Proxmox\WHMCS_Data;

use WHMCS\Database\Capsule;

class WHMCS_Data
{
  public function __construct() {
  }

  public function getCustomField($itemid, $fieldname)
  {
    $PDO = Capsule::connection()->getPdo();

    $sql = $PDO->prepare("CALL `getCustomField` (:itemid, :fieldname)");

    $sql->bindParam(':itemid', $itemid);
    $sql->bindParam(':fieldname', $fieldname);
    $sql->execute();

    return $sql->fetch($PDO::FETCH_ASSOC);
  }

  public function updateCustomField($itemid, $fieldname, $value)
  {
    $sql = Capsule::connection()->getPdo()->prepare("CALL `updateCustomField` (:itemid, :fieldname, :value)");

    $sql->bindParam(':itemid', $itemid);
    $sql->bindParam(':fieldname', $fieldname);
    $sql->bindParam(':value', $value);

    $sql->execute();

    if ($sql->rowCount() > 0)
      logActivity("[Proxmox] WHMCS_Data->updateCustomField(): '$fieldname' of item #$itemid is changed to '$value'");

    return $sql->rowCount();
  }

  public function getItemStatus($itemid)
  {
    $PDO = Capsule::connection()->getPdo();

    $sql = $PDO->prepare("CALL `getItemStatus` (:itemid)");

    $sql->bindParam(':itemid', $itemid);
    $sql->execute();

    return $sql->fetch($PDO::FETCH_ASSOC);
  }


  public function updateItemStatus($itemid, $status)
  {
    $sql = Capsule::connection()->getPdo()->prepare("CALL `updateItemStatus` (:itemid, :status)");

    $sql->bindParam(':itemid', $itemid);
    $sql->bindParam(':status', $status);

    $sql->execute();

    if ($sql->rowCount() > 0)
      logActivity("[Proxmox] WHMCS_Data->updateItemStatus(): Status of item #$item is changed to '$status'");

    return $sql->rowCount();

  }

  public function getItemsByStatus($status, $amount)
  {
    $PDO = Capsule::connection()->getPdo();

    $sql = $PDO->prepare("CALL `getItemsByStatus` (:status, :amount)");

    $sql->bindParam(':status', $status);
    $sql->bindParam(':amount', $amount);
    $sql->execute();

    return $sql->fetch($PDO::FETCH_ASSOC);
  }


  public function createOneInvoiceItem()
  {
    $item = Capsule::table('tblinvoiceitems')
            ->where('status', 'Queued')
            ->where('notes', 'Managed by Proxmox addon')
            ->first();

    Capsule::table('tblinvoiceitems')
            ->where('id', $item->id)
            ->update(array('status' => 'Creating'));

    {
      sleep(20);
      $status = true;
    }

    if ($status)
    {
      logActivity("[Proxmox] Invoice item #{$item->id} is created");
      Capsule::table('tblinvoiceitems')
              ->where('id', $item->id)
              ->update(array('status' => 'Created'));
    } else {
      logActivity("[Proxmox] Fail to create invoice item #{$item->id}.");
      Capsule::table('tblinvoiceitems')
              ->where('id', $item->id)
              ->update(array('status' => 'Fail'));    }

  }
}

?>
