pyproxmox
=========

# A Python wrapper for the Proxmox 2.x API

This readme is edited by me.
- Improve documents
- Fix crash when failed to connect to proxmox server
- Add example code

## 1. Installation and dependencies

1. Install dependencies

```
	sudo apt-get install python2.7 python-pip genisoimage configparser
	sudo pip install --upgrade pip
	sudo wget https://raw.githubusercontent.com/ilovecode1/Pyfancy-2/master/pyfancy/pyfancy.py -P /usr/lib/python2.7/
	sudo pip install requests humanfriendly lazyme
```

## 2. Example usage

### 2.1. Calling pyproxmox

1. Import everything from the module

```
	from pyproxmox import *
```

2. Create an instance of the prox_auth class by passing in the url or ip of a server in the cluster, username and password

```
	connect = prox_auth('mypve.baonq.me', 'root@pam', '123456')`
```

In case of failed connection, `connect.status` will be `False`. Error message can be extracted via `connect.error`

```
	if connect.status is False:
		print 'Error when connect to mypve.baonq.me as root@pam' + ': ' + connect.error
		sys.exit(1)
```

3. Create and instance of the pyproxmox class using the auth object as a parameter

```
	proxmox = pyproxmox(connect)`
```

4. Run the pre-defined methods of the pyproxmox class

```
	status = proxmox.getClusterStatus()
	print json.dumps(status)
```

NOTE They all return data, always in JSON format. For more information see https//github.com/Daemonthread/pyproxmox

Links:
- [Proxmox VE API introduction](https://pve.proxmox.com/wiki/Proxmox_VE_API)
- [Proxmox VE API description](https://pve.proxmox.com/pve-docs/api-viewer/index.html)

### 2.2. Methods requiring post_data

These methods need to passed a correctly formatted dictionary. For example, if I was to use the createOpenvzContainer for the above example node. I would need to pass the post_data with all the required variables for proxmox.

```
	post_data = {'ostemplate':'local:vztmpl/debian-6.0-standard_6.0-4_amd64.tar.gz',
				'vmid':'9001','cpus':'4','description':'test container',
				'disk':'10','hostname':'test.example.org','memory':'1024',
				'password':'testPassword','swap':'1024'}
	
	proxmox.createOpenvzContainer('pve',post_data)
```

For more information on the accepted variables please see https://pve.proxmox.com/pve-docs/api-viewer/index.html

## 3. Current List of Methods

### 3.1. GET Methods

#### 3.1.1. Cluster Methods

##### A. Get cluster status information. Returns JSON

Syntax: `getClusterStatus()`

Example: `print json.dumps(getClusterStatus())`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"ip": "192.168.0.40", "name": "pve", "level": "", "type": "node", "nodeid": 0, "online": 1, "local": 1, "id": "node/pve"}]}
```

##### B. List vzdump backup schedule. Returns JSON

Syntax: `getClusterBackupSchedule()`

Example: `print json.dumps(getClusterBackupSchedule())`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": []}
```

##### C. Get next VM ID (ID that is free and ready to assign to VM) of cluster. Returns JSON

Syntax: `getClusterVmNextId()`	

Example: `print json.dumps(getClusterVmNextId())`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": "105"}
```

#### 3.1.2. Node Methods

##### A. List available networks. Returns JSON

Syntax: `getNodeNetworks(node)`

Example: `print json.dumps(getNodeNetworks('pve'))`
		
Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"method6": "manual", "iface": "vmbr0", "families": ["inet"], "bridge_fd": "0", "method": "static", "priority": 5, "netmask": "255.255.255.0", "bridge_stp": "off", "bridge_ports": "enp1s0f0", "address": "192.168.0.40", "active": 1, "autostart": 1, "type": "bridge", "gateway": "192.168.0.1"}, {"method6": "manual", "iface": "enp1s0f1", "families": ["inet"], "exists": 1, "priority": 4, "type": "eth", "method": "manual"}, {"method6": "manual", "iface": "enp1s0f0", "families": ["inet"], "exists": 1, "priority": 3, "active": 1, "type": "eth", "method": "manual"}]}
```

##### B. Read network device configuration. Returns JSON

Syntax: `getNodeInterface(node,interface)`
		
Example: 

```
print json.dumps(getNodeInterface('pve', 'vmbr0'))
print json.dumps(getNodeInterface('pve', 'enp1s0f0'))
```

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"method6": "manual", "bridge_stp": "off", "families": ["inet"], "bridge_fd": "0", "method": "static", "priority": 5, "netmask": "255.255.255.0", "bridge_ports": "enp1s0f0", "address": "192.168.0.40", "active": 1, "autostart": 1, "type": "bridge", "gateway": "192.168.0.1"}}
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"method6": "manual", "families": ["inet"], "exists": 1, "priority": 4, "type": "eth", "method": "manual"}}
```

##### C. Virtual machine index (per node). Returns JSON

Syntax: `getNodeContainerIndex(node)`

Example: `print json.dumps(getNodeInterface('pve'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz' not implemented", "code": 501, "ok": false}, "data": null}
```

##### D. Service list. Returns JSON
 
 Syntax: `getNodeVirtualIndex(node)`
 
 Example: `print json.dumps(getNodeVirtualIndex('pve'))`
 
 Output:
 
 ```
 {"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"status": "stopped", "uptime": 0, "name": "win10.baonq.me", "diskread": 0, "mem": 0, "pid": null, "vmid": 101, "netin": 0, "cpus": 4, "diskwrite": 0, "template": "", "netout": 0, "disk": 0, "cpu": 0, "maxdisk": 64424509440, "maxmem": 4294967296}, {"status": "stopped", "uptime": 0, "name": "devstack.baonq.me", "diskread": 0, "mem": 0, "pid": null, "vmid": 104, "cpus": 2, "netin": 0, "diskwrite": 0, "template": "", "netout": 0, "disk": 0, "cpu": 0, "maxdisk": 32212254720, "maxmem": 1073741824}, {"status": "stopped", "uptime": 0, "name": "win7.baonq.me", "diskread": 0, "mem": 0, "pid": null, "vmid": 102, "cpus": 2, "netin": 0, "diskwrite": 0, "template": "", "netout": 0, "disk": 0, "cpu": 0, "maxdisk": 64424509440, "maxmem": 2147483648}, {"status": "stopped", "uptime": 0, "name": "desktop.ubuntu.baonq.me", "diskread": 0, "mem": 0, "pid": null, "vmid": 103, "netin": 0, "cpus": 2, "diskwrite": 0, "template": "", "netout": 0, "disk": 0, "cpu": 0, "maxdisk": 42949672960, "maxmem": 2147483648}, {"status": "stopped", "uptime": 0, "name": "template.ubuntu.baonq.me", "diskread": 0, "mem": 0, "pid": null, "vmid": 100, "netin": 0, "cpus": 2, "diskwrite": 0, "template": "", "netout": 0, "disk": 0, "cpu": 0, "maxdisk": 32212254720, "maxmem": 1073741824}]}
```

##### E. Read service properties

Syntax: `getNodeServiceList(node)`
	
Example: `print json.dumps(getNodeVirtualIndex('pve'))`
		
Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"state": "running", "name": "pve-ha-lrm", "service": "pve-ha-lrm", "desc": "PVE Local HA Ressource Manager Daemon"}, {"state": "running", "name": "spiceproxy", "service": "spiceproxy", "desc": "PVE SPICE Proxy Server"}, {"state": "running", "name": "syslog", "service": "syslog", "desc": "System Logging Service"}, {"state": "running", "name": "systemd-timesyncd", "service": "systemd-timesyncd", "desc": "Network Time Synchronization"}, {"state": "running", "name": "ksmtuned", "service": "ksmtuned", "desc": "Kernel Samepage Merging (KSM) Tuning Daemon"}, {"state": "running", "name": "sshd", "service": "sshd", "desc": "OpenBSD Secure Shell server"}, {"state": "running", "name": "pve-ha-crm", "service": "pve-ha-crm", "desc": "PVE Cluster Ressource Manager Daemon"}, {"state": "running", "name": "cron", "service": "cron", "desc": "Regular background program processing daemon"}, {"state": "running", "name": "pve-firewall", "service": "pve-firewall", "desc": "Proxmox VE firewall"}, {"state": "running", "name": "pveproxy", "service": "pveproxy", "desc": "PVE API Proxy Server"}, {"state": "dead", "name": "corosync", "service": "corosync", "desc": "Corosync Cluster Engine"}, {"state": "running", "name": "pve-cluster", "service": "pve-cluster", "desc": "The Proxmox VE cluster filesystem"}, {"state": "running", "name": "pvestatd", "service": "pvestatd", "desc": "PVE Status Daemon"}, {"state": "running", "name": "pvefw-logger", "service": "pvefw-logger", "desc": "Proxmox VE firewall logger"}, {"state": "running", "name": "postfix", "service": "postfix", "desc": "Postfix Mail Transport Agent (instance -)"}, {"state": "running", "name": "pvedaemon", "service": "pvedaemon", "desc": "PVE API Daemon"}]}
```

##### F. Get status for all datastores. Returns JSON

Syntax: `getNodeServiceState(node,service)`

Example: 

```
print json.dumps(getNodeServiceState('pve', 'sshd'))
print json.dumps(getNodeServiceState('pve', 'ksmtuned'))
```

OUtput:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"state": "running", "name": "sshd", "service": "sshd", "desc": "OpenBSD Secure Shell server"}}
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"state": "running", "name": "ksmtuned", "service": "ksmtuned", "desc": "Kernel Samepage Merging (KSM) Tuning Daemon"}}
```

##### G. Read task list for one node (finished tasks). Returns JSON

Syntax: `getNodeStorage(node)`

Example: `print json.dumps(getNodeStorage('pve'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"avail": 633577086976, "used": 67187564544, "storage": "local-zfs", "content": "rootdir,images", "active": 1, "shared": 0, "total": 700764651520, "type": "zfspool"}, {"avail": 633577013248, "used": 14035320832, "storage": "local", "content": "vztmpl,backup,iso", "active": 1, "shared": 0, "total": 647612334080, "type": "dir"}]}
```

##### H. Read task list for one node (finished tasks). Returns JSON

Syntax: `getNodeFinishedTasks(node)`

Example: `print json.dumps(getNodeFinishedTasks('pve'))`
		
Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "total": 236, "data": [{"status": "OK", "node": "pve", "pstart": 6908736, "pid": 2128, "upid": "UPID:pve:00000850:00696B40:59B736D7:vzcreate:106:root@pam:", "user": "root@pam", "starttime": 1505179351, "endtime": 1505179360, "type": "vzcreate", "id": "106"}, {"status": "OK", "node": "pve", "pstart": 6909147, "upid": "UPID:pve:00000873:00696CDB:59B736DC:vzstart:105:root@pam:", "pid": 2163, "user": "root@pam", "starttime": 1505179356, "endtime": 1505179358, "type": "vzstart", "id": "105"}, {"status": "OK", "node": "pve", "pstart": 6906089, "upid": "UPID:pve:00000796:006960E9:59B736BD:vzcreate:105:root@pam:", "pid": 1942, "user": "root@pam", "starttime": 1505179325, "endtime": 1505179333, "type": "vzcreate", "id": "105"}, {"status": "OK", "node": "pve", "pstart": 6902578, "upid": "UPID:pve:000006B8:00695332:59B7369A:vzdestroy:106:root@pam:", "pid": 1720, "user": "root@pam", "starttime": 1505179290, "endtime": 1505179290, "type": "vzdestroy", "id": "106"}, {"node": "pve", "status": "OK", "pstart": 6901844, "pid": 1655, "upid": "UPID:pve:00000677:00695054:59B73693:vzdestroy:105:root@pam:", "user": "root@pam", "starttime": 1505179283, "endtime": 1505179283, "type": "vzdestroy", "id": "105"}, {"node": "pve", "status": "OK", "pstart": 6897501, "pid": 1036, "upid": "UPID:pve:0000040C:00693F5D:59B73667:vncproxy:105:root@pam:", "user": "root@pam", "starttime": 1505179239, "endtime": 1505179241, "type": "vncproxy", "id": "105"}, {"node": "pve", "status": "OK", "pstart": 6894724, "upid": "UPID:pve:00000362:00693484:59B7364B:vzcreate:106:root@pam:", "pid": 866, "user": "root@pam", "starttime": 1505179211, "endtime": 1505179220, "type": "vzcreate", "id": "106"}, {"node": "pve", "status": "OK", "pstart": 6890826, "upid": "UPID:pve:00000275:0069254A:59B73624:vzcreate:105:root@pam:", "pid": 629, "user": "root@pam", "starttime": 1505179172, "endtime": 1505179183, "type": "vzcreate", "id": "105"}, {"node": "pve", "status": "OK", "pstart": 2431039, "pid": 10190, "upid": "UPID:pve:000027CE:0025183F:59B687EE:qmclone:100:root@pam:", "user": "root@pam", "starttime": 1505134574, "endtime": 1505134673, "type": "qmclone", "id": "100"}, {"node": "pve", "status": "Failed to run vncproxy.", "pstart": 2429818, "upid": "UPID:pve:00002787:0025137A:59B687E2:vncproxy:100:root@pam:", "pid": 10119, "user": "root@pam", "starttime": 1505134562, "endtime": 1505134563, "type": "vncproxy", "id": "100"}, {"status": "Failed to run vncproxy.", "node": "pve", "pstart": 2429567, "pid": 10105, "upid": "UPID:pve:00002779:0025127F:59B687E0:vncproxy:103:root@pam:", "user": "root@pam", "starttime": 1505134560, "endtime": 1505134561, "type": "vncproxy", "id": "103"}, {"status": "Failed to run vncproxy.", "node": "pve", "pstart": 2429363, "pid": 10091, "upid": "UPID:pve:0000276B:002511B3:59B687DE:vncproxy:100:root@pam:", "user": "root@pam", "starttime": 1505134558, "endtime": 1505134559, "type": "vncproxy", "id": "100"}, {"node": "pve", "status": "OK", "pstart": 2346488, "pid": 5491, "upid": "UPID:pve:00001573:0023CDF8:59B684A1:vncproxy:100:root@pam:", "user": "root@pam", "starttime": 1505133729, "endtime": 1505134557, "type": "vncproxy", "id": "100"}, {"status": "OK", "node": "pve", "pstart": 2346119, "pid": 5418, "upid": "UPID:pve:0000152A:0023CC87:59B6849D:qmstart:101:root@pam:", "user": "root@pam", "starttime": 1505133725, "endtime": 1505133727, "type": "qmstart", "id": "101"}, {"status": "Failed to run vncproxy.", "node": "pve", "pstart": 2345683, "upid": "UPID:pve:00001513:0023CAD3:59B68499:vncproxy:101:root@pam:", "pid": 5395, "user": "root@pam", "starttime": 1505133721, "endtime": 1505133722, "type": "vncproxy", "id": "101"}, {"node": "pve", "status": "OK", "pstart": 2345455, "pid": 5379, "upid": "UPID:pve:00001503:0023C9EF:59B68497:vncproxy:100:root@pam:", "user": "root@pam", "starttime": 1505133719, "endtime": 1505133721, "type": "vncproxy", "id": "100"}, {"status": "OK", "node": "pve", "pstart": 1628061, "upid": "UPID:pve:00007504:0018D79D:59B66891:vncproxy:100:root@pam:", "pid": 29956, "user": "root@pam", "starttime": 1505126545, "endtime": 1505126554, "type": "vncproxy", "id": "100"}, {"status": "OK", "node": "pve", "pstart": 1627507, "pid": 29918, "upid": "UPID:pve:000074DE:0018D573:59B6688B:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505126539, "endtime": 1505126541, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 1627346, "pid": 29865, "upid": "UPID:pve:000074A9:0018D4D2:59B6688A:qmstart:100:root@pam:", "user": "root@pam", "starttime": 1505126538, "endtime": 1505126539, "type": "qmstart", "id": "100"}, {"status": "OK", "node": "pve", "pstart": 1624197, "pid": 29692, "upid": "UPID:pve:000073FC:0018C885:59B6686A:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505126506, "endtime": 1505126513, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 1515580, "pid": 23900, "upid": "UPID:pve:00005D5C:0017203C:59B6642C:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505125420, "endtime": 1505126505, "type": "vncproxy", "id": "101"}, {"node": "pve", "status": "connection timed out", "pstart": 1515553, "pid": 23898, "upid": "UPID:pve:00005D5A:00172021:59B6642C:vncproxy:100:root@pam:", "user": "root@pam", "starttime": 1505125420, "endtime": 1505125430, "type": "vncproxy", "id": "100"}, {"status": "OK", "node": "pve", "pstart": 1515643, "upid": "UPID:pve:00005D64:0017207B:59B6642D:qmstart:101:root@pam:", "pid": 23908, "user": "root@pam", "starttime": 1505125421, "endtime": 1505125423, "type": "qmstart", "id": "101"}, {"status": "Failed to run vncproxy.", "node": "pve", "pstart": 1440684, "pid": 19814, "upid": "UPID:pve:00004D66:0015FBAC:59B6613F:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505124671, "endtime": 1505124672, "type": "vncproxy", "id": "101"}, {"node": "pve", "status": "OK", "pstart": 1440347, "pid": 19780, "upid": "UPID:pve:00004D44:0015FA5B:59B6613C:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505124668, "endtime": 1505124671, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 1439421, "upid": "UPID:pve:00004CD3:0015F6BD:59B66132:qmstart:101:root@pam:", "pid": 19667, "user": "root@pam", "starttime": 1505124658, "endtime": 1505124661, "type": "qmstart", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 2568, "pid": 2618, "upid": "UPID:pve:00000A3A:00000A08:59B62912:startall::root@pam:", "user": "root@pam", "starttime": 1505110290, "endtime": 1505110290, "type": "startall", "id": ""}, {"status": "OK", "node": "pve", "pstart": 148190, "pid": 5161, "upid": "UPID:pve:00001429:000242DE:59B58571:stopall::root@pam:", "user": "root@pam", "starttime": 1505068401, "endtime": 1505068401, "type": "stopall", "id": ""}, {"node": "pve", "status": "OK", "pstart": 146157, "upid": "UPID:pve:0000134D:00023AED:59B5855C:srvstart:ksmtuned:root@pam:", "pid": 4941, "user": "root@pam", "starttime": 1505068380, "endtime": 1505068380, "type": "srvstart", "id": "ksmtuned"}, {"status": "OK", "node": "pve", "pstart": 145901, "upid": "UPID:pve:0000131E:000239ED:59B5855A:srvstop:ksmtuned:root@pam:", "pid": 4894, "user": "root@pam", "starttime": 1505068378, "endtime": 1505068378, "type": "srvstop", "id": "ksmtuned"}, {"status": "OK", "node": "pve", "pstart": 143794, "pid": 4644, "upid": "UPID:pve:00001224:000231B2:59B58545:srvstart:corosync:root@pam:", "user": "root@pam", "starttime": 1505068357, "endtime": 1505068357, "type": "srvstart", "id": "corosync"}, {"node": "pve", "status": "OK", "pstart": 133040, "pid": 4419, "upid": "UPID:pve:00001143:000207B0:59B584D9:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505068249, "endtime": 1505068281, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 130260, "upid": "UPID:pve:0000112E:0001FCD4:59B584BD:vncproxy:101:root@pam:", "pid": 4398, "user": "root@pam", "starttime": 1505068221, "endtime": 1505068244, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 129617, "pid": 4385, "upid": "UPID:pve:00001121:0001FA51:59B584B7:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505068215, "endtime": 1505068218, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 129179, "pid": 4380, "upid": "UPID:pve:0000111C:0001F89B:59B584B2:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505068210, "endtime": 1505068213, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 128732, "pid": 4326, "upid": "UPID:pve:000010E6:0001F6DC:59B584AE:qmstart:101:root@pam:", "user": "root@pam", "starttime": 1505068206, "endtime": 1505068208, "type": "qmstart", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 124967, "upid": "UPID:pve:000010A7:0001E827:59B58488:vncproxy:100:root@pam:", "pid": 4263, "user": "root@pam", "starttime": 1505068168, "endtime": 1505068194, "type": "vncproxy", "id": "100"}, {"node": "pve", "status": "OK", "pstart": 124085, "upid": "UPID:pve:0000106D:0001E4B5:59B58480:qmstart:101:root@pam:", "pid": 4205, "user": "root@pam", "starttime": 1505068160, "endtime": 1505068161, "type": "qmstart", "id": "101"}, {"node": "pve", "status": "OK", "pstart": 122987, "pid": 4136, "upid": "UPID:pve:00001028:0001E06B:59B58475:qmstart:100:root@pam:", "user": "root@pam", "starttime": 1505068149, "endtime": 1505068150, "type": "qmstart", "id": "100"}, {"status": "MAX 16 vcpus allowed per VM on this node", "node": "pve", "pstart": 121367, "upid": "UPID:pve:00001013:0001DA17:59B58464:qmstart:100:root@pam:", "pid": 4115, "user": "root@pam", "starttime": 1505068132, "endtime": 1505068132, "type": "qmstart", "id": "100"}, {"node": "pve", "status": "OK", "pstart": 116398, "pid": 4046, "upid": "UPID:pve:00000FCE:0001C6AE:59B58433:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505068083, "endtime": 1505068093, "type": "vncproxy", "id": "101"}, {"node": "pve", "status": "OK", "pstart": 115754, "upid": "UPID:pve:00000FBE:0001C42A:59B5842C:vncproxy:102:root@pam:", "pid": 4030, "user": "root@pam", "starttime": 1505068076, "endtime": 1505068083, "type": "vncproxy", "id": "102"}, {"node": "pve", "status": "OK", "pstart": 113060, "upid": "UPID:pve:00000F99:0001B9A4:59B58411:vncproxy:102:root@pam:", "pid": 3993, "user": "root@pam", "starttime": 1505068049, "endtime": 1505068074, "type": "vncproxy", "id": "102"}, {"status": "OK", "node": "pve", "pstart": 112748, "pid": 3990, "upid": "UPID:pve:00000F96:0001B86C:59B5840E:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505068046, "endtime": 1505068049, "type": "vncproxy", "id": "101"}, {"node": "pve", "status": "OK", "pstart": 110460, "pid": 3968, "upid": "UPID:pve:00000F80:0001AF7C:59B583F7:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505068023, "endtime": 1505068028, "type": "vncproxy", "id": "101"}, {"status": "OK", "node": "pve", "pstart": 109980, "pid": 3910, "upid": "UPID:pve:00000F46:0001AD9C:59B583F2:qmstart:102:root@pam:", "user": "root@pam", "starttime": 1505068018, "endtime": 1505068019, "type": "qmstart", "id": "102"}, {"node": "pve", "status": "OK", "pstart": 102156, "upid": "UPID:pve:00000EE5:00018F0C:59B583A4:vncproxy:102:root@pam:", "pid": 3813, "user": "root@pam", "starttime": 1505067940, "endtime": 1505067987, "type": "vncproxy", "id": "102"}, {"status": "OK", "node": "pve", "pstart": 101105, "pid": 3792, "upid": "UPID:pve:00000ED0:00018AF1:59B5839A:vncproxy:102:root@pam:", "user": "root@pam", "starttime": 1505067930, "endtime": 1505067937, "type": "vncproxy", "id": "102"}, {"status": "OK", "node": "pve", "pstart": 99632, "upid": "UPID:pve:00000EC3:00018530:59B5838B:vncproxy:101:root@pam:", "pid": 3779, "user": "root@pam", "starttime": 1505067915, "endtime": 1505067930, "type": "vncproxy", "id": "101"}, {"node": "pve", "status": "OK", "pstart": 98216, "pid": 3765, "upid": "UPID:pve:00000EB5:00017FA8:59B5837D:vncproxy:101:root@pam:", "user": "root@pam", "starttime": 1505067901, "endtime": 1505067913, "type": "vncproxy", "id": "101"}]}
```

##### I. Read DNS settings. Returns JSON

Syntax: `getNodeDNS(node)`

Example: `print json.dumps(getNodeDNS('pve'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"dns3": "192.168.0.1", "dns2": "8.8.4.4", "dns1": "8.8.8.8", "search": "baonq.me"}}
```

##### J. Read node status. Returns JSON

Syntax: `getNodeStatus(node)`

Example: `print json.dumps(getNodeStatus('pve'))`
		
Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"cpuinfo": {"hvm": 1, "cpus": 16, "mhz": "2395.000", "user_hz": 100, "model": "Intel(R) Xeon(R) CPU           E5620  @ 2.40GHz", "sockets": 2}, "uptime": 69349, "memory": {"total": 25215062016, "used": 3211583488, "free": 22003478528}, "kversion": "Linux 4.10.15-1-pve #1 SMP PVE 4.10.15-15 (Fri, 23 Jun 2017 08:57:55 +0200)", "idle": 0, "loadavg": ["0.16", "0.21", "0.17"], "swap": {"total": 8589930496, "used": 0, "free": 8589930496}, "pveversion": "pve-manager/5.0-23/af4267bf", "ksm": {"shared": 0}, "wait": 0, "cpu": 0, "rootfs": {"avail": 633577013248, "total": 647612334080, "used": 14035320832, "free": 619541692416}}}
```

##### K. Read system log. Returns JSON

Syntax: `getNodeSyslog(node)`

Example: `print json.dumps(getNodeSyslog('pve'))`
		
Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "total": 4494, "data": [{"t": "-- Logs begin at Mon 2017-09-11 13:11:14 +07, end at Tue 2017-09-12 08:27:18 +07. --", "n": 1}, {"t": "Sep 11 13:11:14 pve kernel: Linux version 4.10.15-1-pve (root@stretchbuild) (gcc version 6.3.0 20170516 (Debian 6.3.0-18) ) #1 SMP PVE 4.10.15-15 (Fri, 23 Jun 2017 08:57:55 +0200) ()", "n": 2}, {"t": "Sep 11 13:11:14 pve kernel: Command line: BOOT_IMAGE=/ROOT/pve-1@/boot/vmlinuz-4.10.15-1-pve root=ZFS=rpool/ROOT/pve-1 ro root=ZFS=rpool/ROOT/pve-1 boot=zfs quiet", "n": 3}, {"t": "Sep 11 13:11:14 pve kernel: KERNEL supported cpus:", "n": 4}, {"t": "Sep 11 13:11:14 pve kernel:   Intel GenuineIntel", "n": 5}, {"t": "Sep 11 13:11:14 pve kernel:   AMD AuthenticAMD", "n": 6}, {"t": "Sep 11 13:11:14 pve kernel:   Centaur CentaurHauls", "n": 7}, {"t": "Sep 11 13:11:14 pve kernel: x86/fpu: Legacy x87 FPU detected.", "n": 8}, {"t": "Sep 11 13:11:14 pve kernel: e820: BIOS-provided physical RAM map:", "n": 9}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x0000000000000000-0x000000000009a3ff] usable", "n": 10}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000000009a400-0x000000000009ffff] reserved", "n": 11}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x00000000000e0000-0x00000000000fffff] reserved", "n": 12}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x0000000000100000-0x000000008c345fff] usable", "n": 13}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008c346000-0x000000008c422fff] ACPI NVS", "n": 14}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008c423000-0x000000008c4fcfff] ACPI data", "n": 15}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008c4fd000-0x000000008d8fcfff] ACPI NVS", "n": 16}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008d8fd000-0x000000008f601fff] ACPI data", "n": 17}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f602000-0x000000008f64efff] reserved", "n": 18}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f64f000-0x000000008f6e3fff] ACPI data", "n": 19}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f6e4000-0x000000008f6edfff] ACPI NVS", "n": 20}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f6ee000-0x000000008f6f0fff] ACPI data", "n": 21}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f6f1000-0x000000008f7cefff] ACPI NVS", "n": 22}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f7cf000-0x000000008f7fffff] ACPI data", "n": 23}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x000000008f800000-0x000000008fffffff] reserved", "n": 24}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x00000000a0000000-0x00000000afffffff] reserved", "n": 25}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x00000000fc000000-0x00000000fcffffff] reserved", "n": 26}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x00000000fed1c000-0x00000000fed44fff] reserved", "n": 27}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x00000000ff800000-0x00000000ffffffff] reserved", "n": 28}, {"t": "Sep 11 13:11:14 pve kernel: BIOS-e820: [mem 0x0000000100000000-0x000000066fffffff] usable", "n": 29}, {"t": "Sep 11 13:11:14 pve kernel: NX (Execute Disable) protection: active", "n": 30}, {"t": "Sep 11 13:11:14 pve kernel: SMBIOS 2.5 present.", "n": 31}, {"t": "Sep 11 13:11:14 pve kernel: DMI: Intel Corporation S5520UR/S5520UR, BIOS S5500.86B.01.00.0060.090920111354 09/09/2011", "n": 32}, {"t": "Sep 11 13:11:14 pve kernel: e820: update [mem 0x00000000-0x00000fff] usable ==> reserved", "n": 33}, {"t": "Sep 11 13:11:14 pve kernel: e820: remove [mem 0x000a0000-0x000fffff] usable", "n": 34}, {"t": "Sep 11 13:11:14 pve kernel: e820: last_pfn = 0x670000 max_arch_pfn = 0x400000000", "n": 35}, {"t": "Sep 11 13:11:14 pve kernel: MTRR default type: uncachable", "n": 36}, {"t": "Sep 11 13:11:14 pve kernel: MTRR fixed ranges enabled:", "n": 37}, {"t": "Sep 11 13:11:14 pve kernel:   00000-9FFFF write-back", "n": 38}, {"t": "Sep 11 13:11:14 pve kernel:   A0000-BFFFF uncachable", "n": 39}, {"t": "Sep 11 13:11:14 pve kernel:   C0000-DFFFF write-through", "n": 40}, {"t": "Sep 11 13:11:14 pve kernel:   E0000-FFFFF write-protect", "n": 41}, {"t": "Sep 11 13:11:14 pve kernel: MTRR variable ranges enabled:", "n": 42}, {"t": "Sep 11 13:11:14 pve kernel:   0 base 0000000000 mask FF80000000 write-back", "n": 43}, {"t": "Sep 11 13:11:14 pve kernel:   1 base 0080000000 mask FFF0000000 write-back", "n": 44}, {"t": "Sep 11 13:11:14 pve kernel:   2 base 0100000000 mask FF00000000 write-back", "n": 45}, {"t": "Sep 11 13:11:14 pve kernel:   3 base 0200000000 mask FE00000000 write-back", "n": 46}, {"t": "Sep 11 13:11:14 pve kernel:   4 base 0400000000 mask FE00000000 write-back", "n": 47}, {"t": "Sep 11 13:11:14 pve kernel:   5 base 0600000000 mask FFC0000000 write-back", "n": 48}, {"t": "Sep 11 13:11:14 pve kernel:   6 base 0640000000 mask FFE0000000 write-back", "n": 49}, {"t": "Sep 11 13:11:14 pve kernel:   7 base 0660000000 mask FFF0000000 write-back", "n": 50}]}
```

##### L. Read node RRD statistics. Returns PNG

Syntax: `getNodeRRD(node)`

Example: `print json.dumps(getNodeSyslog('pve'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"ds": "property is missing and it is not optional", "timeframe": "property is missing and it is not optional"}, "data": null}
```

##### M. Read node RRD statistics. Returns RRD

Syntax: `getNodeRRDData(node)`

Example: `print json.dumps(getNodeSyslog('pve'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"timeframe": "property is missing and it is not optional"}, "data": null}
```

##### N. Get user_beancounters failcnt for all active containers. Returns JSON

Syntax: `getNodeBeans(node)`

Example: `print json.dumps(getNodeBeans('pve'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/ubfailcnt' not implemented", "code": 501, "ok": false}, "data": null}
```

##### O. Get tasks by UPID. Returns JSON

Syntax: `getNodeTaskByUPID(node,upid)`

Example: `print json.dumps(proxmox.getNodeTaskLogByUPID('pve', 'UPID:pve:00005D64:0017207B:59B6642D:qmstart:101:root@pam:'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"name": "log"}, {"name": "status"}]}
```

##### P. Read task log. Returns JSON

Syntax: `getNodeTaskLogByUPID(node,upid)`

Example: `print json.dumps(proxmox.getNodeTaskLogByUPID('pve', 'UPID:pve:00005D64:0017207B:59B6642D:qmstart:101:root@pam:'))`

Output: 

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "total": 1, "data": [{"t": "TASK OK", "n": 1}]}
```

##### Q. Read task status. Returns JSON

Syntax: `getNodeTaskStatusByUPID(node,upid)`

Example: `print json.dumps(proxmox.getNodeTaskStatusByUPID('pve', 'UPID:pve:00005D64:0017207B:59B6642D:qmstart:101:root@pam:'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"status": "stopped", "node": "pve", "pstart": 1515643, "type": "qmstart", "pid": 23908, "upid": "UPID:pve:00005D64:0017207B:59B6642D:qmstart:101:root@pam:", "user": "root@pam", "starttime": 1505125421, "exitstatus": "OK", "id": "101"}}
```

#### 3.1.3. Scan

##### A. Get index of available scan methods, Returns JSON

Syntax: `getNodeScanMethods(node)`

Example: `print json.dumps(proxmox.getNodeScanMethods('pve'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"method": "lvm"}, {"method": "iscsi"}, {"method": "nfs"}, {"method": "glusterfs"}, {"method": "usb"}, {"method": "zfs"}]}
```

##### B. Scan remote iSCSI server.

Syntax: `getRemoteiSCSI(node)`

Example: `print json.dumps(proxmox.getRemoteiSCSI('pve'))`

Output: 

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"portal": "property is missing and it is not optional"}, "data": null}
```

##### C. Scan local LVM groups

Syntax: `getNodeLVMGroups(node)`

Example: `print json.dumps(proxmox.getNodeLVMGroups('pve'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": []}
```

##### D. Scan remote NFS server

Syntax: `getRemoteNFS(node)`

Example: `print json.dumps(proxmox.getRemoteNFS('pve'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"server": "property is missing and it is not optional"}, "data": null}
```

##### E. List local USB devices

Syntax: `getNodeUSB(node)`

Example: `print json.dumps(proxmox.getNodeUSB('pve'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"vendid": "1d6b", "devnum": 1, "product": "EHCI Host Controller", "busnum": 1, "level": 0, "class": 9, "speed": "480", "port": 0, "prodid": "0002", "manufacturer": "Linux 4.10.15-1-pve ehci_hcd"}, {"vendid": "1d6b", "devnum": 1, "product": "EHCI Host Controller", "busnum": 2, "level": 0, "class": 9, "speed": "480", "port": 0, "prodid": "0002", "manufacturer": "Linux 4.10.15-1-pve ehci_hcd"}, {"vendid": "1d6b", "devnum": 1, "product": "UHCI Host Controller", "busnum": 3, "level": 0, "port": 0, "speed": "12", "class": 9, "prodid": "0001", "manufacturer": "Linux 4.10.15-1-pve uhci_hcd"}, {"vendid": "1d6b", "devnum": 1, "product": "UHCI Host Controller", "busnum": 4, "level": 0, "class": 9, "speed": "12", "port": 0, "prodid": "0001", "manufacturer": "Linux 4.10.15-1-pve uhci_hcd"}, {"vendid": "1a2c", "devnum": 2, "product": "USB Keyboard", "busnum": 4, "level": 1, "usbpath": "2", "class": 0, "speed": "1.5", "port": 1, "prodid": "0e24", "manufacturer": "SEM"}, {"vendid": "1d6b", "devnum": 1, "product": "UHCI Host Controller", "busnum": 5, "level": 0, "port": 0, "speed": "12", "class": 9, "prodid": "0001", "manufacturer": "Linux 4.10.15-1-pve uhci_hcd"}, {"vendid": "046b", "devnum": 2, "product": "Virtual Keyboard and Mouse", "busnum": 5, "level": 1, "usbpath": "1", "port": 0, "speed": "12", "class": 0, "prodid": "ff10", "manufacturer": "American Megatrends Inc."}, {"vendid": "1d6b", "devnum": 1, "product": "UHCI Host Controller", "busnum": 6, "level": 0, "class": 9, "speed": "12", "port": 0, "prodid": "0001", "manufacturer": "Linux 4.10.15-1-pve uhci_hcd"}, {"vendid": "1d6b", "devnum": 1, "product": "UHCI Host Controller", "busnum": 7, "level": 0, "port": 0, "speed": "12", "class": 9, "prodid": "0001", "manufacturer": "Linux 4.10.15-1-pve uhci_hcd"}, {"vendid": "1d6b", "devnum": 1, "product": "UHCI Host Controller", "busnum": 8, "level": 0, "port": 0, "speed": "12", "class": 9, "prodid": "0001", "manufacturer": "Linux 4.10.15-1-pve uhci_hcd"}]}
```

#### 3.1.4. OpenVZ Methods (not implemented)

##### A. Directory index. Returns JSON

Syntax: `getContainerIndex(node,vmid)`

Example: `print json.dumps(proxmox.getContainerIndex('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106' not implemented", "code": 501, "ok": false}, "data": null}
```

##### B. Get virtual machine status. Returns JSON

Syntax: `getContainerStatus(node,vmid)`

Example: `print json.dumps(proxmox.getContainerStatus('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106/status/current' not implemented", "code": 501, "ok": false}, "data": null}
```

##### C. Get container user_beancounters. Returns JSON

Syntax: `getContainerBeans(node,vmid)`

Example: `print json.dumps(proxmox.getContainerBeans('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106/status/ubc' not implemented", "code": 501, "ok": false}, "data": null}
```

##### D. Get container configuration. Returns JSON

Syntax: `getContainerConfig(node,vmid)`

Example: `print json.dumps(proxmox.getContainerConfig('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106/config' not implemented", "code": 501, "ok": false}, "data": null}
```

##### E. Read init log. Returns JSON

Syntax: `getContainerInitLog(node,vmid)`

Example: `print json.dumps(proxmox.getContainerInitLog('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106/initlog' not implemented", "code": 501, "ok": false}, "data": null}
```
		
##### F. Read VM RRD statistics. Returns PNG

Syntax: `getContainerRRD(node,vmid)`

Example: `print json.dumps(proxmox.getContainerRRD('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106/rrd' not implemented", "code": 501, "ok": false}, "data": null}
```

##### G. Read VM RRD statistics. Returns RRD

Syntax: `getContainerRRDData(node,vmid)`

Example: `print json.dumps(proxmox.getContainerRRDData('pve', '106'))`

Output:

```
{"status": {"reason": "Method 'GET /nodes/pve/openvz/106/rrddata' not implemented", "code": 501, "ok": false}, "data": null}
```

#### 3.1.5. KVM Methods

##### A. Directory index. Returns JSON

Syntax: `getVirtualIndex(node,vmid)`

Example: `print json.dumps(proxmox.getVirtualIndex('pve', '106'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"subdir": "config"}, {"subdir": "pending"}, {"subdir": "status"}, {"subdir": "unlink"}, {"subdir": "vncproxy"}, {"subdir": "migrate"}, {"subdir": "resize"}, {"subdir": "move"}, {"subdir": "rrd"}, {"subdir": "rrddata"}, {"subdir": "monitor"}, {"subdir": "agent"}, {"subdir": "snapshot"}, {"subdir": "spiceproxy"}, {"subdir": "sendkey"}, {"subdir": "firewall"}]}
```

##### B. Get virtual machine status. Returns JSON

Syntax: `getVirtualStatus(node,vmid)`

Example: `print json.dumps(proxmox.getVirtualStatus('pve', '103'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"status": "stopped", "uptime": 0, "ha": {"managed": 0}, "name": "desktop.ubuntu.baonq.me", "diskread": 0, "mem": 0, "pid": null, "cpus": 2, "netin": 0, "diskwrite": 0, "template": "", "netout": 0, "qmpstatus": "stopped", "disk": 0, "cpu": 0, "maxdisk": 42949672960, "maxmem": 2147483648}}
```

##### C. Get virtual machine configuration. Returns JSON

Syntax: `getVirtualConfig(node,vmid)`

Example: `print json.dumps(proxmox.getVirtualConfig('pve', '103'))`

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"smbios1": "uuid=51700618-8ace-4729-958f-2c8d8fe7e505", "scsihw": "virtio-scsi-pci", "balloon": 0, "cpu": "host", "scsi0": "local-zfs:vm-103-disk-1,size=40G", "numa": 0, "memory": 2048, "ostype": "l26", "cores": 1, "net0": "virtio=A2:5F:E6:53:92:4C,bridge=vmbr0", "bootdisk": "scsi0", "sockets": 2, "digest": "8c91b8be2101bdfdfd0e583578ffb99af6966ae4", "name": "desktop.ubuntu.baonq.me"}}
```

##### D. Read VM RRD statistics. Returns JSON

Syntax: `getVirtualRRD(node,vmid)`

Example: `print json.dumps(proxmox.getVirtualRRD('pve', '103'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"ds": "property is missing and it is not optional", "timeframe": "property is missing and it is not optional"}, "data": null}
```

##### E. Read VM RRD statistics. Returns JSON

Syntax: `getVirtualRRDData(node,vmid)`

Example: `print json.dumps(proxmox.getVirtualRRDData('pve', '103'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"timeframe": "property is missing and it is not optional"}, "data": null}
```

#### 3.1.6. Storage Methods

##### A. Get volume attributes. Returns JSON

Syntax: `getStorageVolumeData(node,storage,volume)`

Example: 

```
print json.dumps(proxmox.getStorageVolumeData('pve', 'local-zfs', ''))`
print json.dumps(proxmox.getStorageVolumeData('pve', 'local-zfs', 'vm-102-disk-1'))
```

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"name": "subvol-105-disk-1", "parent": null, "format": "subvol", "vmid": "105", "content": "images", "volid": "local-zfs:subvol-105-disk-1", "size": 32212254720}, {"name": "subvol-106-disk-1", "parent": null, "format": "subvol", "vmid": "106", "content": "images", "volid": "local-zfs:subvol-106-disk-1", "size": 32212254720}, {"name": "vm-100-disk-1", "parent": null, "format": "raw", "vmid": "100", "content": "images", "volid": "local-zfs:vm-100-disk-1", "size": 32212254720}, {"name": "vm-101-disk-1", "parent": null, "format": "raw", "vmid": "101", "content": "images", "volid": "local-zfs:vm-101-disk-1", "size": 64424509440}, {"name": "vm-102-disk-1", "parent": null, "format": "raw", "vmid": "102", "content": "images", "volid": "local-zfs:vm-102-disk-1", "size": 64424509440}, {"name": "vm-103-disk-1", "parent": null, "format": "raw", "vmid": "103", "content": "images", "volid": "local-zfs:vm-103-disk-1", "size": 42949672960}, {"name": "vm-104-disk-1", "parent": null, "format": "raw", "vmid": "104", "content": "images", "volid": "local-zfs:vm-104-disk-1", "size": 32212254720}]}
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"path": "/dev/zvol/rpool/data/vm-102-disk-1", "size": 64424509440, "used": 0, "format": "raw"}}
```

##### B. Read storage config. Returns JSON

Syntax: `getStorageConfig(storage)`

Example:

```
print json.dumps(proxmox.getStorageConfig('local-zfs'))
print json.dumps(proxmox.getStorageConfig('local'))
```

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"storage": "local-zfs", "content": "images,rootdir", "pool": "rpool/data", "sparse": 1, "type": "zfspool", "digest": "0e7b92ec3d27fdaae928f801208da4b1001eeaa8"}}
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": {"content": "vztmpl,iso,backup", "type": "dir", "storage": "local", "digest": "0e7b92ec3d27fdaae928f801208da4b1001eeaa8", "path": "/var/lib/vz"}}
```

##### C. List storage content. Returns JSON
    
Syntax: `getNodeStorageContent(node,storage)`

Example:

```
print json.dumps(proxmox.getNodeStorageContent('pve', 'local-zfs'))
print json.dumps(proxmox.getNodeStorageContent('pve', 'local'))
```

Output:

```
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"name": "subvol-105-disk-1", "parent": null, "format": "subvol", "vmid": "105", "content": "images", "volid": "local-zfs:subvol-105-disk-1", "size": 32212254720}, {"name": "subvol-106-disk-1", "parent": null, "format": "subvol", "vmid": "106", "content": "images", "volid": "local-zfs:subvol-106-disk-1", "size": 32212254720}, {"name": "vm-100-disk-1", "parent": null, "format": "raw", "vmid": "100", "content": "images", "volid": "local-zfs:vm-100-disk-1", "size": 32212254720}, {"name": "vm-101-disk-1", "parent": null, "format": "raw", "vmid": "101", "content": "images", "volid": "local-zfs:vm-101-disk-1", "size": 64424509440}, {"name": "vm-102-disk-1", "parent": null, "format": "raw", "vmid": "102", "content": "images", "volid": "local-zfs:vm-102-disk-1", "size": 64424509440}, {"name": "vm-103-disk-1", "parent": null, "format": "raw", "vmid": "103", "content": "images", "volid": "local-zfs:vm-103-disk-1", "size": 42949672960}, {"name": "vm-104-disk-1", "parent": null, "format": "raw", "vmid": "104", "content": "images", "volid": "local-zfs:vm-104-disk-1", "size": 32212254720}]}
{"status": {"reason": "OK", "code": 200, "ok": true}, "data": [{"content": "vztmpl", "volid": "local:vztmpl/centos-7-default_20170504_amd64.tar.xz", "format": "txz", "size": 68497932}, {"content": "vztmpl", "volid": "local:vztmpl/ubuntu-16.04-standard_16.04-1_amd64.tar.gz", "format": "tgz", "size": 197123375}, {"content": "iso", "volid": "local:iso/CentOS-7-x86_64-Minimal-1611.iso", "size": 713031680, "format": "iso"}, {"content": "iso", "volid": "local:iso/gparted-live-0.28.1-1-amd64.iso", "format": "iso", "size": 286261248}, {"content": "iso", "volid": "local:iso/ubuntu-16.04.2-server-amd64.iso", "format": "iso", "size": 869269504}, {"content": "iso", "volid": "local:iso/ubuntu-17.04-desktop-amd64.iso", "format": "iso", "size": 1609039872}, {"content": "iso", "volid": "local:iso/Windows_10_x64.iso", "size": 4083853312, "format": "iso"}, {"content": "iso", "volid": "local:iso/Windows_7_AIO.iso", "format": "iso", "size": 4172677120}]}
```

##### D. Read storage RRD statistics. Returns JSON

Syntax: `getNodeStorageRRD(node,storage)`

Example: `print json.dumps(proxmox.getNodeStorageRRD('pve', 'local'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"ds": "property is missing and it is not optional", "timeframe": "property is missing and it is not optional"}, "data": null}
```

##### E. Read storage RRD statistics. Returns JSON

Syntax: `getNodeStorageRRDData(node,storage)`

Example: `print json.dumps(proxmox.getNodeStorageRRDData('pve', 'local'))`

Output:

```
{"status": {"reason": "Parameter verification failed.", "code": 400, "ok": false}, "errors": {"timeframe": "property is missing and it is not optional"}, "data": null}
```

#### 3.2. POST Methods

	
##### 3.2.1. OpenVZ Methods
	
		createOpenvzContainer(node,post_data)
##### Create or restore a container. Returns JSON
Requires a dictionary of tuples formatted [('postname1','data'),('postname2','data')]

		mountOpenvzPrivate(node,vmid)
##### Mounts container private area. Returns JSON

		shutdownOpenvzContainer(node,vmid)
##### Shutdown the container. Returns JSON

		startOpenvzContainer(node,vmid)
##### Start the container. Returns JSON

		stopOpenvzContainer(node,vmid)
##### Stop the container. Returns JSON

		unmountOpenvzPrivate(node,vmid)
##### Unmounts container private area. Returns JSON

		migrateOpenvzContainer(node,vmid,target)
##### Migrate the container to another node. Creates a new migration task. Returns JSON

##### 3.2.2. KVM Methods

		createVirtualMachine(node,post_data)
##### Create or restore a virtual machine. Returns JSON
Requires a dictionary of tuples formatted [('postname1','data'),('postname2','data')]
		
		cloneVirtualMachine(node,vmid,post_data)
		{"status": {"reason": "OK", "code": 200, "ok": true}, "data": "UPID:pve:00006762:008628C8:59BB8259:qmclone:100:root@pam:"}

##### Create a copy of virtual machine/template. Returns JSON
Requires a dictionary of tuples formatted [('postname1','data'),('postname2','data')]
		
		resetVirtualMachine(node,vmid)
##### Reset a virtual machine. Returns JSON
		
		resumeVirtualMachine(node,vmid)
##### Resume a virtual machine. Returns JSON
	
		shutdownVirtualMachine(node,vmid)
##### Shut down a virtual machine. Returns JSON
	
		startVirtualMachine(node,vmid)
##### Start a virtual machine. Returns JSON
	
		stopVirtualMachine(node,vmid)
##### Stop a virtual machine. Returns JSON

		suspendVirtualMachine(node,vmid)
##### Suspend a virtual machine. Returns JSON
		
		migrateVirtualMachine(node,vmid,target)
##### Migrate a virtual machine. Returns JSON

		monitorVirtualMachine(node,vmid,command)
##### Send monitor command to a virtual machine. Returns JSON
		
		vncproxyVirtualMachine(node,vmid)
##### Creates a VNC Proxy for a virtual machine. Returns JSON

		rollbackVirtualMachine(node,vmid,snapname)
##### Rollback a snapshot of a virtual machine. Returns JSON

		getSnapshotConfigVirtualMachine(node,vmid,snapname)
##### Get snapshot config of a virtual machine. Returns JSON
      
#### 3.3. DELETE Methods
    
##### 3.3.1. OPENVZ
    
		deleteOpenvzContainer(node,vmid)
##### Deletes the specified openvz container

#### 3.3.2. NODE
    
		deleteNodeNetworkConfig(node)
##### Revert network configuration changes.
  
		deleteNodeInterface(node,interface)
##### Delete network device configuration
    
#### 3.3.3. KVM
    
		deleteVirtualMachine(node,vmid)
##### Destroy the vm (also delete all used/owned volumes).
        
#### 3.3.4. POOLS
		deletePool(poolid)
##### Delete Pool

#### 3.3.5. STORAGE
		deleteStorageConfiguration(storageid)
##### Delete storage configuration

### 3.4. PUT Methods

#### 3.4.1. NODE
		setNodeDNSDomain(node,domain)
##### Set the nodes DNS search domain

		setNodeSubscriptionKey(node,key)
##### Set the nodes subscription key
        
		setNodeTimeZone(node,timezone)
##### Set the nodes timezone

#### 3.4.2. OPENVZ
		setOpenvzContainerOptions(node,vmid,post_data)
##### Set openvz virtual machine options.
  
#### 3.4.3. KVM
		setVirtualMachineOptions(node,vmide,post_data)
##### Set KVM virtual machine options.

		sendKeyEventVirtualMachine(node,vmid, key)
##### Send key event to virtual machine

		unlinkVirtualMachineDiskImage(node,vmid, post_data)
##### Unlink disk images
 
#### 3.4.4. POOLS
		setPoolData(poolid, post_data)
##### Update pool data.
 
#### 3.4.5. STORAGE
		updateStorageConfiguration(storageid, post_data)
##### Update storage configuration
