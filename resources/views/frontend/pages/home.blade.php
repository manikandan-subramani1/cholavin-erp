@extends('frontend.layouts.app')

@section('title', 'Cholavin - Your rice expert')


@section('body_class', 'bg-home2')

@section('content')
@php
    $homepageProducts = \App\Models\Product::where('is_active', true)->where('show_on_homepage', true)->orderBy('sort_order')->latest('id')->take(6)->get();
@endphp
@include('frontend.partials.home-products', ['products' => $homepageProducts])

<!--=====HEADER END =======-->

    <!--===== HERO AREA STARTS =======-->
    <div
      class="vl-hero2-area"
      style=" 
        background-position: top center;
        background-repeat: no-repeat;
        background-size: cover;
      "
    >
      <div class="vl-hero3-shape aniamtion-key-1">
        <img src="{{ asset('frontend/assets/') }}/img/shape/hero2-shape(2).png" alt="" />
      </div>
      <div class="container">
        <div class="row">
          <div class="col-xl-8">
            <div class="hero2-heading-area">
              <h3 class="title" data-aos="zoom-in" data-aos-duration="900">
                PURE RICE ,PURE LIFE - WELCOME TO CHOLAVIN - YOUR RICE EXPERT
              </h3>
              <div class="hero2-info-wrap">
                <div
                  class="hero2-btn hm2-hero-btn-fix"
                  data-aos="fade-right"
                  data-aos-duration="900"
                >
                  <a
                    href="https://wa.me/{{ $commonSettings['whatsapp_number'] ?? '919965252555' }}?text=Hello%20Cholavin%20Your%20Rice%20Expert%2C%20I%20want%20to%20order%20rice."
                    class="vl-btn6"
                    target="_blank"
                    rel="noopener"
                    >Order Now<span class="arrow_btn4"
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
                <p class="text" data-aos="fade-right" data-aos-duration="1000">
                  Fresh, high-quality rice delivered to your doorstep in
                  Pallipalayam and Kumarapalayam.<br />
                  Premium quality rice varieties, fast delivery, and easy return
                  if not satisfied.
                </p>
              </div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="hero2-shape aniamtion-key-5">
              <img src="{{ asset('frontend/assets/') }}/img/shape/hero2-shape(1).webp" alt="" />
            </div>
          </div>
          <div class="space60"></div>
          <div class="hero2-slide-area">
            <div class="row align-items-center">
              <div class="col-xl-1 col-lg-1 col-md-1 col-2">
                <div class="hero2-follow-area">
                  <div
                    class="social_link_hm2"
                    data-aos="fade-right"
                    data-aos-duration="1000"
                  >
                    <ul class="social_link">
                      <li>
                        <a
                          href="{{ $commonSettings['facebook'] ?? '#' }}"
                          target="_blank"
                          rel="noopener"
                          ><i class="fa-brands fa-facebook-f"></i
                        ></a>
                      </li>
                      <li>
                        <a
                          href="{{ $commonSettings['instagram'] ?? '#' }}"
                          target="_blank"
                          rel="noopener"
                          ><i class="fa-brands fa-instagram"></i
                        ></a>
                      </li>
                      <!-- <li>
                        <a href="{{ route('frontend.contact') }}"
                          ><i class="fa-brands fa-youtube"></i
                        ></a>
                      </li> -->
                      <!-- <li>
                        <a href="{{ route('frontend.contact') }}"
                          ><i class="fa-brands fa-twitter"></i
                        ></a>
                      </li> -->
                    </ul>
                  </div>
                  <div
                    class="hero2-follow-shape"
                    data-aos="fade-right"
                    data-aos-duration="1100"
                  >
                    <img src="{{ asset('frontend/assets/') }}/img/shape/hm2-follow-shape.webp" alt="" />
                  </div>
                </div>
              </div>
              <div
                class="col-xl-10 col-lg-10 col-md-10 col-10"
                data-aos="zoom-in"
                data-aos-duration="900"
              >
                <div class="swiper mySwipertest">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <div class="hero2-slide-thumb">
                        <img src="{{ asset('frontend/assets/') }}/img/hero/hero2-thumb1.webp" alt="" />
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="hero2-slide-thumb">
                        <img src="{{ asset('frontend/assets/') }}/img/hero/hero2-thumb2.webp" alt="" />
                      </div>
                    </div>
                    <div class="swiper-slide">
                      <div class="hero2-slide-thumb">
                        <img src="{{ asset('frontend/assets/') }}/img/hero/hero2-thumb3.webp" alt="" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="col-xl-1 col-lg-1 col-md-1"
                data-aos="fade-left"
                data-aos-duration="1100"
              >
                <div class="hero2-slide-arrow">
                  <div class="prev_arrow">
                    <img src="{{ asset('frontend/assets/') }}/img/icon/up-arrow-hm2.svg" alt="" />
                  </div>
                  <div class="vl-hero2-shape2 aniamtion-key-5">
                    <img src="{{ asset('frontend/assets/') }}/img/shape/hero2-shape(3).webp" alt="" />
                  </div>
                  <div class="next_arrow">
                    <img src="{{ asset('frontend/assets/') }}/img/icon/down-arrow-hm2.svg" alt="" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--===== HERO AREA ENDS =======-->

    <!--===== ABOUT START =======-->

    <div class="vl-about6-area sp1">
      <div class="container">
        <div class="row">
          <div class="col-xl-5 col-lg-6">
            <div class="about6-thumb">
              <img
                class="thumb1"
                src="{{ asset('frontend/assets/') }}/img/service/services2-thumb.webp"
                alt=""
                data-aos="fade-right"
                data-aos-duration="1000"
              />
              <img
                class="thumb2"
                src="{{ asset($commonSettings['auth_brand_image'] ?? 'frontend/assets/img/logo/logo-hm64.png') }}"
                alt=""
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
                  data-aos-duration="800"
                ><img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.png" alt=""> About Our Rice Business <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.png" alt=""></h3>
                <div class="space16"></div>
                <h2 class="title" data-aos="fade-left" data-aos-duration="900">
                  Growing Naturally, Delivering Quality Rice to Every Home
                </h2>
              </div>
              <div class="row">
                <div class="col-xl-1"></div>
                <div class="col-xl-11">
                  <div class="about6-content">
                    <div class="row">
                      <div
                        class="col-xl-5 col-lg-5 aos-init aos-animate"
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
                              src="{{ asset('frontend/assets/') }}/img/shape/about2-shape(2).webp"
                              alt=""
                            />
                          </div>
                          <div class="about2-box3-shape aniamtion-key-2">
                            <img
                              src="{{ asset('frontend/assets/') }}/img/shape/about2-shape(3).webp"
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
                                src="{{ asset('frontend/assets/') }}/img/icon/about6-icon(1).svg"
                                alt=""
                              />
                            </div>
                            <div class="about6-icons-content">
                              <h3>
                                <a href="{{ route('frontend.about') }}"
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
                                src="{{ asset('frontend/assets/') }}/img/icon/about6-icon(2).svg"
                                alt=""
                              />
                            </div>
                            <div class="about6-icons-content">
                              <h3>
                                <a href="{{ route('frontend.about') }}"
                                  >Hygienic Packing and Storage</a
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
                            At Cholavin, we believe that real change begins at
                            home - in the soil beneath your feet and the food
                            you grow with your own hands. Our mission is to help
                            individuals and families embrace sustainable living
                            through home farming, rice delivery, and
                            eco-conscious choices.
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
                                src="{{ asset('frontend/assets/') }}/img/icon/tick-hm6.svg"
                                alt=""
                              />Trusted by local customers
                            </li>
                            <li>
                              <img
                                src="{{ asset('frontend/assets/') }}/img/icon/tick-hm6.svg"
                                alt=""
                              />100% customer satisfaction
                            </li>
                          </ul>
                          <div class="about6-wrap-line"></div>
                          <div class="about6-wrap-btn">
                            <a href="{{ route('frontend.contact') }}" class="btn-home6"
                              >Learn More</a
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

    <!--===== ABOUT END =======-->

    <!--===== Product area start =======-->
    <div class="vl-product6-area sp1">
      <div class="container">
        <div class="row">
          <div class="product6-heading">
            <div class="service6-top">
              <div class="service6-top-left">
                <h3
                  class="product6-subtitle"
                  data-aos="fade-right"
                  data-aos-duration="900"
                >
                  Our Rice Varieties
                </h3>
                <div class="space16"></div>
                <h2
                  class="clr-white"
                  data-aos="fade-left"
                  data-aos-duration="1000"
                >
                  Quality Rice for Every Meal
                </h2>
              </div>
              <div class="service6-top-right">
                <p
                  class="product6-pera"
                  data-aos="fade-left"
                  data-aos-duration="900"
                >
                  Choose from premium daily-use rice, aromatic biriyani rice,
                  and quality long-grain varieties trusted by homes, hotels, and
                  caterers in your area.
                </p>
                <div class="space24"></div>
                <a
                  href="https://wa.me/{{ $commonSettings['whatsapp_number'] ?? '919965252555' }}?text=Hello%20Cholavin%20Your%20Rice%20Expert%2C%20I%20want%20to%20order%20fresh%20rice."
                  class="btn4-home6"
                  target="_blank"
                  rel="noopener"
                  data-aos="fade-left"
                  data-aos-duration="1000"
                  >Order Fresh Rice Today</a
                >
              </div>
            </div>
          </div>
          <div class="space44"></div>
          <!-- Swiper -->
          <div
            class="swiper myproduct6"
            data-aos="zoom-out"
            data-aos-duration="900"
          >
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <div class="product6-box">
                  <div class="product-thumb">
                    <img
                      class="imgs"
                      src="{{ asset('frontend/assets/') }}/img/products/1.png"
                      alt="Ponni rice"
                    />
                    <div class="product6-badge">Daily Family Choice</div>
                    <div class="product6-grain-note">Soft texture</div>
                    <div class="product6-overlay-copy">
                      <span>Best For</span>
                      <strong>Lunch, dinner, and everyday cooking</strong>
                    </div>
                  </div>
                  <div class="space24"></div>
                  <div class="product6-box-content">
                    <div class="product6-content_text">
                      <h3><a href="{{ route('frontend.contact') }}">Ponni Rice</a></h3>
                      <p>
                        A reliable household rice variety with a gentle aroma
                        and soft finish that suits daily meals.
                      </p>
                    </div>
                    <div class="space16"></div>
                    <div class="product6-tags">
                      <span>Daily Use</span>
                      <span>Soft & Fluffy</span>
                      <span>Popular Choice</span>
                    </div>
                    <div class="space20"></div>
                    <div class="product6_info">
                      <div class="product6-meta">
                        <div>
                          <span class="meta-label">Pack Sizes</span>
                          <strong>10kg, 25kg, 50kg</strong>
                        </div>
                        <div>
                          <span class="meta-label">Suitable For</span>
                          <strong>Homes & mess kitchens</strong>
                        </div>
                      </div>
                      <div class="product6-cta">
                        <a href="{{ route('frontend.contact') }}">Enquire Now</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="product6-box">
                  <div class="product-thumb">
                    <img
                      class="imgs"
                      src="{{ asset('frontend/assets/') }}/img/products/2.png"
                      alt="Seeraga Samba rice"
                    />
                    <div class="product6-badge">Biriyani Special</div>
                    <div class="product6-grain-note">Rich aroma</div>
                    <div class="product6-overlay-copy">
                      <span>Best For</span>
                      <strong>Authentic biriyani and festive cooking</strong>
                    </div>
                  </div>
                  <div class="space24"></div>
                  <div class="product6-box-content">
                    <div class="product6-content_text">
                      <h3><a href="{{ route('frontend.contact') }}">Seeraga Samba</a></h3>
                      <p>
                        A premium small-grain rice treasured for its fragrance,
                        depth of flavor, and classic biriyani finish.
                      </p>
                    </div>
                    <div class="space16"></div>
                    <div class="product6-tags">
                      <span>Biriyani</span>
                      <span>Premium Aroma</span>
                      <span>Special Occasions</span>
                    </div>
                    <div class="space20"></div>
                    <div class="product6_info">
                      <div class="product6-meta">
                        <div>
                          <span class="meta-label">Pack Sizes</span>
                          <strong>5kg, 10kg, 25kg</strong>
                        </div>
                        <div>
                          <span class="meta-label">Suitable For</span>
                          <strong>Homes, catering, functions</strong>
                        </div>
                      </div>
                      <div class="product6-cta">
                        <a href="{{ route('frontend.contact') }}">Enquire Now</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="product6-box">
                  <div class="product-thumb">
                    <img
                      class="imgs"
                      src="{{ asset('frontend/assets/') }}/img/products/4.png"
                      alt="Idli rice"
                    />
                    <div class="product6-badge">Premium Long Grain</div>
                    <div class="product6-grain-note">Elegant finish</div>
                    <div class="product6-overlay-copy">
                      <span>Best For</span>
                      <strong>Pulao, fried rice, and premium meals</strong>
                    </div>
                  </div>
                  <div class="space24"></div>
                  <div class="product6-box-content">
                    <div class="product6-content_text">
                      <h3><a href="{{ route('frontend.contact') }}">Basmati Rice</a></h3>
                      <p>
                        Long slender grains with a refined aroma, ideal when
                        you want a more premium texture on the plate.
                      </p>
                    </div>
                    <div class="space16"></div>
                    <div class="product6-tags">
                      <span>Long Grain</span>
                      <span>Premium Quality</span>
                      <span>Hotel Style</span>
                    </div>
                    <div class="space20"></div>
                    <div class="product6_info">
                      <div class="product6-meta">
                        <div>
                          <span class="meta-label">Pack Sizes</span>
                          <strong>5kg, 10kg, 25kg</strong>
                        </div>
                        <div>
                          <span class="meta-label">Suitable For</span>
                          <strong>Hotels & premium orders</strong>
                        </div>
                      </div>
                      <div class="product6-cta">
                        <a href="{{ route('frontend.contact') }}">Enquire Now</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="product6-box">
                  <div class="product-thumb">
                    <img
                      class="imgs"
                      src="{{ asset('frontend/assets/') }}/img/products/4.png"
                      alt="Idli rice"
                    />
                    <div class="product6-badge">Tiffin Favourite</div>
                    <div class="product6-grain-note">Smooth batter</div>
                    <div class="product6-overlay-copy">
                      <span>Best For</span>
                      <strong>Idli, dosa, appam, and soft batter recipes</strong>
                    </div>
                  </div>
                  <div class="space24"></div>
                  <div class="product6-box-content">
                    <div class="product6-content_text">
                      <h3><a href="{{ route('frontend.contact') }}">Idli Rice</a></h3>
                      <p>
                        Chosen for soft, fluffy breakfast batter with dependable
                        soaking quality and familiar home-style results.
                      </p>
                    </div>
                    <div class="space16"></div>
                    <div class="product6-tags">
                      <span>Tiffin Use</span>
                      <span>Easy Grinding</span>
                      <span>Home Cooking</span>
                    </div>
                    <div class="space20"></div>
                    <div class="product6_info">
                      <div class="product6-meta">
                        <div>
                          <span class="meta-label">Pack Sizes</span>
                          <strong>10kg, 25kg, 50kg</strong>
                        </div>
                        <div>
                          <span class="meta-label">Suitable For</span>
                          <strong>Homes & small food businesses</strong>
                        </div>
                      </div>
                      <div class="product6-cta">
                        <a href="{{ route('frontend.contact') }}">Enquire Now</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="product6-box">
                  <div class="product-thumb">
                    <img
                      class="imgs"
                      src="{{ asset('frontend/assets/') }}/img/products/5.png"
                      alt="Raw rice"
                    />
                    <div class="product6-badge">Bulk Supply Ready</div>
                    <div class="product6-grain-note">Steady quality</div>
                    <div class="product6-overlay-copy">
                      <span>Best For</span>
                      <strong>Shops, hostels, functions, and regular supply</strong>
                    </div>
                  </div>
                  <div class="space24"></div>
                  <div class="product6-box-content">
                    <div class="product6-content_text">
                      <h3><a href="{{ route('frontend.contact') }}">Raw Rice</a></h3>
                      <p>
                        A practical option for steady day-to-day cooking and
                        bulk requirements where consistency matters most.
                      </p>
                    </div>
                    <div class="space16"></div>
                    <div class="product6-tags">
                      <span>Bulk Orders</span>
                      <span>Consistent Stock</span>
                      <span>Regular Supply</span>
                    </div>
                    <div class="space20"></div>
                    <div class="product6_info">
                      <div class="product6-meta">
                        <div>
                          <span class="meta-label">Pack Sizes</span>
                          <strong>25kg, 50kg</strong>
                        </div>
                        <div>
                          <span class="meta-label">Suitable For</span>
                          <strong>Retail, hostels, institutions</strong>
                        </div>
                      </div>
                      <div class="product6-cta">
                        <a href="{{ route('frontend.contact') }}">Enquire Now</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="product6-arrow">
            <div
              class="prev-arrow"
              data-aos="fade-right"
              data-aos-duration="900"
            >
              <img src="{{ asset('frontend/assets/') }}/img/icon/left-arrow-hm6.svg" alt="" />
            </div>
            <div
              class="next-arrow"
              data-aos="fade-left"
              data-aos-duration="900"
            >
              <img src="{{ asset('frontend/assets/') }}/img/icon/right-arrow-hm6.svg" alt="" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--===== WhatsApp Ordersarea start =======-->
    <div class="vl-testimonial6-area sp1">
      <div class="container">
        <div class="row">
          <div class="testimonial6-heading">
            <div class="service6-top">
              <div class="service6-top-left">
                <h3 data-aos="fade-right" data-aos-duration="900">
                  Customer Reviews
                </h3>
                <div class="space16"></div>
                <h2 class=" " data-aos="fade-left" data-aos-duration="1000">
                  Trusted by Families in Pallipalayam & Kumarapalayam
                </h2>
              </div>
              <div class="service6-top-right">
                <p
                  class="text-effect"
                  data-aos="fade-left"
                  data-aos-duration="1000"
                >
                  Trusted by families in Pallipalayam and Kumarapalayam for
                  quality rice, quick delivery, and dependable local service.
                </p>
              </div>
            </div>
          </div>
          <div class="space44"></div>
          <div class="col-xl-6 col-lg-6">
            <!-- Swiper -->
            <div
              class="swiper mytestimo6"
              data-aos="zoom-out"
              data-aos-duration="1000"
            >
              <div class="swiper-wrapper">
                <div class="swiper-slide">
                  <div class="testimonial6-info">
                    <div class="product_star testimonial6_star">
                      <ul>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                      </ul>
                    </div>
                    <div class="space24"></div>
                    <p class="testimonial6-text">
                      "I never imagined our rooftop could become thriving green
                      space. Cholavin didn't just give us a garden they gave us
                      a new way of living. My mornings now start with harvesting
                      fresh vegetables, my children are more connected to nature
                      than ever before."
                    </p>
                    <div class="space38"></div>
                    <div class="testimonial6-bottom">
                      <div class="testimonial6-user">
                        <h3><a href="#">Lakshmi Family</a></h3>
                        <div class="space16"></div>
                        <p>Pallipalayam</p>
                        <h3><a href="#">Manoj Kumar</a></h3>
                        <div class="space16"></div>
                        <p>Kumarapalayam</p>
                        <h3><a href="#">Sathya Catering</a></h3>
                        <div class="space16"></div>
                        <p>Local Biriyani Orders</p>
                      </div>
                      <div class="testimonial6-arrow">
                        <div class="next-arrow mr-12">
                          <a href="#"
                            ><img
                              src="{{ asset('frontend/assets/') }}/img/icon/arrow-left-testi06.svg"
                              alt=""
                          /></a>
                        </div>
                        <div class="prev-arrow">
                          <a href="#"
                            ><img
                              src="{{ asset('frontend/assets/') }}/img/icon/arrow-right-testi06.svg"
                              alt=""
                          /></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="testimonial6-info">
                    <div class="product_star testimonial6_star">
                      <ul>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                      </ul>
                    </div>
                    <div class="space24"></div>
                    <p class="testimonial6-text">
                      "I never imagined our rooftop could become thriving green
                      space. Cholavin didn't just give us a garden they gave us
                      a new way of living. My mornings now start with harvesting
                      fresh vegetables, my children are more connected to nature
                      than ever before."
                    </p>
                    <div class="space38"></div>
                    <div class="testimonial6-bottom">
                      <div class="testimonial6-user">
                        <h3><a href="#">Lakshmi Family</a></h3>
                        <div class="space16"></div>
                        <p>Pallipalayam</p>
                        <h3><a href="#">Manoj Kumar</a></h3>
                        <div class="space16"></div>
                        <p>Kumarapalayam</p>
                        <h3><a href="#">Sathya Catering</a></h3>
                        <div class="space16"></div>
                        <p>Local Biriyani Orders</p>
                      </div>
                      <div class="testimonial6-arrow">
                        <div class="next-arrow mr-12">
                          <a href="#"
                            ><img
                              src="{{ asset('frontend/assets/') }}/img/icon/arrow-left-testi06.svg"
                              alt=""
                          /></a>
                        </div>
                        <div class="prev-arrow">
                          <a href="#"
                            ><img
                              src="{{ asset('frontend/assets/') }}/img/icon/arrow-right-testi06.svg"
                              alt=""
                          /></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="testimonial6-info">
                    <div class="product_star testimonial6_star">
                      <ul>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                        <li>
                          <a href="#"><i class="fa-solid fa-star"></i></a>
                        </li>
                      </ul>
                    </div>
                    <div class="space24"></div>
                    <p class="testimonial6-text">
                      "I never imagined our rooftop could become thriving green
                      space. Cholavin didn't just give us a garden they gave us
                      a new way of living. My mornings now start with harvesting
                      fresh vegetables, my children are more connected to nature
                      than ever before."
                    </p>
                    <div class="space38"></div>
                    <div class="testimonial6-bottom">
                      <div class="testimonial6-user">
                        <h3><a href="#">Lakshmi Family</a></h3>
                        <div class="space16"></div>
                        <p>Pallipalayam</p>
                        <h3><a href="#">Manoj Kumar</a></h3>
                        <div class="space16"></div>
                        <p>Kumarapalayam</p>
                        <h3><a href="#">Sathya Catering</a></h3>
                        <div class="space16"></div>
                        <p>Local Biriyani Orders</p>
                      </div>
                      <div class="testimonial6-arrow">
                        <div class="next-arrow mr-12">
                          <a href="#"
                            ><img
                              src="{{ asset('frontend/assets/') }}/img/icon/arrow-left-testi06.svg"
                              alt=""
                          /></a>
                        </div>
                        <div class="prev-arrow">
                          <a href="#"
                            ><img
                              src="{{ asset('frontend/assets/') }}/img/icon/arrow-right-testi06.svg"
                              alt=""
                          /></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-lg-6">
            <div class="testimonial6-thumb">
              <img
                  src="{{ asset('frontend/assets/') }}/img/hero/2.png"
                  alt=""
                />
              </div>
              <div class="testimonial6-thumb-img2 image-anime"> 
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--===== WhatsApp Ordersarea end =======-->  

    <!--===== CONTACT AREA START =======-->
    <div class="vl-contact2-area sp1">
      <div class="container">
        <div class="row">
          <div class="contact2-header">
            <div class="vl-services2-header">
              <div class="vl-services2-topleft">
                <h3 data-aos="fade-left" data-aos-duration="900">
                  <img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" />
                  Contact
                  <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />
                </h3>
                <div class="space24"></div>
                <h2 class=" " data-aos="fade-right" data-aos-duration="1000">
                  Order Fresh Rice Today - Contact Us
                </h2>
              </div>
              <div class="vl-services2-topright">
                <p
                  class="text-effect"
                  data-aos="fade-left"
                  data-aos-duration="1100"
                >
                  Fresh rice, fast delivery, and instant WhatsApp support for
                  homes, hotels, and catering orders in Pallipalayam and
                  Kumarapalayam.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="space44"></div>
        <div class="row">
         <div class="col-xl-6 col-lg-6 col-md-6">
            <div class="vl-contact2-info mb-30">
              <div
                class="contact2-info-mobile contact-inr-fix"
                data-aos="zoom-in"
                data-aos-duration="1000"
              >
                <div class="contact2-mobile-content md-mb20 xs-mb20">
                  <div class="contact2-mobile-logo">
                    <a href="{{ route('frontend.contact') }}"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        fill="none"
                      >
                        <path
                          d="M18.1569 28.4887C17.5788 29.03 16.8059 29.3327 16.0015 29.3327C15.1971 29.3327 14.4243 29.03 13.846 28.4887C8.55069 23.5007 1.45435 17.9285 4.91503 9.8389C6.78617 5.4649 11.2778 2.66602 16.0015 2.66602C20.7252 2.66602 25.2168 5.4649 27.088 9.8389C30.5443 17.9184 23.4653 23.5179 18.1569 28.4887Z"
                          stroke="white"
                          stroke-width="1.5"
                        />
                        <path
                          d="M20.6673 14.6667C20.6673 17.244 18.578 19.3333 16.0007 19.3333C13.4233 19.3333 11.334 17.244 11.334 14.6667C11.334 12.0893 13.4233 10 16.0007 10C18.578 10 20.6673 12.0893 20.6673 14.6667Z"
                          stroke="white"
                          stroke-width="1.5"
                        /></svg
                    ></a>
                  </div>
                  <div class="contact2-mobile-text">
                    <h3><a href="{{ route('frontend.contact') }}">Pallipalayam Branch</a></h3>
                   
                    <ul>
                      <li>
                        <a href="{{ route('frontend.contact') }}">Cholavin - Your rice expert</a>
                      </li>
                      <li>
                        <a href="{{ route('frontend.contact') }}"
                          >No. 19, Bypass Road, Pallipalayam,<br>
                           Namakkal - 638006</a
                        >
                      </li>
                    </ul>
                  </div>
                </div>
                 <div class="space16"></div>
                <div class="contact2-mobile-content mt-16 md-mb20 xs-mb20">
                  <div class="contact2-mobile-logo">
                    <a href="{{ route('frontend.contact') }}"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        fill="none"
                      >
                        <path
                          d="M2.66602 6.66602L11.8834 11.8988C15.2509 13.8107 16.7477 13.8107 20.1153 11.8988L29.3327 6.66602"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M13.9993 26.0006C13.3776 25.9925 12.7551 25.9806 12.1311 25.9649C7.93312 25.8594 5.83412 25.8066 4.32596 24.2918C2.81779 22.777 2.7742 20.7322 2.68704 16.6425C2.65902 15.3274 2.659 14.0203 2.68703 12.7052C2.7742 8.61549 2.81778 6.57062 4.32595 5.05584C5.83412 3.54105 7.93312 3.48829 12.1311 3.38276C14.7184 3.31772 17.2803 3.31773 19.8676 3.38277C24.0656 3.4883 26.1645 3.54108 27.6727 5.05586C29.1809 6.57064 29.2245 8.6155 29.3116 12.7052C29.3245 13.3107 29.3315 13.596 29.3326 14.0007"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M25.334 22.666C25.334 23.7705 24.4385 24.666 23.334 24.666C22.2295 24.666 21.334 23.7705 21.334 22.666C21.334 21.5615 22.2295 20.666 23.334 20.666C24.4385 20.666 25.334 21.5615 25.334 22.666ZM25.334 22.666V23.3327C25.334 24.4372 26.2295 25.3327 27.334 25.3327C28.4385 25.3327 29.334 24.4372 29.334 23.3327V22.666C29.334 19.3523 26.6477 16.666 23.334 16.666C20.0203 16.666 17.334 19.3523 17.334 22.666C17.334 25.9797 20.0203 28.666 23.334 28.666"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        /></svg
                    ></a>
                  </div>
                  <div class="contact2-mobile-text">
                    <h3><a href="{{ route('frontend.contact') }}">WhatsApp</a></h3> 
                    <ul> 
                      <li>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $commonSettings['contact_phone'] ?? '') }}">{{ $commonSettings['contact_phone'] ?? '+91 99652 52555' }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
                 <div class="space16"></div>
                <div class="contact2-mobile-content mt-16">
                  <div class="contact2-mobile-logo">
                    <a href="{{ route('frontend.contact') }}"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        fill="none"
                      >
                        <path
                          d="M18.666 4V8M25.3327 6.66667L22.666 9.33333M27.9993 13.3333H23.9993"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.211 7.61631L11.6741 6.40833C11.3231 5.61851 11.1476 5.22357 10.8851 4.92135C10.5561 4.54259 10.1273 4.26392 9.64756 4.11713C9.26477 4 8.83261 4 7.96828 4C6.70388 4 6.07167 4 5.54097 4.24305C4.91583 4.52936 4.35124 5.15104 4.12631 5.8008C3.93535 6.35239 3.99004 6.91924 4.09944 8.05293C5.26388 20.1203 11.8797 26.7361 23.9471 27.9005C25.0808 28.01 25.6476 28.0647 26.1992 27.8737C26.8489 27.6488 27.4707 27.0841 27.7569 26.4591C28 25.9283 28 25.2961 28 24.0317C28 23.1673 28 22.7352 27.8829 22.3524C27.7361 21.8727 27.4575 21.4439 27.0787 21.1149C26.7764 20.8524 26.3815 20.6769 25.5916 20.3259L24.3837 19.7889C23.5283 19.4088 23.1007 19.2188 22.666 19.1775C22.2501 19.1379 21.8308 19.1963 21.4415 19.3479C21.0347 19.5063 20.6751 19.8059 19.956 20.4051C19.2401 21.0016 18.8823 21.2999 18.4449 21.4596C18.0573 21.6012 17.5448 21.6537 17.1364 21.5935C16.6759 21.5256 16.3231 21.3372 15.6177 20.9601C13.4229 19.7873 12.2127 18.5771 11.0398 16.3823C10.6629 15.6769 10.4744 15.3241 10.4065 14.8636C10.3463 14.4552 10.3988 13.9427 10.5404 13.5551C10.7002 13.1177 10.9984 12.7598 11.5949 12.044C12.1942 11.3249 12.4938 10.9654 12.6522 10.5585C12.8038 10.1693 12.8621 9.74987 12.8226 9.33397C12.7813 8.89936 12.5912 8.47168 12.211 7.61631Z"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                        /></svg
                    ></a>
                  </div>
                  <div class="contact2-mobile-text">
                    <h3><a href="{{ route('frontend.contact') }}">Working Hours</a></h3> 
                    <ul>
                      <li>
                        <a href="tel:(813)752-5611"
                          >All Days 9:00 AM - 9:00 PM</a
                        >
                      </li>
                      <li><a href="tel:(830)556-6651">Sunday: Open</a></li>
                    </ul>
                  </div>
                </div>
                <div class="contact-box-inr-shape">
                  <img src="{{ asset('frontend/assets/') }}/img/contact/contact-box-inr-shape.webp" alt="">
                </div>
              </div> 
            </div>
            <div class="vl-contact2-info">
              <div
                class="contact2-info-mobile contact-inr-fix"
                data-aos="zoom-in"
                data-aos-duration="1000"
              >
                <div class="contact2-mobile-content md-mb20 xs-mb20">
                  <div class="contact2-mobile-logo">
                    <a href="{{ route('frontend.contact') }}"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        fill="none"
                      >
                        <path
                          d="M18.1569 28.4887C17.5788 29.03 16.8059 29.3327 16.0015 29.3327C15.1971 29.3327 14.4243 29.03 13.846 28.4887C8.55069 23.5007 1.45435 17.9285 4.91503 9.8389C6.78617 5.4649 11.2778 2.66602 16.0015 2.66602C20.7252 2.66602 25.2168 5.4649 27.088 9.8389C30.5443 17.9184 23.4653 23.5179 18.1569 28.4887Z"
                          stroke="white"
                          stroke-width="1.5"
                        />
                        <path
                          d="M20.6673 14.6667C20.6673 17.244 18.578 19.3333 16.0007 19.3333C13.4233 19.3333 11.334 17.244 11.334 14.6667C11.334 12.0893 13.4233 10 16.0007 10C18.578 10 20.6673 12.0893 20.6673 14.6667Z"
                          stroke="white"
                          stroke-width="1.5"
                        /></svg
                    ></a>
                  </div>
                  <div class="contact2-mobile-text">
                    <h3><a href="{{ route('frontend.contact') }}">Kumarapalayam Branch</a></h3> 
                    <ul>
                      <li>
                        <a href="{{ route('frontend.contact') }}">Cholavin - Your rice expert</a>
                      </li>
                      <li>
                          <a href="{{ route('frontend.contact') }}"
                            >No. 66, New Pallipalayam Road, <br>Opposite Gowri Theatre,<br> Namakkal - 638183</a
                          >
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="contact2-mobile-content mt-16 md-mb20 xs-mb20">
                  <div class="contact2-mobile-logo">
                    <a href="{{ route('frontend.contact') }}"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        fill="none"
                      >
                        <path
                          d="M2.66602 6.66602L11.8834 11.8988C15.2509 13.8107 16.7477 13.8107 20.1153 11.8988L29.3327 6.66602"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M13.9993 26.0006C13.3776 25.9925 12.7551 25.9806 12.1311 25.9649C7.93312 25.8594 5.83412 25.8066 4.32596 24.2918C2.81779 22.777 2.7742 20.7322 2.68704 16.6425C2.65902 15.3274 2.659 14.0203 2.68703 12.7052C2.7742 8.61549 2.81778 6.57062 4.32595 5.05584C5.83412 3.54105 7.93312 3.48829 12.1311 3.38276C14.7184 3.31772 17.2803 3.31773 19.8676 3.38277C24.0656 3.4883 26.1645 3.54108 27.6727 5.05586C29.1809 6.57064 29.2245 8.6155 29.3116 12.7052C29.3245 13.3107 29.3315 13.596 29.3326 14.0007"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M25.334 22.666C25.334 23.7705 24.4385 24.666 23.334 24.666C22.2295 24.666 21.334 23.7705 21.334 22.666C21.334 21.5615 22.2295 20.666 23.334 20.666C24.4385 20.666 25.334 21.5615 25.334 22.666ZM25.334 22.666V23.3327C25.334 24.4372 26.2295 25.3327 27.334 25.3327C28.4385 25.3327 29.334 24.4372 29.334 23.3327V22.666C29.334 19.3523 26.6477 16.666 23.334 16.666C20.0203 16.666 17.334 19.3523 17.334 22.666C17.334 25.9797 20.0203 28.666 23.334 28.666"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        /></svg
                    ></a>
                  </div>
                  <div class="contact2-mobile-text">
                    <h3><a href="{{ route('frontend.contact') }}">WhatsApp</a></h3>
                    <div class="space16"></div>
                    <ul>
                      <li>
                        <a href="{{ route('frontend.contact') }}"
                          >Available for instant orders</a
                        >
                      </li>
                      <li>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $commonSettings['contact_phone'] ?? '') }}">{{ $commonSettings['contact_phone'] ?? '+91 99652 52555' }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="contact2-mobile-content mt-16">
                  <div class="contact2-mobile-logo">
                    <a href="{{ route('frontend.contact') }}"
                      ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        fill="none"
                      >
                        <path
                          d="M18.666 4V8M25.3327 6.66667L22.666 9.33333M27.9993 13.3333H23.9993"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.211 7.61631L11.6741 6.40833C11.3231 5.61851 11.1476 5.22357 10.8851 4.92135C10.5561 4.54259 10.1273 4.26392 9.64756 4.11713C9.26477 4 8.83261 4 7.96828 4C6.70388 4 6.07167 4 5.54097 4.24305C4.91583 4.52936 4.35124 5.15104 4.12631 5.8008C3.93535 6.35239 3.99004 6.91924 4.09944 8.05293C5.26388 20.1203 11.8797 26.7361 23.9471 27.9005C25.0808 28.01 25.6476 28.0647 26.1992 27.8737C26.8489 27.6488 27.4707 27.0841 27.7569 26.4591C28 25.9283 28 25.2961 28 24.0317C28 23.1673 28 22.7352 27.8829 22.3524C27.7361 21.8727 27.4575 21.4439 27.0787 21.1149C26.7764 20.8524 26.3815 20.6769 25.5916 20.3259L24.3837 19.7889C23.5283 19.4088 23.1007 19.2188 22.666 19.1775C22.2501 19.1379 21.8308 19.1963 21.4415 19.3479C21.0347 19.5063 20.6751 19.8059 19.956 20.4051C19.2401 21.0016 18.8823 21.2999 18.4449 21.4596C18.0573 21.6012 17.5448 21.6537 17.1364 21.5935C16.6759 21.5256 16.3231 21.3372 15.6177 20.9601C13.4229 19.7873 12.2127 18.5771 11.0398 16.3823C10.6629 15.6769 10.4744 15.3241 10.4065 14.8636C10.3463 14.4552 10.3988 13.9427 10.5404 13.5551C10.7002 13.1177 10.9984 12.7598 11.5949 12.044C12.1942 11.3249 12.4938 10.9654 12.6522 10.5585C12.8038 10.1693 12.8621 9.74987 12.8226 9.33397C12.7813 8.89936 12.5912 8.47168 12.211 7.61631Z"
                          stroke="white"
                          stroke-width="1.5"
                          stroke-linecap="round"
                        /></svg
                    ></a>
                  </div>
                  <div class="contact2-mobile-text">
                    <h3><a href="{{ route('frontend.contact') }}">Working Hours</a></h3>
                    <div class="space16"></div>
                    <ul>
                      <li>
                        <a href="tel:(813)752-5611"
                          >All Days 9:00 AM - 9:00 PM</a
                        >
                      </li>
                      <li><a href="tel:(830)556-6651">Sunday: Open</a></li>
                    </ul>
                  </div>
                </div>
                <div class="contact-box-inr-shape">
                  <img src="{{ asset('frontend/assets/') }}/img/contact/contact-box-inr-shape.webp" alt="">
                </div>
              </div> 
            </div>
          </div>
          <div
            class="col-xl-6 col-lg-6 col-md-6"
            data-aos="zoom-in"
            data-aos-duration="1100"
          >
            <div class="contact2-form-area xs-mt20">
              <h2 class="title">Order Fresh Rice Today - Contact Us</h2>
              <div class="space16"></div>
              <p class="pera_text">
                Place your rice order quickly through WhatsApp. We support
                daily-use rice, biriyani rice, premium rice, and bulk supply
                requirements.
              </p>
              <div class="space32"></div>

              <form id="orderForm" action="{{ route('frontend.enquiry.store') }}" method="post" novalidate>
                @csrf
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="contact2-field">
                      <label class="contact2-label" for="name">Full Name</label>
                      <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        autocomplete="name"
                      />
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="contact2-field">
                      <label class="contact2-label" for="email"
                        >Email Address</label
                      >
                      <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                      />
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="contact2-field">
                      <label class="contact2-label" for="phone"
                        >Phone Number</label
                      >
                      <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="Enter your 10-digit phone number"
                        autocomplete="tel"
                      />
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="contact2-field contact2-select-wrap">
                      <label class="contact2-label" for="service"
                        >Requirement</label
                      >
                      <select
                        id="service"
                        name="service"
                        class="no-nice-select"
                      >
                        <option value="">Select Requirement</option>
                        <option value="Daily Rice">Daily Rice</option>
                        <option value="Biriyani Rice">Biriyani Rice</option>
                        <option value="Bulk Order">Bulk Order</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="contact2-field">
                      <label class="contact2-label" for="message"
                        >Message</label
                      >
                      <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Tell us what you need"
                      ></textarea>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="contact2-submit-wrap align-items-center">
                      <button type="submit" class="btn-whatsapp-submit">
                        <i class="fa-brands fa-whatsapp"></i> Submit
                      </button>
                    </div>
                  </div>
                </div>

                <div class="space28"></div> 
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--===== CONTACT AREA ENDS =======-->
@endsection

