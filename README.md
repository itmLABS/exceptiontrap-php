# Exceptiontrap PHP

This notifier class is used to catch and send exceptions and errors of your PHP (and Zend Framework) applications to the [Exceptiontrap](https://exceptiontrap.com) webservice.

The plugin / class is compatible with PHP >= 5.2

## Setup

### PHP

#### 1. Install

Download the class from here and copy it to your desired folder (i.e. `Exceptiontrap/`).

#### 2. Configure

Now insert the following lines into your applications codebase

```php
require_once 'Exceptiontrap/Exceptiontrap.php';
Exceptiontrap::setup('YOUR_API_KEY', false, 'YOUR_APPLICATION_ENV');
```

and you should be fine.

### Zend Framework 1.x

#### 1. Install

Download the class from here and copy it to your desired library folder (i.e. `/libraries/Exceptiontrap/`).

#### 2. Configure

Now insert the following lines into your applications codebase.

```php
require_once 'Exceptiontrap/Exceptiontrap.php';
Exceptiontrap::setup('YOUR_API_KEY', false, 'YOUR_APPLICATION_ENV');
```

#### 3. Register Plugin for better integration

If you use the Bootsrap class insert the following method.

```php
protected function _initExceptiontrap(){
  Zend_Controller_Front::getInstance()->registerPlugin(new Exceptiontrap_ErrorHandler());
}
```

Or register the plugin manually to the front controller.

```php
$controller = Zend_Controller_Front::getInstance();
$controller->registerPlugin(new Exceptiontrap_ErrorHandler());
```

## Information / Further Configuration

You can find your API-Key by login to your [Exceptiontrap Account](https://exceptiontrap.com/login), select the application and follow the **Setup** Link.

If you have data in your request params, session or environment, which you don't want to be sent to Exceptiontrap, set them as follows:

```php
Exceptiontrap::setFilterParams(array('HTTP_COOKIE', '_app_session', 'password'));
```

You can also specify exceptions and errors, which should be ignored and not sent.

```php
Exceptiontrap::setIgnoreList(array('InvalidArgumentException', 'Zend_Translate_Exception'));
```

### Better integration for your framework (Symfony, CodeIgniter, Lithium, ...)

Until the class is extended to support other frameworks directly as a plugin, you can set the request-components by yourself. The `setRequestComponents` class method expects an associated array to do this.

```php
Exceptiontrap::setRequestComponents(array(
  'module' => 'YOUR_CURRENT_MODULE',
  'controller' => 'YOUR_CURRENT_CONTROLLER',
  'action' => 'YOUR_CURRENT_ACTION'
));
```

## Known Issues / Todo

* Optimize and insert the test suite to plugin.
* Better integration for other frameworks.

Copyright (c) 2013 [Torsten Bühl], released under the MIT license