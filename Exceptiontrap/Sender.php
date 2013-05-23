<?php
class ExceptiontrapSender
{
  private static $contentType = 'application/json';

  public static function notify($data)
  {
    $serializedData = $data->toJson();
    $url = Exceptiontrap::$apiUrl;
    $headers = array(
      'Accept' => self::$contentType,
      'Content-Type' => self::$contentType,
      'X-Api-Key' => Exceptiontrap::$apiKey
    );

    self::postRequest($url, $headers, $serializedData);
  }

  /*
  * Decides if cURL or pure php transport (stream_socket_client) is used
  */
  private static function postRequest($url, $headers, $data)
  {
    if (function_exists('curl_version')) {
      self::curlRequest($url, $headers, $data);
    } else {
      self::phpRequest($url, $headers, $data);
    }
  }

  /*
  * POST the data via cURL extension
  */
  private static function curlRequest($url, $headers, $data)
  {
    $protocol = Exceptiontrap::$ssl ? 'https://' : 'http://';
    $header = array('Expect:'); // Fixes slow requests http://php.net/manual/de/ref.curl.php
    foreach ($headers as $k => $v) {
      array_push($header, $k . ": " . $v);
    }

    $client = curl_init();
    curl_setopt($client, CURLOPT_URL, $protocol . $url);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($client, CURLOPT_CONNECTTIMEOUT, Exceptiontrap::$timeout);
    curl_setopt($client, CURLOPT_HTTPHEADER, $header);
    curl_setopt($client, CURLOPT_POST, true);
    curl_setopt($client, CURLOPT_POSTFIELDS, $data);
    curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($client);
    curl_close($client);
  }

  /*
  * POST the data via stream_socket_client
  * Fallback if cURL extension is not available
  */
  private static function phpRequest($url, $headers, $data)
  {
    $protocol = Exceptiontrap::$ssl ? 'ssl://' : '';
    $url = parse_url($url);
    $host = $url['host'];
    $path = $url['path'];

    if (isset($url['port'])) { // Used in development
      $port = ':' . $url['port'];
    } else {
      $port = Exceptiontrap::$ssl ? ':443' : ':80';
    }

    $headers['Host'] = $host;
    $headers['Content-Length'] = strlen($data);
    $headers['Connection'] = 'close';

    $header = '';
    foreach($headers as $k => $v) {
      $header .= "{$k}: {$v}\r\n";
    }

    $socket = stream_socket_client($protocol . $host . $port, $errno, $errstr, Exceptiontrap::$timeout, STREAM_CLIENT_CONNECT);
    fwrite($socket, "POST $path HTTP/1.1\r\n");
    fwrite($socket, $header . "\r\n");
    fwrite($socket, $data . "\r\n");
    fclose($socket);
  }
}