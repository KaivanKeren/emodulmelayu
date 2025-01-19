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
        $users = User::all();
        $total_users = User::count();

        $materials = Material::count();
        $assessments = Assessment::count();
        $discussions = Discussion::count();
        return view('admin.dashboard', compact('users', 'total_users', 'materials', 'assessments', 'discussions'));
    }

    public function filter(Request $request)
    {
        // Retrieve filters from request
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

        // Get total count of users
        $total_users = User::count();

        $materials = Material::count();
        $assessments = Assessment::count();
        $discussions = Discussion::count();

        // Get users with pagination
        $users = $query->paginate(10);

        // Return view with users and filters
        return view('admin.dashboard', compact('users', 'filters', 'total_users', 'materials', 'assessments', 'discussions'));
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
