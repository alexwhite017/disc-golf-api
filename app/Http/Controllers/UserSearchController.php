<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'required|string|min:2',
        ]);

        $users = User::query()
            ->where('name', 'like', '%' . $request->search . '%')
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json(['data' => $users]);
    }
}
