<?php

/**
 * The actual directory name for the application directory. Normally
 * named 'src'.
 */

define('VENDOR_DIR', 'vendor');

/**
 * Path to the Vendor's directory.
 */

define('VENDOR', ROOT . DS . VENDOR_DIR . DS);
define('SPIDER', \Cake\Core\Plugin::path('Spider'));
define('INSTALL', SPIDER . DS . 'Install');