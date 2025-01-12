<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->only(['id', 'name', 'email', 'current_role_id', 'avatar', 'nama_pemeriksa', 'nip_pemeriksa', 'jabatan', 'org_name', 'org_id', 'print_layout', 'username', 'kabkota', 'kabkota_name']);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->user()->id)],
            'nama_pemeriksa' => ['sometimes'],
            'nip_pemeriksa' => ['sometimes'],
            'jabatan' => ['sometimes'],
            'print_layout' => ['sometimes'],
        ]);

        $request->user()->update($validated);

        return response()->json(['success' => true]);
    }

    public function uploadImage(Request $request)
    {

        if ($request->has('profile_picture')) {
            // $previousPath = $request->user()->getRawOriginal('avatar');

            // $link = Storage::put('/photos', $request->file('profile_picture'));

            // $request->user()->update(['avatar' => $link]);

            // Storage::delete($previousPath);

            $request->user()->update(['avatar' => $request->profile_picture]);

            return response()->json(['message' => 'Profile picture uploaded successfully!']);
        }
    }

    public function changePassword(Request $request, UpdateUserPassword $updater)
    {
        $updater->update(
            auth()->user(),
            [
                'current_password' => $request->currentPassword,
                'password' => $request->password,
                'password_confirmation' => $request->passwordConfirmation,
            ]
        );

        return response()->json(['message' => 'Password changed successfully!']);
    }
}