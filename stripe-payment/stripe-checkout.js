const stripe = Stripe("pk_test_51QD2KcRu9HD10dVm2ywMrxMf0Rr89KdccXhioiMFn325vUhafNV2lyFmYTohgFbr4zXDZZU40vIhWcl0uiKtwibS00nUXQuiuA");

let elements;
let subscription;
let customerId;

async function initialize() {
  try {
    const response = await jQuery.ajax({
      url: "/wp-content/themes/webstarter/stripe-checkout.php",
      method: "POST",
      contentType: "application/json",
    });

    console.log("Stripe checkout response:", response);

    if (response.error) {
      showMessage(response.error);
      return;
    }

    if (!response.clientSecret) {
      showMessage("Invalid response from server: missing client secret");
      return;
    }

    subscription = {
      id: response.subscriptionId,
      isSubscription: response.isSubscription
    };

    customerId = response.customerId;

    const appearance = {
      theme: 'stripe',
      variables: {
        colorPrimary: '#0570de',
      }
    };

    elements = stripe.elements({
      clientSecret: response.clientSecret,
      appearance
    });

    const paymentElement = elements.create("payment", {
      layout: "tabs",
    });

    paymentElement.mount("#payment-element");

  } catch (error) {
    console.error("Stripe initialization error:", error);
    showMessage("Failed to initialize payment form. Please try again.");
  }
}

async function handleSubmit(e) {
  e.preventDefault();
  setLoading(true);

  try {
    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: "https://webstarter.local/payment-success/",
        payment_method_data: {
          billing_details: {
            // Add any billing details if needed
          }
        }
      }
    });

    if (error) {
      console.error("Payment confirmation error:", error);
      if (error.type === "card_error" || error.type === "validation_error") {
        showMessage(error.message);
      } else {
        showMessage("An unexpected error occurred.");
      }
    }
  } catch (error) {
    console.error("Payment submission error:", error);
    showMessage("An unexpected error occurred during payment processing.");
  } finally {
    setLoading(false);
  }
}

function showMessage(messageText) {
  const messageContainer = jQuery("#payment-message");
  messageContainer.removeClass("hidden").text(messageText);

  if (messageText) {
    setTimeout(function () {
      messageContainer.addClass("hidden").text("");
    }, 4000);
  }
}

function setLoading(isLoading) {
  const submitButton = jQuery("#submit");
  const spinner = jQuery("#spinner");
  const buttonText = jQuery("#button-text");

  submitButton.prop("disabled", isLoading);
  spinner.toggleClass("hidden", !isLoading);
  buttonText.toggleClass("hidden", isLoading);
}

// Initialize on document ready
jQuery(document).ready(() => {
  initialize();
  jQuery(document).on("submit","#payment-form", handleSubmit);
});