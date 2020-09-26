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
      style="background-image: url('/assets/images/background-home.jpg')"
    >
      <div class="d-flex flex-column align-items-center">
        <img
          src="/assets/images/logo.png"
          class="d-inline-block mb-2 shadow rounded-circle"
          style="width: 70px"
          alt="Rent & Ride &mdash; Logo"
        />
        <strong class="featured">Chalo Safar Karain!</strong>
      </div>
      <h1 class="featured-2 text-center mt-3" style="font-size: 4em">
        Go, Find & Explore
      </h1>
      <div class="d-flex justify-content-center pt-5 pb-4">
        <button
          class="btn custom primary bold invert pilled wide text-uppercase"
        >
          Book Now
        </button>
      </div>
      <div class="row no-gutters">
        <div
          class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 row no-gutters mt-5 mb-3"
        >
          <div class="selection-box col-12 col-md-6">
            <div class="d-flex justify-content-center">
              <div class="btn-group">
                <button
                  class="btn custom secondary pilled has-icon dropdown-toggle"
                  data-toggle="dropdown"
                  data-offset="30,13"
                >
                  <img
                    src="/assets/images/icon-location.png"
                    class="icon"
                    alt="Location Icon"
                  />
                  Search Location
                </button>
                <div class="dropdown-menu custom dropdown-menu-center">
                  <?php foreach($this->locations as $location): ?>
                    <a href="/location/<?= $location->slug ?>" class="dropdown-item"><?= $location->name ?></a>
                  <?php endforeach ?>
                </div>
              </div>
            </div>
            <div
              class="box watermarked"
              style="
                background-image: url('/assets/images/car-rentals-card-background.jpg');
              "
            >
              <span class="title">Rental Cars</span>
            </div>
          </div>
          <div class="selection-box col-12 col-md-6">
            <div class="d-flex justify-content-center">
              <a
                role="button"
                href="/services"
                class="btn custom secondary pilled has-icon"
              >
                <img
                  src="/assets/images/icon-location.png"
                  class="icon"
                  alt="Location Icon"
                />
                Search Location
              </a>
            </div>
            <div
              class="box watermarked"
              style="
                background-image: url('/assets/images/ride-service-card-background.jpg');
              "
            >
              <span class="title">Ride Service</span>
            </div>
          </div>
        </div>
      </div>
    </main>
