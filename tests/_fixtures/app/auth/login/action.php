<?php

use Symfony\Component\HttpFoundation\Request;

return function(Request $request){
    $username = $request->get("username");
    $session = $request->getSession();

    if($username !== "admin"){
        return null;
    }

    $session->set("username", $username);

    return redirect("/admin");
};