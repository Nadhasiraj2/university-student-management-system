<?php

namespace App\Http\Controllers;

use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'department'])->get();

        return response()->json($students);
    }
}
