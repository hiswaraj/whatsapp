<?php

namespace App\Http\Controllers;

use App\Models\Flow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FlowController extends Controller
{
    /**
     * Display listing of flows.
     */
    public function index(): View
    {
        $userId = Auth::id();
        $flows = Flow::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.flows.index', compact('flows'));
    }

    /**
     * Show empty builder workspace to create a flow.
     */
    public function create(): View
    {
        return view('user.flows.builder');
    }

    /**
     * Store new flow.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_keywords' => 'required|array|min:1',
            'trigger_keywords.*' => 'required|string|max:50',
            'canvas_data' => 'required|string',
            'compiled_data' => 'required|array'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        Flow::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'trigger_keywords' => $validated['trigger_keywords'],
            'canvas_data' => $validated['canvas_data'],
            'compiled_data' => $validated['compiled_data'],
            'is_active' => true
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Chatbot Flow saved successfully!',
            'redirect_url' => route('flows.index')
        ]);
    }

    /**
     * Show editor workspace populated with current flow visual coordinates.
     */
    public function edit(int $id): View
    {
        $userId = Auth::id();
        $flow = Flow::where('user_id', $userId)->findOrFail($id);

        return view('user.flows.builder', compact('flow'));
    }

    /**
     * Update existing flow.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        $flow = Flow::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_keywords' => 'required|array|min:1',
            'trigger_keywords.*' => 'required|string|max:50',
            'canvas_data' => 'required|string',
            'compiled_data' => 'required|array'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        $flow->update([
            'name' => $validated['name'],
            'trigger_keywords' => $validated['trigger_keywords'],
            'canvas_data' => $validated['canvas_data'],
            'compiled_data' => $validated['compiled_data']
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Chatbot Flow updated successfully!',
            'redirect_url' => route('flows.index')
        ]);
    }

    /**
     * Remove flow.
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = Auth::id();
        $flow = Flow::where('user_id', $userId)->findOrFail($id);
        $flow->delete();

        return response()->json([
            'status' => true,
            'message' => 'Chatbot Flow deleted successfully.'
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        $flow = Flow::where('user_id', $userId)->findOrFail($id);

        $flow->update([
            'is_active' => !$flow->is_active
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Flow status toggled successfully.',
            'is_active' => $flow->is_active
        ]);
    }
}
