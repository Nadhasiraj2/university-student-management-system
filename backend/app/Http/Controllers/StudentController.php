<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'department'])->get();

        return response()->json($students);
    }

    public function show($id)
    {
        $student = Student::with(['user', 'department'])->find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        $validated = $request->validate([
            'department_id' => 'sometimes|required|exists:departments,id',
            'student_id' => 'sometimes|required|string|unique:students,student_id,' . $student->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'date_of_birth' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|string|max:20',
            'enrollment_year' => 'sometimes|required|integer',
        ]);

        $student->update($validated);

        return response()->json(
            $student->load(['user', 'department'])
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'department_id' => 'required|exists:departments,id',
            'student_id' => 'required|string|unique:students,student_id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'enrollment_year' => 'required|integer',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'department_id' => $validated['department_id'],
            'student_id' => $validated['student_id'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'enrollment_year' => $validated['enrollment_year'],
        ]);

        return response()->json(
            $student->load(['user', 'department']),
            201
        );
    }
}
