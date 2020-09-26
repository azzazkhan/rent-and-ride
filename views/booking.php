    <div class="spacer"></div>
    <header>
      <div class="logo-wrapper">
        <h1 class="featured-2 mx-0" style="margin-bottom: -5px">Rent & Ride</h1>
        <strong class="featured">Chalo Saffar Karain!</strong>
      </div>
      <ul class="social-icons-list">
        <li class="social-item facebook">
          <a
            href="javascript:void(0)"
            data-toggle="tooltip"
            title="Like us on Facebook"
          >
            <i class="fa fa-facebook" aria-hidden="true"></i>
          </a>
        </li>
        <li class="social-item twitter">
          <a
            href="javascript:void(0)"
            data-toggle="tooltip"
            title="Follow us on Twitter"
          >
            <i class="fa fa-twitter" aria-hidden="true"></i>
          </a>
        </li>
        <li class="social-item google">
          <a
            href="javascript:void(0)"
            data-toggle="tooltip"
            title="Follow us on Google+"
          >
            <i class="fa fa-google-plus" aria-hidden="true"></i>
          </a>
        </li>
        <li class="social-item instagram">
          <a
            href="javascript:void(0)"
            data-toggle="tooltip"
            title="Follow us on Instagram"
          >
            <i class="fa fa-instagram" aria-hidden="true"></i>
          </a>
        </li>
      </ul>
    </header>
    <main
      class="parallax"
      style="background-image: url('./assets/images/background-home.jpg')"
    >
      <div class="d-flex flex-column align-items-center">
        <img
          src="./assets/images/logo.png"
          class="d-inline-block mb-2 shadow rounded-circle"
          style="width: 70px"
          alt="Rent & Ride &mdash; Logo"
        />
        <strong class="featured">Chalo Safar Karain!</strong>
      </div>
      <h1 class="featured-2 text-center mt-3" style="font-size: 4em">
        Booking Form
      </h1>
      <div class="row no-gutters">
        <div
          class="col-10 offset-1 col-md-8 offset-md-2 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4 row no-gutters mt-5 mb-3"
        >
        <?php
          $car = $this->car;
          $shop = $this->shop;
        ?>
          <form action="/booking/submit" method="post" class="form custom w-100 mb-5" id="bookingForm">
            <!-- Car details -->
            <div class="form-group">
              <label>Automobile Details</label>
              <input
                type="text"
                class="form-control"
                value="<?=
                  \sprintf("%s %s", $car->name, $car->specifications);
                ?>"
                readonly
              />
            </div>
            <!-- Dealer Details -->
            <div class="form-group">
              <label>Dealer Details</label>
              <input
                type="text"
                class="form-control"
                value="<?=
                  \sprintf(
                    "%s, %s, Peshawar (%s)",
                    $shop->name, $shop->location->name, $shop->phone
                  );
                ?>"
                readonly
              />
            </div>
            <!-- Customer Data -->
            <div class="form-group customer-name">
              <label for="customerName">Customer Name</label>
              <input
                type="text"
                name="customer_name"
                class="form-control"
                id="customerName"
                minlength="5"
                maxlength="50"
                aria-describedby="customerNameFeedback"
                placeholder="Enter your full name"
                autocomplete="off"
                required
              />
              <div class="feedback"></div>
            </div>
            <div class="form-group customer-email">
              <label for="customerEmail">Email Address</label>
              <input
                type="email"
                name="customer_email"
                class="form-control"
                id="customerEmail"
                minlength="6"
                maxlength="100"
                aria-describedby="customerEmailFeedback"
                placeholder="Enter your email address"
              />
              <div class="feedback"></div>
            </div>
            <div class="form-group customer-number">
              <label for="customerContact">Contact Number</label>
              <input
                type="tel"
                name="customer_contact"
                class="form-control"
                id="customerContact"
                minlength="10"
                maxlength="11"
                aria-describedby="customerContactFeedback"
                placeholder="Enter your contact number"
                required
              />
              <div class="feedback"></div>
            </div>
            <div class="form-group customer-nicn">
              <label for="customerNICN">National Identity Card (NIC)</label>
              <input
                type="text"
                name="customer_nicn"
                class="form-control"
                id="customerNICN"
                minlength="15"
                maxlength="15"
                aria-describedby="customerNICNFeedback"
                placeholder="Enter your NIC number"
                required
              />
              <div class="feedback"></div>
              <small id="customerNICNFeedback" class="form-text text-muted"
                >We'll never share your personal data with others.</small
              >
            </div>
            <div class="form-group customer-address">
              <label for="customerAddress">Customer Address</label>
              <textarea
                class="form-control"
                name="customer_address"
                id="customerAddress"
                rows="4"
                minlength="10"
                maxlength="300"
                aria-describedby="customerAddressFeedback"
                placeholder="Enter your address"
                required
              ></textarea>
              <div class="feedback"></div>
            </div>
            <input type="hidden" name="car_id" value="<?= $car->id() ?>">
            <input type="hidden" name="shop_id" value="<?= $shop->id() ?>">
            <button
              type="submit"
              class="btn btn-block custom primary bold rounded mt-2"
            >
              Book Now
            </button>
          </form>
        </div>
      </div>
    </main>
    <script src="/assets/js/booking.js" defer></script>