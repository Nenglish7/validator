<?php
namespace Genial\Server;
use \Genial\Addon\Function;
use \Genial\Browser;
class Response extends BrowserSupport implements ResponseInterface {
  protected $statuses = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    207 => 'Multi-Status',
    208 => 'Already Reported',
    226 => 'IM Used',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    308 => 'Permanent Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed'
    418 => 'I\'m a teapot',
    421 => 'Misdirected Request',
    422 => 'Unprocessable Entity',
    423 => 'Locked',
    424 => 'Failed Dependency',
    426 => 'Upgrade Required',
    428 => 'Precondition Required',
    429 => 'Too Many Requests',
    431 => 'Request Header Fields Too Large',
    451 => 'Unavailable For Legal Reasons',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    511 => 'Network Authentication Required'
  ];
  public function __construct() {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? true : false;
    if (!$secure && \HTTPS_ACTIVE) {
      $this->refresh(0, 301, true);
    }
    $this->clearDuplicates();
    header_register_callback(function () {
      header_remove('X-Powered-By');
      header('X-Powered-By: Genial');
    });
	}
  public function send(array $data, $statusCode = 200) {
    $this->setPageType('json');
    $this->setStatusHeader($statusCode);
    echo json_encode($data);
    exit;
  }
  public function setPageType($extension = 'txt') {
    if ($extension === 'json') {
      header('Content-Type: application/json');
    } elseif ($extension === 'txt') {
      header('Content-Type: text/plain');
    } elseif ($extension === 'htm' || $extension === 'html' || $extension === 'php') {
      header('Content-Type: text/html');
    } elseif ($extension === 'css') {
      header('Content-Type: text/css');
    } elseif ($extension === 'js') {
      header('Content-Type: application/javascript');
    } elseif ($extension === 'xml') {
      header('Content-Type: application/xml');
    } elseif ($extension === 'swf') {
      header('Content-Type: application/x-shockwave-flash');
    } elseif ($extension === 'flv') {
      header('Content-Type: video/x-flv');
    } elseif ($extension === 'png') {
      header('Content-Type: image/png');
    } elseif ($extension === 'gif') {
      header('Content-Type: image/gif');
    } elseif ($extension === 'jpe' || $extension === 'jpeg' || $extension === 'jpg') {
      header('Content-Type: image/jpeg');
    } elseif ($extension === 'bmp') {
      header('Content-Type: image/bmp');
    } elseif ($extension === 'ico') {
      header('Content-Type: image/vnd.microsoft.icon');
    } elseif ($extension === 'tiff' || $extension === 'tif') {
      header('Content-Type: image/tiff');
    } elseif ($extension === 'svg' || $extension === 'svgz') {
      header('Content-Type: image/svg+xml');
    } elseif ($extension === 'zip') {
      header('Content-Type: application/zip');
    } elseif ($extension === 'rar') {
      header('Content-Type: application/x-rar-compressed');
    } elseif ($extension === 'exe' || $Extension === 'msi') {
      header('Content-Type: application/x-msdownload');
    } elseif ($extension === 'cab') {
      header('Content-Type: application/vnd.ms-cab-compressed');
    } else {
      header('Content-Type: text/plain');
    }
  }
  public function headerSent($Header) {
    $headers = $this->headersList();
    $header = trim($header, ': ');
    $res = false;
    foreach ($headers as $hdr) {
      if (stripos($hdr, $header) !== false) {
        $res = true;
      }
    }
    return $res;
  }
  public function headersList() {
    return php_sapi_name() === 'cli' ? xdebug_get_headers() : headers_list();
  }
  public function clearDuplicates() {
    $dataSet = array();
    foreach ($this->headersList() as $headerItem) {
      array_push($dataSet, $headerItem);
    }
    header_remove();
    foreach(array_unique($dataSet) as $reRunHeader) {
      header($reRunHeader, false);
    }
  }
  public function redirect($url = '/', $delay = 0, $statusCode = 302) {
    $this->setStatusHeader(202);
    $protocol = substr($Url, 0, 8);
    $isExteral = $protocol === 'https://' || substr($protocol, 0, 7), 'http://' ? true : false;
    if (!$isExteral) {
      $url = rtrim(\SCRIPT_URL, '/\\') . '/' . ltrim($url, '/\\');
    }
    if (!headers_sent() && $this->browserSupported()) {
      header("Refresh:{$delay};url=$url", true, $statusCode);
    } else {
      echo '<script type="text/javascript">';
      echo 'var timer = setTimeout(function() {';
      echo 'window.location=\'' . e($url) . '\'';
      echo '}, ' . e($delay) . '000);';
      echo '</script>';
      echo '<noscript>';
      echo '<meta http-equiv="refresh" content="' . e($delay) . ';url=' . e($url) . '" />';
      echo '</noscript>';
    }
    exit;
  }
  public function Refresh($delay = 0, $statusCode = 200, $useHttps = false) {
    $this->setStatusHeader(202);
    $url = '';
    $actUrl = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if ($useHttps) {
      $url .= ";url=https://$actUrl";
    }
    if (!headers_sent() && $this->browserSupported()) {
      header("Refresh:$delay$url", true, $statusCode);
    } else {
      echo '<script type="text/javascript">';
      echo 'var timer = setTimeout(function() {';
      if ($useHttps) {
        echo 'window.location=\'' . e($actUrl) . '\'';
      } else {
        echo 'location.reload();';    
      }
      echo '}, ' . e($delay) . '000);';
      echo '</script>';
      echo '<noscript>';
      echo '<meta http-equiv="refresh" content="' . e($delay) . e($url) . '" />';
      echo '</noscript>';
    }
    exit;
  }
  public function setStatusHeader($code = 200) {
    $text = isset($this->statuses[$code]) ? $this->statuses[$code] : 'Unknown';
    $serverProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : false;
    if (substr(php_sapi_name(), 0, 3) == 'cgi') {
      header("Status: $code $text", true);
    } elseif ($serverProtocol == 'HTTP/1.1' or $serverProtocol == 'HTTP/1.0') {
      header("$serverProtocol $code $text", true, $code);
    } else {
      header("HTTP/1.1 $code $text", true, $code);
    }
  }
}
?>
