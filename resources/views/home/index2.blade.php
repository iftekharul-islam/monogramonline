
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{env("APPLICATION_NAME")}}</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/dashboardv2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/dashboardv2/css/lineicons.css" />
    <link rel="stylesheet" href="assets/dashboardv2/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="assets/dashboardv2/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/dashboardv2/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/dashboardv2/css/main.css" />
</head>
<body">
<!-- ======== sidebar-nav start =========== -->
<aside class="sidebar-nav-wrapper">
    <div class="navbar-logo">
        <a href="/">
            <img src="https://scontent.ftpa1-2.fna.fbcdn.net/v/t1.18169-9/29570818_2015947348433140_5731033133075923085_n.jpg?_nc_cat=111&ccb=1-5&_nc_sid=09cbfe&_nc_ohc=oHGf-Yhwa9UAX9nuSYv&_nc_ht=scontent.ftpa1-2.fna&oh=00_AT_Ir7Ixe9k1BGxSl3lnGxncKUtIj3BVQ1wQqcbC98hJsw&oe=6275E2DC" alt="logo" width="50" />
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_1"
                        aria-controls="ddmenu_1"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg width="22" height="22" viewBox="0 0 22 22">
                  <path
                          d="M17.4167 4.58333V6.41667H13.75V4.58333H17.4167ZM8.25 4.58333V10.0833H4.58333V4.58333H8.25ZM17.4167 11.9167V17.4167H13.75V11.9167H17.4167ZM8.25 15.5833V17.4167H4.58333V15.5833H8.25ZM19.25 2.75H11.9167V8.25H19.25V2.75ZM10.0833 2.75H2.75V11.9167H10.0833V2.75ZM19.25 10.0833H11.9167V19.25H19.25V10.0833ZM10.0833 13.75H2.75V19.25H10.0833V13.75Z"
                  />
                </svg>
              </span>
                    <span class="text">Dashboard</span>
                </a>
                <ul id="ddmenu_1" class="collapse show dropdown-nav">
                    <li>
                        <a href="" class="active"> Home </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_3"
                        aria-controls="ddmenu_3"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Inventory</span>
                </a>
                <ul id="ddmenu_3" class="collapse dropdown-nav">
                    <li>
                        <a href="/purchases/"> Purchase Orders </a>
                    </li>
                    <li>
                        <a href="/purchases/purchasedinvproducts/"> Purchase Products List </a>
                    </li>
                    <li>
                        <a href="/purchases/vendors/"> Vendors </a>
                    </li>
                    <li>
                        <a href="/inventories"> Inventory </a>
                    </li>
                    <li>
                        <a href="/inventory_admin/inventory_adjustments/"> Adjustments </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_4"
                        aria-controls="ddmenu_4"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Graphic</span>
                </a>
                <ul id="ddmenu_4" class="collapse dropdown-nav">
                    <li>
                        <a href="/preview_batch"> Preview Batches </a>
                    </li>
                    <li>
                        <a href="/prod_report/unbatchable"> Unbatchable Items </a>
                    </li>
                    <li>
                        <a href="/graphics"> Create Graphics </a>
                    </li>
                    <li>
                        <a href="/graphics/print_sublimation"> Print Sublimation </a>
                    </li>
                    <li>
                        <a href="/graphics/sent_to_printer"> Sent to Printer </a>
                    </li>
                    <li>
                        <a href="/summaries/print"> Print Batch Summaries </a>
                    </li>
                    <li>
                        <a href="/move_to_production"> Move to Production </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_5"
                        aria-controls="ddmenu_5"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text" style="font-size: large !important;">Customer Service</span>
                </a>
                <ul id="ddmenu_5" class="collapse dropdown-nav">
                    <li>
                        <a href="/orders/list/"> Orders </a>
                    </li>
                    <li>
                        <a href="/items"> Items List </a>
                    </li>
                    <li>
                        <a href="/items_graphic/"> Items List Graphic </a>
                    </li>
                    <li>
                        <a href="/customer_service/index/"> Customer Service Issues </a>
                    </li>
                    <li>
                        <a href="/customer_service/email_templates"> Email Templates </a>
                    </li>
                    <li>
                        <a href="/customer_service/bulk_email"> Send Bulk Emails </a>
                    </li>
                    <li>
                        <a href="/orders/manual"> Add new order manually </a>
                    </li>
                </ul>
            </li>

            <span class="divider"><hr /></span>
            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_6"
                        aria-controls="ddmenu_6"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Product Management</span>
                </a>
                <ul id="ddmenu_6" class="collapse dropdown-nav">
                    <li>
                        <a href="/logistics/sku_list"> Configure Child SKUs </a>
                    </li>
                    <li>
                        <a href="/logistics/create_child_sku"> Create Child SKU </a>
                    </li>
                    <li>
                        <a href="/products"> Products ( SKUs ) </a>
                    </li>
                    <li>
                        <a href="/products_specifications"> Product specification sheet </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_7"
                        aria-controls="ddmenu_7"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Production</span>
                </a>
                <ul id="ddmenu_7" class="collapse dropdown-nav">
                    <li>
                        <a href="/batches/list"> Batch List </a>
                    </li>
                    <li>
                        <a href="/picking/summary"> Inventory Summary </a>
                    </li>
                    <li>
                        <a href="/move_next"> Move Batches </a>
                    </li>
                    <li>
                        <a href="/production/status"> Production Stations </a>
                    </li>
                    <li>
                        <a href="/rejections"> Rejects </a>
                    </li>
                    <li>
                        <a href="/backorders"> Back Orders </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_8"
                        aria-controls="ddmenu_8"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Reports</span>
                </a>
                <ul id="ddmenu_8" class="collapse dropdown-nav">
                    <li>
                        <a href="prod_report/station_summary"> Stations summary </a>
                    </li>
                    <li>
                        <a href="/prod_report/summary"> Section Report </a>
                    </li>
                    <li>
                        <a href="/prod_report/summaryfilter"> Section Report Filter </a>
                    </li>
                    <li>
                        <a href="/prod_report/stockreport"> Stock Report </a>
                    </li>
                    <li>
                        <a href="/report/logs"> Station logs </a>
                    </li>
                    <li>
                        <a href="report/rejects"> Reject Report </a>
                    </li>
                    <li>
                        <a href="/report/ship_date"> Ship Date Report </a>
                    </li>
                    <li>
                        <a href="/report/items"> Order Items Report </a>
                    </li>
                    <li>
                        <a href="/prod_report/missing_report"> WAP Missing Items </a>
                    </li>
                    <li>
                        <a href="/report/sales"> Sales Summary </a>
                    </li>
                    <li>
                        <a href="/report/coupon"> Coupon Report </a>
                    </li>
                </ul>
            </li>

            <span class="divider"><hr /></span>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_9"
                        aria-controls="ddmenu_9"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Maintenance</span>
                </a>
                <ul id="ddmenu_9" class="collapse dropdown-nav">
                    <li>
                        <a href="/users"> Users </a>
                    </li>
                    <li>
                        <a href="/prod_config/sections"> Sections </a>
                    </li>
                    <li>
                        <a href="/prod_config/stations"> Stations </a>
                    </li>
                    <li>
                        <a href="/prod_config/templates"> Route Templates </a>
                    </li>
                    <li>
                        <a href="/prod_config/batch_routes"> Routes </a>
                    </li>
                    <li>
                        <a href="/prod_config/rejection_reasons"> Rejection reasons </a>
                    </li>
                    <li>
                        <a href="/logistics/parameters"> Parameters </a>
                    </li>
                    <li>
                        <a href="/products_config/production_categories"> Production Categories </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_10"
                        aria-controls="ddmenu_10"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text" style="font-size: large !important;">Shipping and WAP&nbsp;</span>
                </a>
                <ul id="ddmenu_10" class="collapse dropdown-nav">
                    <li>
                        <a href="/shipping/must_ship"> Must Ship Report </a>
                    </li>
                    <li>
                        <a href="/shipping/qc_station"> Quality Control </a>
                    </li>
                    <li>
                        <a href="/wap/index"> WAP </a>
                    </li>
                    <li>
                        <a href="/shipping"> Shipment List </a>
                    </li>
                    <li>
                        <a href="/shippingMainfest"> DHL Driver Manifest </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-item-has-children">
                <a
                        href="#0"
                        class="collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#ddmenu_2"
                        aria-controls="ddmenu_2"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                >
              <span class="icon">
                <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                          d="M12.8334 1.83325H5.50008C5.01385 1.83325 4.54754 2.02641 4.20372 2.37022C3.8599 2.71404 3.66675 3.18036 3.66675 3.66659V18.3333C3.66675 18.8195 3.8599 19.2858 4.20372 19.6296C4.54754 19.9734 5.01385 20.1666 5.50008 20.1666H16.5001C16.9863 20.1666 17.4526 19.9734 17.7964 19.6296C18.1403 19.2858 18.3334 18.8195 18.3334 18.3333V7.33325L12.8334 1.83325ZM16.5001 18.3333H5.50008V3.66659H11.9167V8.24992H16.5001V18.3333Z"
                  />
                </svg>
              </span>
                    <span class="text">Marketplace</span>
                </a>
                <ul id="ddmenu_2" class="collapse dropdown-nav">

                    <li>
                        <a href="/stores"> Manage Stores </a>
                    </li>
                    <li>
                        <a href="/transfer/import"> Import Orders </a>
                    </li>
                    <li>
                        <a href="/transfer/export"> Export Shipments </a>
                    </li>
                </ul>
            </li>


        </ul>
    </nav>

</aside>
<div class="overlay"></div>
<!-- ======== sidebar-nav end =========== -->

<!-- ======== main-wrapper start =========== -->
<main class="main-wrapper">
    <!-- ========== header start ========== -->
    <header class="header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-5 col-md-5 col-6">
                    <div class="header-left d-flex align-items-center">
                        <div class="menu-toggle-btn mr-20">
                            <button
                                    id="menu-toggle"
                                    class="main-btn primary-btn btn-hover"
                            >
                                <i class="lni lni-chevron-left me-2"></i> Menu
                            </button>
                        </div>
                        <div class="header-search d-none d-md-flex">
                            <form action="https://order.monogramonline.com/dashboard/search-order">
                                <input type="text" placeholder="Search order, or batch" name="search"/>
                                <button><i class="lni lni-search-alt"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-7 col-6">
                    <div class="header-right">
                        <!-- notification start -->
                        <div class="notification-box ml-15 d-none d-md-flex">
                            <button
                                    class="dropdown-toggle"
                                    type="button"
                                    id="notification"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                            >
                                <i class="lni lni-alarm"></i>
                                <span>1</span>
                            </button>
                            <ul
                                    class="dropdown-menu dropdown-menu-end"
                                    aria-labelledby="notification"
                            >
                                <li>
                                    <a href="#0">
                                        <div class="image">
                                            <img src="https://bestarion.com/wp-content/uploads/2021/03/Web-Developer.jpg" alt="" />
                                        </div>
                                        <div class="content">
                                            <h6>
                                                Andre - Developer
                                            </h6>
                                            <p>
                                                New homepage design
                                                Want old? click <a href="/?old=true" style="color: red">here</a>
                                            </p>
                                            <span>{{\Carbon\Carbon::createFromTimestamp(1649688896)->diffForHumans()}}</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- notification end -->



                        <!-- profile start -->
                        <div class="profile-box ml-15">
                        <div class="profile-box ml-15">
                            <button
                                    class="dropdown-toggle bg-transparent border-0"
                                    type="button"
                                    id="profile"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                            >
                                <div class="profile-info">
                                    <div class="info">
                                        <h6>{{ explode("@", auth()->user()->username)[0] ?? auth()->user()->username }}</h6>
                                        <div class="image">
                                            <img
                                                    src="https://scontent.ftpa1-2.fna.fbcdn.net/v/t1.18169-9/29570818_2015947348433140_5731033133075923085_n.jpg?_nc_cat=111&ccb=1-5&_nc_sid=09cbfe&_nc_ohc=oHGf-Yhwa9UAX9nuSYv&_nc_ht=scontent.ftpa1-2.fna&oh=00_AT_Ir7Ixe9k1BGxSl3lnGxncKUtIj3BVQ1wQqcbC98hJsw&oe=6275E2DC"
                                                    alt=""
                                            />
                                            <span class="status"></span>
                                        </div>
                                    </div>
                                </div>
                                <i class="lni lni-chevron-down"></i>
                            </button>
                            <ul
                                    class="dropdown-menu dropdown-menu-end"
                                    aria-labelledby="profile"
                            >


                                <li>
                                    <a href="{{url('logout')}}"> <i class="lni lni-exit"></i> Sign Out </a>
                                </li>
                            </ul>
                        </div>
                        <!-- profile end -->
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- ========== header end ========== -->

    <!-- ========== section start ========== -->
    <section class="section">
        <div class="container-fluid">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title mb-30">
                            <h2>Monogram Homepage</h2>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert-list-wrapper">

                            <!-- end col -->
                            @foreach($errors->all() as $error)
                            <div class="alert-box danger-alert">
                                <div class="alert">
                                        <p>
                                            {{ $error }}
                                        </p>
                                </div>
                            </div>
                        @endforeach
                            <!-- end alert-box -->
                        </div>
                    @endif



                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper mb-30">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="#0">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Home
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- ========== title-wrapper end ========== -->

            <div class="row">
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon purple">
                            <i class="lni lni-cart-full"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">New Orders</h6>
                            <h3 class="text-bold mb-10">{{$orders}}</h3>
                            <p class="text-sm text-success">
                                <i class="lni lni-arrow-up"></i> +2.00%
                                <span class="text-gray">(30 days)</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Cart -->
                </div>
                <!-- End Col -->
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon success">
                            <i class="lni lni-folder"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Archive Files</h6>
                            <h3 class="text-bold mb-10">{{$archive}}</h3>
                            <p class="texft-sm text-success">
                                <i class="lni lni-arrow-up"></i> +5.45%
                                <span class="text-gray">({{ $archiveSize }})</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Cart -->
                </div>
                <!-- End Col -->

                <!-- End Col -->
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon orange">
                            <i class="lni lni-user"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">New Orders</h6>
                            <h3 class="text-bold mb-10">{{$orders2}}</h3>
                            <p class="text-sm text-success">
                                <i class="lni lni-arrow-up"></i> +2.00%
                                <span class="text-gray">(today)</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Cart -->
                </div>


                <!-- End Col -->
            </div>

            <!-- End Row -->
            <div class="row">
                <div class="col-lg-5">
                    <div class="card-style calendar-card mb-30">
                        <div id="calendar-mini"></div>
                    </div>
                </div>
                <!-- End Col -->
                <div class="col-lg-7">
                    <div class="card-style mb-30">
                        <div
                                class="
                    title
                    d-flex
                    flex-wrap
                    align-items-center
                    justify-content-between
                  "
                        >
                            <div class="left">
                                <h6 class="text-medium mb-30">Recent Orders</h6>
                            </div>
                            <div class="right">
                                <div class="select-style-1">
                                    <div class="select-position select-sm">
                                        <select class="light-bg">
                                            <option value="">Today (Last 4 hours)</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- end select -->
                            </div>
                        </div>
                        <!-- End Title -->
                        <div class="table-responsive">
                            <table class="table top-selling-table">
                                <thead>
                                <tr>
                                    <th>
                                        <h6 class="text-sm text-medium">Product</h6>
                                    </th>
                                    <th class="min-width">
                                        <h6 class="text-sm text-medium">
                                            Order ID <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                    <th class="min-width">
                                        <h6 class="text-sm text-medium">
                                            Price <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                    <th class="min-width">
                                        <h6 class="text-sm text-medium">
                                            Status <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                    <th>
                                        <h6 class="text-sm text-medium text-end">
                                            Actions <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recentOrders as $order)
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="product">
                                                    <div class="image">
                                                        <img
                                                                src="{{ $item['item_thumb'] }}"
                                                                alt=""
                                                        />
                                                    </div>
                                                    <p class="text-sm">{{ $item['item_description'] }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm">{{ $order->id }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm">${{ $order->total }}</p>
                                            </td>

                                            <td>
                                                @if($order->order_status == 4)
                                                    <span class="status-btn secondary-btn btn-hover">TO BE PROCESSED</span>
                                                @else
                                                    @if($order->order_status == 15)
                                                        <span class="status-btn warning-btn btn-hover">INCOMPATIBLE HOLD</span>
                                                    @else
                                                        <span class="status-btn dark-btn btn-hover">{{\App\Order::getStatusFromOrder($order->order_status)}}</span>
                                                        @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action justify-content-end">
                                                    <button class="edit" onclick="window.open('http://order.monogramonline.com/orders/details/{{ $order->id }}', '_blank')">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                            <!-- End Table -->
                        </div>
                    </div>
                </div>
                <!-- End Col -->

                <!-- End Col -->
                <div class="col-lg-5">
                    <div class="card-style mb-30" style="margin-top: -300px !important;">
                        <div
                                class="
                    title
                    d-flex
                    flex-wrap
                    align-items-center
                    justify-content-between
                  "
                        >
                            <div class="left">
                                <h6 class="text-medium mb-30">Recently Ship Orders</h6>
                            </div>
                            <div class="right">
                                <div class="select-style-1">
                                    <div class="select-position select-sm">
                                        <select class="light-bg">
                                            <option value="">Today (Last 24 hours)</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- end select -->
                            </div>
                        </div>
                        <!-- End Title -->
                        <div class="table-responsive">
                            <table class="table top-selling-table">
                                <thead>
                                <tr>
                                    <th>
                                        <h6 class="text-sm text-medium">Product</h6>
                                    </th>
                                    <th class="min-width">
                                        <h6 class="text-sm text-medium">
                                            Order ID <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                    <th class="min-width">
                                        <h6 class="text-sm text-medium">
                                            Shipping Class <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>

                                    <th class="min-width">
                                        <h6 class="text-sm text-medium">
                                            Tracking <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                    <th>
                                        <h6 class="text-sm text-medium text-end">
                                            Actions <i class="lni lni-arrows-vertical"></i>
                                        </h6>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recentOrders2 as $order)
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="product">
                                                    <div class="image">
                                                        <img
                                                                src="{{ $item['item_thumb'] }}"
                                                                alt=""
                                                        />
                                                    </div>
                                                    <p class="text-sm">{{ $item['item_description'] }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm">{{ $order->order_number }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm">{{ $order->mail_class }}</p>
                                            </td>
                                            <td>
                                                @if($order->mail_class == "Priority" or $order->mail_class == "First")
                                                    <a class="text-sm" href="" onclick="window.open('https://tools.usps.com/go/TrackConfirmAction?tLabels={{ $order->tracking_number }}', '_blank')">{{ $order->tracking_number }}</a>
                                                @else
                                                    <p class="text-sm">{{ $order->tracking_number }}</p>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action justify-content-end">
                                                    <form action="https://order.monogramonline.com/dashboard/search-order" method="get">
                                                        <input type="hidden" name="search" value="{{ $order->order_number }}">
                                                    <button class="edit" type="submit">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                            <!-- End Table -->
                        </div>
                    </div>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
        </div>
        <!-- end container -->
    </section>
    <!-- ========== section end ========== -->

    <!-- ========== footer start =========== -->
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 order-last order-md-first">
                    <div class="copyright text-center text-md-start">
                    </div>
                </div>
                <!-- end col-->
                <div class="col-md-6">
                    <div
                            class="
                  terms
                  d-flex
                  justify-content-center justify-content-md-end
                "
                    >
                    </div>
                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </footer>
    <!-- ========== footer end =========== -->
</main>
<!-- ======== main-wrapper end =========== -->

<!-- ========= All Javascript files linkup ======== -->
<script src="assets/dashboardv2/js/bootstrap.bundle.min.js"></script>
<script src="assets/dashboardv2/js/Chart.min.js"></script>
<script src="assets/dashboardv2/js/dynamic-pie-chart.js"></script>
<script src="assets/dashboardv2/js/moment.min.js"></script>
<script src="assets/dashboardv2/js/fullcalendar.js"></script>
<script src="assets/dashboardv2/js/jvectormap.min.js"></script>
<script src="assets/dashboardv2/js/world-merc.js"></script>
<script src="assets/dashboardv2/js/polyfill.js"></script>
<script src="assets/dashboardv2/js/main.js"></script>

<script>
    // ======== jvectormap activation
    var markers = [
        { name: "Egypt", coords: [26.8206, 30.8025] },
        { name: "Russia", coords: [61.524, 105.3188] },
        { name: "Canada", coords: [56.1304, -106.3468] },
        { name: "Greenland", coords: [71.7069, -42.6043] },
        { name: "Brazil", coords: [-14.235, -51.9253] },
    ];

    var jvm = new jsVectorMap({
        map: "world_merc",
        selector: "#map",
        zoomButtons: true,

        regionStyle: {
            initial: {
                fill: "#d1d5db",
            },
        },

        labels: {
            markers: {
                render: (marker) => marker.name,
            },
        },

        markersSelectable: true,
        selectedMarkers: markers.map((marker, index) => {
            var name = marker.name;

            if (name === "Russia" || name === "Brazil") {
                return index;
            }
        }),
        markers: markers,
        markerStyle: {
            initial: { fill: "#4A6CF7" },
            selected: { fill: "#ff5050" },
        },
        markerLabelStyle: {
            initial: {
                fontWeight: 400,
                fontSize: 14,
            },
        },
    });
    // ====== calendar activation
    document.addEventListener("DOMContentLoaded", function () {
        var calendarMiniEl = document.getElementById("calendar-mini");
        var calendarMini = new FullCalendar.Calendar(calendarMiniEl, {
            initialView: "dayGridMonth",
            headerToolbar: {
                end: "today prev,next",
            },
        });
        calendarMini.render();
    });
</script>
</body>
</html>
