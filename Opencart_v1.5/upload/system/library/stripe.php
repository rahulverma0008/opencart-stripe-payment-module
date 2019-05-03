<?php
$req = array(
	// Stripe singleton
	DIR_SYSTEM . 'library/stripe/Stripe.php', 

	// Utilities
	DIR_SYSTEM . 'library/stripe/Util/AutoPagingIterator.php', 
	DIR_SYSTEM . 'library/stripe/Util/CaseInsensitiveArray.php', 
	DIR_SYSTEM . 'library/stripe/Util/LoggerInterface.php', 
	DIR_SYSTEM . 'library/stripe/Util/DefaultLogger.php', 
	DIR_SYSTEM . 'library/stripe/Util/RandomGenerator.php', 
	DIR_SYSTEM . 'library/stripe/Util/RequestOptions.php', 
	DIR_SYSTEM . 'library/stripe/Util/Set.php', 
	DIR_SYSTEM . 'library/stripe/Util/Util.php', 

	// HttpClient
	DIR_SYSTEM . 'library/stripe/HttpClient/ClientInterface.php', 
	DIR_SYSTEM . 'library/stripe/HttpClient/CurlClient.php', 

	// Errors
	DIR_SYSTEM . 'library/stripe/Error/Base.php', 
	DIR_SYSTEM . 'library/stripe/Error/Api.php', 
	DIR_SYSTEM . 'library/stripe/Error/ApiConnection.php', 
	DIR_SYSTEM . 'library/stripe/Error/Authentication.php', 
	DIR_SYSTEM . 'library/stripe/Error/Card.php', 
	DIR_SYSTEM . 'library/stripe/Error/Idempotency.php', 
	DIR_SYSTEM . 'library/stripe/Error/InvalidRequest.php', 
	DIR_SYSTEM . 'library/stripe/Error/Permission.php', 
	DIR_SYSTEM . 'library/stripe/Error/RateLimit.php', 
	DIR_SYSTEM . 'library/stripe/Error/SignatureVerification.php', 

	// OAuth errors
	DIR_SYSTEM . 'library/stripe/Error/OAuth/OAuthBase.php', 
	DIR_SYSTEM . 'library/stripe/Error/OAuth/InvalidClient.php', 
	DIR_SYSTEM . 'library/stripe/Error/OAuth/InvalidGrant.php', 
	DIR_SYSTEM . 'library/stripe/Error/OAuth/InvalidRequest.php', 
	DIR_SYSTEM . 'library/stripe/Error/OAuth/InvalidScope.php', 
	DIR_SYSTEM . 'library/stripe/Error/OAuth/UnsupportedGrantType.php', 
	DIR_SYSTEM . 'library/stripe/Error/OAuth/UnsupportedResponseType.php', 

	// API operations
	DIR_SYSTEM . 'library/stripe/ApiOperations/All.php', 
	DIR_SYSTEM . 'library/stripe/ApiOperations/Create.php', 
	DIR_SYSTEM . 'library/stripe/ApiOperations/Delete.php', 
	DIR_SYSTEM . 'library/stripe/ApiOperations/NestedResource.php', 
	DIR_SYSTEM . 'library/stripe/ApiOperations/Request.php', 
	DIR_SYSTEM . 'library/stripe/ApiOperations/Retrieve.php', 
	DIR_SYSTEM . 'library/stripe/ApiOperations/Update.php', 

	// Plumbing
	DIR_SYSTEM . 'library/stripe/ApiResponse.php', 
	DIR_SYSTEM . 'library/stripe/StripeObject.php', 
	DIR_SYSTEM . 'library/stripe/ApiRequestor.php', 
	DIR_SYSTEM . 'library/stripe/ApiResource.php', 
	DIR_SYSTEM . 'library/stripe/SingletonApiResource.php', 

	// Stripe API Resources
	DIR_SYSTEM . 'library/stripe/Account.php', 
	DIR_SYSTEM . 'library/stripe/AlipayAccount.php', 
	DIR_SYSTEM . 'library/stripe/ApplePayDomain.php', 
	DIR_SYSTEM . 'library/stripe/ApplicationFee.php', 
	DIR_SYSTEM . 'library/stripe/ApplicationFeeRefund.php', 
	DIR_SYSTEM . 'library/stripe/Balance.php', 
	DIR_SYSTEM . 'library/stripe/BalanceTransaction.php', 
	DIR_SYSTEM . 'library/stripe/BankAccount.php', 
	DIR_SYSTEM . 'library/stripe/BitcoinReceiver.php', 
	DIR_SYSTEM . 'library/stripe/BitcoinTransaction.php', 
	DIR_SYSTEM . 'library/stripe/Card.php', 
	DIR_SYSTEM . 'library/stripe/Charge.php', 
	DIR_SYSTEM . 'library/stripe/Collection.php', 
	DIR_SYSTEM . 'library/stripe/CountrySpec.php', 
	DIR_SYSTEM . 'library/stripe/Coupon.php', 
	DIR_SYSTEM . 'library/stripe/Customer.php', 
	DIR_SYSTEM . 'library/stripe/Discount.php', 
	DIR_SYSTEM . 'library/stripe/Dispute.php', 
	DIR_SYSTEM . 'library/stripe/EphemeralKey.php', 
	DIR_SYSTEM . 'library/stripe/Event.php', 
	DIR_SYSTEM . 'library/stripe/ExchangeRate.php', 
	DIR_SYSTEM . 'library/stripe/File.php', 
	DIR_SYSTEM . 'library/stripe/FileLink.php', 
	DIR_SYSTEM . 'library/stripe/FileUpload.php', 
	DIR_SYSTEM . 'library/stripe/Invoice.php', 
	DIR_SYSTEM . 'library/stripe/InvoiceItem.php', 
	DIR_SYSTEM . 'library/stripe/InvoiceLineItem.php', 
	DIR_SYSTEM . 'library/stripe/IssuerFraudRecord.php', 
	DIR_SYSTEM . 'library/stripe/Issuing/Authorization.php', 
	DIR_SYSTEM . 'library/stripe/Issuing/Card.php', 
	DIR_SYSTEM . 'library/stripe/Issuing/CardDetails.php', 
	DIR_SYSTEM . 'library/stripe/Issuing/Cardholder.php', 
	DIR_SYSTEM . 'library/stripe/Issuing/Dispute.php', 
	DIR_SYSTEM . 'library/stripe/Issuing/Transaction.php', 
	DIR_SYSTEM . 'library/stripe/LoginLink.php', 
	DIR_SYSTEM . 'library/stripe/Order.php', 
	DIR_SYSTEM . 'library/stripe/OrderItem.php', 
	DIR_SYSTEM . 'library/stripe/OrderReturn.php', 
	DIR_SYSTEM . 'library/stripe/PaymentIntent.php', 
	DIR_SYSTEM . 'library/stripe/Payout.php', 
	DIR_SYSTEM . 'library/stripe/Plan.php', 
	DIR_SYSTEM . 'library/stripe/Product.php', 
	DIR_SYSTEM . 'library/stripe/Recipient.php', 
	DIR_SYSTEM . 'library/stripe/RecipientTransfer.php', 
	DIR_SYSTEM . 'library/stripe/Refund.php', 
	DIR_SYSTEM . 'library/stripe/Reporting/ReportRun.php', 
	DIR_SYSTEM . 'library/stripe/Reporting/ReportType.php', 
	DIR_SYSTEM . 'library/stripe/SKU.php', 
	DIR_SYSTEM . 'library/stripe/Sigma/ScheduledQueryRun.php', 
	DIR_SYSTEM . 'library/stripe/Source.php', 
	DIR_SYSTEM . 'library/stripe/SourceTransaction.php', 
	DIR_SYSTEM . 'library/stripe/Subscription.php', 
	DIR_SYSTEM . 'library/stripe/SubscriptionItem.php', 
	DIR_SYSTEM . 'library/stripe/Terminal/ConnectionToken.php', 
	DIR_SYSTEM . 'library/stripe/Terminal/Location.php', 
	DIR_SYSTEM . 'library/stripe/Terminal/Reader.php', 
	DIR_SYSTEM . 'library/stripe/ThreeDSecure.php', 
	DIR_SYSTEM . 'library/stripe/Token.php', 
	DIR_SYSTEM . 'library/stripe/Topup.php', 
	DIR_SYSTEM . 'library/stripe/Transfer.php', 
	DIR_SYSTEM . 'library/stripe/TransferReversal.php', 
	DIR_SYSTEM . 'library/stripe/UsageRecord.php', 
	DIR_SYSTEM . 'library/stripe/UsageRecordSummary.php', 

	// OAuth
	DIR_SYSTEM . 'library/stripe/OAuth.php', 

	// Webhooks
	DIR_SYSTEM . 'library/stripe/Webhook.php', 
	DIR_SYSTEM . 'library/stripe/WebhookSignature.php', 
);

foreach($req as $k=>$v){
	if(file_exists($v)){
		require($v);
	}
}

class Stripe {
    
}
