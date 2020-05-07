let slideNow = 1;
let slideCount = $(".carousel__slides").children().length;
let slideInterval = 10000;
let translateWidth = 0;

$(document).ready(function () {
  let switchInterval = setInterval(nextSlide, slideInterval);

  $(".carousel").hover(
    function () {
      clearInterval(switchInterval);
    },
    function () {
      switchInterval = setInterval(nextSlide, slideInterval);
    }
  );

  $(".carousel__arrow-right").click(function () {
    nextSlide();
  });

  $(".carousel__arrow-left").click(function () {
    prevSlide();
  });
});

function nextSlide() {
  if (slideNow === slideCount || slideNow <= 0 || slideNow > slideCount) {
    $(".carousel__slides").css("transform", "translate(0, 0)");
    slideNow = 1;
  } else {
    translateWidth = -$(".carousel").width() * slideNow;
    $(".carousel__slides").css({
      transform: "translate(" + translateWidth + "px, 0)",
      "-webkit-transform": "translate(" + translateWidth + "px, 0)",
      "-ms-transform": "translate(" + translateWidth + "px, 0)",
    });
    slideNow++;
  }
}

function prevSlide() {
  if (slideNow === 1 || slideNow <= 0 || slideNow > slideCount) {
    translateWidth = -$(".carousel").width() * (slideCount - 1);
    $(".carousel__slides").css({
      transform: "translate(" + translateWidth + "px, 0)",
      "-webkit-transform": "translate(" + translateWidth + "px, 0)",
      "-ms-transform": "translate(" + translateWidth + "px, 0)",
    });
    slideNow = slideCount;
  } else {
    translateWidth = -$(".carousel").width() * (slideNow - 2);
    $(".carousel__slides").css({
      transform: "translate(" + translateWidth + "px, 0)",
      "-webkit-transform": "translate(" + translateWidth + "px, 0)",
      "-ms-transform": "translate(" + translateWidth + "px, 0)",
    });
    slideNow--;
  }
}
