#!/usr/bin/python2

# Ref: https://github.com/baonq-me/pyproxmox
# Ref: https://github.com/Daemonthread/pyproxmox
# Ref: https://github.com/frederickding/Cloud-Init-ISO
"""

Installation: 

sudo pip install requests
sudo pip install requests==2.6.0

"""

# Use print() of Python 3 on Python 2
from __future__ import print_function

# Handle input as arguments
import argparse


from pyproxmox import *


# Ultilities
import sys, os, warnings
import time, datetime, json, re

# Call genisoimage via cli
import subprocess

# Print colored text 
from lazyme.string import palette, highlighter, formatter
from lazyme.string import color_print

# Convert unicode to float
#import decimal

# pyfancy: https://github.com/ilovecode1/Pyfancy-2
# Print colored text
from pyfancy import *

CONFIG_FILE = 'proxmox.conf'
TEMPLATE_VMID = '104'

HTTP_STATUS_CODE = {400: 'Bad request', 500: 'Internal Server Error'}

def formatData(bytes, unit, decimals):
	units = {'KB': 1, 'MB': 2, 'GB': 3}
	return round(bytes*1.0 / 1024**units[unit], decimals)

def translateToMB(data):
	units = {'M': 0, 'G': 1}
	sizes = filter(None, re.split('(\d+)', data))
	convertToMB = int(sizes[0]) * (1024**units[sizes[1]])
	return str(convertToMB)

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
		if jsonResponse['errors'] is not None:
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
	def loadConfig(self, filename):
		if os.path.exists(filename) is False:
			return False
		with open(filename) as data:
			conf = json.load(data)
			return conf

	#def debug(self, functionName, response):


	def __init__(self):
		# Check config file
		self.config = self.loadConfig(CONFIG_FILE)
		if self.config is False:
			pyfancy().red('[ERROR]\t').raw('Config file not found !').output()
			sys.exit(1)

		connect = prox_auth(self.config['host'], self.config['user'], self.config['password'])
		if connect.status is False:
			pyfancy().red('[ERROR]\t').raw('Could not connect to ' + self.config['host'] + ' as ' + self.config['user'] + ': ' + connect.error).output()
			sys.exit(1)
		else:
			self.proxmox = pyproxmox(connect)
			pyfancy().green('[INFO]\t').raw('Connected to ' + self.config['host'] + ' as ' + self.config['user']).output()


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
							total = formatData(getNodeStorage['data'][j]['total'], 'GB', 2)
							used = formatData(getNodeStorage['data'][j]['used'], 'GB', 2)
							percent = round(1.0*used/total*100, 2)
							alert(percent, 'STAT', 75, 90)
							pyfancy().raw(getClusterStatus['data'][i]['name'] + ' storage ' + getNodeStorage['data'][j]['storage'] + ' ' + str(used) + 'GB ' + str(total) + 'GB ' + str(percent) + '%').output()
				
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
						total = formatData(getNodeStatus['data']['memory']['total'], 'GB', 2)
						used = formatData(getNodeStatus['data']['memory']['used'], 'GB', 2)
						percent = round(1.0*used/total*100, 2)						
						alert(percent, 'STAT', 75, 90)
						pyfancy().raw(getClusterStatus['data'][i]['name'] + ' mem ram ' + str(used) + 'GB ' + str(total) + 'GB ' + str(round(used/total*100, 2)) + '%').output()

						# Swap
						total = formatData(getNodeStatus['data']['swap']['total'], 'GB', 2)
						used = formatData(getNodeStatus['data']['swap']['used'], 'GB', 2)
						percent = round(1.0*used/total*100, 2)
						alert(percent, 'STAT', 75, 90)
						pyfancy().raw(getClusterStatus['data'][i]['name'] + ' mem swap ' + str(used) + 'GB ' + str(total) + 'GB ' + str(round(used/total*100, 2)) + '%').output()

					# CPU
					if len(self.args.detail) == 0 or 'net' in self.args.detail:
						getNodeVirtualIndex = self.proxmox.getNodeVirtualIndex(getClusterStatus['data'][i]['name'])
						netin = 0
						netout = 0
						for instance in getNodeVirtualIndex['data']:
							if instance['uptime'] != 0:
								pyfancy().green('[STAT]\t').raw(getClusterStatus['data'][i]['name'] + ' net vmid ' + str(instance['vmid']) + ' ' + str(formatData(instance['netin'], 'MB', 2)) + 'MB ' + str(formatData(instance['netout'], 'MB', 2)) + 'MB').output()
								netin = netin + instance['netin']
								netout += instance['netout']
						pyfancy().green('[STAT]\t').raw(getClusterStatus['data'][i]['name'] + ' net ' + str(formatData(netin, 'GB', 3)) + 'GB ' + str(formatData(netout, 'GB', 3)) + 'GB').output()
		
		elif self.args.clone is not None:
			if self.args.debug is not None:
				pyfancy().yellow('[WARN]\t').raw('Debugging mode is enabled. After cloning, new VM will be deleted.').output()

			cloneConfig = {}
			cloneConfig['node'] = self.args.node[0]
			cloneConfig['hostname'] = self.args.hostname[0]
			cloneConfig['newvmid'] = str(self.proxmox.getClusterVmNextId()['data'])
			cloneConfig['cpus'] = self.args.cpu[0]
			cloneConfig['mem'] = self.args.mem[0]
			cloneConfig['storage'] = self.args.storage[0]
			cloneConfig['newdisk'] = 'vm-' + cloneConfig['newvmid'] + '-disk-2'

			pyfancy().green('[CONF]\t').raw('Template VMID:       ' + TEMPLATE_VMID).output()
			pyfancy().green('[CONF]\t').raw('Node:                ' + cloneConfig['node']).output()
			pyfancy().green('[CONF]\t').raw('Hostname:            ' + cloneConfig['hostname']).output()
			pyfancy().green('[CONF]\t').raw('New VMID:            ' + cloneConfig['newvmid']).output()
			pyfancy().green('[CONF]\t').raw('CPU:                 ' + cloneConfig['cpus']).output()
			pyfancy().green('[CONF]\t').raw('RAM:                 ' + cloneConfig['mem']).output()
			pyfancy().green('[CONF]\t').raw('Additional Storage:  ' + cloneConfig['storage'] + ' (' + cloneConfig['newdisk'] + ')').output()
			pyfancy().green('[CONF]\t').raw('Storage engine:      ' + self.config['storage_engine']).output()
			pyfancy().green('[CONF]\t').raw('Storage bus:         ' + self.config['storage_bus']).output()
			pyfancy().green('[CONF]\t').raw('Storage format:      ' + self.config['storage_format']).output()

			response = self.proxmox.cloneVirtualMachine(cloneConfig['node'], TEMPLATE_VMID, \
																newid=cloneConfig['newvmid'], \
																full='1', \
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
				if getNodeTaskStatusByUPID['data']['status'] == 'stopped':
					pyfancy().green('[INFO]\t').raw('VM ' + cloneConfig['newvmid'] + ' is ready. Starting configuration on hardware ...').output()
					break
			
			response = self.proxmox.allocDiskImages(cloneConfig['node'], self.config['storage_engine'], \
							filename = cloneConfig['newdisk'], \
							size = cloneConfig['storage'], \
							vmid = cloneConfig['newvmid'], \
							format = self.config['storage_format'] \
							)
			
			if debug('allocDiskImages()', response, self.args.debug) == 0:
				pyfancy().green('[INFO]\t').raw('Disk ').green(cloneConfig['newdisk']).raw(' for VM ' + cloneConfig['newvmid'] + ' is allocated.').output()
			else:
				return 1

			# upload image
			cloudinitISO = makeCloudInitISO(cloneConfig['newvmid'])
			response = self.proxmox.uploadContent(cloneConfig['node'], self.config['cloudinit_storage'], 'cloudinit/iso/' + cloudinitISO, 'iso')
			
			if debug('uploadContent()', response, self.args.debug) == 0:
				pyfancy().green('[INFO]\t').raw('cloudinit datasource ').green(cloudinitISO).raw(' for VM ' + cloneConfig['newvmid'] + ' is uploaded.').output()
			else:
				return 1
				

			response = self.proxmox.configVirtualmachine(cloneConfig['node'], cloneConfig['newvmid'], \
						{	'sockets': '2' if int(cloneConfig['cpus']) >= 2 else '1', \
							'cores': str(int(cloneConfig['cpus']) / 2) if int(cloneConfig['cpus']) >= 2 else '1', \
							'cpu': 'host', \
							'memory': translateToMB(cloneConfig['mem']), \
							 self.config['storage_bus'] + '1': 'file=' + self.config['storage_engine'] + ':vm-' + cloneConfig['newvmid'] + '-disk-2', \
							'ide2': 'file=' + self.config['cloudinit_storage'] + ':iso/' + cloudinitISO + ',media=cdrom,size=10M' \
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

			if self.args.debug is not None:
				json.dumps(self.proxmox.stopVirtualMachine(cloneConfig['node'], cloneConfig['newvmid']))
				time.sleep(3)
				json.dumps(self.proxmox.deleteVirtualMachine(cloneConfig['node'], cloneConfig['newvmid']))

			return 0

if __name__ == '__main__':
	# Disable warning for SSL verification
	warnings.filterwarnings('ignore')

	proxmoxcli = ProxmoxCLI()
	proxmoxcli.parse_option()

