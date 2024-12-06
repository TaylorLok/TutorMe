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
        $videoCalls = [];

        if ($user->user_type === UserTypes::TUTOR->value) {
            $videoCalls = $user->tutorVideoCalls()
                ->with('student')
                ->orderBy('scheduled_at')
                ->get();
        } 
        else {
            $videoCalls = $user->studentVideoCalls()
                ->with('tutor')
                ->orderBy('scheduled_at')
                ->get();
        }

        return Inertia::render('VideoCalls/Index', [
            'videoCalls' => $videoCalls
        ]);
    }

    public function create()
    {
        $tutors = User::where('user_type', UserTypes::TUTOR->value)->get();
        $students = User::where('user_type', UserTypes::STUDENT->value)->get();

        return Inertia::render('VideoCalls/Create', [
            'tutors' => $tutors,
            'students' => $students,
            'callStatuses' => CallStatuses::cases() 
        ]);
    }

    public function store(VideoCallRequest $request)
    {
        $videoCall = VideoCall::create([
            'tutor_id' => $request->validated('tutor_id'),
            'student_id' => $request->validated('student_id'),
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
            $channelName = $videoCall->agora_channel;
            $uid = auth()->id();
            $role = \App\Utils\RtcTokenBuilder::ROLE_PUBLISHER; 
            $expireTimeInSeconds = 3600;
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            $token = \App\Utils\RtcTokenBuilder::buildTokenWithUid(
                $appId,
                $appCertificate,
                $channelName,
                $uid,
                $role,
                $privilegeExpiredTs
            );

            return Inertia::render('VideoCalls/Show', [
                'videoCall' => $videoCall->load(['tutor', 'student']),
                'agoraToken' => $token,
                'agoraAppId' => $appId,
                'channel' => $channelName,
                'uid' => $uid,
                'callStatuses' => CallStatuses::cases()
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate video call token');
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
