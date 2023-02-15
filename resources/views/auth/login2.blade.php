<html lang="en"><head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Monogram</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/dashboardv2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/dashboardv2/css/lineicons.css">
    <link rel="stylesheet" href="assets/dashboardv2/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/dashboardv2/css/fullcalendar.css">
    <link rel="stylesheet" href="assets/dashboardv2/css/main.css">
</head>
<body>
<!-- ======== sidebar-nav start =========== -->

<div class="overlay"></div>
<!-- ======== sidebar-nav end =========== -->

<!-- ======== main-wrapper start =========== -->
<main class="main-wrapper">
    <!-- ========== header start ========== -->
    <header class="header">
        <div class="container-fluid">

        </div>
    </header>
    <!-- ========== header end ========== -->

    <!-- ========== signin-section start ========== -->
    <section class="signin-section">
        <div class="container-fluid">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">


                    <!-- end col -->
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper mb-30">

                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- ========== title-wrapper end ========== -->

            <div class="row g-0 auth-row">
                <div class="col-lg-6">
                    <div class="auth-cover-wrapper bg-primary-100">
                        <div class="auth-cover">
                            <div class="title text-center">
{{--                                <h1 class="text-primary mb-10">Monogram <span style="font-weight: bolder">(5P)</span></h1>--}}
                                <h1 class="text-primary mb-10">Welcome Back</h1>
                                <p class="text-medium">
                                    Sign in to your account to gain access
                                </p>
                            </div>
                            <div class="cover-image">
                                <img src="assets/dashboardv2/images/auth/signin-image.svg" alt="">
                            </div>
                            <div class="shape-image">
                                <img src="assets/dashboardv2/images/auth/shape.svg" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-6">
                    <div class="signin-wrapper">
                        <div class="form-wrapper">
                            <h6 class="mb-15">Sign in</h6>
                            <p class="text-sm mb-25">
                                If you don't remember your credentials or
                                don't have access, contact the IT or an administrator
                            </p>
                            <form action="/login" method="post">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label>Email</label>
                                            <input type="email" placeholder="Email" name="email">
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label>Password</label>
                                            <input type="password" placeholder="Password" name="password">

                                        </div>
                                    </div>

                                {!! csrf_field() !!}

                                    <!-- end col -->
                                    <div class="col-xxl-6 col-lg-12 col-md-6">
                                        <div class="form-check checkbox-style mb-30">
                                            <input class="form-check-input" type="checkbox" value="" id="checkbox-remember" name="remember">
                                            <label class="form-check-label" for="checkbox-remember">
                                                Remember me next time</label>
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

                                    <!-- end col -->
                                    <div class="col-xxl-6 col-lg-12 col-md-6">
                                        <div class="
                            text-start text-md-end text-lg-start text-xxl-end
                            mb-30
                          ">
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="
                            button-group
                            d-flex
                            justify-content-center
                            flex-wrap
                          ">
                                            <button class="
                              main-btn
                              primary-btn
                              btn-hover
                              w-100
                              text-center
                            ">
                                                Sign In
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->
                            </form>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
    </section>
    <!-- ========== signin-section end ========== -->

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
                    <div class="
                  terms
                  d-flex
                  justify-content-center justify-content-md-end
                ">

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


</body></html>