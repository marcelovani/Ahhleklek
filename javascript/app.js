$(document).ready(function() {
  $(".button.preview")..click(function() {
    $("#preview .question").text($(".form .question").val());
    $("#preview .answer").text($(".form .answer").val());
  });
});