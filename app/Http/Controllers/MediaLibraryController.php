<?php

namespace App\Http\Controllers;

use App\Models\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    /**
     * Display a listing of the media assets.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $query = MediaLibrary::where('user_id', $userId);

        // Filter by file type if provided
        if ($request->has('type') && in_array($request->input('type'), ['image', 'video', 'audio', 'document'])) {
            $query->where('file_type', $request->input('type'));
        }

        // Filter by search query if provided
        if ($request->has('search') && !empty($request->input('search'))) {
            $query->where('filename', 'like', '%' . $request->input('search') . '%');
        }

        $media = $query->latest()->paginate(12);

        return view('user.media.index', compact('media'));
    }

    /**
     * Store a newly uploaded media asset.
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:16384|mimes:jpeg,jpg,png,gif,webp,mp4,mp3,wav,ogg,pdf,doc,docx,xls,xlsx,txt'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->with('error', $validator->errors()->first());
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        
        // Map mime-type to simple resource types
        $mime = $file->getMimeType();
        $fileType = 'document';
        if (str_starts_with($mime, 'image/')) {
            $fileType = 'image';
        } elseif (str_starts_with($mime, 'video/')) {
            $fileType = 'video';
        } elseif (str_starts_with($mime, 'audio/')) {
            $fileType = 'audio';
        }

        // Clean filename and make it unique
        $cleanName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', $originalName);
        
        // Define public storage directory path
        $destinationPath = public_path('uploads/media');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // Move the file
        $file->move($destinationPath, $cleanName);
        $filePath = 'uploads/media/' . $cleanName;

        // Save entry to the media_library table
        $media = MediaLibrary::create([
            'user_id' => $userId,
            'filename' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Asset uploaded successfully!',
                'media' => $media
            ]);
        }

        return back()->with('success', 'Asset uploaded successfully!');
    }

    /**
     * Remove the specified media asset from disk and database.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $media = MediaLibrary::where('user_id', $userId)->findOrFail($id);

        // Delete physical file asset from public directory
        $physicalPath = public_path($media->file_path);
        if (File::exists($physicalPath)) {
            File::delete($physicalPath);
        }

        $media->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Asset deleted successfully!'
            ]);
        }

        return back()->with('success', 'Asset deleted successfully!');
    }

    /**
     * API Endpoint for Live Chat media picker.
     */
    public function picker(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $query = MediaLibrary::where('user_id', $userId);

        if ($request->has('type') && in_array($request->input('type'), ['image', 'video', 'audio', 'document'])) {
            $query->where('file_type', $request->input('type'));
        }

        if ($request->has('search') && !empty($request->input('search'))) {
            $query->where('filename', 'like', '%' . $request->input('search') . '%');
        }

        $items = $query->latest()->get();

        // Map item array to include formatted sizing and absolute public asset URLs
        $formattedItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'filename' => $item->filename,
                'file_path' => $item->file_path,
                'file_url' => asset($item->file_path),
                'file_type' => $item->file_type,
                'file_size' => $item->file_size,
                'formatted_size' => $this->formatBytes($item->file_size),
                'created_at' => $item->created_at->format('M d, Y')
            ];
        });

        return response()->json([
            'status' => true,
            'media' => $formattedItems
        ]);
    }

    /**
     * Format bytes to readable string representation.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
