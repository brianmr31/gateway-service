<?php
namespace App\Services\Response;

class Response {

    private $status;
    private $success;
    private $data;
    private $error;

    public function __construct( $status, $success, $data, $error) {
      $this->status = $status;
      $this->success = $success;
      $this->data = $data;
      $this->error = $error;
    }

    public function getStatus(){
      return $this->status;
    }
    public function getData(){
      return $this->data;
    }
    public function getSuccess(){
      return $this->success;
    }
    public function getError(){
      return $this->error;
    }

    public function getJson(){
      return array(
        'data' => $this->data,
        'success' => $this->success,
        'error' => $this->error,
      );
    }
}
