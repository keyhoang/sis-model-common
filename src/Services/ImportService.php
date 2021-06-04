<?php


namespace App\Services;


use Illuminate\Http\Request;

interface ImportService
{
    function import(Request $request);
}
