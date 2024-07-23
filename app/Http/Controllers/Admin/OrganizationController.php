<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kabkota;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->search_query;
        $orgs = Kabkota::query()
                ->when(request('search_query'), function ($q) use ($searchQuery) {
                    return $q->where('name', 'like', "%{$searchQuery}%");
                })
                // ->latest()
                ->paginate(setting('pagination_limit'));

        return $orgs;
    }

    public function store()
    {

        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
        ]);

        return Kabkota::create([
            'name' => request('name'),
            'email' => request('email'),
        ]);

    }

    public function update(Organization $org)
    {

        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $org->id,
        ]);

        $org->update([
            'name' => request('name'),
            'email' => request('email'),
        ]);

        return $org;
    }

    public function destroy(Kabkota $org)
    {
        $org->delete();
        return response()->noContent();
    }

    public function bulkDelete()
    {
        Kabkota::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Kabkota deleted successfully']);
    }

    public function fetch(Request $request)
    {
        $orgs = Kabkota::all();
        return $orgs;
    }
}
