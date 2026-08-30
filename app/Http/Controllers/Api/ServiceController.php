<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5',
            'price' => 'required|numeric|min:0',
        ]);

        $service = Service::create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json(null, 204);
    }
}