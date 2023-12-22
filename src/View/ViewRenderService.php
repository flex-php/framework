<?php

namespace Flex\View;

use Flex\Event\PreRenderEvent;
use Flex\View\Variables\VariablesBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Markup;

class ViewRenderService
{

    protected array $data = [];

    public function __construct(protected RequestStack $requestStack, protected Environment $twig, protected string $projectDir, protected EventDispatcherInterface $eventDispatcher)
    {
    }

    protected function renderView(string $path, array $data = []): string
    {
        $fileName = basename($path);
        [, $extension] = explode('.', $fileName, 2);

        if ($extension !== "html.twig") {
            throw new \Exception("Only twig templates are supported");
        }

        $filePath = substr($path, strlen(realpath($this->projectDir)) + 1);

        $variablesBag = new VariablesBag($data);
        $this->eventDispatcher->dispatch(new PreRenderEvent($variablesBag, $this->requestStack, $filePath), PreRenderEvent::PRE_RENDER);

        return $this->twig->render($filePath, $variablesBag->all());
    }

    public function render(Request $request, array $data = []): Response
    {
        $stack = $request->attributes->get("_details")["stack"];

        return new Response($this->renderStack($stack, $data));
    }

    public function renderStack(array $stack, array $data = []): string
    {
        $this->data = $data;
        $this->data["outlet"] = new Markup($this->renderView($stack["page"], $this->data), 'UTF-8');
        $layouts = $stack["layouts"];

        while ($layout = array_pop($layouts)) {
            $this->data["outlet"] = new Markup($this->renderView($layout, $this->data), 'UTF-8');
        }

        return $this->data["outlet"];
    }
}