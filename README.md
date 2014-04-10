# fuel LbUrl

fuel-lbUrl-package is a package for easily creating and managing urls redirect.

## Installation

* Clone or download the fuel-lbUrl-package repository
* Move it into your packages folder, and rename it to "lbUrl"
* Add 'lbUrl' to the 'always_load.packages' array in app/config/config.php.
* Open your oil console
* Run 'refine migrate --packages=lbUrl' to generate model

## Configuration

You can configure the package with the config url file :

```php
return array(
	'generate' => array(
		'type'   => 'alnum', // Type for random short url (use \Str::random())
		'length' => 4, // Length of the random short url
		'suffix' => '',
		'prefix' => '',
	),
);
```

### Create the redirection

I want my short redirect url is : http://your-fuel-url.com/go/(the slug)

1. In my index controller (app/classes/controller/index.php) :

```php
	public function action_redirect()
	{
		// Get the slug
		$slug = $this->param('slug');
		// Load lbUrl package
		\Package::load('lbUrl');

		// Do the redirection or return 404
		if (\LbUrl\Helper_Url::redirect($slug) === false)
			throw new \HttpNotFoundException;
	}
```

2. In your route.php

You MUST have a route named "module_url_redirect" for redirect uri

```php
return array(
	...
	'go/(:slug)' => array('index/redirect', 'name' => 'module_url_redirect'),   
	...
);
```

## Usage (example)

### Create easily a short url

```php
	$url = \LbUrl\Helper_Url::generate("http://www.fuelphp.com");
    echo 'your short URL : ' . \LbUrl\Helper_Url::getUrl($url, true);
```

### Create a short url

```php
	$url = \LbUrl\Helper_Url::forge();

    // Set params for save the url
    $data = array(
        'slug'        => 'my-url',
        'url_target' => 'http://www.fuelphp.com',
        'code'        => '302',
        'description' => 'the description',
        'method'      => 'location',
        'active'      => true,
    );
    $url->from_array($data);

    // Save
    $url = \LbUrl\Helper_Url::manage($url);

    echo 'your short URL : ' . \LbUrl\Helper_Url::getUrl($url, true);
```

### Get the URL from the model

```php
	$url = \LbUrl\Helper_Url::find('my-url');

	// Return http://www.fuelphp.com
	\LbUrl\Helper_Url::getUrl($url);

	// Return http://your-fuel-url/go/my-url
	\LbUrl\Helper_Url::getUrl($url, true);
```

### Get model from URL target
```php
	$url = \LbUrl\Helper_Url::findByUrl('http://www.fuelphp.com');

	// Return http://www.fuelphp.com
	\LbUrl\Helper_Url::getUrl($url);

	// Return http://your-fuel-url/go/my-url
	\LbUrl\Helper_Url::getUrl($url, true);
```

## Helper functions

### Generate

> LbUrl\Helper_Url::generate($urlTarget, $slug = false, $prefix = false, $suffix = false, $code = '302', $method = 'location', $randomType = false, $length = false);

For generate rapidly an short url. If $slug = false, the function will create a random slug.

### randomSlug

> LbUrl\Helper_Url::randomSlug($randomType = false, $length = false)

For create a random slug. $randomType is the type used in the function \Str::random()

### redirect

> LbUrl\Helper_Url::redirect($url)

For redirect to the url target. $url is not a string, it's the object LbUrl\Model_Url.

### getUrl

> LbUrl\Helper_Url::getUrl($url, $slug = false)

Return the url from the model Model_Url ($url).

The long url (url target) if $slug=false, else the short url (slug)

## Helper Model functions

### The model ( Model_Url )

* id
* slug (for the short URL)
* url_target (long URL)
* code (301 or 302)
* method (refresh or location)
* description
* active
* hits
* url_master (BelongsTo Model_Url)
* associated_urls (HasMany Model_Url)

### forge

> LbUrl\Helper_Url::forge($data = array())

### findByUrl

> LbUrl\Helper_Url::findByUrl($urlTarget, $active = false, $getMaster = true, $strict = false)

### find

> LbUrl\Helper_Url::find($id, $active = false, $getMaster = true, $strict = false)

$id can be the slug or the id

### delete

> LbUrl\Helper_Url::delete($url)

$url can the model, the slug, or the id.

### manage

> LbUrl\Helper_Url::manage($url)

For save/update the model

### getAllUrls

> LbUrl\Helper_Url::getAllUrls($getMaster = true, $active = false)

Return the list of URLs model

## Url module

A module for manage the package (administration) : [The link](https://github.com/jhuriez/fuel-module-url)