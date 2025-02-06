<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    /**
     * Display a listing of the schools (API).
     */
    public function indexApi(): JsonResponse
    {
        $schools = School::all();
        return response()->json([
            "code" => 200,
            "message" => "School retrieved successfully",
            "data" => $schools
        ], 200);
    }

    /**
     * Display a listing of the schools (Web).
     */
    public function index()
    {
        $schools = School::paginate(10);
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.school.index', compact('schools', 'pendingUsers'));
    }

    public function filter(Request $request)
    {
        // Retrieve filters from request
        $filters = $request->only(['name', 'address']);

        // Build query with optional filters
        $query = School::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['address'])) {
            $query->where('address', $filters['address']);
        }

        // Get users with pagination
        $schools = $query->paginate(10);
        $pendingUsers = User::where('status', 'Pending')->count();

        // Return view with schools and filters
        return view('admin.school.index', compact('schools', 'filters', 'pendingUsers'));
    }

    /**
     * Store a newly created school in storage (API).
     */
    public function storeApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $school = School::create($validated);

        return response()->json([
            'code' => 201,
            'message' => 'School created successfully.',
            'data' => $school
        ], 201);
    }

    /**
     * Show the form for creating a new school (Web).
     */
    public function create()
    {
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.school.create', compact('pendingUsers'));
    }

    /**
     * Store a newly created school in storage (Web).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        School::create($validated);

        return redirect()->route('schools.index')->with('success', 'School created successfully.');
    }

    /**
     * Display the specified school (API).
     */
    public function showApi(School $school): JsonResponse
    {
        return response()->json([
            "code" => 200,
            "message" => "School retrieved successfully",
            "data" => $school
        ], 200);
    }

    /**
     * Display the specified school (Web).
     */
    public function show(School $school)
    {
        return view('admin.schools.show', compact('school'));
    }

    /**
     * Update the specified school in storage (API).
     */
    public function updateApi(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $school->update($validated);

        return response()->json([
            'code' => 200,
            'message' => 'School updated successfully.',
            'data' => $school
        ], 200);
    }

    /**
     * Show the form for editing the specified school (Web).
     */
    public function edit(School $school)
    {
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.school.edit', compact('school', 'pendingUsers'));
    }

    /**
     * Update the specified school in storage (Web).
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $school->update($validated);

        return redirect()->route('schools.index')->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified school from storage (API).
     */
    public function destroyApi(School $school): JsonResponse
    {
        $school->delete();

        return response()->json([
            'code' => 200,
            'message' => 'School deleted successfully.'
        ], 200);
    }

    /**
     * Remove the specified school from storage (Web).
     */
    public function destroy(School $school)
    {
        $school->delete();

        return redirect()->route('schools.index')->with('success', 'School deleted successfully.');
    }
}
