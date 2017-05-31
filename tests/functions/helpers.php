<?php

function create($class, $attributes = [], $times = 1)
{
	return factory($class, $times)->create($attributes);
}