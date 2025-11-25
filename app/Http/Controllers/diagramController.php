<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class diagramController extends Controller
{
    public function index(){
        $data = array(
            "title" => "Diagram",
            "menuDiagram" => "Active",
            "menuDashboard" => "",
            'menuDokumen' => '',
        );
        return view('diagram',$data);
    }
}
