<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StreamInvitation;

class StreamParticipantController extends Controller
{
    /**
     * Invite a user to join a stream
     */
    public function invite(Request $request, Stream $stream): JsonResponse
    {
        // Check if current user is the host
        if ($stream->host_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the host can invite participants',
            ], 403);
        }

        $request->validate([
            'email' => 'required|email',
            'role' => 'nullable|in:guest,moderator',
        ]);

        $email = $request->input('email');
        $role = $request->input('role', 'guest');

        // Find or create user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found with this email address',
            ], 404);
        }

        // Check if user is already invited or participating
        $existingParticipant = StreamParticipant::where('stream_id', $stream->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingParticipant) {
            if ($existingParticipant->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already an active participant',
                ]);
            }

            if ($existingParticipant->status === 'invited') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already invited',
                ]);
            }

            // Reactivate declined invitation
            $existingParticipant->update([
                'status' => 'invited',
                'role' => $role,
            ]);
        } else {
            // Check participant limit
            $activeParticipants = $stream->participants()->where('status', 'active')->count();
            if ($activeParticipants >= $stream->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream is at maximum capacity',
                ], 400);
            }

            StreamParticipant::create([
                'stream_id' => $stream->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'invited',
            ]);
        }

        // Send invitation notification
        try {
            $user->notify(new StreamInvitation($stream, Auth::user()));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send stream invitation: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully',
        ]);
    }

    /**
     * Accept a stream invitation
     */
    public function accept(Stream $stream, StreamParticipant $participant): JsonResponse
    {
        // Check if the participant belongs to the current user
        if ($participant->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if invitation is still valid
        if ($participant->status !== 'invited') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invitation status',
            ], 400);
        }

        // Check participant limit
        $activeParticipants = $stream->participants()->where('status', 'active')->count();
        if ($activeParticipants >= $stream->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Stream is now full',
            ], 400);
        }

        $participant->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the stream',
        ]);
    }

    /**
     * Decline a stream invitation
     */
    public function decline(Stream $stream, StreamParticipant $participant): JsonResponse
    {
        // Check if the participant belongs to the current user
        if ($participant->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if invitation is still valid
        if ($participant->status !== 'invited') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invitation status',
            ], 400);
        }

        $participant->update([
            'status' => 'declined',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation declined',
        ]);
    }

    /**
     * Remove a participant from a stream
     */
    public function remove(Stream $stream, StreamParticipant $participant): JsonResponse
    {
        // Check if current user is the host
        if ($stream->host_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the host can remove participants',
            ], 403);
        }

        // Don't allow removing the host
        if ($participant->role === 'host') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the host',
            ], 400);
        }

        $participant->update([
            'status' => 'left',
            'left_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Participant removed successfully',
        ]);
    }

    /**
     * Get stream participants
     */
    public function index(Stream $stream): JsonResponse
    {
        // Check if current user is a participant in the stream
        $isParticipant = StreamParticipant::where('stream_id', $stream->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['active', 'invited'])
            ->exists();

        if (!$isParticipant && $stream->host_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $participants = $stream->participants()
            ->with('user:id,name,email')
            ->get();

        return response()->json([
            'success' => true,
            'participants' => $participants,
        ]);
    }

    /**
     * Update participant role
     */
    public function updateRole(Request $request, Stream $stream, StreamParticipant $participant): JsonResponse
    {
        // Check if current user is the host
        if ($stream->host_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the host can update participant roles',
            ], 403);
        }

        $request->validate([
            'role' => 'required|in:guest,moderator',
        ]);

        // Don't allow changing host role
        if ($participant->role === 'host') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change host role',
            ], 400);
        }

        $participant->update([
            'role' => $request->input('role'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Participant role updated successfully',
        ]);
    }
}
