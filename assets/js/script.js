// Prevent conflicts with other libraries that use ($) as reference.
var $ = window.jQuery.noConflict();

$(function ($) {
  // Enable Bootstrap tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Settings for carousel
  $(".owl-carousel").owlCarousel({
    autoWidth: true,
    checkVisible: false,
    rewind: false,
    navs: false,
    dots: false,
    margin: 30,
  });
});
