<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContaAReceberController extends Controller
{
    public function index()
    {
        return view('contas_a_receber.index');
    }

    public function create()
{
    return view('contas_a_receber.create');
}
}