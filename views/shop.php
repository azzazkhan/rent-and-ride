    <!-- Black bar above header -->
    <div class="spacer"></div>
    <header
      class="flex-column flex-lg-row justify-content-between align-items-center"
    >
      <!-- The logo is in absolute page flow and will overlap elements under it -->
      <div class="logo-wrapper">
        <h1 class="featured-2 mx-0" style="margin-bottom: -5px">Rent & Ride</h1>
        <strong class="featured">Chalo Saffar Karain!</strong>
      </div>
      <!-- A flexbox wrapper element is used to properly align home icon in the middle of the navbar -->
      <!-- justify-content-center align-items-center ml-2 ml-lg-4 -->
      <div class="d-flex w-100 mt-4 pt-3 pl-3 mt-lg-0 p-lg-0 ml-lg-4">
        <a
          href="/"
          data-toggle="tooltip"
          data-placement="right"
          title="Back to home"
        >
          <img
            src="/assets/images/icon-home.svg"
            style="height: 40px"
            alt=""
          />
        </a>
      </div>
      <!-- The header is designed (in CSS) to have social icons list only -->
      <ul class="social-icons-list">
        <!-- Custom classes defined in stylesheet for each social network to have it's appropriate color -->
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
    <!--
      Add your own background image through inline CSS property.
      Use the `.parallax` class to make unscrollable.
    -->
    <main
      class="parallax pb-5"
      style="background-image: url('/assets/images/background-services.jpg')"
    >
      <!-- Logo and tagline wrapped in flexbox for center alignment -->
      <div class="d-flex flex-column align-items-center">
        <img
          src="/assets/images/logo.png"
          class="d-inline-block mb-2 shadow rounded-circle"
          style="width: 70px"
          alt="Rent & Ride &mdash; Logo"
        />
        <strong class="featured">Chalo Safar Karain!</strong>
      </div>
      <!-- This element contains main heading of the page -->
      <h1 class="featured-2 text-center mt-3" style="font-size: 4em">
        <?= $this->shop->name ?>
      </h1>
      <div class="d-flex justify-content-center pt-5 pb-4">
        <button
          class="btn custom primary bold invert pilled wide text-uppercase"
        >
          Contact us
        </button>
      </div>
      <?php
        if (\is_array($this->shop->cars) && count($this->shop->cars) > 0):
      ?>
      <div class="owl-carousel products-slider ml-3 ml-lg-5 mt-4">
        <?php foreach($this->shop->cars as $car): ?>
        <div class="card bg-mask dark-light">
          <div
            class="background"
            style="
              background-image: url('/assets/images/cars/<?= $car->slug ?>.jpg');
              background-position: -75px 0;
            "
          ></div>
          <div class="content">
            <?php
              if (! is_null($car->tags) && ! empty($car->tags) && strlen($car->tags) > 0):
                $tags = json_decode($car->tags);
            ?>
            <div class="tags fadeable">
              <?php foreach($tags as $tag): ?>
              <a href="javascript:void(0)"><?= $tag; ?></a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="popup">
              <h2 class="title"><?= $car->name ?></h2>
              <div class="details">
                <div class="d-flex justify-content-between">
                  <span>Price Per Day</span>
                  <strong>
                    PKR <?= $car->daily_price ?>
                    <i
                      class="fa fa-info-circle"
                      aria-hidden="true"
                      data-toggle="tooltip"
                      title="Total price including all taxes"
                    ></i>
                  </strong>
                </div>
                <div class="d-flex justify-content-between">
                  <span>Price Per Week</span>
                  <strong>
                    PKR <?= $car->weekly_price ?>
                    <i
                      class="fa fa-info-circle"
                      aria-hidden="true"
                      data-toggle="tooltip"
                      title="Total price including all taxes"
                    ></i>
                  </strong>
                </div>
              </div>
              <form action="/booking" method="post">
                <input type="hidden" name="car_name" value="<?= $car->slug ?>">
                <input type="hidden" name="shop_id" value="<?= $this->shop->id() ?>">
                <button class="btn-action">Book Now</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php
        else:
      ?>
      <h1 class="featured-2 text-center mt-5">No Cars Available</h1>
      <?php endif; ?>
    </main>