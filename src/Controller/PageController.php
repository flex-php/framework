<?php

namespace Flex\Controller;

use Flex\Script\ScriptFile;
use Flex\View\ViewRenderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PageController extends BaseController
{
    public function __construct(protected ViewRenderService $viewRenderer)
    {
    }

    public function handle(Request $request)
    {
        $middlewaresResponse = $this->runMiddlewares($request);
        if ($middlewaresResponse) {
            return $middlewaresResponse;
        }

        $stack = $request->attributes->get("_details")["stack"];

        if ($stack["static"] != null) {
            throw new BadRequestHttpException("Static paths should be built using `php flex build`");
        }

        $data = [];
        if (!empty($stack["data"])) {
            $script = new ScriptFile($stack["data"], [
                "request" => $request
            ]);
            $data = $this->getPageData($script);
        }

        $templateData = !empty($data["data"]) && is_array($data["data"]) ? $data["data"] : [];
        $templateData["request"] = $request;

        $content = $this->viewRenderer->renderStack($stack, $templateData);
        return new Response($content);
    }

    protected function getPageData(ScriptFile $scriptFile): array
    {
        $data = [];

        $result = $scriptFile->getReturn();

        if (is_array($result)) {
            $data = $result;
        }

        return $data;
    }
}