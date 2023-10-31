<?php

namespace Flex\Controller;

use Flex\Exceptions\ScriptFunctionNotFoundException;
use Flex\Script\ScriptFile;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteController extends BaseController
{
    public function handle(Request $request)
    {
        $middlewaresResponse = $this->runMiddlewares($request);
        if ($middlewaresResponse) {
            return $middlewaresResponse;
        }

        $method = $request->getMethod();
        $action = $request->attributes->get("_details")["stack"]["route"];

        $script = new ScriptFile($action);

        try {
            $response = $script->callFunction($method);

            if ($response instanceof Response) {
                return $response;
            }

            if (is_array($response)) {
                return new JsonResponse($response);
            }

            return new Response();
        } catch (NotFoundHttpException|ScriptFunctionNotFoundException $e) {
            return new JsonResponse(["error" => "Not found"], 404);
        } catch (Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
    }
}