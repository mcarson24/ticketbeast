<?php

namespace App;

interface TicketCodeGenerator
{
	public function generateFor(Ticket $ticket);
}