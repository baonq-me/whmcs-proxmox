<?php

namespace WHMCS\Module\Addon\Proxmox\PHPProxmox;

class PHPProxmox
{
  private $home = "../modules/addons/proxmox/pyproxmox/";
  private $vars;

  public function __construct($vars) {
    $this->vars = $vars;

    if ($vars['PyProxmox Module'] != 'auto')
      $this->home = $vars['PyProxmox Module'];
  }

  public function buildConfig()
  {
    $config = <<<EOF
{
   "host": "{$this->vars['PVE Hostname']}",
   "user": "{$this->vars['PVE User']}",
   "password": "{$this->vars['PVE Password']}",
   "storage_bus": "{$this->vars['Default Storage Bus']}",
   "storage_engine": "{$this->vars['Default Storage Engine']}",
   "storage_format": "{$this->vars['Default Storage Format']}",
   "cloudinit_storage": "{$this->vars['CloudInit Storage']}"
}
EOF;

    file_put_contents($this->home."proxmox.conf", $config) === TRUE;
    logActivity("PHPProxmox/Config: ".file_get_contents($this->home."proxmox.conf"));
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
