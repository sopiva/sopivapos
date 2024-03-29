<?php
/**
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 */

if (!defined('DS'))
  {
    define('DS', DIRECTORY_SEPARATOR);
  }
if (!defined('ROOT'))
  {
    define('ROOT', dirname(dirname(dirname(__FILE__))));
  }
if (!defined('APP_DIR'))
  {
    define('APP_DIR', basename(dirname(dirname(__FILE__))));
  }
if (!defined('CAKE_CORE_INCLUDE_PATH'))
  {
    define('CAKE_CORE_INCLUDE_PATH', "/usr/share/cakephp13");
  }

if (!defined('WEBROOT_DIR'))
  {
    define('WEBROOT_DIR', basename(dirname(__FILE__)));
  }
if (!defined('WWW_ROOT'))
  {
    define('WWW_ROOT', dirname(__FILE__) . DS);
  }
if (!defined('CORE_PATH'))
  {
    if (function_exists('ini_set') &&
        ini_set('include_path', CAKE_CORE_INCLUDE_PATH . PATH_SEPARATOR . ROOT . DS . APP_DIR . DS . PATH_SEPARATOR . ini_get('include_path')))
      {
        define('APP_PATH', null);
        define('CORE_PATH', null);
      }
    else
      {
        define('APP_PATH', ROOT . DS . APP_DIR . DS);
        define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
      }
  }

if (!include(CORE_PATH . 'cake' . DS . 'bootstrap.php'))
  {
    trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
  }

if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico')
  {
    return;
  }
else
  {
    $Dispatcher = new Dispatcher();
    $Dispatcher->dispatch();
  }
