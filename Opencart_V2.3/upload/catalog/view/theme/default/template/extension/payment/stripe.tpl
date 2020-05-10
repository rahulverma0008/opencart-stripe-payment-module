<link rel="stylesheet" type="text/css" href="<?php echo $store_url; ?>catalog/view/theme/default/stylesheet/stripe.css">
<div class="payment-form-wrapper">
	<?php if($test_mode) { ?>
	<small class="text-info"><?php echo $text_debug; ?></small>
	<?php } ?>
	<form id="payment-form">
		<div id="payment-request-button"></div>
		<fieldset>
			<legend class="card-only"><?php echo $text_pay_with_card; ?></legend>
			<legend class="payment-request-available"><?php echo $text_or_pay_with_card; ?></legend>
			<div class="container-stripe">
				<!-- placeholder for Elements -->
				<div id="card-element"></div>
				<button type="button" id="button-confirm" class="buttons"><?php echo $button_submit_payment; ?></button>
			</div>
		</fieldset>
		<div class="error-stripe" role="alert">
			<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17">
				<path class="base" fill="#000" d="M8.5,17 C3.80557963,17 0,13.1944204 0,8.5 C0,3.80557963 3.80557963,0 8.5,0 C13.1944204,0 17,3.80557963 17,8.5 C17,13.1944204 13.1944204,17 8.5,17 Z"></path>
				<path class="glyph" fill="#FFF" d="M8.5,7.29791847 L6.12604076,4.92395924 C5.79409512,4.59201359 5.25590488,4.59201359 4.92395924,4.92395924 C4.59201359,5.25590488 4.59201359,5.79409512 4.92395924,6.12604076 L7.29791847,8.5 L4.92395924,10.8739592 C4.59201359,11.2059049 4.59201359,11.7440951 4.92395924,12.0760408 C5.25590488,12.4079864 5.79409512,12.4079864 6.12604076,12.0760408 L8.5,9.70208153 L10.8739592,12.0760408 C11.2059049,12.4079864 11.7440951,12.4079864 12.0760408,12.0760408 C12.4079864,11.7440951 12.4079864,11.2059049 12.0760408,10.8739592 L9.70208153,8.5 L12.0760408,6.12604076 C12.4079864,5.79409512 12.4079864,5.25590488 12.0760408,4.92395924 C11.7440951,4.59201359 11.2059049,4.59201359 10.8739592,4.92395924 L8.5,7.29791847 L8.5,7.29791847 Z"></path>
			</svg>
			<span id="card-errors" class="message"></span>
		</div>
	</form>
	<div class="success-stripe">
		<div class="icon">
			<svg width="84px" height="84px" viewBox="0 0 84 84" version="1.1" xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink">
				<circle class="border" cx="42" cy="42" r="40" stroke-linecap="round" stroke-width="4" stroke="#000" fill="none"></circle>
			</svg>
		</div>
	</div>
</div>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
function initStripe() {
	if (window.Stripe) {

		const stripe = Stripe('<?php echo $stripe_public_key; ?>');
		const elements = stripe.elements();

		var style = {
					base: {
						color: "#32325D",
						fontWeight: 500,
						fontFamily: "Inter UI, Open Sans, Segoe UI, sans-serif",
						fontSize: "15px",
						fontSmoothing: "antialiased",
						"::placeholder": {
							color: "#CFD7DF"
						}
					},
					invalid: {
						 color: "#E25950"
					}
				};

		const cardElement = elements.create('card', {style: style, hidePostalCode: true});
		cardElement.mount('#card-element');
		const cardButton = document.getElementById('button-confirm');

		var billing_details = <?php echo json_encode($billing_details); ?>;

		cardButton.addEventListener('click', async (ev) => {
			$('.payment-form-wrapper').addClass('submitting');
			const {paymentMethod, error} = await stripe.createPaymentMethod('card', cardElement, billing_details);
			if (error) {
				// Show error in payment form
				showErrorMessage(error.message);
			} else {
				// Send paymentMethod.id to your server (see Step 2)
				const response = await fetch('<?php echo $action; ?>', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ payment_method_id: paymentMethod.id })
				});

				const json = await response.text();

				// Handle server response (see Step 3)
				handleServerResponse(json);
			}
		});


		const handleServerResponse = async (response) => {

			try {
				response = JSON.parse(response);
			} catch {
				console.warn("Stripe App have encountered with some error. This error might not be caused by Stripe App. Such errors come when Stripe App receive unexpected JSON response from your server.");
				console.warn("Please see below the response your server sent, whereas JSON was expected by Stripe App.");
				console.error(response);
				return;
			}

			if (response.error) {
				// Show error from server on payment form
				showErrorMessage(response.error);
			} else if (response.requires_action) {
				// Use Stripe.js to handle the required card action
				const { error: errorAction, paymentIntent } = await stripe.handleCardAction(response.payment_intent_client_secret);
	
				if (errorAction) {
					// Show error from Stripe.js in payment form
					showErrorMessage(errorAction.message);
				} else {
					// The card action has been handled
					// The PaymentIntent can be confirmed again on the server
					const serverResponse = await fetch('<?php echo $action; ?>', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({ payment_intent_id: paymentIntent.id })
					});
					handleServerResponse(await serverResponse.text());
				}
			} else {
				// Show success message
				window.location = response.success;
			}
		}

		const showErrorMessage = (error) => {
			$(".payment-form-wrapper #card-errors").text(error);
			$(".payment-form-wrapper .error-stripe").addClass("visible");
			$('.payment-form-wrapper').removeClass('submitting');
		}
	} else {
		 setTimeout(function() { initStripe() }, 50);
	}
}

initStripe();
</script>