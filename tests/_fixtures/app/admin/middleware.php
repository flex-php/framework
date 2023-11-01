<?php

$username = $session->get("username");

if ($username !== "admin") return redirect("/auth/login");

if ($request->getPathInfo() === "/admin") {
    return redirect("/admin/dashboard");
}