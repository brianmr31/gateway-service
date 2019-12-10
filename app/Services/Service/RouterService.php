<?php // AuthService.php
namespace App\Services\Service;

use App\Services\Response\Response;
use App\Services\Response\ResponseMsg;

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

  public function sendBackendGet( $host, $port, $service, $hostDb, $portDb, $username, $password ){
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
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($err) {
      $response = json_decode($err,true);
      return [ $response, $httpcode];
    } else {
      $response = json_decode($response,true);
      return [ $response, $httpcode];
    }
  }

  public function sendBackendPost( $contentType, $contentbody, $host, $port, $service, $hostDb, $portDb, $username, $password ){
    $curl = curl_init();
    if( $contentType == "application/json" ){
      curl_setopt_array($curl, array(
        CURLOPT_PORT => $port,
        CURLOPT_URL => "http:/".$host.":".$port.$this->uriPath,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($contentbody),
        CURLOPT_HTTPHEADER => array(
          "Authorization: Basic ".$this->authorization,
          "Content-Type: application/json",
          "database: ".$this->service."_".$service,
          "host: ".$hostDb,
          "password: ".$password,
          "port: ".$portDb,
          "username: ".$username
        ),
      ));
    }
    // else if( $contentType == "application/x-www-form-urlencoded" ){
    //   curl_setopt_array($curl, array(
    //     CURLOPT_PORT => $port,
    //     CURLOPT_URL => "http://".$host.":".$port."/".$path,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => "",
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 30,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => "POST",
    //     CURLOPT_POSTFIELDS => "woowow=asooooo",
    //     CURLOPT_HTTPHEADER => array(
    //       "Authorization: Basic ".$this->authorization,
    //       "Content-Type: application/x-www-form-urlencoded",
    //     ),
    //   ));
    // }
    else{
      return array("msg"=>"wkwkwk");
    }
    // curl_setopt_array($curl, array(
    //   CURLOPT_PORT => "8082",
    //   CURLOPT_URL => "http://localhost:8082/user/api/v1/U004/1",
    //   CURLOPT_RETURNTRANSFER => true,
    //   CURLOPT_ENCODING => "",
    //   CURLOPT_MAXREDIRS => 10,
    //   CURLOPT_TIMEOUT => 30,
    //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //   CURLOPT_CUSTOMREQUEST => "POST",
    //   CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"py\"\r\n\r\nkw\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
    //   CURLOPT_HTTPHEADER => array(
    //     "Authorization: Basic eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhcHBfY29tcGFueSI6InNlcnZpY2UiLCJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6IkJyMWFOIiwicm9sZSI6InVzZXIiLCJwaG9uZSI6IjA4OTUzNjMyNTg4NTEiLCJjcmVkZW50aWFsIjoiZXlKcGRpSTZJblZ4VUZaTU1XZzFXSHBzUXpWaFNtTmtPVzluYmtFOVBTSXNJblpoYkhWbElqb2lTVzlHU2xoVU1HRjFTblpHUzNWa1ZFSm9XR1ZsS3pCYVkwUkxPRFJMUkUxY0wwMXdkRVp1T1VwRE1tODlJaXdpYldGaklqb2lNREpoTnpJNU9ESXpNelk1T0RVNU0yWXhZak5qT1dGbU5tTmpPR1F5TWpVMU1tSXdabUpoTmpSak1HWmlOR1V4TVRNM04yWmhZakJrTWpoaE5EVmhNU0o5IiwidGltZSI6MTU3NTgwNDIwMi4zOTUzNjh9.yhlVTJwvtdz5ocq-tVkUOpKNYblUBYti4BTL--Iz7_E",
    //     "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
    //   ),
    // ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($err) {
      $response = json_decode($err,true);
      return [ $response, $httpcode];
    } else {
      $response = json_decode($response,true);
      return [ $response, $httpcode];
    }
  }

  public function router( $request, $method ){
    $response = null ;
    $result = $this->getAuthorization($request->header('Authorization'));
    if( $result == null ){
      $response = new Response(400, false, null, ResponseMsg::NOT_AUTHORIZATION);
      return response($response->getJson(), $response->getStatus() )->header('Content-Type', 'application/json');
    }
    $this->getService($request->path(), $request->getRequestUri());
    $backends = $this->sendVerify();
    if( $method == 'get' ){
      if( $backends['success'] == true ){
        $response = $this->sendBackendGet($backends['backend_host'], $backends['backend_port'], $backends['service'],
          $backends['host'], $backends['port'], $backends['username'], $backends['password']);
        return response( $response[0], $response[1] )->header('Content-Type', 'application/json');
      }else{
        $response = new Response(400, false, null, ResponseMsg::PAGE_IS_MISSING);
        return response($response->getJson(), $response->getStatus() )->header('Content-Type', 'application/json');
      }
    }else if( $method == 'post' ){
      $requestContentType = $request->header('Content-Type');
      $all = $request->all();
      $response = $this->sendBackendPost( $requestContentType, $all, $backends['backend_host'], $backends['backend_port'], $backends['service'],
        $backends['host'], $backends['port'], $backends['username'], $backends['password']);
      return response(array("data"=> $response['data']), 200 )->header('Content-Type', 'application/json');
    }else{
      $response = new Response(400, false, null, ResponseMsg::PAGE_IS_MISSING);
      return response($response->getJson(), $response->getStatus() )->header('Content-Type', 'application/json');
    }
  }
}
