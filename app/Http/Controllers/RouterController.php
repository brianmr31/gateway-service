<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RouterController extends Controller
{
    protected $routerService;

    public function __construct(){
        $this->routerService = app()->make('RouterService');
    }

    public function routerGet(Request $request){
      return $this->routerService->router( $request, 'get');
    }
    public function routerPost(Request $request){
      return $this->routerService->router( $request, 'post');
    }
}
