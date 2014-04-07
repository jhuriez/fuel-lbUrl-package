<?php

/**
 * LbUrl : Manage and Build url
 *
 * @package    LbMenu
 * @version    v1.00
 * @author     Julien Huriez
 * @license    MIT License
 * @copyright  2013 Julien Huriez
 * @link   https://github.com/jhuriez/fuel-lbUrl-package
 */
Autoloader::add_core_namespace('LbUrl');

Autoloader::add_classes(array(
    'LbUrl\\Helper_Url' => __DIR__ . '/classes/helper/url.php',
    'LbUrl\\Model_Url' => __DIR__ . '/classes/model/url.php',
));

// Load config
\Config::load('url', true);

/* End of file bootstrap.php */
