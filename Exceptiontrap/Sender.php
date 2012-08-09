<?php
class ExceptiontrapSender
{
  static $contentType = 'application/json';

  static function notify($data)
  {
    $serializedData = $data->toJson();
    $url = Exceptiontrap::$apiUrl;
    $headers = array(
      'Accept' => self::$contentType,
      'Content-Type' => self::$contentType,
      'X-Api-Key' => Exceptiontrap::$apiKey
    );

    // self::debugRequest($url, $headers, $xml_data);
    self::zendRequest($url, $headers, $serializedData);
  }

  static function zendRequest($url, $headers, $data)
  {
    require_once('Zend/Loader.php');
    require_once('Zend/Http/Client.php');

    $client = new Zend_Http_Client($url);
    $client->setHeaders($headers);
    $client->setRawData($data, self::$contentType);
    $client->setConfig(array('maxredirects' => 1, 'timeout' => Exceptiontrap::$timeout));

    // try{
    //   $response = $client->request('POST');
    // }catch(Exception $e){ // Timeout Exception because the HTTP_CLIENT dont terminate gracefully
    //   throw new RuntimeException($e->getMessage());
    // }

    $response = $client->request('POST');
    // return $response->getStatus();
  }

  static function debugRequest($url, $headers, $data)
  {
    header("Content-type: text/xml");
    echo $data;

    die();
  }

}