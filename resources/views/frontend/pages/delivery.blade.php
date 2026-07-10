@extends('frontend.layouts.app')

@section('title', 'Delivery | Cholavin')


@section('content')
<!--=====HEADER END =======-->
    <div
      class="vl-hero6-area parallaxie delivery-hero"
      style="
        background-image: url({{ asset('frontend/assets/') }}/img/hero/hero6-thumb-bg1.png);
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
      "
    >
      <div class="hero6-bg1">
        <div class="bg-shape-imgs-1">
          <img src="{{ asset('frontend/assets/') }}/img/shape/hero-hm6-circle.png" alt="" />
        </div>
        <div class="container">
          <div class="row align-items-center">
            <div class="col-xl-7 col-lg-8">
              <div class="vl-hero6-info">
                <div class="hero6-heading">
                  <h3 data-aos="fade-left" data-aos-duration="900">
                  <img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" />
                  Rice Delivery And Return Support
                  <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />
                </h3>
                  <div class="space16"></div>
                  <h2
                    class="title"
                    data-aos="fade-left"
                    data-aos-duration="900"
                  >
                    Track Every Step From Order To Safe Return
                  </h2>
                  <div class="space16"></div>
                  <p
                    class="pera-text"
                    data-aos="fade-left"
                    data-aos-duration="1000"
                  >
                    Cholovin Your rice expert makes rice ordering simple with doorstep
                    delivery, clear packing updates, and an easy return flow for
                    damaged or incorrect bags.
                  </p>
                </div>
                <div class="space38"></div>
                <div class="hero6-btn-area">
                  <a
                    href="{{ route('frontend.contact') }}"
                    class="btn-home6 hero6-btn-fxr"
                    data-aos="zoom-out"
                    data-aos-duration="900"
                    >Book Delivery</a
                  ><a
                    href="{{ route('frontend.contact') }}"
                    class="btn2-home6"
                    data-aos="zoom-out"
                    data-aos-duration="900"
                    >Request Return</a
                  >
                </div>
                <div class="space32"></div>
                <div
                  class="delivery-hero-points"
                  data-aos="fade-up"
                  data-aos-duration="1000"
                >
                  <span>Same-day local dispatch</span
                  ><span>Careful bag handling</span
                  ><span>Easy return coordination</span>
                </div>
              </div>
            </div>
            <div class="col-xl-5 col-lg-4">
              <div
                class="delivery-hero-card"
                data-aos="zoom-out"
                data-aos-duration="1000"
              >
                <div class="delivery-hero-card-top">
                  <span class="status-badge">Live Process</span
                  ><span class="status-time">6 Steps</span>
                </div>
                <h3>Rice Delivery Timeline</h3>
                <p>
                  Order confirmation, packing, dispatch, doorstep handover,
                  return pickup, and replacement closure in one clear workflow.
                </p>
                <ul>
                  <li>
                    <i class="fa-solid fa-check"></i> WhatsApp and phone booking
                    support
                  </li>
                  <li>
                    <i class="fa-solid fa-check"></i> Delivery across nearby
                    local areas
                  </li>
                  <li>
                    <i class="fa-solid fa-check"></i> Return support for damage
                    or wrong item
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="hero_bottom_slider">
          <div class="hero6-bottom-text"><h2>Rice Delivery</h2></div>
          <div class="hero6-bottom-text"><h2>Order Tracking</h2></div>
          <div class="hero6-bottom-text"><h2>Safe Packing</h2></div>
          <div class="hero6-bottom-text"><h2>Doorstep Service</h2></div>
          <div class="hero6-bottom-text"><h2>Easy Return</h2></div>
          <div class="hero6-bottom-text"><h2>Customer Support</h2></div>
        </div>
      </div>
    </div>
    <div class="delivery-overview-area sp1">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-xl-6 col-lg-6">
            <div class="service6-top-left">
              <h3 data-aos="fade-left" data-aos-duration="900">
                  <img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" />
                  How We Handle It
                  <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />
                </h3>
              <div class="space16"></div>
              <h2
                class="text-anime-style-3"
                data-aos="fade-left"
                data-aos-duration="1000"
              >
                A Clear Delivery Journey For Every Rice Order
              </h2>
              <div class="space24"></div>
              <p
                class="delivery-section-text"
                data-aos="fade-left"
                data-aos-duration="1100"
              >
                We use a simple step-by-step process so families, hotels, and
                retail buyers know exactly what happens after placing an order.
                If something is wrong with the rice bag, we also guide the
                return and replacement smoothly.
              </p>
            </div>
          </div>
          <div class="col-xl-6 col-lg-6">
            <div class="delivery-overview-grid">
              <div
                class="delivery-overview-item"
                data-aos="zoom-out"
                data-aos-duration="900"
              >
                <h3>01</h3>
                <p>
                  Quick order confirmation with rice type, weight, and delivery
                  area.
                </p>
              </div>
              <div
                class="delivery-overview-item"
                data-aos="zoom-out"
                data-aos-duration="1000"
              >
                <h3>02</h3>
                <p>
                  Clean packing and careful loading before dispatch from our
                  store.
                </p>
              </div>
              <div
                class="delivery-overview-item"
                data-aos="zoom-out"
                data-aos-duration="1100"
              >
                <h3>03</h3>
                <p>Fast local delivery with clear customer communication.</p>
              </div>
              <div
                class="delivery-overview-item"
                data-aos="zoom-out"
                data-aos-duration="1200"
              >
                <h3>04</h3>
                <p>Easy return pickup and replacement support when required.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="delivery-timeline-section sp1">
      <div class="container">
        <div class="service6-top">
          <div class="service6-top-left">
            <h3 data-aos="fade-left" data-aos-duration="900">
                  <img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" />
                  Timeline
                  <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />
                </h3>
            <div class="space16"></div>
            <h2
              class="text-anime-style-3"
              data-aos="fade-left"
              data-aos-duration="1000"
            >
              Rice Delivery And Return Timeline
            </h2>
          </div>
          <div class="service6-top-right">
            <p class="text-effect" data-aos="fade-left" data-aos-duration="900">
              This layout shows the full customer journey, from placing the
              order to getting support after delivery.
            </p>
          </div>
        </div>
        <div class="space44"></div>
        <div class="delivery-timeline">
          <div
            class="delivery-timeline-row"
            data-aos="fade-right"
            data-aos-duration="900"
          >
            <div class="delivery-timeline-card">
              <div class="service6-box">
                <div class="service6-logos">
                  <h3 class="title">
                    <a href="{{ route('frontend.contact') }}"
                      >Order Placed <br />
                      By Call Or WhatsApp</a
                    >
                  </h3>
                  <div class="inons">
                    <i class="fa-solid fa-phone-volume"></i>
                  </div>
                </div>
                <div class="space24"></div>
                <p class="pera-text">
                  Customers share the rice variety, bag size, quantity, and
                  address. Our team confirms stock, price, and expected delivery
                  slot before processing.
                </p>
                <div class="space28"></div>
                <div class="service6-box-bottom">
                  <a href="{{ route('frontend.contact') }}" class="btn3-home6">Place Order</a>
                  <div class="step-number">01</div>
                </div>
              </div>
            </div>
            <div class="delivery-timeline-marker">
              <span><i class="fa-solid fa-cart-shopping"></i></span
              ><strong>Order Received</strong>
            </div>
            <div class="delivery-timeline-space"></div>
          </div>
          <div
            class="delivery-timeline-row is-right"
            data-aos="fade-left"
            data-aos-duration="900"
          >
            <div class="delivery-timeline-space"></div>
            <div class="delivery-timeline-marker">
              <span><i class="fa-solid fa-box-open"></i></span
              ><strong>Packed Securely</strong>
            </div>
            <div class="delivery-timeline-card">
              <div class="service6-box">
                <div class="service6-logos">
                  <h3 class="title">
                    <a href="{{ route('frontend.contact') }}"
                      >Bag Checking <br />
                      And Packing</a
                    >
                  </h3>
                  <div class="inons"><i class="fa-solid fa-wheat-awn"></i></div>
                </div>
                <div class="space24"></div>
                <p class="pera-text">
                  Each rice bag is checked for correct brand, weight, and
                  condition. We pack it carefully to avoid tearing, moisture
                  contact, or wrong item mix-up.
                </p>
                <div class="space28"></div>
                <div class="service6-box-bottom">
                  <a href="{{ route('frontend.products') }}" class="btn3-home6">View Rice</a>
                  <div class="step-number">02</div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="delivery-timeline-row"
            data-aos="fade-right"
            data-aos-duration="900"
          >
            <div class="delivery-timeline-card">
              <div class="service6-box">
                <div class="service6-logos">
                  <h3 class="title">
                    <a href="{{ route('frontend.contact') }}"
                      >Dispatch And <br />
                      Doorstep Delivery</a
                    >
                  </h3>
                  <div class="inons">
                    <i class="fa-solid fa-truck-fast"></i>
                  </div>
                </div>
                <div class="space24"></div>
                <p class="pera-text">
                  Our delivery team calls before arrival when needed and hands
                  over the order safely. Bulk customers can also receive
                  scheduled recurring delivery support.
                </p>
                <div class="space28"></div>
                <div class="service6-box-bottom">
                  <a href="{{ route('frontend.contact') }}" class="btn3-home6">Delivery Areas</a>
                  <div class="step-number">03</div>
                </div>
              </div>
            </div>
            <div class="delivery-timeline-marker">
              <span><i class="fa-solid fa-house-circle-check"></i></span
              ><strong>Delivered</strong>
            </div>
            <div class="delivery-timeline-space"></div>
          </div>
          <div
            class="delivery-timeline-row is-right"
            data-aos="fade-left"
            data-aos-duration="900"
          >
            <div class="delivery-timeline-space"></div>
            <div class="delivery-timeline-marker">
              <span><i class="fa-solid fa-triangle-exclamation"></i></span
              ><strong>Issue Reported</strong>
            </div>
            <div class="delivery-timeline-card">
              <div class="service6-box">
                <div class="service6-logos">
                  <h3 class="title">
                    <a href="{{ route('frontend.contact') }}"
                      >Return Request <br />
                      For Wrong Or Damaged Rice</a
                    >
                  </h3>
                  <div class="inons">
                    <i class="fa-solid fa-rotate-left"></i>
                  </div>
                </div>
                <div class="space24"></div>
                <p class="pera-text">
                  If the delivered bag is damaged, incorrect, or not in expected
                  condition, customers can contact us with order details and
                  photos for quick review.
                </p>
                <div class="space28"></div>
                <div class="service6-box-bottom">
                  <a href="{{ route('frontend.contact') }}" class="btn3-home6">Raise Return</a>
                  <div class="step-number">04</div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="delivery-timeline-row"
            data-aos="fade-right"
            data-aos-duration="900"
          >
            <div class="delivery-timeline-card">
              <div class="service6-box">
                <div class="service6-logos">
                  <h3 class="title">
                    <a href="{{ route('frontend.contact') }}"
                      >Pickup And <br />
                      Quality Verification</a
                    >
                  </h3>
                  <div class="inons">
                    <i class="fa-solid fa-clipboard-check"></i>
                  </div>
                </div>
                <div class="space24"></div>
                <p class="pera-text">
                  Our team arranges pickup or verifies the issue based on local
                  support terms. Once reviewed, we confirm whether the customer
                  receives replacement rice or another resolution.
                </p>
                <div class="space28"></div>
                <div class="service6-box-bottom">
                  <a href="{{ route('frontend.contact') }}" class="btn3-home6">Need Help</a>
                  <div class="step-number">05</div>
                </div>
              </div>
            </div>
            <div class="delivery-timeline-marker">
              <span><i class="fa-solid fa-magnifying-glass"></i></span
              ><strong>Checked</strong>
            </div>
            <div class="delivery-timeline-space"></div>
          </div>
          <div
            class="delivery-timeline-row is-right"
            data-aos="fade-left"
            data-aos-duration="900"
          >
            <div class="delivery-timeline-space"></div>
            <div class="delivery-timeline-marker">
              <span><i class="fa-solid fa-circle-check"></i></span
              ><strong>Resolved</strong>
            </div>
            <div class="delivery-timeline-card">
              <div class="service6-box">
                <div class="service6-logos">
                  <h3 class="title">
                    <a href="{{ route('frontend.contact') }}"
                      >Replacement Delivery <br />
                      Or Return Closure</a
                    >
                  </h3>
                  <div class="inons">
                    <i class="fa-solid fa-handshake-angle"></i>
                  </div>
                </div>
                <div class="space24"></div>
                <p class="pera-text">
                  After approval, we complete the replacement delivery or close
                  the return request with transparent customer communication so
                  the order ends smoothly.
                </p>
                <div class="space28"></div>
                <div class="service6-box-bottom">
                  <a href="{{ route('frontend.contact') }}" class="btn3-home6">Talk To Team</a>
                  <div class="step-number">06</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="vl-service6-area sp1 delivery-support-area">
      <div class="container">
        <div class="service6-top">
          <div class="service6-top-left"> 
                  <h3 data-aos="fade-left" data-aos-duration="900">
                  <img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" />
                  Why Customers Choose Us
                  <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />
                </h3> 
            <div class="space16"></div>
            <h2
              class="text-anime-style-3"
              data-aos="fade-left"
              data-aos-duration="1000"
            >
              Delivery Support Built Around Trust
            </h2>
          </div>
          <div class="service6-top-right">
            <p class="text-effect" data-aos="fade-left" data-aos-duration="900">
              These service points explain the value behind the timeline and
              keep the page content complete.
            </p>
          </div>
        </div>
        <div class="space44"></div>
        <div class="row">
          <div class="col-xl-4 col-md-6">
            <div
              class="service6-box"
              data-aos="zoom-out"
              data-aos-duration="900"
            >
              <div class="service6-logos">
                <h3 class="title">
                  <a href="{{ route('frontend.contact') }}"
                    >Fast Local <br />
                    Dispatch</a
                  >
                </h3>
                <div class="inons">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
              </div>
              <div class="space24"></div>
              <p class="pera-text">
                We serve nearby areas with quick scheduling so regular household
                orders and urgent kitchen needs are handled on time.
              </p>
              <div class="space28"></div>
              <div class="service6-box-bottom">
                <a href="{{ route('frontend.contact') }}" class="btn3-home6">Check Area</a>
                <div class="step-number">A1</div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6">
            <div
              class="service6-box"
              data-aos="zoom-out"
              data-aos-duration="1000"
            >
              <div class="service6-logos">
                <h3 class="title">
                  <a href="{{ route('frontend.contact') }}"
                    >Simple Return <br />
                    Communication</a
                  >
                </h3>
                <div class="inons"><i class="fa-solid fa-comments"></i></div>
              </div>
              <div class="space24"></div>
              <p class="pera-text">
                We keep the return process easy to understand, with clear
                updates on issue review, pickup timing, and replacement status.
              </p>
              <div class="space28"></div>
              <div class="service6-box-bottom">
                <a href="{{ route('frontend.contact') }}" class="btn3-home6">Get Support</a>
                <div class="step-number">A2</div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-12">
            <div
              class="service6-box"
              data-aos="zoom-out"
              data-aos-duration="1100"
            >
              <div class="service6-logos">
                <h3 class="title">
                  <a href="{{ route('frontend.contact') }}"
                    >Bulk Orders <br />
                    And Repeat Supply</a
                  >
                </h3>
                <div class="inons"><i class="fa-solid fa-warehouse"></i></div>
              </div>
              <div class="space24"></div>
              <p class="pera-text">
                Hotels, shops, and event kitchens can coordinate repeated rice
                deliveries with dependable stock planning and practical support.
              </p>
              <div class="space28"></div>
              <div class="service6-box-bottom">
                <a href="{{ route('frontend.contact') }}" class="btn3-home6">Bulk Enquiry</a>
                <div class="step-number">A3</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="vl-faq6-area sp1">
      <div class="container">
        <div class="row align-items-center">
          <div class="faq6-header">
            <div class="service6-top">
              <div class="service6-top-left">
                <h3 data-aos="fade-left" data-aos-duration="900">
                  <img src="{{ asset('frontend/assets/') }}/img/icon/left_icon_hm2_about.webp" alt="" />
                  FAQ
                  <img src="{{ asset('frontend/assets/') }}/img/icon/right_icon_hm2_about.webp" alt="" />
                </h3>
                <div class="space16"></div>
                <h2
                  class="text-anime-style-3"
                  data-aos="fade-left"
                  data-aos-duration="1000"
                >
                  Common Questions About Delivery And Return
                </h2>
              </div>
              <div class="service6-top-right">
                <p
                  class="text-effect"
                  data-aos="fade-left"
                  data-aos-duration="1000"
                >
                  Quick answers for local delivery timing, return approval, and
                  bulk rice support.
                </p>
              </div>
            </div>
          </div>
          <div class="space44"></div>
          <div class="col-xl-6 col-lg-6">
            <div
              class="vl-faq-content-wrap-2 vl-faq6"
              data-sal="slide-up"
              data-sal-duration="1000"
              data-sal-delay="100"
              data-sal-easing="ease-in-out"
            >
              <div class="vl-faq-accordion">
                <div class="accordion" id="accordionExample">
                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="800"
                  >
                    <h2 class="accordion-header" id="headingOne">
                      <button
                        class="accordion-button"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseOne"
                        aria-expanded="true"
                        aria-controls="collapseOne"
                      >
                        <span>01</span> How long does rice delivery take?<span
                          class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="collapseOne"
                      class="accordion-collapse collapse show"
                      data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          Local orders are usually arranged for same-day or
                          next-day delivery based on stock, route, and order
                          timing.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="900"
                  >
                    <h2 class="accordion-header" id="headingTwo">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo"
                        aria-expanded="false"
                        aria-controls="collapseTwo"
                      >
                        <span>02</span> When can I request a return?<span
                          class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="collapseTwo"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          You can contact us if the delivered rice is damaged,
                          the wrong product was sent, or the bag condition is
                          not acceptable.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="1000"
                  >
                    <h2 class="accordion-header" id="headingThree">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseThree"
                        aria-expanded="false"
                        aria-controls="collapseThree"
                      >
                        <span>03</span> Do you support bulk delivery for shops ?<span class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="collapseThree"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          Yes. We support recurring bulk supply for businesses
                          and large-volume buyers with planned delivery
                          coordination.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div
                    class="vl-accordion-item"
                    data-aos="fade-right"
                    data-aos-duration="1100"
                  >
                    <h2 class="accordion-header" id="heading4">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse4"
                        aria-expanded="false"
                        aria-controls="collapse4"
                      >
                        <span>04</span> How do I contact your team quickly?<span
                          class="vl-faqarrow vl-faqarrow-2"
                          ><i class="fa-solid fa-angle-down"></i
                        ></span>
                      </button>
                    </h2>
                    <div
                      id="collapse4"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <p class="para">
                          Use the contact page or phone support to place an
                          order, ask for delivery status, or raise a return
                          request.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-lg-6">
            <div
              class="delivery-cta-box"
              data-aos="fade-left"
              data-aos-duration="1000"
            >
              <h3>Need rice delivery or want to return a bag?</h3>
              <p>
                Our team can help with new orders, repeat supply, delivery
                timing, and return coordination for eligible issues.
              </p>
              <div class="space24"></div>
              <div class="delivery-cta-actions">
                <a href="{{ route('frontend.contact') }}" class="btn-home6">Contact Us</a
                ><a href="{{ route('frontend.products') }}" class="btn2-home6">Browse Products</a>
              </div>
              <div class="space24"></div>
              <ul>
                <li>
                  <i class="fa-solid fa-check"></i> Rice varieties for home and
                  business
                </li>
                <li>
                  <i class="fa-solid fa-check"></i> Friendly local order support
                </li>
                <li>
                  <i class="fa-solid fa-check"></i> Return assistance when
                  needed
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection

