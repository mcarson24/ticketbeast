<?php

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
	use PaymentGatewayContractsTest;

	protected function getPaymentGateway()
	{
		return new StripePaymentGateway(config('services.stripe.secret'));
	}
}
