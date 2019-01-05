<?php
// Heading
$_['heading_title']		 = 'Stripe';

// Text
$_['text_edit']		   = 'Edit Stripe';
$_['text_payment']		= 'Payment';
$_['text_extension']		= 'Extension';
$_['text_payments']		= 'Payments';
$_['text_success']		= 'Success: You have modified Stripe Payment Module!';
$_['text_stripe']			= '<img src="view/image/payment/stripe_logo.png" alt="Stripe" title="Stripe" style="border: 1px solid #EEEEEE;" />';
$_['text_next']			= 'Next';
$_['text_live']			= 'Live (Production)';
$_['text_test']			= 'Test (Sandbox)';

// Tab
$_['tab_general']			= 'General';
$_['tab_secret_test']	= 'Test Secret';
$_['tab_secret_live']	= 'Live Secret';

// Entry
$_['entry_environment'] = 'Environment';
$_['entry_environment_help'] = 'Please choose an environment. Test for Testing (Sandbox) account and Live for Production account';

$_['entry_status'] = 'Status';
$_['entry_status_help'] = 'Enable this to accept payment using Stripe';

$_['entry_sort_order'] = 'Sort Order';

$_['entry_order_success_status'] = 'Order Success Status';
$_['entry_order_success_status_help'] = 'Order status that will set for Successful Payment';

$_['entry_order_failed_status'] = 'Order Failed Status';
$_['entry_order_failed_status_help'] = 'Order status that will set for Failed Payment';

$_['entry_stripe_currency'] = 'Currency';
$_['entry_stripe_currency_help'] = 'Select Currency';

$_['entry_store_card'] = 'Store Customer Card';
$_['entry_store_card_help'] = 'Enable this if you want to allow customers to save their cards.';

$_['entry_test_public_key'] = 'Test Public Key';
$_['entry_test_public_key_help'] = 'Public Key for Sandbox Accuont';

$_['entry_test_secret_key'] = 'Test Secret Key';
$_['entry_test_secret_key_help'] = 'Secret Key for Sandbox Accuont';

$_['entry_live_public_key'] = 'Live Public Key';
$_['entry_live_public_key_help'] = 'Public Key for Production Accuont';

$_['entry_live_secret_key'] = 'Live Secret Key';
$_['entry_live_secret_key_help'] = 'Secret Key for Production Accuont';

$_['entry_debug'] = 'Debug';
$_['entry_debug_help'] = 'Enable this will write Stripe Payment logs to help you finding any issue';

$_['entry_3d_secure_supported'] = 'Enable 3D Secure for';
$_['entry_3d_secure_supported_help1'] = 'Check the card types you want to secure with 3D authentication payment.';
$_['entry_3d_secure_supported_help2'] = $_['entry_3d_secure_supported_help1'].' Click <a target="_blank" href="https://stripe.com/docs/sources/three-d-secure#check-requirement">here</a> for more info';

// Error
$_['error_permission']	= 'Warning: You do not have permission to modify Stripe Payment!';
$_['error_test_public_key'] = 'Test Public Key Required!';
$_['error_test_secret_key'] = 'Test Secret Key Required!';
$_['error_live_public_key'] = 'Live Public Key Required!';
$_['error_live_secret_key'] = 'Live Secret Key Required!';
$_['error_3d_secure_supported_required'] = 'You can not remove 3D Secure authentication for "required" cards!';
$_['error_3d_secure_supported_not_supported'] = 'You can not add 3D Secure authentication for "not_supported" cards!';