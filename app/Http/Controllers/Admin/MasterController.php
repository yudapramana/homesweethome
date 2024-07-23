<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\KabkotaResource;
use App\Http\Resources\OrgsResource;
use App\Models\Category;
use App\Models\Kabkota;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $type = request()->query('type');
        $id = request()->query('id');

        $data = [];

        switch ($type) {
            case 'orgs':
                $data = OrgsResource::collection(Organization::all());
                break;

            case 'users':
                $data = User::where('organization_id', $id)->get()->map(function ($item) {

                    return [
                        'id' => $item->id,
                        'text' => $item->name
                    ];
                });
                break;

            case 'kabkota':
                $data = KabkotaResource::collection(Kabkota::all());
                break;

            case 'category':
                $data = CategoryResource::collection(Category::all());
                break;

            default:
                # code...
                break;
        }

        return response()->json([
            'data' => $data
        ]);
    }

}
