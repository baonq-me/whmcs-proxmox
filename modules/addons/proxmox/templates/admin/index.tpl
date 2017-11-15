<link rel="stylesheet" href="http://whmcs.vietnix.vn/modules/addons/proxmox/templates/admin/proxmox.css">
<script type="text/javascript" src="http://whmcs.vietnix.vn/modules/addons/proxmox/templates/admin/proxmox.js"></script>
<div class="" id="proxmox">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#info">Sytem Infomation</a></li>
        <li><a href="#invoice">Invoices</a></li>
        <li><a href="#vm">VM</a></li>
        <li><a href="#pending">Pending</a></li>
    </ul>
    <div class="tab-content">
        <div id="info" class="tab-pane fade in active">
            <div class="icon-stats">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="icon-holder text-center">
                            <img style="width: 64px; margin: 10px;" src="../modules/addons/proxmox/templates/admin/images/icons/cpu.png"/>
                        </div>
                        <div class="note">
                            CPU
                        </div>
                        <div class="number">
                            <span style="color:#49a94d;">{$cpu_usage}%</span>
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
                        <div class="note">
                            Memory
                        </div>
                        <div class="number">
                            <span style="color:#49a94d;">{$memory_usage}% ({$memory_total})</span>
                        </div>
                        <div class="clear:both;"></div>
                        <div class="progress">
                            <span class="progress-bar progress-bar-success progress-bar-striped" style="width: {$memory_usage}%"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="icon-holder text-center">
                            <img style="width: 64px; margin: 10px;" src="../modules/addons/proxmox/templates/admin/images/icons/disk.png"/>
                        </div>
                        <div class="note">
                            Disk
                        </div>
                        <div class="number">
                            <span style="color:#49a94d;">{$disk_usage} ({$disk_total})</span>
                        </div>
                        <div class="clear:both;"></div>
                        <div class="progress">
                            <span class="progress-bar progress-bar-success progress-bar-striped" style="width: {$disk_usage}%"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="invoice" class="tab-pane fade">
            <p>Invoices that are paid will display here.</p>
            <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
                <tbody>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 10%;">Invoice ID</th>
                        <th style="width: 30%;">Customer</th>
                        <th style="width: 15%;">Amount</th>
                        <th style="width: 15%;">Time</th>
                        <th style="width: 5%;"></th>
                        <th style="width: 5%;"></th>
                        <th style="width: 5%;"></th>
                    </tr>
                </tbody>
            </table>
            <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
                <tbody class="list-group">
                    <tr class="text-center">
                        <td style="width: 15%;">Tue, 14-11-2017</td>
                        <td style="width: 10%;"><a href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id=48">#123</a></td>
                        <td style="width: 30%;"><a href="http://whmcs.vietnix.vn/admin/clientssummary.php?userid=1">Quoc-Bao Nguyen</a></td>
                        <td style="width: 15%;">730.000đ</td>
                        <td style="width: 15%;" title="This invoice will be processed automatically after this time."><span id="clock-invoice-48"></span></td>
                        <td style="width: 5%;">
                            <a title="Create VM for this invoice now">
                            <img src="images/icons/add.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="View list of VMs">
                            <img src="images/icons/logs.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="Hide this invoice and move it to pending list">
                            <img src="images/icons/disabled.png"/>
                            </a>
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td style="width: 15%;">Tue, 14-11-2017</td>
                        <td style="width: 10%;"><a href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id=48">#123</a></td>
                        <td style="width: 30%;"><a href="http://whmcs.vietnix.vn/admin/clientssummary.php?userid=1">Quoc-Bao Nguyen</a></td>
                        <td style="width: 15%;">730.000đ</td>
                        <td style="width: 15%;" title="This invoice will be processed automatically after this time."><span id="clock-invoice-48"></span></td>
                        <td style="width: 5%;">
                            <a title="Create VM for this invoice now">
                            <img src="images/icons/add.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="View list of VMs">
                            <img src="images/icons/logs.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="Hide this invoice and move it to pending list">
                            <img src="images/icons/disabled.png"/>
                            </a>
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td style="width: 15%;">Tue, 14-11-2017</td>
                        <td style="width: 10%;"><a href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id=48">#123</a></td>
                        <td style="width: 30%;"><a href="http://whmcs.vietnix.vn/admin/clientssummary.php?userid=1">Quoc-Bao Nguyen</a></td>
                        <td style="width: 15%;">730.000đ</td>
                        <td style="width: 15%;" title="This invoice will be processed automatically after this time."><span id="clock-invoice-48"></span></td>
                        <td style="width: 5%;">
                            <a title="Create VM for this invoice now">
                            <img src="images/icons/add.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="View list of VMs">
                            <img src="images/icons/logs.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="Hide this invoice and move it to pending list">
                            <img src="images/icons/disabled.png"/>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="vm" class="tab-pane fade">
            <p>VMs belong to invoices will be shown here.</p>
            <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
                <tbody>
                    <tr>
                        <th style="width: 10%;">Invoice ID</th>
                        <th style="width: 10%;">Items ID</th>
                        <th style="width: 10%;">Type</th>
                        <th style="width: 45%;">Description</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 5%;"></th>
                        <th style="width: 5%;"></th>
                    </tr>
                </tbody>
            </table>
            <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
                <tbody class="list-group">
                    <tr class="text-center">
                        <td style="width: 10%;"><a href="http://whmcs.vietnix.vn/admin/invoices.php?action=edit&id=48">#123</a></td>
                        <td style="width: 10%;">#64</td>
                        <td style="width: 10%;">VPS</td>
                        <td style="width: 45%;">1 CPU, 2GB RAM, 30GB HDD, Ubuntu 16.04, 1 IPv4</td>
                        <td style="width: 15%;">Pending</td>
                        <td style="width: 5%;">
                            <a title="Create VM">
                            <img src="images/icons/add.png"/>
                            </a>
                        </td>
                        <td style="width: 5%;">
                            <a title="Hide this VM and move it to pending list">
                            <img src="images/icons/disabled.png"/>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="pending" class="tab-pane fade">
            <div class="container">
                <p>Unusual or special VMs/Invoices will be hold here.</p>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                    </tbody>
                </table>
            </div>
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
