<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * Upload a video file
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:1048576', // 1GB max
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('video');
        $title = $request->input('title');
        $description = $request->input('description');

        // Generate unique filename
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.' . $file->getClientOriginalExtension();

        // Store file
        $path = $file->storeAs('videos', $filename, 'public');

        // Get video metadata
        $metadata = $this->getVideoMetadata($file->getPathname());

        // Create video record
        $video = Video::create([
            'title' => $title,
            'description' => $description,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'video_path' => $path,
            'status' => 'processing',
            'metadata' => $metadata,
            'uploaded_by' => Auth::id(),
        ]);

        // Process video in background (generate thumbnail, etc.)
        $this->processVideo($video);

        return response()->json([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'video' => $video,
        ]);
    }

    /**
     * Get all videos for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $query = Video::where('uploaded_by', Auth::id());

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $videos = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'videos' => $videos,
        ]);
    }

    /**
     * Get video details
     */
    public function show(Video $video): JsonResponse
    {
        // Check if user owns the video or if it's used in a public stream
        if ($video->uploaded_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'video' => $video,
        ]);
    }

    /**
     * Delete a video
     */
    public function destroy(Video $video): JsonResponse
    {
        // Check if user owns the video
        if ($video->uploaded_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete files from storage
        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }

        if ($video->thumbnail_path && Storage::disk('public')->exists($video->thumbnail_path)) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }

        // Delete video record
        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Video deleted successfully',
        ]);
    }

    /**
     * Stream video content
     */
    public function stream(Video $video)
    {
        // Check if user owns the video or if it's used in a public stream
        if ($video->uploaded_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$video->isReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Video is not ready for streaming',
            ], 404);
        }

        $path = storage_path('app/public/' . $video->video_path);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Video file not found',
            ], 404);
        }

        return response()->file($path, [
            'Content-Type' => $video->mime_type,
            'Content-Disposition' => 'inline; filename="' . $video->original_filename . '"',
        ]);
    }

    /**
     * Get video metadata using ffprobe (if available)
     */
    private function getVideoMetadata(string $filePath): array
    {
        $metadata = [];

        // Try to get video duration and dimensions using ffprobe if available
        if (function_exists('exec')) {
            $ffprobe = '/usr/local/bin/ffprobe'; // Adjust path as needed

            if (file_exists($ffprobe)) {
                // Get duration
                $duration = exec("$ffprobe -v quiet -show_entries format=duration -of csv=p=0 \"$filePath\" 2>/dev/null");
                if ($duration && is_numeric($duration)) {
                    $metadata['duration'] = floatval($duration);
                }

                // Get video stream info
                $streamInfo = exec("$ffprobe -v quiet -select_streams v:0 -show_entries stream=width,height -of csv=p=0 \"$filePath\" 2>/dev/null");
                if ($streamInfo) {
                    $dimensions = explode(',', $streamInfo);
                    if (count($dimensions) >= 2) {
                        $metadata['width'] = intval($dimensions[0]);
                        $metadata['height'] = intval($dimensions[1]);
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Process video in background (generate thumbnail, etc.)
     */
    private function processVideo(Video $video): void
    {
        // This would typically be handled by a queued job
        // For now, we'll just mark it as ready after a delay
        // In a real application, you'd use Laravel queues for this

        // Simulate processing time
        sleep(2);

        // Generate thumbnail using ffmpeg (if available)
        $this->generateThumbnail($video);

        // Mark as ready
        $video->update(['status' => 'ready']);
    }

    /**
     * Generate thumbnail for video
     */
    private function generateThumbnail(Video $video): void
    {
        $videoPath = storage_path('app/public/' . $video->video_path);
        $thumbnailPath = 'thumbnails/' . pathinfo($video->filename, PATHINFO_FILENAME) . '.jpg';

        if (function_exists('exec')) {
            $ffmpeg = '/usr/local/bin/ffmpeg'; // Adjust path as needed

            if (file_exists($ffmpeg) && file_exists($videoPath)) {
                // Generate thumbnail at 1 second
                $command = "$ffmpeg -i \"$videoPath\" -ss 00:00:01.000 -vframes 1 -vf scale=320:240 " .
                          storage_path('app/public/' . $thumbnailPath) . " 2>/dev/null";

                exec($command);

                if (file_exists(storage_path('app/public/' . $thumbnailPath))) {
                    $video->update(['thumbnail_path' => $thumbnailPath]);
                }
            }
        }
    }
}
