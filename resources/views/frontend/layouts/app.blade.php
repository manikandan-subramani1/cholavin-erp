<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="title"
      content="Cholavin - Your rice expert - Best Rice Shop in Pallipalayam & Kumarapalayam"
    />
    <meta
      name="description"
      content="Buy premium quality rice online in Pallipalayam & Kumarapalayam. Doorstep delivery, biriyani rice, daily rice, and easy return policy."
    />
    <meta
      name="keywords"
      content="rice shop Pallipalayam, rice shop Kumarapalayam, biriyani rice, basmati rice, rice delivery near me"
    />
    <title>@yield('title', 'Cholavin - Your rice expert')</title>

    <!--=====FAB ICON=======-->
    <link rel="shortcut icon" href="{{ asset('frontend/assets/') }}/img/logo/favicon.png" type="image/x-icon" />

    <!--===== CSS LINK =======-->
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/aos.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/fontawesome.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/magnific-popup.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/owlcarousel.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/slick-slider.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/nice-select.css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/plugins/swiper.min.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
    />
    <link rel="stylesheet" href="{{ asset('frontend/assets/') }}/css/main.css" />
    <style>
      .dynamic-home-products { background: #fff8e6; }
      .dynamic-product-card { height: 100%; background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 40px rgba(94, 0, 27, .08); transform: translateY(20px); opacity: 0; animation: productRise .55s ease forwards; transition: transform .3s ease, box-shadow .3s ease; }
      .dynamic-product-card:hover { transform: translateY(-8px); box-shadow: 0 24px 55px rgba(94, 0, 27, .16); }
      .product-media { position: relative; aspect-ratio: 4 / 3; background: #fff8e6; overflow: hidden; }
      .product-media img { width: 100%; height: 100%; object-fit: contain; padding: 28px; transition: transform .45s ease; }
      .dynamic-product-card:hover .product-media img { transform: scale(1.06) rotate(1deg); }
      .price-chip { position: absolute; left: 18px; bottom: 18px; background: #8f0028; color: #f4c430; padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 800; }
      .product-info { padding: 24px; }
      .product-kicker { display: inline-block; color: #8f0028; font-weight: 800; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 8px; }
      .product-info h3 { font-size: 22px; line-height: 1.25; color: #5e001b; margin-bottom: 8px; }
      .product-info .sub-title { color: #b88719; font-weight: 700; margin-bottom: 8px; }
      .product-info p { color: #665; font-size: 14px; line-height: 1.7; }
      .product-enquiry-btn { display: inline-flex; align-items: center; gap: 8px; margin-top: 12px; color: #8f0028; font-weight: 800; }
      @keyframes productRise { to { transform: translateY(0); opacity: 1; } }
    </style>
    @stack('styles')

    <!--=====  JS SCRIPT LINK =======-->
    <script src="{{ asset('frontend/assets/') }}/js/plugins/jquery-3-7-1.min.js"></script>
  </head>
<body class="@yield('body_class')">
    <!--===== PRELODER STARTS=======-->
    <div class="preloader">
      <div class="loading-container">
        <div class="loading"></div>
        <div id="loading-icon">
          <img src="{{ asset('frontend/assets/') }}/img/logo/cholavin.png" alt="Cholovin logo" />
        </div>
        <span class="loading-shape shape-one"></span>
        <span class="loading-shape shape-two"></span>
        <span class="loading-shape shape-three"></span>
      </div>
      <div class="loading-copy">
        <span class="loading-label">Loading</span>
        <h2>Cholavin</h2>
      </div>
    </div>
    <!--===== PRELODER ENDS=======-->

    <!--===== PROGRESS STARTS=======-->
    <div class="paginacontainer">
      <div class="progress-wrap">
        <svg
          class="progress-circle svg-content"
          width="100%"
          height="100%"
          viewBox="-1 -1 102 102"
        >
          <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
      </div>
    </div>
    <!--===== PROGRESS ENDS=======-->

    <!--===== MOBILE HEADER STARTS =======-->
    <div class="homepage1-body">
      <div class="vl-offcanvas">
        <div class="vl-offcanvas-wrapper">
          <div
            class="vl-offcanvas-header d-flex justify-content-between align-items-center mb-90"
          >
            <div class="vl-offcanvas-logo">
              <a href="{{ route('frontend.home') }}"
                ><img src="{{ asset('frontend/assets/') }}/img/logo/logo-hm62.png" alt=""
              /></a>
            </div>
            <div class="vl-offcanvas-close">
              <button class="vl-offcanvas-close-toggle">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>

          <div class="vl-offcanvas-menu d-xl-none mb-40">
            <nav></nav>
          </div>

          <div class="space20"></div>
          <div class="vl-offcanvas-info">
            <h3 class="vl-offcanvas-sm-title">Contact Us</h3>
            <div class="space20"></div>
            <span
              ><a href="#">
                <i class="fa-regular fa-envelope"></i> WhatsApp Orders
                Available</a
              ></span
            >
            <span
              ><a href="tel:+9965252555"
                ><i class="fa-solid fa-phone"></i> +91 99652 52555</a
              ></span
            >
            <span
              ><a href="#"
                ><i class="fa-solid fa-location-dot"></i> Cholavin - Your rice expert,
                Pallipalayam & Kumarapalayam</a
              ></span
            >
          </div>
          <div class="space20"></div>
          <div class="vl-offcanvas-social">
            <h3 class="vl-offcanvas-sm-title">Follow Us</h3>
            <div class="space20"></div>
            <a
              href="https://www.facebook.com/cholavinrice"
              target="_blank"
              rel="noopener"
              ><i class="fab fa-facebook-f"></i
            ></a>
            <!-- <a href="#"><i class="fab fa-twitter"></i></a> -->
            <!-- <a href="#"><i class="fab fa-linkedin-in"></i></a> -->
            <a
              href="https://www.instagram.com/cholavin_"
              target="_blank"
              rel="noopener"
              ><i class="fab fa-instagram"></i
            ></a>
          </div>
        </div>
      </div>
      <div class="vl-offcanvas-overlay"></div>
    </div>
    <!--===== MOBILE HEADER STARTS =======-->

    <!--=====HEADER START=======-->
    <header class="homepage6-body">
      <div class="vl-header-area vl-transparent-header" id="vl-header-sticky">
        <div class="header-top-area header6-top-bg d-lg-block d-none">
          <div class="container">
            <div class="row">
              <div class="col-xl-12">
                <div class="header-top-main">
                  <ul class="header-location">
                    <li>
                      <a class="clr-white" href="#"
                        >Rice shop in Pallipalayam & Kumarapalayam with fast
                        doorstep delivery. <span>Order Today</span></a
                      >
                    </li>
                  </ul>
                  <div class="header-phn-area">
                    <a class="header-phn-mail clr-white" href="#"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 20 20"
                        fill="none"
                      >
                        <path
                          d="M10 1.875C5.5125 1.875 1.875 5.5125 1.875 10C1.875 11.4354 2.25729 12.7854 2.92708 13.9479L1.875 18.125L6.17708 17.099C7.30208 17.724 8.60938 18.125 10 18.125C14.4875 18.125 18.125 14.4875 18.125 10C18.125 5.5125 14.4875 1.875 10 1.875ZM7.34375 6.5625C7.52083 6.5625 7.70313 6.5625 7.86458 6.57292C8.04167 6.58333 8.23958 6.59896 8.42708 7.02083C8.64583 7.51563 9.11458 8.67708 9.17708 8.80208C9.23958 8.92708 9.28125 9.07292 9.19792 9.23958C9.11458 9.40625 9.07292 9.51042 8.94792 9.65625C8.82292 9.80208 8.6875 9.97917 8.57292 10.0833C8.44792 10.2083 8.31771 10.3438 8.46354 10.5938C8.60938 10.8438 9.10417 11.6615 9.84896 12.3229C10.8021 13.1667 11.6042 13.4167 11.8542 13.5417C12.1042 13.6667 12.25 13.6458 12.3958 13.4792C12.5417 13.3125 13.0156 12.7604 13.1771 12.5104C13.3385 12.2604 13.5 12.3021 13.7292 12.3854C13.9583 12.4688 15.1146 13.0573 15.3646 13.1823C15.6146 13.3073 15.776 13.3698 15.8385 13.4688C15.901 13.5677 15.901 14.0365 15.7083 14.5885C15.5156 15.1406 14.5625 15.6615 14.1198 15.7083C13.6771 15.7552 13.2552 15.9219 11.3229 15.1667C8.98958 14.2604 7.47396 11.901 7.34896 11.7344C7.22396 11.5677 6.35417 10.401 6.35417 9.19271C6.35417 7.98438 7.00521 7.38021 7.23438 7.13021C7.46354 6.88021 7.73438 6.82292 7.89583 6.82292L7.34375 6.5625Z"
                          fill="#F4C430"
                        />
                      </svg>WhatsApp: +91 99652 52555</a
                    >
                    <span class="header-top-line">|</span>
                    <a href="#" class="header-time header6-time"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                      >
                        <path
                          d="M12 3C7.03125 3 3 7.03125 3 12C3 16.9688 7.03125 21 12 21C16.9688 21 21 16.9688 21 12C21 7.03125 16.9688 3 12 3Z"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-miterlimit="10"
                        />
                        <path
                          d="M12 6V12.75H16.5"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      All Days 9:00 AM - 9:00 PM
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="vl-header-content-area white-bg">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-xl-2 col-md-6 col-6">
                <div class="vl-logo">
                  <a href="{{ route('frontend.home') }}"
                    ><img src="{{ asset('frontend/assets/') }}/img/logo/logo-hm62.png" alt=""
                  /></a>
                </div>
              </div>
              <div class="col-xl-7 d-none d-xl-block">
                <div class="vl-main-menu text-center">
                  <nav class="vl-mobile-menu-active vl-menu-hm6-fxr">
                    <ul>
                      <li><a href="{{ route('frontend.home') }}">Home</a></li>
                      <li><a href="{{ route('frontend.about') }}">About Us</a></li>
                      <li><a href="{{ route('frontend.products') }}">Products</a></li>
                      <li><a href="{{ route('frontend.delivery') }}">Delivery</a></li>
                      <li><a href="{{ route('frontend.contact') }}">Contact Us</a></li>
                    </ul>
                    </nav>
                </div>
              </div>
              <div class="col-xl-3 col-md-6 col-6">
                <div class="vl-menu-sidebar-area">
                  <div class="header-translate">
                    <button
                      class="translate-toggle"
                      type="button"
                      aria-label="Select language"
                      aria-expanded="false"
                    >
                      <span class="translate-toggle-icon">
                        <i class="fa-solid fa-language"></i>
                      </span>
                      <span class="translate-toggle-text">EN</span>
                      <span class="translate-toggle-caret">
                        <i class="fa-solid fa-angle-down"></i>
                      </span>
                    </button>
                    <div class="translate-dropdown" role="menu">
                      <button
                        class="translate-option is-active"
                        type="button"
                        data-lang="en"
                      >
                        English
                      </button>
                      <button
                        class="translate-option"
                        type="button"
                        data-lang="ta"
                      >
                        Tamil
                      </button>
                    </div>
                  </div>
                  <div class="menu-line">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="1"
                      height="25"
                      viewBox="0 0 1 25"
                      fill="none"
                    >
                      <path
                        d="M0.5 0.5L0.499999 24.5"
                        stroke="#8F0028"
                        stroke-opacity="0.3"
                        stroke-linecap="round"
                      />
                    </svg>
                  </div>
                  <div class="vl-header-btn d-none d-xl-block text-end">
                    <div
                      class="btn_area3 aos-init aos-animate"
                      data-aos="fade-left"
                      data-aos-duration="900"
                    >
                      <a href="{{ route('frontend.contact') }}" class="vl-btnhm2"
                        >Enquire
                        <span class="arrow_btn3"
                          ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="32"
                            height="32"
                            viewBox="0 0 32 32"
                            fill="none"
                          >
                            <path
                              d="M27.002 16.002H5.00195"
                              stroke="#3B0010"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            ></path>
                            <path
                              d="M21.0021 22.002C21.0021 22.002 27.002 17.5831 27.002 16.002C27.002 14.4208 21.002 10.002 21.002 10.002"
                              stroke="#3B0010"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            ></path></svg></span
                      ></a>
                    </div>
                  </div>
                  <div class="vl-header-action-item d-block d-xl-none">
                    <button type="button" class="vl-offcanvas-toggle">
                      <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
    @yield('content')
<!--===== footer area start =======-->
        <div class="vl-footer1-area vl-footer6-area">
      <div class="container">
        <div class="row">
          <div class="vl-footer1-top">
            <div class="row align-items-center">
              <div class="col-xl-3 col-lg-3 col-md-6">
                <div class="footer1-mobile">
                  <div class="icons">
                    <a href="#"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                      >
                        <path
                          d="M13.6177 21.367C13.1841 21.773 12.6044 22 12.0011 22C11.3978 22 10.8182 21.773 10.3845 21.367C6.41302 17.626 1.09076 13.4469 3.68627 7.37966C5.08963 4.09916 8.45834 2 12.0011 2C15.5439 2 18.9126 4.09916 20.316 7.37966C22.9082 13.4393 17.599 17.6389 13.6177 21.367Z"
                          stroke="white"
                          stroke-width="1.5"
                        ></path>
                        <path
                          d="M15.5 11C15.5 12.933 13.933 14.5 12 14.5C10.067 14.5 8.5 12.933 8.5 11C8.5 9.067 10.067 7.5 12 7.5C13.933 7.5 15.5 9.067 15.5 11Z"
                          stroke="white"
                          stroke-width="1.5"
                        ></path></svg
                      >Pallipalayam Branch</a
                    >
                  </div>
                  <div class="space24"></div>
                  <ul> 
                   <li>
                      <a href="#">
                        No. 19, Bypass Road, Pallipalayam, Namakkal - 638006
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-xl-3 col-lg-3 col-md-6">
                <div class="footer1-mobile">
                  <div class="icons">
                    <a href="#"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                      >
                        <path
                          d="M13.6177 21.367C13.1841 21.773 12.6044 22 12.0011 22C11.3978 22 10.8182 21.773 10.3845 21.367C6.41302 17.626 1.09076 13.4469 3.68627 7.37966C5.08963 4.09916 8.45834 2 12.0011 2C15.5439 2 18.9126 4.09916 20.316 7.37966C22.9082 13.4393 17.599 17.6389 13.6177 21.367Z"
                          stroke="white"
                          stroke-width="1.5"
                        ></path>
                        <path
                          d="M15.5 11C15.5 12.933 13.933 14.5 12 14.5C10.067 14.5 8.5 12.933 8.5 11C8.5 9.067 10.067 7.5 12 7.5C13.933 7.5 15.5 9.067 15.5 11Z"
                          stroke="white"
                          stroke-width="1.5"
                        ></path></svg
                      >Kumarapalayam Branch</a
                    >
                  </div>
                  <div class="space24"></div>
                  <ul> 
                   <li>
                      <a href="#">
                        No. 66, New Pallipalayam Road, Opposite Gowri Theatre,<br> Namakkal - 638183
                      </a>
                    </li>
                  </ul>
                </div>
              </div> 
              <div class="col-xl-3 col-lg-3 col-md-6">
                <div class="footer1-mobile lg-mt20">
                  <div class="icons">
                    <a href="#"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                      >
                        <path
                          d="M14 3V6M19 5L17 7M21 10H18"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        ></path>
                        <path
                          d="M9.15825 5.71223L8.7556 4.80625C8.49232 4.21388 8.36068 3.91768 8.1638 3.69101C7.91707 3.40694 7.59547 3.19794 7.23567 3.08785C6.94858 3 6.62446 3 5.97621 3C5.02791 3 4.55375 3 4.15573 3.18229C3.68687 3.39702 3.26343 3.86328 3.09473 4.3506C2.95151 4.76429 2.99253 5.18943 3.07458 6.0397C3.94791 15.0902 8.90981 20.0521 17.9603 20.9254C18.8106 21.0075 19.2357 21.0485 19.6494 20.9053C20.1367 20.7366 20.603 20.3131 20.8177 19.8443C21 19.4462 21 18.9721 21 18.0238C21 17.3755 21 17.0514 20.9122 16.7643C20.8021 16.4045 20.5931 16.0829 20.309 15.8362C20.0823 15.6393 19.7861 15.5077 19.1937 15.2444L18.2878 14.8417C17.6462 14.5566 17.3255 14.4141 16.9995 14.3831C16.6876 14.3534 16.3731 14.3972 16.0811 14.5109C15.776 14.6297 15.5063 14.8544 14.967 15.3038C14.4301 15.7512 14.1617 15.9749 13.8337 16.0947C13.543 16.2009 13.1586 16.2403 12.8523 16.1951C12.5069 16.1442 12.2423 16.0029 11.7133 15.7201C10.0672 14.8405 9.15953 13.9328 8.27986 12.2867C7.99714 11.7577 7.85578 11.4931 7.80487 11.1477C7.75974 10.8414 7.79908 10.457 7.9053 10.1663C8.02512 9.83828 8.24881 9.56986 8.69619 9.033C9.14562 8.49368 9.37034 8.22402 9.48915 7.91891C9.60285 7.62694 9.64661 7.3124 9.61694 7.00048C9.58594 6.67452 9.44338 6.35376 9.15825 5.71223Z"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                        ></path></svg
                      >Working Hours
                    </a>
                  </div>
                  <div class="space24"></div>
                  <ul>
                    <li>
                      <a href="#">All Days 9:00 AM - 9:00 PM</a>
                    </li>
                    <li><a href="#">Sunday: Open</a></li>
                  </ul>
                </div>
              </div>
              <div class="col-xl-3 col-lg-3 col-md-6">
                <div class="space30 d-xl-none d-block"></div>
                <div class="footer1-mobile footer1-mobile-fixxer lg-mt20">
                  <a
                    href="https://wa.me/9965252555?text=Hello%20Cholavin%20Your%20Rice%20Expert%2C%20I%20want%20to%20place%20an%20order."
                    class="btn4-home6"
                    target="_blank"
                    rel="noopener"
                    >Place Your Order Here</a
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="vl-footer1-info">
            <div class="row">
              <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="footer1-logo-area">
                  <img src="{{ asset('frontend/assets/') }}/img/logo/logo-hm62.png" alt="" />
                  <div class="space24"></div>
                  <p>
                    Cholavin - Your rice expert is your trusted source for premium rice in Pallipalayam & Kumarapalayam. We deliver quality rice varieties straight to your doorstep.
                  </p>
                  <div class="space28"></div>
                  <div class="footer1-subscribe">
                    <form action="#">
                      <input type="email" placeholder="Email" />
                      <div class="sub-btn">
                        <button type="submit" class="btn4-home6">
                          Subscribe
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div class="col-xl col-lg-4 col-md-6">
                <div class="space30 d-md-none d-block"></div>
                <div class="footer-widget-area wid1-fix mt-20xs_brk">
                  <h3>Top Links</h3>
                  <div class="space28"></div>
                  <ul>
                    <li><a href="{{ route('frontend.home') }}">Home</a></li>
                    <li><a href="{{ route('frontend.about') }}">About Us</a></li>
                    <li><a href="{{ route('frontend.products') }}">Products</a></li>
                    <li><a href="{{ route('frontend.delivery') }}">Delivery</a></li>
                    <li><a href="{{ route('frontend.contact') }}">Contact Us</a></li>
                  </ul>
                </div>
              </div>
              <div class="col-xl col-lg-4 col-md-6">
                <div class="space30 d-md-none d-block"></div>
                <div class="footer-widget-area">
                  <h3>Services</h3>
                  <div class="space28"></div>
                  <ul>
                    <li><a href="{{ route('frontend.products') }}">Daily Rice Supply</a></li>
                    <li><a href="{{ route('frontend.products') }}">Premium Quality Rice</a></li>
                    <li><a href="{{ route('frontend.contact') }}">Bulk Orders</a></li>
                    <li><a href="{{ route('frontend.delivery') }}">Doorstep Delivery</a></li>
                  </ul>
                </div>
              </div>
              <div class="col-xl col-lg-4 col-md-6">
                <div class="space30 d-xl-none d-block"></div>
                <div class="footer1-widget-hour-info">
                  <div class="footer-widget-hour">
                    <h3>Working Hours</h3>
                    <div class="space28"></div>
                    <ul>
                      <li>
                        <span><a class="f-date" href="#">All Days:</a></span>
                        <span><a href="#">9:00 AM - 9:00 PM</a></span>
                      </li>
                      <li>
                        <span><a class="f-date" href="#">Sunday:</a></span>
                        <span><a href="#">Open</a></span>
                      </li>
                      <li>
                        <span
                          ><a class="f-date" href="#">Service Area:</a></span
                        >
                        <span
                          ><a href="#">Pallipalayam & Kumarapalayam</a></span
                        >
                      </li>
                    </ul>
                  </div>
                  <div class="space28"></div>
                  <div class="footer1-widget-social footer6-widget-social">
                    <ul class="social_link social1-footer">
                      <li>
                        <a
                          href="https://www.facebook.com/cholavinrice"
                          target="_blank"
                          rel="noopener"
                          ><i class="fa-brands fa-facebook"></i
                        ></a>
                      </li>
                      <li>
                        <a
                          href="https://www.instagram.com/cholavin_"
                          target="_blank"
                          rel="noopener"
                          ><i class="fa-brands fa-instagram"></i
                        ></a>
                      </li>
                      <!-- <li>
                        <a href="#"><i class="fa-brands fa-youtube"></i></a>
                      </li> -->
                      <!-- <li>
                        <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                      </li> -->
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="space60"></div>
        <div class="footer1-copyright-area">
          <ul class="footer1-copyright-wrap">
            <li>
              <a href="#">&copy; Cholavin - Your rice expert 2026. All Rights Reserved.</a>
            </li>
            <li>
              <a class="fw-500" href="#"
                >Premium rice <span>|</span> Trusted local delivery</a
              >
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!--===== footer area end =======-->

    <!-- MouseCursor Start -->
    <div class="mouseCursor cursor-outer"></div>
    <div class="mouseCursor cursor-inner"></div>

    
<!--===== JS SCRIPT LINK =======-->
    <script src="{{ asset('frontend/assets/') }}/js/plugins/bootstrap.min.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/fontawesome.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/aos.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/counter.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/magnific-popup.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/owlcarousel.min.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/nice-select.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/waypoints.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/slick-slider.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/swiper.min.js"></script>

    <!-- GSAP ANIMATION -->
    <script src="{{ asset('frontend/assets/') }}/js/plugins/gsap.min.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/ScrollTrigger.min.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/SmoothScroll.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/Splitetext.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/plugins/parallaxie.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ asset('frontend/assets/') }}/js/main.js"></script>
    <script src="{{ asset('frontend/assets/') }}/js/google-translate.js"></script>
    <script data-loader-fallback>
      window.addEventListener('load', function () {
        setTimeout(function () {
          if (window.jQuery) {
            jQuery('.preloader').fadeOut(250);
          }
        }, 700);
      });
    </script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        var heroTitle = document.querySelector(".hero2-heading-area .title");
        if (heroTitle) {
          heroTitle.textContent =
            "PURE RICE, PURE LIFE - WELCOME TO Cholavin - Your rice expert";
        }

        var aboutText = document.querySelector(".about6-pera_text p");
        if (aboutText) {
          aboutText.textContent =
            "At Cholavin - Your rice expert, we are committed to delivering the finest quality rice directly from trusted sources to your home. Our mission is to provide clean, fresh, and affordable rice varieties for everyday cooking and special occasions. We proudly serve families, hotels, and caterers in Pallipalayam and Kumarapalayam, ensuring every grain meets our quality standards.";
        }

        var testimonialSectionText = document.querySelector(
          ".vl-testimonial6-area .service6-top-right p",
        );
        if (testimonialSectionText) {
          testimonialSectionText.textContent =
            "Excellent rice quality, quick delivery, and customer-first support have made Cholavin - Your rice expert a trusted local choice.";
        }

        var testimonialCards = document.querySelectorAll(
          ".vl-testimonial6-area .swiper-slide",
        );
        var testimonialContent = [
          {
            quote:
              '"Excellent quality rice and fast delivery. Very satisfied with the service!"',
            name: "Lakshmi Family",
            meta: "Pallipalayam",
          },
          {
            quote:
              '"The return option gave me confidence. Truly customer-friendly business."',
            name: "Manoj Kumar",
            meta: "Kumarapalayam",
          },
          {
            quote:
              '"Best place to buy biriyani rice locally. Highly recommended!"',
            name: "Sathya Catering",
            meta: "Local Biriyani Orders",
          },
        ];
        testimonialCards.forEach(function (card, index) {
          var data = testimonialContent[index];
          if (!data) return;
          var quote = card.querySelector(".testimonial6-text");
          var user = card.querySelector(".testimonial6-user");
          if (quote) quote.textContent = data.quote;
          if (user) {
            user.innerHTML =
              '<h3><a href="#">' +
              data.name +
              '</a></h3><div class="space16"></div><p>' +
              data.meta +
              "</p>";
          }
        });

        var teamText = document.querySelector(
          ".vl-team6-area .service6-top-right p",
        );
        if (teamText) {
          teamText.textContent =
            "Our team ensures every order is carefully packed and delivered on time. We believe in maintaining quality and building long-term customer relationships.";
        }

        var teamCards = document.querySelectorAll(
          ".vl-team6-area .team6-content",
        );
        var teamContent = [
          ["Quality Selection Team", "Carefully selected rice varieties"],
          ["Packing Team", "Hygienic packing and storage"],
          ["Delivery Team", "Fast doorstep delivery"],
        ];
        teamCards.forEach(function (card, index) {
          var data = teamContent[index] || teamContent[teamContent.length - 1];
          card.innerHTML =
            '<h3><a href="#">' +
            data[0] +
            '</a></h3><div class="space16"></div><p>' +
            data[1] +
            "</p>";
        });

        var faqText = document.querySelector(
          ".vl-faq6-area .service6-top-right p",
        );
        if (faqText) {
          faqText.textContent =
            "Find quick answers about delivery areas, return support, delivery timing, and bulk rice supply.";
        }

        var faqButtons = document.querySelectorAll(
          ".vl-faq6-area .accordion-button",
        );
        var faqAnswers = document.querySelectorAll(
          ".vl-faq6-area .accordion-body .para",
        );
        var faqContent = [
          [
            "Do you deliver to my area?",
            "Yes, we deliver across Pallipalayam and Kumarapalayam.",
          ],
          [
            "What if I am not satisfied with the rice?",
            "We offer an easy return policy within 24 hours.",
          ],
          [
            "How long does delivery take?",
            "Same-day or next-day delivery depending on availability.",
          ],
          [
            "Do you provide bulk orders?",
            "Yes, we supply rice for hotels, functions, and catering services.",
          ],
        ];
        faqContent.forEach(function (item, index) {
          if (faqButtons[index]) {
            var numberSpan = faqButtons[index].querySelector("span");
            var labelNode = Array.from(faqButtons[index].childNodes).find(
              function (node) {
                return (
                  node.nodeType === Node.TEXT_NODE && node.textContent.trim()
                );
              },
            );
            if (labelNode) {
              labelNode.textContent = " " + item[0] + " ";
            }
            if (numberSpan) {
              numberSpan.textContent = String(index + 1).padStart(2, "0");
            }
          }
          if (faqAnswers[index]) {
            faqAnswers[index].textContent = item[1];
          }
        });

        var contactTag = document.querySelector(".contact2-header h3");
        if (contactTag) {
          contactTag.innerHTML =
            '<img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" /> Contact Us <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />';
        }

        var contactTitle = document.querySelector(".contact2-header h2");
        if (contactTitle) {
          contactTitle.textContent = "Order Fresh Rice Today - Contact Us";
        }

        var headerTime = document.querySelector(".header6-time");
        if (headerTime) {
          var headerTimeText = Array.from(headerTime.childNodes).find(
            function (node) {
              return (
                node.nodeType === Node.TEXT_NODE && node.textContent.trim()
              );
            },
          );
          if (headerTimeText && headerTimeText.nodeType === Node.TEXT_NODE) {
            headerTimeText.textContent =
              " All Days 9:00 AM - 9:00 PM | Sunday: Open";
          }
        }

        var contactLead = document.querySelector(
          ".contact2-form-area .pera_text",
        );
        if (contactLead) {
          contactLead.textContent =
            "Place your rice order quickly through WhatsApp. We support daily-use rice, biriyani rice, premium rice, and bulk supply requirements.";
        }

        var footerQuickTitle = document.querySelector(
          ".footer-widget-area.wid1-fix h3",
        );
        if (footerQuickTitle) {
          footerQuickTitle.textContent = "Quick Links";
        }

        var footerQuickLinks = document.querySelectorAll(
          ".footer-widget-area.wid1-fix ul li a",
        );
        var quickLinks = [
          ["Home", "index.html"],
          ["Rice Varieties", "products.html"],
          ["About", "aboutus.html"],
          ["Contact", "contactus.html"],
        ];
        footerQuickLinks.forEach(function (link, index) {
          if (quickLinks[index]) {
            link.textContent = quickLinks[index][0];
            link.setAttribute("href", quickLinks[index][1]);
          }
        });

        var footerServiceTitle = document.querySelector(
          ".footer-widget-area:not(.wid1-fix) h3",
        );
        if (footerServiceTitle) {
          footerServiceTitle.textContent = "Services";
        }

        var footerServiceLinks = document.querySelectorAll(
          ".footer-widget-area:not(.wid1-fix) ul li a",
        );
        var serviceLinks = [
          ["Daily Rice Supply", "products.html"],
          ["Biriyani Rice", "products.html"],
          ["Bulk Orders", "products.html"],
          ["Doorstep Delivery", "delivery.html"],
        ];
        footerServiceLinks.forEach(function (link, index) {
          if (serviceLinks[index]) {
            link.textContent = serviceLinks[index][0];
            link.setAttribute("href", serviceLinks[index][1]);
          }
        });

        var footerAbout = document.querySelector(".footer1-logo-area p");
        if (footerAbout) {
          footerAbout.textContent =
            "Cholavin - Your rice expert is a trusted local rice supplier offering premium quality rice with doorstep delivery and easy return policy in Pallipalayam and Kumarapalayam.";
        }

        var footerHoursLabel = document.querySelector(
          ".footer-widget-hour .f-date",
        );
        if (footerHoursLabel) {
          footerHoursLabel.textContent = "All Days :";
        }

        var copyright = document.querySelector(
          ".footer1-copyright-wrap li:first-child a",
        );
        if (copyright) {
          copyright.textContent =
            "\u00A9 Cholavin - Your rice expert " + new Date().getFullYear() + ". All Rights Reserved.";
        }
      });
    </script>
<script>
      (function ($) {
        function buildWhatsAppMessage(formData) {
          return [
            "*New Website Enquiry*",
            "Cholavin - Your rice expert",
            "",
            "*Customer Details*",
            "Name: " + formData.name,
            "Email: " + formData.email,
            "Phone: " + formData.phone,
            "",
            "*Requirement*",
            formData.service,
            "",
            "*Message*",
            formData.message,
            "",
            "Please share price, stock availability, and delivery details.",
          ].join("\n");
        }

        $(function () {
          var $form = $("#orderForm");

          if (!$form.length || !$.validator) {
            return;
          }

          if (window.toastr) {
            toastr.options = {
              closeButton: true,
              progressBar: true,
              newestOnTop: true,
              positionClass: "toast-top-right",
              timeOut: "3500",
              preventDuplicates: true,
            };
          }

          $.validator.addMethod(
            "phoneDigits",
            function (value, element) {
              return (
                this.optional(element) ||
                /^\d{10}$/.test(value.replace(/\D/g, ""))
              );
            },
            "Please enter a valid 10-digit phone number.",
          );

          $form.validate({
            errorElement: "span",
            errorClass: "contact2-error",
            highlight: function (element) {
              $(element).addClass("is-invalid");
            },
            unhighlight: function (element) {
              $(element).removeClass("is-invalid");
            },
            invalidHandler: function (_event, validator) {
              if (validator.errorList.length) {
                validator.errorList[0].element.scrollIntoView({
                  behavior: "smooth",
                  block: "center",
                });
              }
            },
            rules: {
              name: {
                required: true,
                minlength: 2,
              },
              email: {
                required: true,
                email: true,
              },
              phone: {
                required: true,
                phoneDigits: true,
              },
              service: {
                required: true,
              },
              message: {
                required: true,
                minlength: 10,
              },
            },
            messages: {
              name: {
                required: "Please enter your name.",
                minlength: "Name must be at least 2 characters long.",
              },
              email: {
                required: "Please enter your email address.",
                email: "Please enter a valid email address.",
              },
              phone: {
                required: "Please enter your phone number.",
              },
              service: {
                required: "Please select a requirement.",
              },
              message: {
                required: "Please enter your message.",
                minlength: "Message must be at least 10 characters long.",
              },
            },
            submitHandler: function (form, event) {
              if (event) {
                event.preventDefault();
              }

              var formData = {
                name: $.trim($("#name").val()),
                email: $.trim($("#email").val()),
                phone: $.trim($("#phone").val()).replace(/\D/g, ""),
                service: $("#service").val(),
                message: $.trim($("#message").val()),
              };

              var whatsappNumber = "9965252555";
              var whatsappUrl =
                "https://wa.me/" +
                whatsappNumber +
                "?text=" +
                encodeURIComponent(buildWhatsAppMessage(formData));

              var whatsappWindow = window.open(
                whatsappUrl,
                "_blank",
                "noopener",
              );

              if (window.toastr) {
                if (whatsappWindow) {
                  toastr.success("WhatsApp opened successfully.");
                } else {
                  toastr.info(
                    "Form is ready, but your browser blocked the WhatsApp popup.",
                  );
                }
              }

              form.reset();

              return false;
            },
          });

          $form.on("keyup change", "input, select, textarea", function () {
            if ($(this).hasClass("is-invalid")) {
              $(this).valid();
            }
          });
        });
      })(jQuery);
    </script>
  
    @stack('scripts')
  </body>
</html>

