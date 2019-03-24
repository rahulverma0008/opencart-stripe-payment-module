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
				<div id="card-element"></div>
				<button type="submit" id="button-confirm" class="buttons"><?php echo $button_submit_payment; ?></button>
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
	var stripe = null;
	
	function finishPayment(source, callback) {
		 $.ajax({
			  url: '<?php echo $form_action; ?>',
			  type: 'post',
			  data: { 'source': source },
			  dataType: 'json',
			  beforeSend: function() {
					
			  },
			  success: function(json) {
			  		// console.log(json);
			  		if (json['error']){
			  			$(".payment-form-wrapper #card-errors").text(json['error']);
			  			$(".payment-form-wrapper .error-stripe").addClass("visible");
			  			$('.payment-form-wrapper').removeClass('submitting');
			  		} else if(json['redirect']){
			  			location = json['redirect'];
			  			// window.location = json['redirect'];
			  		} else if (json['success']) {
						 location = json['success'];
					}
					if(callback)    { callback('success'); }
			  },
			  error: function (xhr, ajaxOptions, thrownError) {
					console.log(xhr, ajaxOptions, thrownError);
					if(callback)    { callback('error'); }
			  }
		 });
	}
	
	function initStripe() {
		 if (window.Stripe) {
				stripe = Stripe('<?php echo $stripe_public_key; ?>');
	
				var elements = stripe.elements();
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
	
				var card = elements.create('card', {style: style, hidePostalCode: true});
				card.mount('#card-element');
	
				// Handle real-time validation errors from the card Element.
				card.addEventListener('change', function(event) {
					var displayError = document.getElementById('card-errors');
					
					if (event.error) {
						displayError.textContent = event.error.message;
						$('.payment-form-wrapper .error-stripe').addClass('visible');
					} else {
						$('.payment-form-wrapper .error-stripe').removeClass('visible');
						displayError.textContent = '';
					}
				});
	
	
				// Create a source or display an error when the form is submitted.
				var form = document.getElementById('payment-form');
				form.addEventListener('submit', function(event) {
					event.preventDefault();
					$('.payment-form-wrapper').addClass('submitting');
	
					stripe.createSource(card).then(function(result) {
						if (result.error) {
							// Inform the user if there was an error
							var errorElement = document.getElementById('card-errors');
							errorElement.textContent = result.error.message;
							$('.payment-form-wrapper .error').addClass('visible');
							$('.payment-form-wrapper').removeClass('submitting');
						} else {
	
							console.warn("3D Secure : "+result.source.card.three_d_secure);
	
							if(result.source.card.three_d_secure == 'required' || <?php echo json_encode($stripe_3d_secure_supported, JSON_UNESCAPED_SLASHES); ?>.indexOf(result.source.card.three_d_secure) >= 0){

								console.warn("Payment will process with 3D Secure");

								var secure_return_url = '<?php echo $form_callback; ?>&order_id=<?php echo $order_id; ?>&secure=3D';

								// use 3D secure only
								stripe.createSource({
									type: 'three_d_secure',
									// if I uncomment below line then it show some error says "The usage `reusable` is not supported by payment method: three_d_secure."
									// usage: 'reusable',
									amount: parseInt('<?php echo $amount; ?>'),
									currency: '<?php echo $currency; ?>'.toLowerCase(),
									three_d_secure: {
										card: result.source.id
									},
									redirect: {
										return_url: secure_return_url,
									}
								}).then(function(res) {
									// handle result.error or result.source
									// debugger;
									if(res.error){
										$(".payment-form-wrapper #card-errors").text(res.error.message);
										$(".payment-form-wrapper .error-stripe").addClass("visible");
										$('.payment-form-wrapper').removeClass('submitting');
									} else {
										window.location = res.source.redirect.url;
									}
								});
							} else {
								// Send the source to your server
								finishPayment(result.source.id, function (res) {
									$('.payment-form-wrapper').removeClass('submitting');
								});
							}
						}
					});
				});
	
		 } else {
			  setTimeout(function() { initStripe() }, 50);
		 }
	}
	initStripe();
</script>