<?php // AuthService.php
namespace App\Services\Service;

class RouterService {

  private $currentDate;

  private $service = null;
  private $urlCore = null;
  private $uriPath = null;
  private $authorization = null;

  private $curl = null;

  private $verify_port = 8080;
  private $verify_host = '127.0.0.1';
  private $verify_url = '/api/v1/verify';

  public function __construct() {
     $this->currentDate = date("Y-m-d h:m:s");
  }

  private function getService( $path, $uri ){
    $uriArray = explode("/", $uri);
    $this->service = $uriArray[1];
    $this->urlCore = $uriArray[4];
    $this->uriPath = preg_replace('/^\/'.$uriArray[1].'/','', $uri);
    // var_dump( $this->service." ".$this->uriPath." ".$this->urlCore );die;
  }

  private function getAuthorization( $authorization ){
    if( is_null($authorization) ){
      return null;
    }
    $arrAuth = explode(" ", $authorization);
    if( count($arrAuth) != 2){
      return null;
    }
    $this->authorization = $arrAuth[1];
    return true;
  }

  private function sendVerify( ){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_PORT => $this->verify_port,
      CURLOPT_URL => "http://".$this->verify_host.":".$this->verify_port."/".$this->verify_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Basic ".$this->authorization,
        "Service: ".$this->service,
        "Core: ".$this->urlCore,
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return null;
    } else {
      $response = json_decode($response, true);
      return $response;
    }
  }

  public function sendBackend( $host, $port, $service, $hostDb, $portDb, $username, $password ){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_PORT => $port,
      CURLOPT_URL => "http:/".$host.":".$port.$this->uriPath,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "database: ".$this->service."_".$service,
        "host: ".$hostDb,
        "password: ".$password,
        "port: ".$portDb,
        "username: ".$username
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      var_dump($err);die;
      $response = json_decode($err,true);
      return $response;
    } else {
      $response = json_decode($response,true);
      return $response;
    }
  }

  public function router( $request, $method ){
    $response = null ;
    $result = $this->getAuthorization($request->header('Authorization'));
    if( $result == null ){
      return response(array("msg"=> false), 403 )->header('Content-Type', 'application/json');
    }
    $this->getService($request->path(), $request->getRequestUri());
    $backends = $this->sendVerify();
    if( $method == 'get' ){
      if( $backends['success'] == true ){
        $response = $this->sendBackend($backends['backend_host'], $backends['backend_port'], $backends['service'],
          $backends['host'], $backends['port'], $backends['username'], $backends['password']);
        return response( array("data" => $response['data'] ), 200 )->header('Content-Type', 'application/json');
      }else{
        return response(array("msg"=> false), 500 )->header('Content-Type', 'application/json');
      }
    }else if( $method == 'post' ){
      return response(array("msg"=> true), 200 )->header('Content-Type', 'application/json');
    }else{
      return response(array("msg"=> false), 500 )->header('Content-Type', 'application/json');
    }
  }
}
