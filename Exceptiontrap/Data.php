<?php
class ExceptiontrapData
{
  public function __construct($exception)
  {
    $this->exception = $exception;
    $this->collectData();
  }

  private function collectData()
  {
    $data = array();
    $data['notifier'] = Exceptiontrap::$notifierName;
    $data['app_environment'] = Exceptiontrap::$environment;

    $data['name'] = get_class($this->exception);
    $data['message'] = $this->exception->getMessage();
    $data['root'] = $this->getApplicationRoot();
    $data['request_uri'] = $this->getRequestUri();
    $data['location'] = $this->getLocation();
    # return $_SERVER['REQUEST_METHOD'];
    $data['request_params'] = $this->filterParams($this->getRequestParams());
    $data['request_session'] = $this->filterParams($this->getSessionData());
    $data['environment'] = $this->filterParams($this->getEnvironmentData());
    $data['trace'] = $this->filterBacktrace($this->getTrace());
    $data['request_components'] = $this->getRequestComponents();

    $this->data = $data;
  }

  private function getRequestComponents()
  {
    return array(
      'controller' => Exceptiontrap::$controller,
      'action' => Exceptiontrap::$action,
      'module' => Exceptiontrap::$module
    );
  }

  private function getLocation()
  {
    return $this->exception->getLine() . ' in ' . $this->exception->getFile();
  }

  private function getRequestParams()
  {
    return $_REQUEST;
  }

  private function getTrace()
  {
    return explode("\n", $this->exception->getTraceAsString());
  }

  private function getApplicationRoot()
  {
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
      return $_SERVER['DOCUMENT_ROOT'];
    } else {
      return dirname(__FILE__);
    }
  }

  private function getRequestUri()
  {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
    $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
    $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    if ($port == 80 || $port == 443)
      $port = null;
    else
      $port = ":" . $port;

    return $protocol . $host . $port . $path;
  }

  private function getSessionData()
  {
    return isset($_SESSION) ? $_SESSION : '';
  }

  private function getEnvironmentData()
  {
    return $_SERVER;
  }

  private function filterParams($params)
  {
    if (is_array($params)) {
      foreach ($params as $k => $v){
        if (is_array($v)){
          $params[$k] = $this->filterParams($v);
        } else {
          if (in_array($k, Exceptiontrap::$filterParams)) $params[$k] = '[FILTERED]';
        }
      }
    }
    return $params;
  }

  private function filterBacktrace($backtrace)
  {
    return $backtrace; // TODO: Implement
  }

  public function toJson()
  {
    return json_encode(array('problem' => $this->data));
  }

  public function toXml()
  {
    $doc = new SimpleXMLElement('<problem />');

    foreach ($this->data as $key => $value) {
      if (is_array($value)) {
        $node = $doc->addChild($key);
        foreach ($value as $innerKey => $innerValue) {
          if (!is_array($innerValue))
            $node->addChild($innerKey, htmlentities($innerValue));
        }
      } else {
        $doc->addChild($key, htmlentities($value));
      }
    }

    return $doc->asXML();
  }

}