<?php

namespace Flex\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewRenderService
{

    protected array $data = [];

    public function __construct(protected ViewEngineManager $engineManager)
    {
    }

    public function outlet(): void {
        if (
            !isset($this->data['__content']) ||
            empty($this->data['__content']) ||
            !is_string($this->data['__content']))
        {
            return;
        }

        echo $this->data['__content'];
    }

    protected function renderView(string $path, array $data = []): string
    {
        $fileName = basename($path);
        [,$extension] = explode('.', $fileName, 2);

        $engine = $this->engineManager->getEngine($extension);

        if (empty($engine)) {
            throw new \Exception("No engine found for extension: $extension");
        }

        return $engine->render($path, $data);
    }

    public function render(Request $request, array $data = []): Response
    {
        $stack = $request->attributes->get("_details")["stack"];

        return new Response($this->renderStack($stack, $data));
    }

    public function renderStack(array $stack, array $data = []) : string
    {
        global $viewRenderService;
        $viewRenderService = $this;

        $this->data = $data;
        $this->data["__content"] = $this->renderView($stack["page"], $this->data);
        $layouts = $stack["layouts"];

        while($layout = array_pop($layouts)){
            $this->data["__content"] = $this->renderView($layout, $this->data);
        }

        return $this->data["__content"];
    }

}