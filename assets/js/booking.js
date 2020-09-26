jQuery(document).ready(function ($) {
  run_validations();
  $("#bookingForm").on("submit", function (e) {
    run_validations(e);
  });
});
function run_validations(e) {
  var name_validated = validate_field(
    "customer-name",
    /^([A-Za-z\s]){5,50}$/,
    "Please enter a valid name!",
  );
  var email_validated = validate_field(
    "customer-email",
    /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
    "Please enter a valid email address!",
    true,
  );
  var number_validated = validate_field(
    "customer-number",
    /^03([\d]){9}$/,
    "Please enter a valid contact number!",
  );
  var nicn_validated = validate_field(
    "customer-nicn",
    /^\d{5}-\d{7}-\d$/,
    "Please enter a valid CNIC number!",
  );

  if (
    !name_validated ||
    !email_validated ||
    !number_validated ||
    !nicn_validated
  )
    e.preventDefault();
}
function validate_field(
  group,
  regex,
  err = "Please enter a proper value!",
  optional = false,
  textarea = false,
) {
  var group = $(".form-group." + group)[0];
  var field = $(group).find(".form-control");
  var feedback = $(group).find(".feedback");
  var value = textarea ? field.html() : field.val();
  field.removeClass("is-valid is-invalid");
  feedback.removeClass("valid-feedback invalid-feedback").text("");
  // IF optional THEN IF value.length > 0 AND ! applycheck
  if (optional && value.length == 0) return true;
  return !regex.test(value)
    ? (function () {
        field.addClass("is-invalid");
        feedback.addClass("invalid-feedback").text(err);
        console.log(value);
        return false;
      })()
    : (function () {
        field.addClass("is-valid");
        return true;
      })();
}
