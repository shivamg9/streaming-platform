<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StreamController extends Controller
{
    /**
     * Get user's streams
     */
    public function index(): JsonResponse
    {
        $streams = Stream::where('host_id', Auth::id())
            ->with(['participants.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'streams' => $streams,
        ]);
    }

    /**
     * Create a new stream
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:live,prerecorded',
            'scheduled_start_time' => 'nullable|date',
            'max_participants' => 'nullable|integer|min:1|max:50',
            'is_public' => 'nullable|boolean',
        ]);

        $stream = Stream::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'stream_key' => Str::uuid()->toString(),
            'scheduled_start_time' => $request->scheduled_start_time,
            'max_participants' => $request->max_participants ?? 10,
            'is_public' => $request->is_public ?? true,
            'host_id' => Auth::id(),
            'status' => $request->scheduled_start_time ? 'scheduled' : 'active',
        ]);

        // Add host as participant
        StreamParticipant::create([
            'stream_id' => $stream->id,
            'user_id' => Auth::id(),
            'role' => 'host',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'stream' => $stream,
            'stream_key' => $stream->stream_key,
        ]);
    }

    /**
     * Get stream details
     */
    public function show(Stream $stream): JsonResponse
    {
        $stream->load(['host', 'participants.user', 'streamVideos.video']);

        return response()->json([
            'success' => true,
            'stream' => $stream,
        ]);
    }

    /**
     * Start a stream
     */
    public function start(Stream $stream): JsonResponse
    {
        if ($stream->host_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the host can start the stream',
            ], 403);
        }

        $stream->update([
            'status' => 'active',
            'actual_start_time' => now(),
        ]);

        // Broadcast stream started event
        broadcast(new StreamStarted($stream));

        return response()->json([
            'success' => true,
            'message' => 'Stream started successfully',
            'stream' => $stream,
        ]);
    }

    /**
     * Stop a stream
     */
    public function stop(Stream $stream): JsonResponse
    {
        if ($stream->host_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the host can stop the stream',
            ], 403);
        }

        $stream->update([
            'status' => 'ended',
            'end_time' => now(),
        ]);

        // Mark all participants as left
        $stream->participants()->update([
            'status' => 'left',
            'left_at' => now(),
        ]);

        // Broadcast stream ended event
        broadcast(new StreamEnded($stream));

        return response()->json([
            'success' => true,
            'message' => 'Stream stopped successfully',
        ]);
    }

    /**
     * Join a stream
     */
    public function join(Stream $stream): JsonResponse
    {
        // Check if user is already a participant
        $existingParticipant = StreamParticipant::where('stream_id', $stream->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingParticipant) {
            if ($existingParticipant->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Already joined the stream',
                ]);
            }

            // Reactivate participant
            $existingParticipant->update([
                'status' => 'active',
                'joined_at' => now(),
                'left_at' => null,
            ]);
        } else {
            // Check participant limit
            $activeParticipants = $stream->participants()->where('status', 'active')->count();
            if ($activeParticipants >= $stream->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream is full',
                ], 400);
            }

            StreamParticipant::create([
                'stream_id' => $stream->id,
                'user_id' => Auth::id(),
                'role' => 'guest',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        // Broadcast participant joined event
        broadcast(new ParticipantJoined($stream, Auth::user()));

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the stream',
        ]);
    }

    /**
     * Leave a stream
     */
    public function leave(Stream $stream): JsonResponse
    {
        $participant = StreamParticipant::where('stream_id', $stream->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($participant) {
            $participant->update([
                'status' => 'left',
                'left_at' => now(),
            ]);

            // Broadcast participant left event
            broadcast(new ParticipantLeft($stream, Auth::user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully left the stream',
        ]);
    }

    /**
     * Get WebRTC configuration for streaming
     */
    public function getWebRTCConfig(Stream $stream): JsonResponse
    {
        // Check if user is participant in the stream
        $participant = StreamParticipant::where('stream_id', $stream->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized to access stream configuration',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'config' => [
                'iceServers' => [
                    ['urls' => 'stun:stun.l.google.com:19302'],
                    ['urls' => 'stun:stun1.l.google.com:19302'],
                ],
                'stream_key' => $stream->stream_key,
                'rtmp_url' => $stream->rtmp_url,
                'hls_url' => $stream->hls_url,
            ],
        ]);
    }

    /**
     * Show stream view
     */
    public function showView(Stream $stream)
    {
        // Check if user is participant in the stream
        $participant = StreamParticipant::where('stream_id', $stream->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['active', 'invited'])
            ->first();

        if (!$participant && $stream->host_id !== Auth::id()) {
            abort(403, 'Unauthorized access to stream');
        }

        return view('stream', compact('stream'));
    }
}
