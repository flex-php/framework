<?php

namespace Flex\Controller;

use Flex\Script\ScriptFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionController extends BaseController
{
    public function handle(Request $request)
    {
        $middlewaresResponse = $this->runMiddlewares($request);
        if($middlewaresResponse){
            return $middlewaresResponse;
        }

        $method = $request->getMethod();
        $action = $request->attributes->get("_details")["stack"]["action"];

        $script = new ScriptFile($action);

        $response = $script->callFunction($method);

        if($response instanceof Response){
            return $response;
        }

        if(is_array($response)){
            return new JsonResponse($response);
        }

        return new RedirectResponse($request->getUri());
    }
}