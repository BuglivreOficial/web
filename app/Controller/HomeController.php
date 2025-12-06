<?php
namespace App\Controller;

use Core\Http\Response;

class HomeController
{
    public function index()
    {
        Response::render("app/home/index.html", [
            'title' => 'Home Page',
            'content' => 'Welcome to the Home Page!'
        ]);
    }
}