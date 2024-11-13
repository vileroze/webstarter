// This is your test publishable API key.
const stripe = Stripe("pk_test_51QD2KcRu9HD10dVm2ywMrxMf0Rr89KdccXhioiMFn325vUhafNV2lyFmYTohgFbr4zXDZZU40vIhWcl0uiKtwibS00nUXQuiuA");


let elements;

initialize();

jQuery("#payment-form").on("submit", handleSubmit);

// Fetches a payment intent and captures the client secret
async function initialize() {
  const { clientSecret, dpmCheckerLink } = await jQuery.ajax({
    url: "/wp-content/themes/webstarter/stripe-checkout.php",
    method: "POST",
    contentType: "application/json",
  });

  elements = stripe.elements({ clientSecret });
 
  const paymentElementOptions = {
    layout: "tabs",
  };

  const paymentElement = elements.create("payment", paymentElementOptions);
  paymentElement.mount("#payment-element");

  // [DEV] For demo purposes only
  setDpmCheckerLink(dpmCheckerLink);
}

async function handleSubmit(e) {
  e.preventDefault();
  setLoading(true);

  const { error } = await stripe.confirmPayment({
    elements,
    confirmParams: {
      return_url: "https://webstarter.local/payment-success/",
    },
  });

  // This point will only be reached if there is an immediate error when
  // confirming the payment. Otherwise, your customer will be redirected to
  // your `return_url`. For some payment methods like iDEAL, your customer will
  // be redirected to an intermediate site first to authorize the payment, then
  // redirected to the `return_url`.
  if (error.type === "card_error" || error.type === "validation_error") {
    showMessage(error.message);
  } else {
    showMessage("An unexpected error occurred.");
  }

  setLoading(false);
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = $("#payment-message");

  messageContainer.removeClass("hidden").text(messageText);

  setTimeout(function () {
    messageContainer.addClass("hidden").text("");
  }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
  if (isLoading) {
    // Disable the button and show a spinner
    jQuery("#submit").prop("disabled", true);
    jQuery("#spinner").removeClass("hidden");
    jQuery("#button-text").addClass("hidden");
  } else {
    jQuery("#submit").prop("disabled", false);
    jQuery("#spinner").addClass("hidden");
    jQuery("#button-text").removeClass("hidden");
  }
}

function setDpmCheckerLink(url) {
  jQuery("#dpm-integration-checker").attr("href", url);
}