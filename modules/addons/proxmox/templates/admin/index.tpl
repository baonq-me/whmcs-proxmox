<h2>System Infomation</h2>
<code>{$status}</code>
<br/><br/>
<p>Proxmox VE Configurations:</p>

<blockquote>
    PVE Hostname: {$configPVEHostname}<br>
    PVE User: {$configPVEUser}<br>
    PVE Password: <code>ít ít ít</code><br>
    Default Storage Bus: {$configStorageBus}<br>
    Default Storage Engine: {$configStorageEngine}<br>
    Default Storage Format: {$configStorageFormat}<br>
    CloudInit Storage: {$configCloudInitStorage}
</blockquote>

<p>
    <a href="{$modulelink}&action=show" class="btn btn-success">
        <i class="fa fa-check"></i>
        Visit valid action link
    </a>
    <a href="{$modulelink}&action=invalid" class="btn btn-default">
        <i class="fa fa-times"></i>
        Visit invalid action link
    </a>
</p>
