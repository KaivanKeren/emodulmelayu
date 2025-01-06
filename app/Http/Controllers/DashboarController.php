<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Discussion;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;

class DashboarController extends Controller
{
    public function studentDashboard()
    {
        return view('student.dashboard');
    }

    public function teacherDashboard()
    {
        return view('teacher.dashboard');
    }


    public function adminDashboard()
    {
        $users = User::all();
        $total_users = User::count();

        $materials = Material::count();
        $assessments = Assessment::count();
        $discussions = Discussion::count();
        return view('admin.dashboard', compact('users', 'total_users', 'materials', 'assessments', 'discussions'));
    }
}
