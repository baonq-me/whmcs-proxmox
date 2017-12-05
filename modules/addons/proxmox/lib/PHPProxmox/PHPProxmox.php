<?php

namespace WHMCS\Module\Addon\Proxmox\PHPProxmox;

class PHPProxmox
{
  private $home = "../modules/addons/proxmox/pyproxmox/";
  private $vars;

  public function __construct($vars) {
    $this->vars = $vars;
  }

  public function buildConfig()
  {
    $config = <<<EOF
[server]
; PVE server
host = {$this->vars['PVE Hostname']}

; User (must include realm)
user = {$this->vars['PVE User']}

; Password
password = {$this->vars['PVE Password']}

[storage]
; Default storage bus: ide, sata, scsi, virtio
bus = {$this->vars['Default Storage Bus']}

; Default storage engine
engine = {$this->vars['Default Storage Engine']}

; Default storage format: raw, qcow2, vmdk
format = {$this->vars['Default Storage Format']}

; Size of root partition of template VM
root = 3 GiB

; Storage engine that will store cloudinit datasources (ISO image)
cloudinit = {$this->vars['CloudInit Storage']}

[qemu]
; Emulated CPU type. Use 'host' to maximize VM performance. Use 'kvm64' for compability.
cpu_type = host

[template]
; Template VM id to be cloned
vmid = 104
EOF;
    $result = file_put_contents($this->home.'proxmox.cfg', $config) == TRUE ? 'Success': 'Failed to write !';
    logActivity('PHPProxmox/Config: '.$result);
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
