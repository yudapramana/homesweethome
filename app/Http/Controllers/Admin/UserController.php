<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->search_query;

        $user = auth()->user();


        $user_org_id = $user->org_id;

        if($user->role == 'ADMIN') {
            $users = User::query()
            ->when(request('search_query'), function ($q) use ($searchQuery) {
                return $q->where('name', 'like', "%{$searchQuery}%");
            })
            ->whereHas('org', function ($q) use ($user_org_id) {
                $q->where('id', $user_org_id);
            })
            ->with('org')
            ->paginate(setting('pagination_limit'));
        } else {
            $users = User::query()
            ->when(request('search_query'), function ($q) use ($searchQuery) {
                return $q->where('name', 'like', "%{$searchQuery}%");
            })
            
            ->with('kabkota')
            ->paginate(setting('pagination_limit'));
        }

        return $users;
    }

    public function store()
    {

        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8',
            'organization_id' => 'required',

        ]);

        return User::create([
            'name' => request('name'),
            'email' => request('email'),
            'username' => strstr(request('email'), '@', true),
            'organization_id' => request('organization_id'),
            'password' => bcrypt(request('password'))
        ]);

    }

    public function update(User $user)
    {

        $validated = request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8',
            'organization_id' => 'required',
        ]);

        $user->update([
            'name' => request('name'),
            'email' => request('email'),
            'organization_id' => request('organization_id'),
            'password' => request('password') ? bcrypt(request('password')) : $user->password,
        ]);

        return $user;
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }

    public function changeRole(User $user)
    {

        $user->update([
            'current_role_id' => request('current_role_id'),
        ]);

        return response()->json(['success' => true]);
    }

    public function bulkDelete()
    {
        User::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Users deleted successfully']);
    }

    public function fetch()
    {

        return auth()->user()->id;

    }

}
