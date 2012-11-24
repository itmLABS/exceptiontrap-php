# Exceptiontrap

This notifier class is used to send the exceptions and errors of your PHP (and Zend Framework) applications to the [Exceptiontrap](https://alpha.exceptiontrap.com) webservice.

The plugin / class is compatible with PHP >= 5.2

**This is an Alpha Release**

## Setup

### Pure PHP

#### 1. Install

Download the class from here and copy it to your desired folder (i.e. `Exceptiontrap/`)

#### 2. Configure

Now insert the following lines into your applications codebase

    require_once 'Exceptiontrap/Exceptiontrap.php';
    Exceptiontrap::setup('YOUR_API_KEY', false, 'YOUR_APPLICATION_ENV');

and you should be fine.

### Zend Framework 1.x

#### 1. Install

Download the class from here and copy it to your desired library folder (i.e. `/libraries/Exceptiontrap/`)

#### 2. Configure

Now insert the following lines into your applications codebase

    require_once 'Exceptiontrap/Exceptiontrap.php';
    Exceptiontrap::setup('YOUR_API_KEY', false, 'YOUR_APPLICATION_ENV');

#### 3. Register Plugin for better integration

If you use the Bootsrap class insert the following method.

    protected function _initExceptiontrap(){
      Zend_Controller_Front::getInstance()->registerPlugin(new Exceptiontrap_ErrorHandler());
    }

Or register the plugin manually to the front controller

    $controller = Zend_Controller_Front::getInstance();
    $controller->registerPlugin(new Exceptiontrap_ErrorHandler());

## Information / Further Configuration

You can find your API-Key by login to your [Exceptiontrap Account](https://alpha.exceptiontrap.com/login), select the application and follow the **Setup** Link.

## Known Issues / Todo

Optimize and insert the test suite to plugin.


Copyright (c) 2012 [Torsten Bühl], released under the MIT license