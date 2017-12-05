<link rel="stylesheet" href="http://whmcs.vietnix.vn/modules/addons/proxmox/templates/admin/proxmox.css">
<script type="text/javascript" src="http://whmcs.vietnix.vn/modules/addons/proxmox/templates/admin/proxmox.js"></script>
<script src="https://rawgit.com/notifyjs/notifyjs/master/dist/notify.js"></script>

<div>
  <a class="btn btn-default" href="http://whmcs.vietnix.vn/admin/addonmodules.php?module=proxmox">Reload page</a>
</div>
<br/>
<div id="proxmox">
   <ul class="nav nav-tabs">
      <li class="active"><a href="#info">Sytem Infomation <span class="glyphicon glyphicon-arrow-right"></span></a></li>
      <li><a href="#paid">Paid Items <span class="glyphicon glyphicon-arrow-right"></span></a></li>
      <li><a href="#queued">Queued Items <span class="glyphicon glyphicon-arrow-right"></span></a></li>
      <li><a href="#created">Created Items</a></li>
   </ul>
   <div class="tab-content">
      <div id="info" class="tab-pane fade in active">

         <div class="icon-stats">
            <div class="row">
               <div class="col-sm-6">
                  <div class="icon-holder text-center">
                     <img style="width: 64px; margin: 10px;" src="../modules/addons/proxmox/templates/admin/images/icons/cpu.png"/>
                  </div>
                  <div class="note">CPU</div>
                  <div class="number">
                    {if $pveConnection eq 0}
                      <span style="color: red">Could not connect to server</span>
                    {else}
                      <span style="color:#49a94d;">{$cpu_percent}% ({$cpu_load1} {$cpu_load2} {$cpu_load3})</span>
                    {/if}
                  </div>
                  <div class="clear:both;"></div>
                  <div class="progress">
                     <span class="progress-bar progress-bar-success progress-bar-striped" style="width: {$cpu_usage}%"></span>
                  </div>
               </div>
               <div class="col-sm-6">
                  <div class="icon-holder text-center">
                     <img style="width: 64px; margin: 10px;" src="../modules/addons/proxmox/templates/admin/images/icons/memory.png"/>
                  </div>
                  <div class="note">Memory</div>
                  <div class="number">
                    {if $pveConnection eq 0}
                      <span style="color: red">Could not connect to server</span>
                    {else}
                      <span style="color:#49a94d;">{$mem_percent}% ({$mem_used} GB / {$mem_total} GB)</span>
                    {/if}
                  </div>
                  <div class="clear:both;"></div>
                  <div class="progress">
                     <span class="progress-bar progress-bar-success progress-bar-striped" style="width: {$mem_percent}%"></span>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-sm-6">
                  <div class="icon-holder text-center">
                     <img style="width: 64px; margin: 10px;" src="../modules/addons/proxmox/templates/admin/images/icons/disk.png"/>
                  </div>
                  <div class="note">Storage ({$storage_engine})</div>
                  <div class="number">
                    {if $pveConnection eq 0}
                      <span style="color: red">Could not connect to server</span>
                    {else}
                      <span style="color:#49a94d;">{$storage_percent}% ({$storage_used} GB / {$storage_total} GB)</span>
                    {/if}
                  </div>
                  <div class="clear:both;"></div>
                  <div class="progress">
                     <span class="progress-bar progress-bar-success progress-bar-striped" style="width: {$storage_percent}%"></span>
                  </div>
               </div>
            </div>
         </div>
         <h2>
            Proxmox System Configuration
         </h2>
         <div class="rows">
            <div class="col-sm-6">
               <table class="table table-hover">
                  <tbody>
                     {foreach from=$configPVE key=k item=v}
                     <tr>
                        <td>{$k}</td>
                        <td>{$v}</td>
                     </tr>
                     {/foreach}
                  </tbody>
               </table>
            </div>
            <div class="col-sm-6">
               <table class="table table-hover">
                  <tbody>
                     {foreach from=$configPVE key=k item=v}
                     <tr>
                        <td>{$k}</td>
                        <td>{$v}</td>
                     </tr>
                     {/foreach}
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <div id="paid" class="tab-pane fade">
         <p>Items that are paid will display here.</p>
         <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody>
               <tr>
                  <th style="width: 7%;">Item ID</th>
                  <th style="width: 8%;">Invoice ID</th>
                  <th style="width: 12%;">Customer</th>
                  <th style="width: 6%;">Type</th>
                  <th style="width: 35%;">Description</th>
                  <th style="width: 15%;">Updated on</th>
                  <th style="width: 12%;">IP address</th>
                  <th style="width: 5%;">Action</th>
               </tr>
            </tbody>
         </table>
         <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody class="list-group">
               {foreach from=$paiditems key=ID item=i}
               <tr class="text-center" id="item-{$i.id}">
                  <td style="width: 7%;">{'#'}{$i.id}</td>
                  <td style="width: 8%;"><a target="_blank" href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id={$i.invoiceid}">{'#'}{$i.invoiceid}</a></td>
                  <td style="width: 12%;"><a target="_blank" href="http://whmcs.vietnix.vn/admin/clientssummary.php?userid={$i.userid}">{$i.username}</a></td>
                  <td style="width: 6%;">{$i.type}</td>
                  <td style="width: 35%;">{$i.description}</td>
                  <td style="width: 15%;">{$i.updated_at}</td>
                  <td style="width: 12%;">
                      <input type="text" class="form-control ipaddress">
                  </td>
                  <td style="width: 5%;">
                     <button class="btn btn-success" onclick="create('{$i.id}')">
                       <span class="glyphicon glyphicon-plus"></span>
                     </button>
                  </td>
               </tr>
               {/foreach}
               {if count($paiditems) eq 0}
               <tr>
                 <td colspan="8">No paid items</td>
               </tr>
               {/if}
            </tbody>
         </table>
      </div>
      <div id="queued" class="tab-pane fade">
         <p>Items that are waiting for creation process will be shown here.</p>
         <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody>
               <tr>
                  <th style="width: 8%;">Item ID</th>
                  <th style="width: 8%;">Invoice ID</th>
                  <th style="width: 12%;">Customer</th>
                  <th style="width: 7%;">Type</th>
                  <th style="width: 40%;">Description</th>
                  <th style="width: 15%;">Updated on</th>
                  <th style="width: 10%;">Status</th>
               </tr>
            </tbody>
         </table>
         <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody class="list-group">
                {foreach from=$queueditems key=ID item=i}
               <tr class="text-center">
                  <td style="width: 8%;">#{$i.id}</td>
                  <td style="width: 8%;"><a target="_blank" href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id={$i.invoiceid}">{'#'}{$i.invoiceid}</a></td>
                  <td style="width: 12%;"><a target="_blank" href="http://whmcs.vietnix.vn/admin/clientssummary.php?userid={$i.userid}">{$i.username}</a></td>
                  <td style="width: 7%;">{$i.type}</td>
                  <td style="width: 40%;">{$i.description}</td>
                  <td style="width: 15%;">{$i.updated_at}</td>
                  <td style="width: 10%;">{$i.status}</td>
               </tr>
               {/foreach}
               {if count($queueditems) eq 0}
               <tr>
                 <td colspan="8">No queued items</td>
               </tr>
               {/if}
            </tbody>
         </table>
      </div>


      <div id="created" class="tab-pane fade">
         <p>Items that are created will be shown here.</p>
         <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody>
               <tr>
                  <th style="width: 8%;">Item ID</th>
                  <th style="width: 8%;">Invoice ID</th>
                  <th style="width: 12%;">Customer</th>
                  <th style="width: 7%;">Type</th>
                  <th style="width: 40%;">Description</th>
                  <th style="width: 15%;">Updated on</th>
                  <th style="width: 10%;">Status</th>
               </tr>
            </tbody>
         </table>
         <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody class="list-group">
              {foreach from=$createditems key=ID item=i}
               <tr class="text-center">
                  <td style="width: 8%;">#{$i.id}</td>
                  <td style="width: 8%;"><a target="_blank" href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id={$i.invoiceid}">{'#'}{$i.invoiceid}</a></td>
                  <td style="width: 12%;"><a target="_blank" href="http://whmcs.vietnix.vn/admin/clientssummary.php?userid={$i.userid}">{$i.username}</a></td>
                  <td style="width: 7%;">{$i.type}</td>
                  <td style="width: 40%;">
                    IP address: 57457457<br/>
                    Username: s252gdfh<br/>
                    Password: asfasfasf<br/>
                    <br/>
                    {$i.description}
                  </td>
                  <td style="width: 15%;">{$i.updated_at}</td>
                  <td style="width: 10%;">{$i.status}</td>
               </tr>
               {/foreach}
               {if count($createditems) eq 0}
               <tr>
                 <td colspan="8">No created items</td>
               </tr>
               {/if}
            </tbody>
         </table>
      </div>


   </div>
</div>
<script>
   $(document).ready(function(){
       $(".nav-tabs a").click(function(){
           $(this).tab('show');
       });
   });
</script>


<!--{$debug|var_dump}-->
