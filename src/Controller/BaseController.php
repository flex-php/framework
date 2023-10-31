<?php

namespace Flex\Controller;

use Flex\Script\ScriptFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected function runMiddlewares(Request $request): ?Response {
        $stack = $this->getStack($request);
        $middlewares = $stack["middlewares"];

        foreach($middlewares as $middleware){
            $middlewareResponse = (new ScriptFile($middleware))->getReturn();

            if($middlewareResponse instanceof Response){
                return $middlewareResponse;
            }
        }

        return null;
    }

    protected function getStack(Request $request){
        return $request->attributes->get("_details")["stack"];
    }

}