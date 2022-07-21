<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\PasswordChangeRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $auth = Auth::attempt(
            $request->only([
                    'email',
                    'password',
                ])
        );
        if (! $auth) {
            return response([
                'message' => 'Invalid credentials!',
                'success' => 0,
            ]);
        }
        
        $accessToken = Auth::user()->createToken('authToken')->accessToken;

        return  response([
            'user' => Auth::user(),
            'access_token' => $accessToken,
            'success' => 1,
        ]);
    }



    public function profile(Request $request)
    {
        $user = Auth::user();
        $roles = $user->getRoleNames();
        $permission = $user->getAllPermissions();

        return response([
            'user' => $user,
            'success' => 1,
        ]);
    }


    public function changePassword(PasswordChangeRequest $request)
    {
        // match old password
        if (Hash::check($request->old_password, Auth::user()->password)) {
            User::find(auth()->user()->id)
            ->update([
                'password' => Hash::make($request->password),
            ]);

            return response([
                'message' => 'Password has been changed',
                'status' => 1,
            ]);
        }

        return response([
            'message' => 'Password not matched!',
            'status' => 0,
        ]);
    }


    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = Auth::user();
        // check unique email except this user
        if (isset($request->email)) {
            $check = User::where('email', $request->email)
                     ->where('id', '!=', $user->id)
                     ->first();

            if ($check) {
                return response([
                    'message' => 'The email address is already used!',
                    'success' => 0,
                ]);
            }
        }

        $user->update(
            $request->only([
                'name',
                'email',
            ])
        );

        
        return response([
            'message' => 'Profile updated successfully!',
            'status' => 1,
        ]);
    }


    public function logout(Request $request)
    {
        $user = Auth::user()->token();
        $user->revoke();

        return response([
            'message' => 'Logged out succesfully!',
            'status' => 0,
        ]);
    }
}
