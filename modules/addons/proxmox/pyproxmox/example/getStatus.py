#!/usr/bin/python2.7

import warnings

execfile('../pyproxmox.py')

if __name__ == '__main__':
	# Disable warning for SSL verification
	warnings.filterwarnings('ignore')

	proxmox = pyproxmox(prox_auth("pve.example.com", "root@pam", "mypassword"))
	print json.dumps(proxmox.getClusterStatus())

