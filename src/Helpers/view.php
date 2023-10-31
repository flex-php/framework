<?php

use Flex\View\ViewRenderService;

function outlet():void {
    global $viewRenderService;

    if ($viewRenderService instanceof ViewRenderService) {
        $viewRenderService->outlet();
    }
}