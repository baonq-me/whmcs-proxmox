#!/usr/bin/python3

# Ref: https://github.com/baonq-me/pyproxmox
# Ref: https://github.com/Daemonthread/pyproxmox
# Ref: https://github.com/frederickding/Cloud-Init-ISO

# Parse config from proxmox.cfg
import configparser

CONFIG_FILE = 'proxmox.cfg'



# Handle input as arguments
import argparse

# Format size
import humanfriendly

from pyproxmox import *

# Ultilities
import sys, os, warnings
import time, datetime, json, re

# Call genisoimage via cli
import subprocess

# pyfancy: https://github.com/ilovecode1/Pyfancy-2
# Print colored text
from pyfancy import *


HTTP_STATUS_CODE = {400: 'Bad request', 500: 'Internal Server Error'}

def execBash(cmd):
	process = subprocess.Popen(cmd.split(), stdout=subprocess.PIPE)
	output, error = process.communicate()
	exitcode = process.returncode

	return exitcode

def makeCloudInitISO(vmid):
	filename = 'VM' + vmid + '-' + datetime.datetime.now().strftime('%d_%m_%Y_%H_%M_%S') +'.iso'
	execBash('genisoimage -quiet -output cloudinit/iso/' + filename + ' -volid cidata -joliet -rock cloudinit/user-data cloudinit/meta-data')
	return filename

def debug(functionName, jsonResponse, debugMode):
	if debugMode is not None:
		pyfancy().yellow('[DEBUG]\t').raw(functionName + ' ' + json.dumps(jsonResponse)).output()
	if jsonResponse['status']['ok'] is False:
		pyfancy().red('[ERROR]\t') \
				 .red(functionName).raw(' fail with HTTP code ') \
				 .red('%s (%s)' % (jsonResponse['status']['code'],HTTP_STATUS_CODE[jsonResponse['status']['code']])).output()
		pyfancy().red('\t').raw(jsonResponse['status']['reason']).output()
		if 'errors' in jsonResponse:
			for message in jsonResponse['errors']:
				pyfancy().red('\t').raw('[%s] %s' % (message,jsonResponse['errors'][message])).output()
		return 1
	return 0

def alert(percent, content, yellowLevel, redLevel):
	if percent > redLevel:
		print(pyfancy().red('[%s]\t' % content), end='')
	elif percent > yellowLevel:
		print(pyfancy().yellow('[%s]\t' % content), end='')
	else:
		print(pyfancy().green('[%s]\t' % content), end='')


class ProxmoxCLI():
	def __init__(self, configFile):
		self.conf = configparser.ConfigParser()
		try:
			self.conf.read(configFile)
		except:
			pyfancy().red('[ERROR]\t').raw('Could not read configuration file ' + configFile).output()
			sys.exit(1)


		connect = prox_auth(self.conf.get('server', 'host'), self.conf.get('server', 'user'), self.conf.get('server', 'password'))
		if connect.status is False:
			pyfancy().red('[ERROR]\t').raw('Could not connect to ' + self.conf.get('server', 'host') + ' as ' + self.conf.get('server', 'user') + ': ' + connect.error).output()
			sys.exit(1)
		else:
			self.proxmox = pyproxmox(connect)
			pyfancy().green('[INFO]\t').raw('Connected to ' + self.conf.get('server', 'host') + ' as ' + self.conf.get('server', 'user')).output()


	def parse_option(self):
		self.parser = argparse.ArgumentParser(description = 'Proxmox API client')
		
		# Debugging mode
		self.parser.add_argument('-debug', '--debug', nargs = '*', help='Enable debugging mode')

		# System status
		self.parser.add_argument('-l', '--list', nargs = '*', help='List nodes in cluster')
		self.parser.add_argument('-d', '--detail', nargs = '*', help='with -l, display more details')

		# Cloning new VM
		self.parser.add_argument('-c', '--clone', nargs = '*', default=1, help='List nodes in cluster')
		self.parser.add_argument('-cpu', '--cpu', nargs = 1, help='Define number of CPU when cloning new VM in -c option')
		self.parser.add_argument('-hostname', '--hostname', nargs = 1, help='Define hostname when cloning new VM in -c option ')
		self.parser.add_argument('-mem', '--mem', nargs = 1, help='Define amount of memory when cloning new VM in -c option ')
		self.parser.add_argument('-storage', '--storage', nargs = 1, help='Define additional storage when cloning new VM in -c option ')
		self.parser.add_argument('-node', '--node', nargs = 1, help='Define proxmox node to be used when cloning new VM in -c option ')

		self.parser.parse_args()
		self.args = self.parser.parse_args()

		if self.args.list is not None:
			getClusterStatus = self.proxmox.getClusterStatus()
			pyfancy().green('[INFO]\t').raw('Cluster has ' + str(len(getClusterStatus['data'])) + ' node(s).').output()
			for i in range(0, len(getClusterStatus['data'])):
				if len(self.args.list) == 0 or getClusterStatus['data'][i]['name'] in self.args.list:
					pyfancy().green('[STAT]\t').raw(getClusterStatus['data'][i]['name'] + ' ' + getClusterStatus['data'][i]['ip'] + ' ' + ('online' if getClusterStatus['data'][i]['online'] == 1 else 'offline')).output()

				if self.args.detail is not None:
					# Storage
					if len(self.args.detail) == 0 or 'storage' in self.args.detail:
						getNodeStorage = self.proxmox.getNodeStorage(getClusterStatus['data'][i]['name'])
						for j in range(0, len(getNodeStorage['data'])):
							total = getNodeStorage['data'][j]['total']
							used = getNodeStorage['data'][j]['used']
							percent = round(1.0*used/total*100, 2)
							alert(percent, 'STAT', 75, 90)
							pyfancy().raw(getClusterStatus['data'][i]['name'] + ' storage ' + getNodeStorage['data'][j]['storage'] + ' ' + humanfriendly.format_size(used, binary = True) + ' ' + humanfriendly.format_size(total, binary = True) + ' ' + str(percent) + '%').output()
				
					# CPU
					if len(self.args.detail) == 0 or 'cpu' in self.args.detail:
						getNodeStatus = self.proxmox.getNodeStatus(getClusterStatus['data'][i]['name'])
						percent = (float(getNodeStatus['data']['loadavg'][0]) / float(getNodeStatus['data']['cpuinfo']['cpus'])) * 100
						alert(percent, 'STAT', 75, 90)
						pyfancy().raw(getClusterStatus['data'][i]['name'] + ' cpu thread ' + str(getNodeStatus['data']['cpuinfo']['cpus']) + ' loadavg ' + str(getNodeStatus['data']['loadavg'][0]) + ' ' + str(getNodeStatus['data']['loadavg'][1]) + ' ' + str(getNodeStatus['data']['loadavg'][2])).output()
					
					# Memory
					if len(self.args.detail) == 0 or 'mem' in self.args.detail:
						# RAM
						getNodeStatus = self.proxmox.getNodeStatus(getClusterStatus['data'][i]['name'])
						total = getNodeStatus['data']['memory']['total']
						used = getNodeStatus['data']['memory']['used']
						percent = round(1.0*used/total*100, 2)						
						alert(percent, 'STAT', 75, 90)
						pyfancy().raw(getClusterStatus['data'][i]['name'] + ' mem ram ' + humanfriendly.format_size(used, binary = True) + ' ' + humanfriendly.format_size(total, binary = True) + ' ' + str(percent) + '%').output()

						# Swap
						total = getNodeStatus['data']['swap']['total']
						used = getNodeStatus['data']['swap']['used']
						percent = round(1.0*used/total*100, 2)
						alert(percent, 'STAT', 75, 90)
						pyfancy().raw(getClusterStatus['data'][i]['name'] + ' mem swap ' + humanfriendly.format_size(used, binary = True) + ' ' + humanfriendly.format_size(total, binary = True) + ' ' + str(percent) + '%').output()

					# CPU
					if len(self.args.detail) == 0 or 'net' in self.args.detail:
						getNodeVirtualIndex = self.proxmox.getNodeVirtualIndex(getClusterStatus['data'][i]['name'])
						netin = 0
						netout = 0
						for instance in getNodeVirtualIndex['data']:
							if instance['uptime'] != 0:
								pyfancy().green('[STAT]\t').raw(getClusterStatus['data'][i]['name'] + ' net vmid ' + str(instance['vmid']) + ' ' + humanfriendly.format_size(instance['netin'], binary = True) + ' ' + humanfriendly.format_size(instance['netout'], binary = True)).output()
								netin = netin + instance['netin']
								netout += instance['netout']
						pyfancy().green('[STAT]\t').raw(getClusterStatus['data'][i]['name'] + ' net ' + humanfriendly.format_size(netin, binary = True)  + ' ' + humanfriendly.format_size(netout, binary = True)).output()
		
		elif self.args.clone is not None:
			if self.args.debug is not None:
				pyfancy().yellow('[WARN]\t').raw('Debugging mode is enabled. After cloning, new VM will be deleted.').output()

			cloneConfig = {}
			cloneConfig['node'] = self.args.node[0]
			cloneConfig['hostname'] = self.args.hostname[0]
			cloneConfig['newvmid'] = str(self.proxmox.getClusterVmNextId()['data'])
			cloneConfig['cpus'] = self.args.cpu[0]
			
			cloneConfig['mem'] = humanfriendly.parse_size(self.args.mem[0], binary=True)
			cloneConfig['storage'] = humanfriendly.parse_size(self.args.storage[0], binary = True)
			
			cloneConfig['newdisk'] = 'vm-' + cloneConfig['newvmid'] + '-disk-2'

			pyfancy().green('[CONF]\t').raw('Node:                ' + cloneConfig['node']).output()
			pyfancy().green('[CONF]\t').raw('Template VMID:       ' + self.conf.get('template', 'vmid')).output()
			pyfancy().green('[CONF]\t').raw('New VMID:            ' + cloneConfig['newvmid']).output()
			pyfancy().green('[CONF]\t').raw('Hostname:            ' + cloneConfig['hostname']).output()
			pyfancy().green('[CONF]\t').raw('CPU unit:            ' + cloneConfig['cpus']).output()
			pyfancy().green('[CONF]\t').raw('Emulated CPU type:   ' + self.conf.get('qemu', 'cpu_type')).output()
			pyfancy().green('[CONF]\t').raw('Memory (RAM):        ' + humanfriendly.format_size(cloneConfig['mem'], binary = True)).output()
			pyfancy().green('[CONF]\t').raw('Inital Storage:      ' + self.conf.get('storage', 'root')).output()
			pyfancy().green('[CONF]\t').raw('Additional Storage:  ' + humanfriendly.format_size(cloneConfig['storage'], binary = True) + ' (' + cloneConfig['newdisk'] + ')').output()
			pyfancy().green('[CONF]\t').raw('Storage engine:      ' + self.conf.get('storage', 'engine')).output()
			pyfancy().green('[CONF]\t').raw('Storage bus:         ' + self.conf.get('storage', 'bus')).output()
			pyfancy().green('[CONF]\t').raw('Storage format:      ' + self.conf.get('storage', 'format')).output()

			response = self.proxmox.cloneVirtualMachine(cloneConfig['node'], self.conf.get('template', 'vmid'),
																newid=cloneConfig['newvmid'],
																full='1',
																name=cloneConfig['hostname'])
			
			if debug('cloneVirtualMachine()', response, self.args.debug) == 0:
				pyfancy().green('[INFO]\t').raw('Cloning VM ' + cloneConfig['newvmid'] + ', please wait ... ').output()
			else:
				return 1
			
			# Wait for cloning process complete
			UPID = response['data']
			while (True):
				time.sleep(1)
				getNodeTaskStatusByUPID = self.proxmox.getNodeTaskStatusByUPID(cloneConfig['node'], UPID)
				if 'status' in getNodeTaskStatusByUPID['data']:
					if getNodeTaskStatusByUPID['data']['status'] == 'stopped':
						pyfancy().green('[INFO]\t').raw('VM ' + cloneConfig['newvmid'] + ' is ready. Starting configuration on hardware ...').output()
						break


			response = self.proxmox.allocDiskImages(cloneConfig['node'], self.conf.get('storage', 'engine'),
							filename = cloneConfig['newdisk'],
							size = str(int(cloneConfig['storage']/1024**2))+'M',
							vmid = cloneConfig['newvmid'],
							format = self.conf.get('storage', 'format')
							)
			
			if debug('allocDiskImages()', response, self.args.debug) == 0:
				pyfancy().green('[INFO]\t').raw('Disk ').green(cloneConfig['newdisk']).raw(' for VM ' + cloneConfig['newvmid'] + ' is allocated.').output()
			else:
				return 1

			# upload image
			cloudinitISO = makeCloudInitISO(cloneConfig['newvmid'])
			response = self.proxmox.uploadContent(cloneConfig['node'], self.conf.get('storage', 'cloudinit'), 'cloudinit/iso/' + cloudinitISO, 'iso')
			
			if debug('uploadContent()', response, self.args.debug) == 0:
				pyfancy().green('[INFO]\t').raw('cloudinit datasource ').green(cloudinitISO).raw(' for VM ' + cloneConfig['newvmid'] + ' is uploaded.').output()
			else:
				return 1
				

			response = self.proxmox.configVirtualmachine(cloneConfig['node'], cloneConfig['newvmid'],
						{	
							'kvm': '1', 			# Enable KVM virtualization
							'onboot': '1', 			# VM will start automatically after host is up
							'balloon': '0',			# Disable balloon driver
							'shares': '0', 			# Disable auto ballooning
							'sockets': '1', 
							'cores': cloneConfig['cpus'], 
							'cpu': self.conf.get('qemu', 'cpu_type'),
							'memory': str(int(cloneConfig['mem']/1024**2)), 
							 self.conf.get('storage', 'bus') + '1': 'file=' + self.conf.get('storage', 'engine') + ':vm-' + cloneConfig['newvmid'] + '-disk-2',
							'ide2': 'file=' + self.conf.get('storage', 'cloudinit') + ':iso/' + cloudinitISO + ',media=cdrom'
						})

			if debug('configVirtualmachine()', response, self.args.debug) == 0:
				pyfancy().green('[INFO]\t').raw('VM ' + cloneConfig['newvmid'] + ' is successfully configured and will start shortly ...').output()
			else:
				return 1


			response = self.proxmox.startVirtualMachine(cloneConfig['node'], cloneConfig['newvmid'])
			if debug('startVirtualMachine()', response, self.args.debug) == 0:
				# Wait for VM to be started and check for its status
				time.sleep(4)
				for i in range(0, 4):
					time.sleep(1)
					upid = response['data']
					getNodeTaskStatusByUPID = self.proxmox.getNodeTaskStatusByUPID(cloneConfig['node'], upid)
					if 'exitstatus' in getNodeTaskStatusByUPID['data']:
						if getNodeTaskStatusByUPID['data']['exitstatus'] != 'OK':
							getNodeTaskLogByUPID = self.proxmox.getNodeTaskLogByUPID(cloneConfig['node'], response['data'])
							if self.args.debug is not None:
								pyfancy().yellow('[DEBUG]\t').raw('getNodeTaskLogByUPID() ' + json.dumps(getNodeTaskLogByUPID)).output()
							pyfancy().red('[ERROR]\t').raw('Could not start VM ' + cloneConfig['newvmid'] + '. Please check configurations.').output()
							for message in getNodeTaskLogByUPID['data']:
								pyfancy().raw('\t').raw(message['t']).output()
							return 1
				pyfancy().green('[INFO]\t').raw('VM ' + cloneConfig['newvmid'] + ' is successfully started.').output()
			else:
				return 1
			
			#if self.args.debug is not None:
				#json.dumps(self.proxmox.stopVirtualMachine(cloneConfig['node'], cloneConfig['newvmid']))
				#time.sleep(3)
				#json.dumps(self.proxmox.deleteVirtualMachine(cloneConfig['node'], cloneConfig['newvmid']))

			return 0

if __name__ == '__main__':
	# Disable warning for SSL verification
	warnings.filterwarnings('ignore')

	proxmoxcli = ProxmoxCLI(CONFIG_FILE)
	#for i in range(108,118):
	#	proxmoxcli.proxmox.deleteVirtualMachine('pve', str(i))
	proxmoxcli.parse_option()

