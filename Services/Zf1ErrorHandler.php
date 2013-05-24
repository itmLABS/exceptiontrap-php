<?php
require_once 'Zend/Controller/Plugin/ErrorHandler.php';
require_once dirname(__FILE__) . '/../Exceptiontrap.php';
/**
* Subclass default error handler to make it called upon 'preDispatch' event and not 'postDispatch' to prevent query from being executed if an error occured within pre-preDispatch event plugins (ex: request extractor, param cleaner, queried account filter...)
*
*/
class Exceptiontrap_Services_Zf1ErrorHandler extends Zend_Controller_Plugin_ErrorHandler
{
  /**
  * Upon starting application, unregister default error handler
  *
  */
  public function __construct(Array $options = array()){
    $this->setErrorHandler($options);
  }

  public function routeStartup(Zend_Controller_Request_Abstract $request)
  {
    Zend_Controller_Front::getInstance()->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
    Zend_Controller_Front::getInstance()->throwExceptions(true);
  }

  public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
  {
    Exceptiontrap::setRequestComponents($request->getParams());
  }

  /**
  * Update $request object prior to entering dispatch loop
  *
  * @return parent::postDispatch
  */
  public function preDispatch(Zend_Controller_Request_Abstract $request)
  {
  }

  /**
  * Forward to error controller if not previously done
  *
  * @return parent::postDispatch
  */
  public function postDispatch(Zend_Controller_Request_Abstract $request)
  {
    if ($request->getControllerName() != 'error') {
      #parent::postDispatch($request);
    }
  }
}