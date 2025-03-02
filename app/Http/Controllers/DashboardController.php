<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Discussion;
use App\Models\Event;
use App\Models\Material;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $schools = School::all();
        $users = User::all();

        $total_users = User::count();
        $pendingUsers = User::where('status', 'Pending')->count();
        $materials = Material::count();
        $assessments = Assessment::count();
        $discussions = Discussion::count();
        return view('admin.dashboard', compact('users', 'total_users', 'materials', 'assessments', 'discussions', 'pendingUsers', 'schools'));
    }

    public function teacherDashboard(Request $request)
    {
        $sort = $request->input('sort', 'title'); // Changed default to title
        $direction = $request->input('direction', 'asc');

        // Validate sort parameter to prevent SQL injection
        $allowedSorts = ['title', 'category', 'created_at', 'status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'title'; // Changed default to title
        }

        // Validate direction parameter
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        
        $assessments = Assessment::orderBy($sort, $direction)
            ->paginate(10);

        return view('teacher.dashboard', compact('assessments'));
    }

    public function filter(Request $request)
    {
        $filters = $request->only(['name', 'role', 'school', 'status']);

        // Build query with optional filters
        $query = User::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['school'])) {
            $query->whereHas('school', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['school'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Get users with pagination
        $users = $query->paginate(10);
        $pendingUsers = User::where('status', 'Pending')->count();
        $schools = School::all();

        // Get total count of users
        $total_users = User::count();

        $materials = Material::count();
        $assessments = Assessment::count();
        $discussions = Discussion::count();

        // Return view with users and filters
        return view('admin.dashboard', compact('users', 'filters', 'total_users', 'materials', 'assessments', 'discussions', 'schools', 'pendingUsers'));
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('search');

            if (!$query) {
                return response()->json([
                    'users' => [],
                    'schools' => [],
                    'materials' => [],
                    'assessments' => [],
                    'discussions' => [],
                    'events' => [],
                ]);
            }

            $users = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            $schools = School::where('name', 'LIKE', "%{$query}%")
                ->orWhere('address', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            $materials = Material::where('title', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            $assessments = Assessment::where('title', 'LIKE', "%{$query}%")
                ->orWhere('category', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            $discussions = Discussion::where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            $events = Event::query()
                ->where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->orWhereDate('date', 'LIKE', "%{$query}%")
                ->orderBy('date')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'content' => $event->content,
                        'date' => $event->date,
                        'year' => $event->date->year,
                        'month' => $event->date->month
                    ];
                });

            return response()->json([
                'status' => 'success',
                'users' => $users,
                'schools' => $schools,
                'materials' => $materials,
                'assessments' => $assessments,
                'discussions' => $discussions,
                'events' => $events,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while searching'
            ], 500);
        }
    }
}
