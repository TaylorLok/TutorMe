<?php

namespace App\Http\Controllers;

use App\Models\VideoCall;
use App\Models\User;
use App\Enums\CallStatuses;
use App\Enums\UserTypes;
use App\Utils\RtcTokenBuilder;
use Illuminate\Http\Request;
use App\Http\Requests\VideoCallRequest;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class VideoCallController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $videoCalls = VideoCall::where('caller_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['caller', 'receiver'])
            ->orderBy('scheduled_at')
            ->get();

        return Inertia::render('VideoCalls/Index', [
            'videoCalls' => $videoCalls
        ]);
    }

    public function create()
    {
        // Get all users except current user
        $users = User::where('id', '!=', auth()->id())
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'user_type' => $user->user_type->value, 
                    'label' => "{$user->name} (" . $user->user_type->value . ")"
            ];
        });

        return Inertia::render('VideoCalls/Create', [
            'users' => $users,
            'callStatuses' => CallStatuses::cases()
        ]);
    }

    public function store(VideoCallRequest $request)
    {
        $videoCall = VideoCall::create([
            'caller_id' => auth()->id(),
            'receiver_id' => $request->validated('receiver_id'),
            'scheduled_at' => $request->validated('scheduled_at'),
            'agora_channel' => Str::random(16),
            'call_status' => CallStatuses::SCHEDULED->value
        ]);

        return redirect()->route('video-calls.show', $videoCall->id);
    }

    public function show(VideoCall $videoCall)
    {
        try {
            $appId = config('services.agora.app_id');
            $appCertificate = config('services.agora.app_certificate');
            
            // Debug logging
            \Log::info('Agora credentials check', [
                'appId' => $appId ? 'set' : 'missing',
                'certificate' => $appCertificate ? 'set' : 'missing',
                'config_path' => config_path('services.php')
            ]);

            if (!$appId || !$appCertificate) {
                throw new \Exception('Agora credentials not properly configured');
            }

            $channelName = $videoCall->agora_channel;
            $uid = auth()->id();
            $role = RtcTokenBuilder::ROLE_PUBLISHER;
            $expireTimeInSeconds = 3600;
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            // Debug token generation
            \Log::info('Generating Agora token', [
                'channel' => $channelName,
                'uid' => $uid,
                'role' => $role,
                'expires' => $privilegeExpiredTs
            ]);

            $token = RtcTokenBuilder::buildTokenWithUid(
                $appId,
                $appCertificate,
                $channelName,
                $uid,
                $role,
                $privilegeExpiredTs
            );

            \Log::info('Token generated successfully', [
                'token_length' => strlen($token)
            ]);

            return Inertia::render('VideoCalls/Show', [
                'videoCall' => $videoCall->load(['caller', 'receiver']),
                'agoraToken' => $token,
                'agoraAppId' => $appId,
                'channel' => $channelName,
                'uid' => $uid,
                'callStatuses' => CallStatuses::cases(),
                'debug' => [
                    'hasAppId' => !empty($appId),
                    'hasToken' => !empty($token),
                    'channel' => $channelName
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Video call error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate video call token: ' . $e->getMessage());
        }
    }

    public function updateStatus(VideoCall $videoCall, Request $request)
    {
        $validated = $request->validate([
           'status' => 'required|in:' . implode(',', CallStatuses::getValues()),
        ]);

        $videoCall->update([
            'call_status' => CallStatuses::from($validated['status'])->value,
        ]);

        return back();
    }
}
