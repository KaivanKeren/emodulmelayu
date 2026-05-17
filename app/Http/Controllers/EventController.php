<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
                'date' => 'required|date',
                'is_recurring' => 'boolean',   // ← tambahan
            ]);

            $event = Event::create($validated);

            return response()->json([
                'code' => 201,
                'message' => 'Event added successfully',
                'data' => $event,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan event',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_recurring' => 'boolean',   // ← tambahan
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = Event::findOrFail($id);
            $event->update($validator->validated());

            return response()->json([
                'code' => 200,
                'message' => 'Event updated successfully',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui event',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus event'
            ], 500);
        }
    }

    public function getTodayEvents()
    {
        try {
            $today = Carbon::now();
            $events = Event::whereDate('date', $today)->get();

            return response()->json([
                'code' => 200,
                'message' => 'Events retrieved successfully',
                'data' => $events,
                'date' => $today
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'date' => $today,
                'status' => 'error',
                'message' => 'Events not found'
            ], 404);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => 'Event retrieved successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }
    }
}
