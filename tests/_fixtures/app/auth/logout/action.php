<?php

use Symfony\Component\HttpFoundation\Request;

return function(Request $request){
    $session = $request->getSession();
    $session->clear();

    return redirect("/auth/login");
};