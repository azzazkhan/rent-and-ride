    <div class="spacer"></div>
    <header class="align-items-stretch justify-content-start">
      <div class="logo-wrapper">
        <h1 class="featured-2 mx-0" style="margin-bottom: -5px">Rent & Ride</h1>
        <strong class="featured">Chalo Saffar Karain!</strong>
      </div>
      <div
        class="d-flex justify-content-center align-items-center ml-2 ml-lg-4"
      >
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
    </header>
    <main
      class="parallax has-social-footer"
      style="background-image: url('/assets/images/background-services.jpg')"
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
      <?php
        if (
          \is_array($this->location->shops) &&
          count($this->location->shops) > 0
        ):
      ?>
      <h1 class="featured-2 text-center mt-3" style="font-size: 4em">
        Rental Car Shops in <?= $this->location->name ?>
      </h1>
      <div class="row no-gutters">
        <div
          class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 row no-gutters mt-5 mb-3"
        >
          <?php foreach($this->location->shops as $shop): ?>
          <div class="selection-box col-12 col-md-6">
            <div class="d-flex justify-content-center">
              <a
                role="button"
                href="<?=
                  \sprintf(
                    "/location/%s/%s",
                    $this->location->slug, $shop->slug
                  );
                ?>"
                class="btn custom secondary pilled heading regular"
              ><?= $shop->name ?></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <h1 class="featured-2 text-center mt-3" style="font-size: 4em">
        No Shops available in <?= $this->location->name ?>
      </h1>
      <?php endif; ?>
      <div class="social-icons-footer">
        <div class="social-icon facebook">
          <a href="javascript:void(0)">
            <i class="fa fa-facebook" aria-hidden="true"></i>
          </a>
        </div>
        <div class="social-icon twitter">
          <a href="javascript:void(0)">
            <i class="fa fa-twitter" aria-hidden="true"></i>
          </a>
        </div>
        <div class="social-icon google">
          <a href="javascript:void(0)">
            <i class="fa fa-google-plus" aria-hidden="true"></i>
          </a>
        </div>
        <div class="social-icon instagram">
          <a href="javascript:void(0)">
            <i class="fa fa-instagram" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </main>