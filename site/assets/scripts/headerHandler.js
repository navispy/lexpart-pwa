$(document).ready(() => {
  let clicked = false;
  $(".header__nav-toggle").click(function () {
    clicked = !clicked;
    $(this).toggleClass("header__nav-toggle_open");
    $(".header__nav-menu").slideToggle().css("display", "grid");
  });
  $(".header").click((e) => {
    e.stopPropagation();
  });
  $(document).click(() => {
    if (clicked) {
      clicked = !clicked;
      $(".header__nav-menu").slideToggle("normal");
      $(".header__nav-toggle").toggleClass("header__nav-toggle_open");
    }
  });
});
jQuery(window).scroll(function () {
  if (jQuery(window).scrollTop() > 1) {

    jQuery(".header__nav").addClass("header__nav_fixed");
  } else {

    jQuery(".header__nav").removeClass("header__nav_fixed");
  }
});
