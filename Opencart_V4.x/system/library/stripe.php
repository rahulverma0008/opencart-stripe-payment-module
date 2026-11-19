<?php
namespace StripeFree;

// Load the bundled Stripe PHP SDK once.
// If another extension has already loaded a Stripe SDK in this request we re-use it
// instead of redeclaring its classes (which would be a fatal error).
if (!class_exists('\Stripe\Stripe', false)) {
	require __DIR__ . '/stripe/init.php';
}
