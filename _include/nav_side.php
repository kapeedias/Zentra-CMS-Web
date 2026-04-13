<nav class="sidebar">
    <div class="sidebar-header">
        <a href="/myaccount.php" class="sidebar-brand">
            <img src="assets/images/fleet-centra-logo-dark.png" class="img-responsive-brand">
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <!-- DASHBOARD -->
            <li class="nav-item">
                <a href="myaccount.php" class="nav-link">
                    <i class="link-icon" data-feather="box"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>

            <!-- JOBS -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#jobsMenu" role="button" aria-expanded="false"
                    aria-controls="jobsMenu">
                    <i class="link-icon" data-feather="briefcase"></i>
                    <span class="link-title">Jobs</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="jobsMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">All Jobs</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Pending Jobs</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Assigned Jobs</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Completed Jobs</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Create New Job</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Time Tracking</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Diagnostics / Notes</a></li>
                    </ul>
                </div>
            </li>

            <!-- MOBILE UNITS -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#unitsMenu" role="button" aria-expanded="false"
                    aria-controls="unitsMenu">
                    <i class="link-icon" data-feather="truck"></i>
                    <span class="link-title">Units / Vehicles</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="unitsMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Unit List & Status</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Assign to Jobs</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Unit Inventory</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Vehicle Location Tracker</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Maintenance Logs</a></li>
                    </ul>
                </div>
            </li>

            <!-- MECHANICS -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#mechanicsMenu" role="button" aria-expanded="false"
                    aria-controls="mechanicsMenu">
                    <i class="link-icon" data-feather="users"></i>
                    <span class="link-title">Mechanics / Teams</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="mechanicsMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Team Directory</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Assign Mechanics</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Skills & Certification</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Workload Overview</a></li>
                    </ul>
                </div>
            </li>

            <!-- CUSTOMERS -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#customersMenu" role="button" aria-expanded="false"
                    aria-controls="customersMenu">
                    <i class="link-icon" data-feather="user"></i>
                    <span class="link-title">Customers</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="customersMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Customer Directory</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Contacts</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Credit Info</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Customer History</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">VIP Alerts</a></li>
                    </ul>
                </div>
            </li>

            <!-- INVENTORY -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#inventoryMenu" role="button" aria-expanded="false"
                    aria-controls="inventoryMenu">
                    <i class="link-icon" data-feather="archive"></i>
                    <span class="link-title">Inventory / Parts</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="inventoryMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Central Inventory</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Vehicle Inventory</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Parts Used Per Job</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Replenishment Requests</a></li>
                    </ul>
                </div>
            </li>

            <!-- INVOICES -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#billingMenu" role="button" aria-expanded="false"
                    aria-controls="billingMenu">
                    <i class="link-icon" data-feather="dollar-sign"></i>
                    <span class="link-title">Invoices / Billing</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="billingMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Create Invoice</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Pending / Unpaid</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Paid Invoices</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Send Invoice</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Credit Tracking</a></li>
                    </ul>
                </div>
            </li>

            <!-- COMMUNICATION -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#communicationMenu" role="button" aria-expanded="false"
                    aria-controls="communicationMenu">
                    <i class="link-icon" data-feather="send"></i>
                    <span class="link-title">Communication</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="communicationMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Send Email / Work Order</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Approval Requests</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Customer Notifications</a></li>
                    </ul>
                </div>
            </li>

            <!-- REPORTS -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false"
                    aria-controls="reportsMenu">
                    <i class="link-icon" data-feather="bar-chart-2"></i>
                    <span class="link-title">Reports / Analytics</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="reportsMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Job Reports</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Revenue Reports</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Mechanic Performance</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Unit Utilization</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Inventory Usage</a></li>
                    </ul>
                </div>
            </li>

            <!-- SETTINGS -->
            <!--            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#settingsMenu" role="button" aria-expanded="false"
                    aria-controls="settingsMenu">
                    <i class="link-icon" data-feather="settings"></i>
                    <span class="link-title">Master Admin / Settings</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                 <div class="collapse" id="settingsMenu">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="#" class="nav-link">Roles & Permissions</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">System Settings</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Notifications</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Integrations</a></li>
                    </ul>
                </div>
                
            </li>-->
            <li class="nav-item">
                <a href="master-admin.php" class="nav-link">
                    <i class="link-icon" data-feather="settings"></i>
                    <span class="link-title">Master Admin / Settings</span>
                </a>
            </li>
            <!-- HELP / DOCUMENTATION -->
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="link-icon" data-feather="book-open"></i>
                    <span class="link-title">Documentation</span>
                </a>
            </li>
        </ul>
    </div>
</nav>