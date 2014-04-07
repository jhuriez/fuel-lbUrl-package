<?php

return array(
	'generate' => array(
		'type'   => 'alnum', // Type for random short url (use \Str::random())
		'length' => 4, // Length of the random short url
		'suffix' => '',
		'prefix' => '',
	),
	// 'base' => 'go/(:slug)', // The base for all your short URLs. For this example, a random short url will be : http://my-project.com/go/gD4s
);