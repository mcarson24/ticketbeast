<p>Thanks for your order!</p>

<p>You can view your order anytime by visiting this URL:</p>

<p>
	<a href="{{ action('OrdersController@show', $order->confirmation_number) }}">
		{{ action('OrdersController@show', $order->confirmation_number) }}		
	</a>
</p>

