<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        
        // No explicit authorization needed here as we're getting the authenticated user
        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request)
    {
        $user = Auth::user();
        
        // Check authorization using policy
        $this->authorize('update', $user);
        
        // Filter out fields that shouldn't be updated
        $data = $request->except(['email', 'password', 'role']);
        
        // Update user profile
        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Check authorization using policy
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }
}