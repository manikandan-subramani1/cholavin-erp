@extends('frontend.layouts.app')

@section('title', 'Contact Us | Cholavin')


@push('styles')
<style>
  .maps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
  }
  .vl-contact_inner-map-area {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #E8D6B5;
  }
  .vl-contact_inner-map-area iframe {
    width: 100%;
    height: 280px;
    border: none;
    display: block;
  }
  .map-branch-title {
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #8F0028;
    margin-bottom: 12px;
    padding: 8px 0;
    background: #FFF8E6;
    border-radius: 8px 8px 0 0;
  }
</style>
@endpush

@section('content')
<!--=====HEADER END =======-->




  <!-- ===== HERO START =======-->
  <div class="vl-hero-inner-area parallaxie" style="background-image: url({{ asset('frontend/assets/') }}/img/hero/about-us-inr-herothumb.webp); background-position: center; background-size: cover; background-repeat: no-repeat;">
    <div class="container">
      <div class="row">
        <div class="col-xl-6">
          <div class="inner-hero-info">
            <h2>Contact Us</h2>
            <div class="space16"></div>
            <ul>
              <li><a href="{{ route('frontend.home') }}">Home</a></li>
              <li><img src="{{ asset('frontend/assets/') }}/img/icon/arrow-right-testi06 - Copy.svg" alt=""></li>
              <li><a class="aboutus_titlefix" href="#"> Contact Us</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--===== HERO END ======= -->




  <!--===== CONTACT AREA START =======-->
  <div class="vl-contact-inr-area sp1">
    <div class="container">
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
                        <a href="{{ route('frontend.contact') }}">+91 99652 52555</a>
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
                        <a href="{{ route('frontend.contact') }}">+91 99652 52555</a>
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


  <!--===== CONTACT AREA END =======-->

     
  <div class="maps-grid">
    <div class="vl-contact_inner-map-area">
      <div class="map-branch-title">Pallipalayam Branch</div>
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3911.5701228109256!2d77.74520687452438!3d11.366076848041873!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba96743aa62fbaf%3A0x433f52e9e861f68f!2sCHOLA%20FOODS!5e0!3m2!1sen!2sin!4v1776516436699!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <div class="vl-contact_inner-map-area">
      <div class="map-branch-title">Kumarapalayam Branch</div>
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3910.647077452689!2d77.69280739999999!3d11.433141599999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba96951d8408453%3A0x76162402d67d5e45!2sCHOLAVIN%20-%20KOMARAPALAYAM!5e0!3m2!1sen!2sin!4v1780941456080!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
@endsection

