@extends('layouts.base')

@section('title', $settings->site_name . ' - Contact Us')

@section('content')

    <!-- ==================== Breadcrumb Start Here ==================== -->
    <section class="breadcrumb">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="breadcrumb__wrapper">
                        <h2 class="breadcrumb__title"> Contact Us</h2>
                        <ul class="breadcrumb__list">
                            <li class="breadcrumb__item"><a href="{{ route('home') }}" class="breadcrumb__link"> <i
                                        class="las la-home"></i> Home</a> </li>
                            <li class="breadcrumb__item"><i class="fa-solid fa-minus"></i></li>
                            <li class="breadcrumb__item"> <span class="breadcrumb__item-text"> Contact Us
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ==================== Breadcrumb End Here ==================== -->
    <!--==========================  Contact Section Start  ==========================-->
    <section class="contact-section bg--black-two py-120">
        <div class="container">
            <div class="row pb-120 row-gap-4 align-items-center">
                <div class="col-lg-5 order-1 order-lg-0">
                    <div class="contact-info">
                        <h3 class="mb-1">Contact Information</h3>
                        <p>For Business Plan Submissions. Please submit using this</p>
                        <ul class="d-grid gap-3 mt-4 pt-lg-3">
                            <li class="d-flex gap-2">
                                <div class="icon">
                                    <i class="fa-solid fa-phone-volume"></i>
                                </div>
                                <p><a href="tel:+8243944562">(+1) 824 394 4562</a></p>
                            </li>
                            <li class="d-flex gap-2">
                                <div class="icon">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <p><a
                                        href="https://photoclerks.com/cdn-cgi/l/email-protection#3d54535b521345494f5c59447d5a505c5451135e5250"><span
                                            class="__cf_email__"
                                            data-cfemail="dab3b4bcb5f4a2aea8bbbea39abdb7bbb3b6f4b9b5b7">{{ $settings->email }}</span></a>
                                </p>
                            </li>
                            <li class="d-flex gap-2">
                                <div class="icon">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <p>New Street 243 West Victoria Vip Road 3527 Canada</p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="enquiry-form">
                        <h3>Enquiry Form</h3>
                        <form id="contact-form" action="https://photoclerks.com/html/xtrady-update/xtrady/sendmail.php"
                            method="post">
                            <div class="row row-gap-4">
                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control" placeholder="First Name"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="last_name" class="form-control" placeholder="Last Name"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="tel" name="phone" class="form-control" placeholder="Phone">
                                </div>
                                <div class="col-md-12">
                                    <textarea name="message" class="form-control" rows="6" placeholder="Message" required></textarea>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-message"></div>
                                    <button type="submit" class="btn btn--base-two">
                                        Send Message <i class="flaticon-arrow-upper-right"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2822.7806761080233!2d-93.29138368446431!3d44.96844997909819!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x52b32b6ee2c87c91%3A0xc20dff2748d2bd92!2sWalker+Art+Center!5e0!3m2!1sen!2sus!4v1514524647889"
                        allowfullscreen class="contact-map">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Contact Section End  ==========================-->
    <!--==========================  Call-to-action Section Start  ==========================-->
    <section class="call-to-action-section bg--black-two">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="call-action-box">
                        <div class="call-shape">
                            <img src="assets/svg/twostar.svg" alt="star">
                        </div>
                        <div class="row row-gap-5">
                            <div class="col-lg-6 align-self-center order-1 order-lg-0">
                                <div class="call-text text-center text-lg-start">
                                    <span class="text--base pb-3 right-reveal">Start Your Forex Journey
                                        Today</span>
                                    <h2 class="mb-4 right-reveal">Learn, Trade, And Grow With Trusted Tools And
                                        Signals.</h2>
                                    <a href="{{ route('register') }}" class="btn btn--base-two right-reveal">
                                        Register Now <i class="flaticon-arrow-upper-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-6 align-self-center">
                                <div class="call-img text-lg-end text-center scaleUp">
                                    <img src="assets/images/call/call-img.png" alt="call" class="img-fluid m-auto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Call-to-action Section End  ==========================-->

    <!-- Live Chat Suggestion Popup -->
    <div id="livechat-popup"
        style="
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    display:flex;
    align-items:center;
    justify-content:center;
    opacity:0;
    visibility:hidden;
    transition:0.3s ease;
    z-index:9999;
    ">

        <div style="
    background:white;
    padding:25px 30px;
    border-radius:12px;
    max-width:400px;
    width:90%;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    transform:translateY(20px);
    transition:0.3s ease;
    "
            id="livechat-box">

            <div style="font-size:40px; margin-bottom:10px;">💬</div>

            <h3 style="margin-bottom:10px; color:#222;">
               For Faster Response
            </h3>

            <p style="color:#666; font-size:15px; margin-bottom:20px;">
                Please contact us through Live Chat for faster response and instant assistance.
            </p>

            <button onclick="closeLiveChatPopup()"
                style="
        background:#28a745;
        color:white;
        border:none;
        padding:10px 20px;
        border-radius:6px;
        cursor:pointer;
        font-weight:600;
        ">
                OK
            </button>

        </div>

    </div>


    <script>
        const form = document.getElementById("contact-form");
        const popup = document.getElementById("livechat-popup");
        const box = document.getElementById("livechat-box");

        form.addEventListener("submit", function(e) {

            e.preventDefault();

            popup.style.visibility = "visible";
            popup.style.opacity = "1";

            setTimeout(() => {
                box.style.transform = "translateY(0)";
            }, 50);

        });

        function closeLiveChatPopup() {

            popup.style.opacity = "0";

            setTimeout(() => {
                popup.style.visibility = "hidden";
                box.style.transform = "translateY(20px)";
            }, 300);

        }

        // close when clicking outside
        popup.addEventListener("click", function(e) {
            if (e.target === popup) {
                closeLiveChatPopup();
            }
        });
    </script>


@endsection
