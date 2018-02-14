<?php

namespace App\Billing;

class Charge
{
	protected $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function cardLastFour()
	{
		return $this->data['card_last_four'];
	}

	public function destination()
	{
		return $this->data['destination'];
	}

	public function amount()
	{
		return $this->data['amount'];
	}
}
