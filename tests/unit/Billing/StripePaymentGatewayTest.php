<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\StripePaymentGateway;

/** @group integration */
class StripePaymentGatewayTest extends TestCase
{
	use PaymentGatewayContractsTest;

	protected function getPaymentGateway()
	{
		return new StripePaymentGateway(config('services.stripe.secret'));
	}
}
