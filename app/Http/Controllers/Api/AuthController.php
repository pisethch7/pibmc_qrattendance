<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (teacher or student).
     * Students: require username + sex, no email needed.
     * Teachers: require username, email optional.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['required', 'string', 'max:60', 'unique:users,username', 'regex:/^[a-zA-Z0-9_.]+$/'],
            'password'    => ['required', 'string', 'min:6'],
            'role'        => ['required', 'in:teacher,student'],
            'sex'         => ['required_if:role,student', 'nullable', 'in:male,female'],
            'email'       => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'device_id'   => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $deviceId   = $validated['device_id']   ?? $request->header('X-Device-Id');
        $deviceName = $validated['device_name'] ?? $request->header('X-Device-Name');

        $userData = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'email'    => $validated['email'] ?? null,
        ];

        if ($validated['role'] === 'student') {
            $userData['sex'] = $validated['sex'];

            if ($deviceId) {
                $userData['device_id']            = $deviceId;
                $userData['device_name']          = $deviceName ?: 'Browser Device';
                $userData['device_registered_at'] = now();
            }
        }

        $user  = User::create($userData);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    /**
     * Authenticate user via username + password and issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username'    => ['required', 'string'],
            'password'    => ['required', 'string'],
            'device_id'   => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $deviceId   = $validated['device_id']   ?? $request->header('X-Device-Id');
        $deviceName = $validated['device_name'] ?? $request->header('X-Device-Name');

        $user = User::where('username', $validated['username'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Student Device Lock Policy (Strict: 1 Account, 1 Device)
        if ($user->isStudent()) {
            $activeDeviceId   = $deviceId   ?: 'dev_' . md5(($request->userAgent() ?? '') . $user->id);
            $activeDeviceName = $deviceName ?: ($request->userAgent() ? substr($request->userAgent(), 0, 60) : 'Registered Browser Device');

            if (empty($user->device_id)) {
                // First login: bind this device to the student's account
                $user->bindDevice($activeDeviceId, $activeDeviceName);
            } else {
                // Subsequent logins: verify device ID match
                if ($user->device_id !== $activeDeviceId) {
                    $deviceHint = $user->device_name ? " ('{$user->device_name}')" : '';
                    throw ValidationException::withMessages([
                        'device' => [
                            "This student account is registered to another device{$deviceHint}. Under institute policy, each student account is locked to one device only. Please contact your instructor to reset your device lock."
                        ],
                    ]);
                }
            }

            // Revoke any previous tokens to enforce a single active session on the registered device
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Log out and revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
