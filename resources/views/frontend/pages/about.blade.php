@extends('frontend.layouts.app')

@section('title', 'About Us | Cholavin')


@push('styles')
<style>
      /* -- STAT BANNER -- */
      .cholavin-stats {
        background: #8F0028;
        padding: 60px 0;
      }
      .stat-item {
        text-align: center;
        padding: 20px;
      }
      .stat-item h2 {
        font-size: 3rem;
        font-weight: 800;
        color: #F4C430;
        line-height: 1;
        margin-bottom: 8px;
      }
      .stat-item p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
        margin: 0;
      }

      /* -- MISSION / VISION -- */
      .mv-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px 32px;
        box-shadow: 0 6px 32px rgba(143, 0, 40, 0.08);
        height: 100%;
      }
      .mv-card .mv-icon {
        width: 56px;
        height: 56px;
        background: #FFF2CF;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
      }
      .mv-card .mv-icon img {
        width: 28px;
      }
      .mv-card h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #8F0028;
        margin-bottom: 12px;
      }
      .mv-card p {
        color: #555;
        font-size: 0.93rem;
        line-height: 1.75;
        margin: 0;
      }

      /* -- STORY TIMELINE -- */
      .story-section {
        background: #FFF8E6;
      }
      .timeline {
        position: relative;
        padding: 0;
        list-style: none;
      }
      .timeline::before {
        content: "";
        position: absolute;
        left: 24px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #E7C067;
      }
      .timeline-item {
        position: relative;
        padding-left: 68px;
        margin-bottom: 44px;
      }
      .timeline-item:last-child {
        margin-bottom: 0;
      }
      .timeline-dot {
        position: absolute;
        left: 12px;
        top: 4px;
        width: 28px;
        height: 28px;
        background: #8F0028;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .timeline-dot span {
        font-size: 0.65rem;
        font-weight: 800;
        color: #fff;
      }
      .timeline-item h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #8F0028;
        margin-bottom: 6px;
      }
      .timeline-item p {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.7;
        margin: 0;
      }

      /* -- WHAT WE SUPPLY -- */
      .supply-card {
        background: #fff;
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(143, 0, 40, 0.07);
        margin-bottom: 24px;
        transition: transform 0.3s;
      }
      .supply-card:hover {
        transform: translateY(-6px);
      }
      .supply-card .supply-icon {
        font-size: 2rem;
        margin-bottom: 14px;
      }
      .supply-card h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #8F0028;
        margin-bottom: 8px;
      }
      .supply-card p {
        font-size: 0.85rem;
        color: #666;
        margin: 0;
      }

      /* -- PROCESS -- */
      .process-section {
        background: #8F0028;
      }
      .process-step {
        text-align: center;
        padding: 20px 10px;
      }
      .process-num {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 1.25rem;
        font-weight: 800;
        color: #F4C430;
      }
      .process-step h4 {
        color: #fff;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 8px;
      }
      .process-step p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.85rem;
        margin: 0;
      }
      .process-arrow {
        color: rgba(255, 255, 255, 0.3);
        font-size: 1.5rem;
        padding-top: 14px;
      }

      /* -- WHY CHOOSE -- */
      .why-card {
        background: #fff;
        border-radius: 16px;
        padding: 32px 20px;
        box-shadow: 0 4px 24px rgba(143, 0, 40, 0.08);
        text-align: center;
        margin-bottom: 24px;
      }
      .why-card img {
        width: 48px;
        margin-bottom: 14px;
      }
      .why-card h4 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 8px;
      }
      .why-card p {
        font-size: 0.875rem;
        color: #666;
        margin: 0;
      }

      /* -- TESTIMONIALS -- */
      .testi-card {
        background: #fff;
        border-radius: 20px;
        padding: 32px 28px;
        box-shadow: 0 4px 24px rgba(143, 0, 40, 0.08);
        margin-bottom: 24px;
      }
      .testi-card .stars {
        color: #F4C430;
        font-size: 0.85rem;
        margin-bottom: 16px;
      }
      .testi-card blockquote {
        font-size: 0.93rem;
        color: #444;
        line-height: 1.75;
        font-style: italic;
        margin: 0 0 20px;
      }
      .testi-card .testi-author strong {
        font-size: 0.9rem;
        color: #8F0028;
      }
      .testi-card .testi-author span {
        font-size: 0.82rem;
        color: #888;
        margin-left: 6px;
      }

      /* -- COVERAGE -- */
      .coverage-badge {
        display: inline-block;
        background: #FFF2CF;
        color: #8F0028;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 8px 18px;
        border-radius: 50px;
        margin: 5px;
      }

      /* -- CTA STRIP -- */
      .cta-strip {
        background: linear-gradient(135deg, #5E001B, #8F0028);
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        color: #fff;
        margin: 60px 0;
      }
      .cta-strip h2 {
        color: #fff;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 12px;
      }
      .cta-strip p {
        color: rgba(255, 255, 255, 0.85);
        max-width: 520px;
        margin: 0 auto 28px;
      }
      .cta-strip-btns {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
      }
      .cta-wa {
        background: #fff;
        color: #8F0028 !important;
        padding: 13px 30px;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
      }
      .cta-tel {
        background: transparent;
        border: 2px solid #fff;
        color: #fff !important;
        padding: 13px 30px;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
      }

      /* -- RESPONSIVE TWEAKS -- */
      @media (max-width: 991px) {
        .process-arrow {
          display: none !important;
        }
        .process-step {
          margin-bottom: 24px;
        }
        .stat-item h2 {
          font-size: 2.2rem;
        }
      }
      @media (max-width: 767px) {
        .cholavin-stats {
          padding: 40px 0;
        }
        .mv-card {
          padding: 24px 20px;
        }
        .timeline::before {
          left: 16px;
        }
        .timeline-item {
          padding-left: 48px;
        }
        .timeline-dot {
          left: 4px;
          width: 24px;
          height: 24px;
        }
        .timeline-dot span {
          font-size: 0.55rem;
        }
        .cta-strip h2 {
          font-size: 1.5rem;
        }
        .cta-strip {
          padding: 40px 20px;
        }
        .stat-item h2 {
          font-size: 2rem;
        }
      }
    </style>
@endpush

@section('content')
<!--=====HEADER END =======-->

    <!--===== HERO INNER =======-->
    <div
      class="vl-hero-inner-area parallaxie"
      style="
        background-image: url({{ asset('frontend/assets/img/hero/about-us-inr-herothumb.webp') }});
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
      "
    >
      <div class="container">
        <div class="row">
          <div class="col-xl-6">
            <div class="inner-hero-info">
              <h2>About Us</h2>
              <div class="space16"></div>
              <ul>
                <li><a href="{{ route('frontend.home') }}">Home</a></li>
                <li>
                  <i
                    class="fa-solid fa-angle-right"
                    style="font-size: 0.75rem; color: #fff"
                  ></i>
                </li>
                <li><a class="aboutus_titlefix" href="#">About Us</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--===== HERO END =======-->

    <!-- ===================================================================
       SECTION 1 — WHO WE ARE
  ==================================================================== -->
    <div class="vl-about6-area sp1">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-5 col-lg-6 mb-40 mb-lg-0">
            <div class="about6-thumb">
              <img
                class="thumb1"
                src="{{ asset('frontend/assets/img/service/services2-thumb.webp') }}"
                alt="Cholavin - Your rice expert store"
                data-aos="fade-right"
                data-aos-duration="1000"
              />
              <img
                class="thumb2"
                src="{{ asset($commonSettings['auth_brand_image'] ?? 'frontend/assets/img/logo/logo-hm64.png') }}"
                alt="Cholavin logo badge"
                data-aos="fade-left"
                data-aos-duration="1000"
              />
            </div>
          </div>
          <div class="col-xl-7 col-lg-6">
            <div class="about6-info">
              <div class="about6-heading">
                <h3
                  class="sub-title"
                  data-aos="fade-left"
                  data-aos-duration="900"
                >
                  <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
                  About Our Rice Business
                  <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
                </h3>
                <div class="space16"></div>
                <h2 class="title" data-aos="fade-left" data-aos-duration="900">
                  Supplying Quality Rice to Families &amp; Businesses Since Day
                  One
                </h2>
              </div>
              <div class="row">
                <div class="col-xl-1"></div>
                <div class="col-xl-11">
                  <div class="about6-content">
                    <div class="row">
                      <div
                        class="col-xl-5 col-lg-5"
                        data-aos="zoom-in"
                        data-aos-duration="1000"
                      >
                        <div class="about2-content-box2">
                          <div class="about2-content-text2">
                            <h2><span class="counter">10</span>+</h2>
                            <div class="space16"></div>
                            <p>Years of Experience in Rice Supply</p>
                          </div>
                          <div class="about2-box2-shape aniamtion-key-5">
                            <img
                              src="{{ asset('frontend/assets/img/shape/about2-shape(2).webp') }}"
                              alt=""
                            />
                          </div>
                          <div class="about2-box3-shape aniamtion-key-2">
                            <img
                              src="{{ asset('frontend/assets/img/shape/about2-shape(3).webp') }}"
                              alt=""
                            />
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-6">
                        <div class="about6-icons-info">
                          <div
                            class="about6-icons-box"
                            data-aos="fade-left"
                            data-aos-duration="900"
                          >
                            <div class="about6-icons-logo">
                              <img
                                src="{{ asset('frontend/assets/img/icon/about6-icon(1).svg') }}"
                                alt=""
                              />
                            </div>
                            <div class="about6-icons-content">
                              <h3>
                                <a href="{{ route('frontend.products') }}"
                                  >Carefully Selected Rice Varieties</a
                                >
                              </h3>
                              <div class="space16"></div>
                              <p>
                                Premium grains chosen for daily meals, special
                                occasions, and bulk orders.
                              </p>
                            </div>
                          </div>
                          <div
                            class="about6-icons-box about6-icons-fxr"
                            data-aos="fade-left"
                            data-aos-duration="900"
                          >
                            <div class="about6-icons-logo">
                              <img
                                src="{{ asset('frontend/assets/img/icon/about6-icon(2).svg') }}"
                                alt=""
                              />
                            </div>
                            <div class="about6-icons-content">
                              <h3>
                                <a href="{{ route('frontend.delivery') }}"
                                  >Hygienic Packing &amp; Storage</a
                                >
                              </h3>
                              <div class="space16"></div>
                              <p>
                                Clean packing, safe storage, and reliable
                                handling for every order.
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-12">
                        <div class="about6-pera_text">
                          <p data-aos="fade-left" data-aos-duration="900">
                            Cholavin - Your rice expert was built on one simple promise
                            – to deliver the freshest, highest-quality rice
                            straight to the homes and businesses of Pallipalayam
                            and Kumarapalayam. We source directly from trusted
                            mills, ensure rigorous quality checks, and pack
                            every order with care so that every grain you
                            receive is exactly what you expected.
                          </p>
                        </div>
                        <div
                          class="about6-wrap"
                          data-aos="fade-left"
                          data-aos-duration="1000"
                        >
                          <ul>
                            <li>
                              <img
                                src="{{ asset('frontend/assets/img/icon/tick-hm6.svg') }}"
                                alt=""
                              />Trusted by local customers &amp; businesses
                            </li>
                            <li>
                              <img
                                src="{{ asset('frontend/assets/img/icon/tick-hm6.svg') }}"
                                alt=""
                              />100% customer satisfaction guaranteed
                            </li>
                          </ul>
                          <div class="about6-wrap-line"></div>
                          <div class="about6-wrap-btn">
                            <a href="{{ route('frontend.products') }}" class="btn-home6"
                              >View Our Rice</a
                            >
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 2 — STATS BANNER
  ==================================================================== -->
    <div class="cholavin-stats">
      <div class="container">
        <div class="row text-center">
          <div
            class="col-xl-3 col-lg-3 col-6"
            data-aos="zoom-in"
            data-aos-duration="800"
          >
            <div class="stat-item">
              <h2><span class="counter">10</span>+</h2>
              <p>Years in Business</p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-6"
            data-aos="zoom-in"
            data-aos-duration="900"
          >
            <div class="stat-item">
              <h2><span class="counter">500</span>+</h2>
              <p>Happy Customers</p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-6"
            data-aos="zoom-in"
            data-aos-duration="1000"
          >
            <div class="stat-item">
              <h2><span class="counter">7</span>+</h2>
              <p>Rice Varieties Stocked</p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-6"
            data-aos="zoom-in"
            data-aos-duration="1100"
          >
            <div class="stat-item">
              <h2><span class="counter">2</span></h2>
              <p>Locations Served Daily</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 3 — MISSION & VISION
  ==================================================================== -->
    <div class="vl-about6-area sp1" style="background: #FFF8E6">
      <div class="container">
        <div class="row">
          <div class="col-xl-12 text-center mb-50">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              What Drives Us
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-up" data-aos-duration="900">
              Our Mission &amp; Vision
            </h2>
          </div>
          <div
            class="col-xl-4 col-lg-4 col-md-12 mb-30"
            data-aos="fade-up"
            data-aos-duration="800"
          >
            <div class="mv-card">
              <div class="mv-icon">
                <img src="{{ asset('frontend/assets/img/icon/about6-icon(1).svg') }}" alt="" />
              </div>
              <h3>Our Mission</h3>
              <p>
                To make quality rice accessible to every home and business in
                our region – through honest pricing, consistent supply, and
                doorstep delivery that people can rely on every single day.
              </p>
            </div>
          </div>
          <div
            class="col-xl-4 col-lg-4 col-md-12 mb-30"
            data-aos="fade-up"
            data-aos-duration="900"
          >
            <div class="mv-card">
              <div class="mv-icon">
                <img src="{{ asset('frontend/assets/img/icon/about6-icon(2).svg') }}" alt="" />
              </div>
              <h3>Our Vision</h3>
              <p>
                To become the most trusted rice supplier in the Erode district –
                a name that families, hotels, and institutions think of first
                when they need rice delivered on time, every time.
              </p>
            </div>
          </div>
          <div
            class="col-xl-4 col-lg-4 col-md-12 mb-30"
            data-aos="fade-up"
            data-aos-duration="1000"
          >
            <div class="mv-card">
              <div class="mv-icon">
                <img src="{{ asset('frontend/assets/img/icon/tick-hm6.svg') }}" alt="" />
              </div>
              <h3>Our Promise</h3>
              <p>
                Every bag we dispatch goes through a quality check. We never
                compromise on grain quality, packing hygiene, or delivery
                timelines – because your trust is worth more than any sale.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 4 — OUR STORY (TIMELINE)
  ==================================================================== -->
    <div class="vl-about6-area sp1 story-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-5 col-lg-5 mb-40 mb-lg-0">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              Our Journey
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-right" data-aos-duration="900">
              How Cholavin Came to Be
            </h2>
            <div class="space24"></div>
            <p
              data-aos="fade-right"
              data-aos-duration="1000"
              style="color: #555; line-height: 1.8"
            >
              What started as a small family-run rice shop has grown into one of
              the most reliable rice supply businesses in the region. Our story
              is one of dedication, community trust, and a relentless focus on
              quality at every step.
            </p>
            <div class="space28"></div>
            <a
              href="{{ route('frontend.contact') }}"
              class="btn-home6"
              data-aos="fade-right"
              data-aos-duration="1100"
              >Get in Touch</a
            >
          </div>
          <div class="col-xl-7 col-lg-7">
            <ul class="timeline" data-aos="fade-left" data-aos-duration="900">
              <li class="timeline-item">
                <div class="timeline-dot"><span>01</span></div>
                <h4>The Beginning – A Family Vision</h4>
                <p>
                  Cholavin - Your rice expert was founded with a simple goal: to supply
                  clean, quality rice directly to households in Pallipalayam at
                  fair prices. The first orders were delivered personally by the
                  founder.
                </p>
              </li>
              <li class="timeline-item">
                <div class="timeline-dot"><span>02</span></div>
                <h4>Expanding to Kumarapalayam</h4>
                <p>
                  Growing demand led us to extend our delivery network to
                  Kumarapalayam, doubling our customer base and establishing
                  ties with hotels, hostels, and small businesses in the area.
                </p>
              </li>
              <li class="timeline-item">
                <div class="timeline-dot"><span>03</span></div>
                <h4>Bulk &amp; Institutional Supply</h4>
                <p>
                  We began supplying schools, colleges, and large catering
                  operations – developing reliable bulk ordering processes and
                  same-day delivery capabilities for high-volume clients.
                </p>
              </li>
              <li class="timeline-item">
                <div class="timeline-dot"><span>04</span></div>
                <h4>WhatsApp Ordering &amp; Digital Presence</h4>
                <p>
                  To serve customers better, we introduced WhatsApp ordering, a
                  dedicated product catalogue, and a website – making it easier
                  than ever to order any rice variety from anywhere in the
                  region.
                </p>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 5 — WHAT WE SUPPLY
  ==================================================================== -->
    <div class="vl-about6-area sp1">
      <div class="container">
        <div class="row">
          <div class="col-xl-12 text-center mb-50">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              Our Products
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-up" data-aos-duration="900">
              Rice Varieties We Supply
            </h2>
            <div class="space16"></div>
            <p
              data-aos="fade-up"
              data-aos-duration="1000"
              style="max-width: 580px; margin: 0 auto; color: #666"
            >
              From everyday cooking to festive biriyani and bulk institutional
              supply – we have the right rice for every need.
            </p>
          </div>
          <div
            class="col-xl-2 col-lg-3 col-md-4 col-6"
            data-aos="zoom-in"
            data-aos-duration="800"
          >
            <div class="supply-card">
              <div class="supply-icon"><i class="fa-solid fa-wheat-awn"></i></div>
              <h4>Ponni Rice</h4>
              <p>Soft daily family favourite</p>
            </div>
          </div>
          <div
            class="col-xl-2 col-lg-3 col-md-4 col-6"
            data-aos="zoom-in"
            data-aos-duration="850"
          >
            <div class="supply-card">
              <div class="supply-icon"><i class="fa-solid fa-bowl-rice"></i></div>
              <h4>Raw Rice</h4>
              <p>Consistent bulk supply</p>
            </div>
          </div>
          <div
            class="col-xl-2 col-lg-3 col-md-4 col-6"
            data-aos="zoom-in"
            data-aos-duration="900"
          >
            <div class="supply-card">
              <div class="supply-icon"><i class="fa-solid fa-mortar-pestle"></i></div>
              <h4>Idli Rice</h4>
              <p>Smooth tiffin batter</p>
            </div>
          </div>
          <div
            class="col-xl-2 col-lg-3 col-md-4 col-6"
            data-aos="zoom-in"
            data-aos-duration="950"
          >
            <div class="supply-card">
              <div class="supply-icon"><i class="fa-solid fa-bowl-food"></i></div>
              <h4>Seeraga Samba</h4>
              <p>Authentic biriyani rice</p>
            </div>
          </div>
          <div
            class="col-xl-2 col-lg-3 col-md-4 col-6"
            data-aos="zoom-in"
            data-aos-duration="1000"
          >
            <div class="supply-card">
              <div class="supply-icon">?</div>
              <h4>Basmati</h4>
              <p>Premium long grain</p>
            </div>
          </div>
          <div
            class="col-xl-2 col-lg-3 col-md-4 col-6"
            data-aos="zoom-in"
            data-aos-duration="1050"
          >
            <div class="supply-card">
              <div class="supply-icon"><i class="fa-solid fa-seedling"></i></div>
              <h4>Kolam Rice</h4>
              <p>Bright medium grain</p>
            </div>
          </div>
          <div
            class="col-xl-12 text-center mt-20"
            data-aos="fade-up"
            data-aos-duration="900"
          >
            <a href="{{ route('frontend.products') }}" class="btn-home6">View All Products</a>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 6 — HOW WE WORK (PROCESS)
  ==================================================================== -->
    <div class="process-section sp1">
      <div class="container">
        <div class="row">
          <div class="col-xl-12 text-center mb-50">
            <h3
              style="
                color: #F4C430;
                font-size: 0.85rem;
                letter-spacing: 2px;
                text-transform: uppercase;
                margin-bottom: 10px;
              "
              data-aos="fade-up"
              data-aos-duration="800"
            >
              Simple &amp; Reliable
            </h3>
            <h2
              style="color: #fff; font-size: 2rem; font-weight: 700"
              data-aos="fade-up"
              data-aos-duration="900"
            >
              How Your Order Works
            </h2>
          </div>
          <div
            class="col-xl-2 col-lg-2 col-6 text-center"
            data-aos="fade-up"
            data-aos-duration="800"
          >
            <div class="process-step">
              <div class="process-num">01</div>
              <h4>Choose Your Rice</h4>
              <p>Browse varieties on our products page or call us directly.</p>
            </div>
          </div>
          <div
            class="col-xl-1 col-lg-1 d-none d-lg-flex align-items-start justify-content-center process-arrow"
          >
            <i class="fa-solid fa-arrow-right"></i>
          </div>
          <div
            class="col-xl-2 col-lg-2 col-6 text-center"
            data-aos="fade-up"
            data-aos-duration="900"
          >
            <div class="process-step">
              <div class="process-num">02</div>
              <h4>Place Your Order</h4>
              <p>WhatsApp us or call {{ $commonSettings['contact_phone'] ?? '+91 99652 52555' }} with your requirement.</p>
            </div>
          </div>
          <div
            class="col-xl-1 col-lg-1 d-none d-lg-flex align-items-start justify-content-center process-arrow"
          >
            <i class="fa-solid fa-arrow-right"></i>
          </div>
          <div
            class="col-xl-2 col-lg-2 col-6 text-center"
            data-aos="fade-up"
            data-aos-duration="1000"
          >
            <div class="process-step">
              <div class="process-num">03</div>
              <h4>We Pack It Fresh</h4>
              <p>
                Your rice is weighed, quality-checked, and hygienically packed.
              </p>
            </div>
          </div>
          <div
            class="col-xl-1 col-lg-1 d-none d-lg-flex align-items-start justify-content-center process-arrow"
          >
            <i class="fa-solid fa-arrow-right"></i>
          </div>
          <div
            class="col-xl-2 col-lg-2 col-6 text-center"
            data-aos="fade-up"
            data-aos-duration="1100"
          >
            <div class="process-step">
              <div class="process-num">04</div>
              <h4>Doorstep Delivery</h4>
              <p>
                Fast delivery to your home or business in Pallipalayam &amp;
                Kumarapalayam.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 7 — WHY CHOOSE US
  ==================================================================== -->
    <div class="vl-about6-area sp1" style="background: #FFF8E6">
      <div class="container">
        <div class="row">
          <div class="col-xl-12 text-center mb-50">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              Why Cholavin?
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-up" data-aos-duration="900">
              Quality You Can Trust, Every Order
            </h2>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6"
            data-aos="zoom-in"
            data-aos-duration="800"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/about6-icon(1).svg') }}" alt="" />
              <h4>5+ Years Experience</h4>
              <p>Trusted by families and businesses since we opened.</p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6"
            data-aos="zoom-in"
            data-aos-duration="900"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/about6-icon(2).svg') }}" alt="" />
              <h4>Doorstep Delivery</h4>
              <p>Fast delivery to Pallipalayam and Kumarapalayam.</p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6"
            data-aos="zoom-in"
            data-aos-duration="1000"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/tick-hm6.svg') }}" alt="" />
              <h4>Hygienic Packing</h4>
              <p>Clean storage and safe packaging in every single order.</p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6"
            data-aos="zoom-in"
            data-aos-duration="1100"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/about6-icon(1).svg') }}" alt="" />
              <h4>Bulk &amp; Trade Ready</h4>
              <p>
                We supply shops, hotels, schools, hostels, and institutions.
              </p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6 mt-0 mt-md-4"
            data-aos="zoom-in"
            data-aos-duration="800"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/about6-icon(2).svg') }}" alt="" />
              <h4>Easy WhatsApp Orders</h4>
              <p>
                Place your order in seconds via WhatsApp – no complicated
                process.
              </p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6 mt-0 mt-md-4"
            data-aos="zoom-in"
            data-aos-duration="900"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/tick-hm6.svg') }}" alt="" />
              <h4>Honest Pricing</h4>
              <p>
                Transparent rates with no hidden charges – what we quote is what
                you pay.
              </p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6 mt-0 mt-md-4"
            data-aos="zoom-in"
            data-aos-duration="1000"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/about6-icon(1).svg') }}" alt="" />
              <h4>Multiple Pack Sizes</h4>
              <p>
                From 5kg home packs to 50kg institutional bags – we have every
                size.
              </p>
            </div>
          </div>
          <div
            class="col-xl-3 col-lg-3 col-md-6 col-6 mt-0 mt-md-4"
            data-aos="zoom-in"
            data-aos-duration="1100"
          >
            <div class="why-card">
              <img src="{{ asset('frontend/assets/img/icon/about6-icon(2).svg') }}" alt="" />
              <h4>Easy Returns</h4>
              <p>Not satisfied? We make it right – no questions asked.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 8 — TESTIMONIALS
  ==================================================================== -->
    <div class="vl-about6-area sp1">
      <div class="container">
        <div class="row">
          <div class="col-xl-12 text-center mb-50">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              What Our Customers Say
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-up" data-aos-duration="900">
              Trusted by Families &amp; Businesses
            </h2>
          </div>
          <div
            class="col-xl-4 col-lg-4 col-md-12 mb-30"
            data-aos="fade-up"
            data-aos-duration="800"
          >
            <div class="testi-card">
              <div class="stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i
                ><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i
                ><i class="fa-solid fa-star"></i>
              </div>
              <blockquote>
                "We have been ordering Ponni rice from Cholavin for over two
                years. The quality is consistently excellent and the delivery is
                always on time. Highly recommended for families."
              </blockquote>
              <div class="testi-author">
                <strong>Meena Devi</strong>
                <span>– Regular Home Customer, Pallipalayam</span>
              </div>
            </div>
          </div>
          <div
            class="col-xl-4 col-lg-4 col-md-12 mb-30"
            data-aos="fade-up"
            data-aos-duration="900"
          >
            <div class="testi-card">
              <div class="stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i
                ><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i
                ><i class="fa-solid fa-star"></i>
              </div>
              <blockquote>
                "Our hotel requires large quantities of Seeraga Samba every
                week. Cholavin has never let us down – same quality, same price,
                always delivered when promised. Best bulk supplier around."
              </blockquote>
              <div class="testi-author">
                <strong>Rajan Hotel</strong>
                <span>– Hotel Owner, Kumarapalayam</span>
              </div>
            </div>
          </div>
          <div
            class="col-xl-4 col-lg-4 col-md-12 mb-30"
            data-aos="fade-up"
            data-aos-duration="1000"
          >
            <div class="testi-card">
              <div class="stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i
                ><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i
                ><i class="fa-solid fa-star"></i>
              </div>
              <blockquote>
                "We order idli rice and raw rice for our hostel every month.
                Cholavin is reliable, hygienic, and the WhatsApp ordering makes
                the whole process very simple. Very happy with the service."
              </blockquote>
              <div class="testi-author">
                <strong>Sri Lakshmi Hostel</strong>
                <span>– Hostel Manager, Pallipalayam</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 9 — DELIVERY COVERAGE
  ==================================================================== -->
    <div class="vl-about6-area sp1" style="background: #FFF8E6">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-6 col-lg-6 mb-40 mb-lg-0">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              Where We Deliver
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-right" data-aos-duration="900">
              Serving the Heart of Erode District
            </h2>
            <div class="space24"></div>
            <p
              data-aos="fade-right"
              data-aos-duration="1000"
              style="color: #555; line-height: 1.8"
            >
              Our delivery network covers key towns and surrounding areas. We
              offer same-day delivery on orders placed before noon, and
              scheduled delivery for bulk and institutional customers. Contact
              us to confirm delivery to your specific location.
            </p>
            <div class="space28"></div>
            <div data-aos="fade-right" data-aos-duration="1100">
              <span class="coverage-badge">Pallipalayam</span>
              <span class="coverage-badge">Kumarapalayam</span>
              <span class="coverage-badge">Erode</span>
              <span class="coverage-badge">Bhavani</span>
              <span class="coverage-badge">Sankari</span>
              <span class="coverage-badge">Rasipuram</span>
              <span class="coverage-badge">Nearby Areas on Request</span>
            </div>
            <div class="space28"></div>
            <a
              href="{{ route('frontend.delivery') }}"
              class="btn-home6"
              data-aos="fade-right"
              data-aos-duration="1200"
              >View Delivery Info</a
            >
          </div>
          <div
            class="col-xl-6 col-lg-6"
            data-aos="fade-left"
            data-aos-duration="900"
          >
            <div
              class="vl-service6-area"
              style="border-radius: 20px; overflow: hidden"
            >
              <div class="row g-0">
                <div class="col-6 p-3">
                  <div
                    class="service6-box"
                    style="border-radius: 16px; padding: 24px 18px"
                  >
                    <div class="service6-logos mb-3">
                      <div class="inons">
                        <img
                          src="{{ asset('frontend/assets/img/icon/service6-icon(1).svg') }}"
                          alt=""
                        />
                      </div>
                    </div>
                    <h4
                      style="
                        font-size: 0.95rem;
                        font-weight: 700;
                        margin-bottom: 8px;
                      "
                    >
                      Same-Day Delivery
                    </h4>
                    <p class="pera-text" style="font-size: 0.82rem">
                      Orders placed before 12pm delivered same day within our
                      coverage area.
                    </p>
                  </div>
                </div>
                <div class="col-6 p-3">
                  <div
                    class="service6-box"
                    style="border-radius: 16px; padding: 24px 18px"
                  >
                    <div class="service6-logos mb-3">
                      <div class="inons">
                        <img
                          src="{{ asset('frontend/assets/img/icon/service6-icon(2).svg') }}"
                          alt=""
                        />
                      </div>
                    </div>
                    <h4
                      style="
                        font-size: 0.95rem;
                        font-weight: 700;
                        margin-bottom: 8px;
                      "
                    >
                      Scheduled Bulk Runs
                    </h4>
                    <p class="pera-text" style="font-size: 0.82rem">
                      Weekly and monthly delivery schedules for businesses and
                      institutions.
                    </p>
                  </div>
                </div>
                <div class="col-6 p-3">
                  <div
                    class="service6-box"
                    style="border-radius: 16px; padding: 24px 18px"
                  >
                    <div class="service6-logos mb-3">
                      <div class="inons">
                        <img
                          src="{{ asset('frontend/assets/img/icon/service6-icon(3).svg') }}"
                          alt=""
                        />
                      </div>
                    </div>
                    <h4
                      style="
                        font-size: 0.95rem;
                        font-weight: 700;
                        margin-bottom: 8px;
                      "
                    >
                      WhatsApp Ordering
                    </h4>
                    <p class="pera-text" style="font-size: 0.82rem">
                      Simply message us your order – no forms, no fuss, just
                      rice delivered.
                    </p>
                  </div>
                </div>
                <div class="col-6 p-3">
                  <div
                    class="service6-box"
                    style="border-radius: 16px; padding: 24px 18px"
                  >
                    <div class="service6-logos mb-3">
                      <div class="inons">
                        <img
                          src="{{ asset('frontend/assets/img/icon/service6-icon(1).svg') }}"
                          alt=""
                        />
                      </div>
                    </div>
                    <h4
                      style="
                        font-size: 0.95rem;
                        font-weight: 700;
                        margin-bottom: 8px;
                      "
                    >
                      Free Delivery on Bulk
                    </h4>
                    <p class="pera-text" style="font-size: 0.82rem">
                      Free delivery included on bulk orders above minimum
                      quantity. Ask us for details.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 10 — FAQ
  ==================================================================== -->
    <div class="vl-faq6-area sp1">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-12 text-center mb-50">
            <h3 class="sub-title" data-aos="fade-left" data-aos-duration="900">
              <img src="{{ asset('frontend/assets/img/icon/left_icon_hm2_about.webp') }}" alt="" />
              Got Questions?
              <img src="{{ asset('frontend/assets/img/icon/right_icon_hm2_about.webp') }}" alt="" />
            </h3>
            <div class="space16"></div>
            <h2 class="title" data-aos="fade-up" data-aos-duration="900">
              Frequently Asked Questions
            </h2>
          </div>
          <div class="col-xl-6 col-lg-6">
            <div class="vl-faq-content-wrap-2 vl-faq6">
              <div class="vl-faq-accordion">
                <div class="accordion" id="accordionAbout">
                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="800"
                  >
                    <h2 class="accordion-header" id="faqH1">
                      <button
                        class="accordion-button"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faqC1"
                        aria-expanded="true"
                        aria-controls="faqC1"
                      >
                        <span>01</span> What rice varieties do you stock?
                        <span class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="faqC1"
                      class="accordion-collapse collapse show"
                      data-bs-parent="#accordionAbout"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          We stock Ponni Rice, Raw Rice, Idli Rice, Seeraga
                          Samba, Jeeraga Samba, Basmati, and Kolam Rice. All
                          available in multiple pack sizes from 5kg to 50kg.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="900"
                  >
                    <h2 class="accordion-header" id="faqH2">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faqC2"
                        aria-expanded="false"
                        aria-controls="faqC2"
                      >
                        <span>02</span> Do you deliver to my area?
                        <span class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="faqC2"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordionAbout"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          We currently deliver to Pallipalayam, Kumarapalayam,
                          and surrounding areas in Erode district. WhatsApp us
                          your location and we will confirm delivery
                          availability for you.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="1000"
                  >
                    <h2 class="accordion-header" id="faqH3">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faqC3"
                        aria-expanded="false"
                        aria-controls="faqC3"
                      >
                        <span>03</span> Can I place a bulk or trade order?
                        <span class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="faqC3"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordionAbout"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          Yes, we supply bulk orders for shops, hotels,
                          restaurants, schools, hostels, and institutions. Call
                          or WhatsApp us with your rice type and quantity for a
                          custom quote.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="1100"
                  >
                    <h2 class="accordion-header" id="faqH4">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faqC4"
                        aria-expanded="false"
                        aria-controls="faqC4"
                      >
                        <span>04</span> How do I place an order?
                        <span class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="faqC4"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordionAbout"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          WhatsApp us at {{ $commonSettings['contact_phone'] ?? '+91 99652 52555' }} or call during business
                          hours (Mon–Sat, 8am–6pm). Tell us the rice variety,
                          pack size, quantity, and your delivery address.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-lg-6">
            <div class="faq6-thumb image-anime reveal">
              <img
                src="{{ asset('frontend/assets/img/faq/faq6-thumb.png') }}"
                alt="Cholavin Rice FAQ"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================================================================
       SECTION 11 — CTA STRIP
  ==================================================================== -->
    <div class="container">
      <div class="cta-strip" data-aos="zoom-in" data-aos-duration="900">
        <h2>Ready to Order Fresh Rice?</h2>
        <p>
          Call or WhatsApp us with your rice variety, quantity, and delivery
          location – we will take care of the rest.
        </p>
        <div class="cta-strip-btns">
          <a
            href="https://wa.me/{{ $commonSettings['whatsapp_number'] ?? '919965252555' }}?text=Hello%20Cholavin%2C%20I%20would%20like%20to%20place%20an%20order."
            class="cta-wa"
            target="_blank"
            rel="noopener"
            >WhatsApp Now</a
          >
          <a href="tel:{{ preg_replace('/[^0-9+]/', '', $commonSettings['contact_phone'] ?? '') }}" class="cta-tel">Call: {{ $commonSettings['contact_phone'] ?? '+91 99652 52555' }}</a>
        </div>
      </div>
    </div>
@endsection

