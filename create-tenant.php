<?php
// ==== CONFIG & DEPENDENCIES ====
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/classes/User.php';

secureSessionStart();
enforceSessionSecurity();



?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('_include/head.php'); ?>

<body>
  <div class="main-wrapper">

    <!-- partial:../../partials/_sidebar.html -->
    <nav class="sidebar">
      <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
          Noble<span>UI</span>
        </a>
        <div class="sidebar-toggler">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
      <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">
          <li class="nav-item nav-category">Main</li>
          <li class="nav-item">
            <a href="../../dashboard.html" class="nav-link">
              <i class="link-icon" data-lucide="box"></i>
              <span class="link-title">Dashboard</span>
            </a>
          </li>
          <li class="nav-item nav-category">web apps</li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#emails" role="button" aria-expanded="false"
              aria-controls="emails">
              <i class="link-icon" data-lucide="mail"></i>
              <span class="link-title">Email</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="emails">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/email/inbox.html" class="nav-link">Inbox</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/email/read.html" class="nav-link">Read</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/email/compose.html" class="nav-link">Compose</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a href="../../pages/apps/chat.html" class="nav-link">
              <i class="link-icon" data-lucide="message-square"></i>
              <span class="link-title">Chat</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="../../pages/apps/calendar.html" class="nav-link">
              <i class="link-icon" data-lucide="calendar"></i>
              <span class="link-title">Calendar</span>
            </a>
          </li>
          <li class="nav-item nav-category">Components</li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#uiComponents" role="button"
              aria-expanded="false" aria-controls="uiComponents">
              <i class="link-icon" data-lucide="feather"></i>
              <span class="link-title">UI Kit</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="uiComponents">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/ui-components/accordion.html" class="nav-link">Accordion</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/alerts.html" class="nav-link">Alerts</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/badges.html" class="nav-link">Badges</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/breadcrumbs.html"
                    class="nav-link">Breadcrumbs</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/buttons.html" class="nav-link">Buttons</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/button-group.html" class="nav-link">Button
                    group</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/cards.html" class="nav-link">Cards</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/carousel.html" class="nav-link">Carousel</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/collapse.html" class="nav-link">Collapse</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/dropdowns.html" class="nav-link">Dropdowns</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/list-group.html" class="nav-link">List group</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/media-object.html" class="nav-link">Media
                    object</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/modal.html" class="nav-link">Modal</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/navs.html" class="nav-link">Navs</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/offcanvas.html" class="nav-link">Offcanvas</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/pagination.html" class="nav-link">Pagination</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/placeholders.html"
                    class="nav-link">Placeholders</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/popover.html" class="nav-link">Popovers</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/progress.html" class="nav-link">Progress</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/scrollbar.html" class="nav-link">Scrollbar</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/scrollspy.html" class="nav-link">Scrollspy</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/spinners.html" class="nav-link">Spinners</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/tabs.html" class="nav-link">Tabs</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/toasts.html" class="nav-link">Toasts</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/ui-components/tooltips.html" class="nav-link">Tooltips</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#advancedUI" role="button"
              aria-expanded="false" aria-controls="advancedUI">
              <i class="link-icon" data-lucide="anchor"></i>
              <span class="link-title">Advanced UI</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="advancedUI">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/advanced-ui/cropper.html" class="nav-link">Cropper</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/advanced-ui/owl-carousel.html" class="nav-link">Owl
                    carousel</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/advanced-ui/sortablejs.html" class="nav-link">SortableJs</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/advanced-ui/sweet-alert.html" class="nav-link">Sweet Alert</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#forms" role="button" aria-expanded="false"
              aria-controls="forms">
              <i class="link-icon" data-lucide="inbox"></i>
              <span class="link-title">Forms</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="forms">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/forms/basic-elements.html" class="nav-link">Basic Elements</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/forms/advanced-elements.html" class="nav-link">Advanced
                    Elements</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/forms/editors.html" class="nav-link">Editors</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/forms/wizard.html" class="nav-link">Wizard</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#charts" role="button" aria-expanded="false"
              aria-controls="charts">
              <i class="link-icon" data-lucide="pie-chart"></i>
              <span class="link-title">Charts</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="charts">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/charts/apex.html" class="nav-link">Apex</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/charts/chartjs.html" class="nav-link">ChartJs</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/charts/flot.html" class="nav-link">Flot</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/charts/peity.html" class="nav-link">Peity</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/charts/sparkline.html" class="nav-link">Sparkline</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#tables" role="button" aria-expanded="false"
              aria-controls="tables">
              <i class="link-icon" data-lucide="layout"></i>
              <span class="link-title">Table</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="tables">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/tables/basic-table.html" class="nav-link">Basic Tables</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/tables/data-table.html" class="nav-link">Data Table</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#icons" role="button" aria-expanded="false"
              aria-controls="icons">
              <i class="link-icon" data-lucide="smile"></i>
              <span class="link-title">Icons</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="icons">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/icons/lucide-icons.html" class="nav-link">Lucide Icons</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/icons/flag-icons.html" class="nav-link">Flag Icons</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/icons/mdi-icons.html" class="nav-link">Mdi Icons</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item nav-category">Pages</li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#general-pages" role="button"
              aria-expanded="false" aria-controls="general-pages">
              <i class="link-icon" data-lucide="book"></i>
              <span class="link-title">Special pages</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="general-pages">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/general/blank-page.html" class="nav-link">Blank page</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/general/faq.html" class="nav-link">Faq</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/general/invoice.html" class="nav-link">Invoice</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/general/profile.html" class="nav-link">Profile</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/general/pricing.html" class="nav-link">Pricing</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/general/timeline.html" class="nav-link">Timeline</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#authPages" role="button"
              aria-expanded="false" aria-controls="authPages">
              <i class="link-icon" data-lucide="unlock"></i>
              <span class="link-title">Authentication</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="authPages">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/auth/login.html" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/auth/register.html" class="nav-link">Register</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/auth/forgot-password.html" class="nav-link">Forgot Password</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#errorPages" role="button"
              aria-expanded="false" aria-controls="errorPages">
              <i class="link-icon" data-lucide="cloud-off"></i>
              <span class="link-title">Error</span>
              <i class="link-arrow" data-lucide="chevron-down"></i>
            </a>
            <div class="collapse" data-bs-parent="#sidebarNav" id="errorPages">
              <ul class="nav sub-menu">
                <li class="nav-item">
                  <a href="../../pages/error/404.html" class="nav-link">404</a>
                </li>
                <li class="nav-item">
                  <a href="../../pages/error/500.html" class="nav-link">500</a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item nav-category">Docs</li>
          <li class="nav-item">
            <a href="https://nobleui.com/html/documentation/docs.html" target="_blank" class="nav-link">
              <i class="link-icon" data-lucide="hash"></i>
              <span class="link-title">Documentation</span>
            </a>
          </li>
        </ul>
      </div>
    </nav>
    <!-- partial -->

    <div class="page-wrapper">

      <!-- Start Side Navigation -->
      <?php require_once('_include/nav_side.php'); ?>
      <!-- End Side Navigation -->

      <div class="page-content container-xxl">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Tenant</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Tenant</li>
          </ol>
        </nav>
        <div class="row">
          <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">

                <h6 class="card-title">Create Tenant</h6>

                <form class="forms-sample">

                  <div class="row mb-3">
                    <label for="tenant_id" class="col-sm-4 col-form-label">Tenant Id</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="tenant_id" name="tenant_id"
                        placeholder="Tenant Id">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="tenant_name" class="col-sm-4 col-form-label">Tenant Name</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="tenant_name" name="tenant_name"
                        placeholder="Tenant Name">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="tenant_code" class="col-sm-4 col-form-label">Tenant Code</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="tenant_code" name="tenant_code"
                        placeholder="Tenant Code">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="industry" class="col-sm-4 col-form-label">Industry</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="industry" name="industry"
                        placeholder="Industry">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="owner_user_id" class="col-sm-4 col-form-label">Owner User Id</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="owner_user_id"
                        name="owner_user_id" placeholder="Owner User Id">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="contact_email" class="col-sm-4 col-form-label">Contact Email</label>
                    <div class="col-sm-8">
                      <input type="email" class="form-control" id="contact_email"
                        name="contact_email" placeholder="Contact Email">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="contact_phone" class="col-sm-4 col-form-label">Contact Phone</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="contact_phone"
                        name="contact_phone" placeholder="Contact Phone">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="address_line1" class="col-sm-4 col-form-label">Address Line1</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="address_line1"
                        name="address_line1" placeholder="Address Line1">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="address_line2" class="col-sm-4 col-form-label">Address Line2</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="address_line2"
                        name="address_line2" placeholder="Address Line2">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="city" class="col-sm-4 col-form-label">City</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="city" name="city"
                        placeholder="City">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="province" class="col-sm-4 col-form-label">Province</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="province" name="province"
                        placeholder="Province">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="postal_code" class="col-sm-4 col-form-label">Postal Code</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="postal_code" name="postal_code"
                        placeholder="Postal Code">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="country" class="col-sm-4 col-form-label">Country</label>
                    <div class="col-sm-8">

                      <label class="form-label">Basic</label>
                      <div id="the-basics" class="d-flex flex-column">
                        <span class="twitter-typeahead"
                          style="position: relative; display: inline-block;"><input
                            class="typeahead tt-hint" autocomplete="off" type="text"
                            readonly="" spellcheck="false" tabindex="-1" dir="ltr"
                            style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);"><input
                            class="typeahead tt-input" autocomplete="off" type="text"
                            placeholder="States of USA" spellcheck="false" dir="auto"
                            style="position: relative; vertical-align: top; background-color: transparent;">
                          <pre aria-hidden="true"
                            style="position: absolute; visibility: hidden; white-space: pre; font-family: Roboto, Helvetica, sans-serif; font-size: 14px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;">Alaska</pre>
                          <div class="tt-menu"
                            style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
                            <div class="tt-dataset tt-dataset-states">
                              <div class="tt-suggestion tt-selectable"><strong
                                  class="tt-highlight">Alaska</strong></div>
                            </div>
                          </div>
                        </span>
                      </div>

                      <input type="text" class="form-control" id="country" name="country"
                        placeholder="Country">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="currency_code" class="col-sm-4 col-form-label">Currency Code</label>
                    <div class="col-sm-8">
                      <select class="form-select form-select-sm mb-3" id="currency_code"
                        name="currency_code" required>
                        <option selected="">Open this select menu</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                      </select>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="logo_url" class="col-sm-4 col-form-label">Logo Url</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="logo_url" name="logo_url"
                        placeholder="Logo Url">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="primary_color" class="col-sm-4 col-form-label">Primary Color</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="primary_color"
                        name="primary_color" placeholder="Primary Color">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="secondary_color" class="col-sm-4 col-form-label">Secondary
                      Color</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="secondary_color"
                        name="secondary_color" placeholder="Secondary Color">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="custom_domain" class="col-sm-4 col-form-label">Custom Domain</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="custom_domain"
                        name="custom_domain" placeholder="Custom Domain">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="subdomain" class="col-sm-4 col-form-label">Subdomain</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="subdomain" name="subdomain"
                        placeholder="Subdomain">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="subscription_plan" class="col-sm-4 col-form-label">Subscription
                      Plan</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="subscription_plan"
                        name="subscription_plan" placeholder="Subscription Plan">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="subscription_status" class="col-sm-4 col-form-label">Subscription
                      Status</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" id="subscription_status"
                        name="subscription_status" placeholder="Subscription Status">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="subscription_start" class="col-sm-4 col-form-label">Subscription
                      Start</label>
                    <div class="col-sm-8">
                      <input type="date" class="form-control" id="subscription_start"
                        name="subscription_start" placeholder="Subscription Start">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="trial_start" class="col-sm-4 col-form-label">Trial Start</label>
                    <div class="col-sm-8">
                      <input type="date" class="form-control" id="trial_start" name="trial_start"
                        placeholder="Trial Start">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="trial_end" class="col-sm-4 col-form-label">Trial End</label>
                    <div class="col-sm-8">
                      <input type="date" class="form-control" id="trial_end" name="trial_end"
                        placeholder="Trial End">
                    </div>
                  </div>




                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">
                      Remember me
                    </label>
                  </div>
                  <button type="submit" class="btn btn-primary me-2">Submit</button>
                  <button class="btn btn-secondary">Cancel</button>
                </form>

              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- partial:../../partials/_footer.html -->
      <footer
        class="footer d-flex flex-row align-items-center justify-content-between px-4 py-3 border-top small">
        <p class="text-secondary mb-1 mb-md-0">Copyright © 2026 <a href="https://nobleui.com"
            target="_blank">NobleUI</a>.</p>
        <p class="text-secondary">Handcrafted With <i class="mb-1 text-primary ms-1 icon-sm"
            data-lucide="heart"></i></p>
      </footer>
      <!-- partial -->

    </div>
  </div>

  <!-- core:js -->
  <script src="../../../assets/vendors/core/core.js"></script>
  <!-- endinject -->

  <!-- Plugin js for this page -->
  <!-- End plugin js for this page -->

  <!-- inject:js -->
  <script src="../../../assets/js/app.js"></script>
  <!-- endinject -->

  <!-- Custom js for this page -->
  <!-- End custom js for this page -->
</body>

</html>