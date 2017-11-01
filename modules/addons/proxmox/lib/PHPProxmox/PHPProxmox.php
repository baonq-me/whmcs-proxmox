<?php

namespace WHMCS\Module\Addon\Proxmox\PHPProxmox;

class PHPProxmox
{
  private $home = __DIR__."/../../pyproxmox/";
  private $vars;

  public function __construct($vars) {
    $this->vars = $vars;      // absolute path to home folder
    logActivity('PHPProxmox: '.json_encode($vars));
    return true;
  }

  public function buildConfig($vars)
  {
    $config = <<<EOF
{
   "host": "{$vars['PVE Hostname']}",
   "user": "{$vars['PVE User']}",
   "password": "{$vars['PVE Password']}",
   "storage_bus": "{$vars['Default Storage Bus']}",
   "storage_engine": "{$vars['Default Storage Engine']}",
   "storage_format": "{$vars['Default Storage Format']}",
   "cloudinit_storage": "{$vars['CloudInit Storage']}"
}
EOF;

    return file_put_contents($this->home."proxmox.conf", $config) !== FALSE;
  }

  public function getSystemStatus()
  {
    $output;
    exec('cd '.$this->home.'; ./proxmox.py -l -d | sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[mGK]//g"', $output);
    return $output;
  }

  public function cloneVM($productID)
  {
    $info = array(
      'VM IP'                 => '',
      'VM Hostname'           => 'vm.baonq.me',
      'ProxmoxCluster'        => 'pve.baonq.me',
      'VM Root Password'      => 'password',
      'VM OS'                 => 'Ubuntu 16.04',
      'VM Disk'               => 'HDD 20GB',
      'VM Memory'             => '2GB'
    );

    return $info;
  }

  public function getConfig($config)
  {
    return $this->vars[$config];
  }


}


?>
