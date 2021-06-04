<?php


namespace App\Services;


use Illuminate\Http\Request;

interface ImportUserService extends ImportService
{
    function importStaffs(Request $request);

    function importStudents(Request $request);
}
