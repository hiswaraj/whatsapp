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
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $wabas = \App\Models\WhatsappAccount::where('user_id', $userId)->get();

        $query = Flow::where('user_id', $userId)->with('whatsappAccount');
        if ($request->filled('waba_id')) {
            $query->where('whatsapp_account_id', $request->query('waba_id'));
        }

        $flows = $query->orderBy('created_at', 'desc')->get();

        return view('user.flows.index', compact('flows', 'wabas'));
    }

    /**
     * Show empty builder workspace to create a flow.
     */
    public function create(): View
    {
        $userId = Auth::id();
        $wabas = \App\Models\WhatsappAccount::where('user_id', $userId)->where('status', true)->get();
        return view('user.flows.builder', compact('wabas'));
    }

    /**
     * Store new flow.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validation = Validator::make($request->all(), [
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId,
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
            'whatsapp_account_id' => $validated['whatsapp_account_id'],
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
        $wabas = \App\Models\WhatsappAccount::where('user_id', $userId)->where('status', true)->get();

        return view('user.flows.builder', compact('flow', 'wabas'));
    }

    /**
     * Update existing flow.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        $flow = Flow::where('user_id', $userId)->findOrFail($id);

        $validation = Validator::make($request->all(), [
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id,user_id,' . $userId,
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
            'whatsapp_account_id' => $validated['whatsapp_account_id'],
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
