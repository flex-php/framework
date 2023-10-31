<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

function request(): Request {
    global $kernel;

    if ($kernel instanceof HttpKernelInterface) {
        $requestStack = $kernel->getContainer()->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        if ($request) {
            return $request;
        }
    }

    throw new \RuntimeException("Request not found");
}

function session(): ?SessionInterface
{
    global $kernel;

    if ($kernel instanceof HttpKernelInterface) {
        $requestStack = $kernel->getContainer()->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        if ($request) {
            return $request->getSession();
        }
    }

    return null;
}

function redirect(string $url, int $status = 302): RedirectResponse
{
    return new RedirectResponse($url, $status);
}