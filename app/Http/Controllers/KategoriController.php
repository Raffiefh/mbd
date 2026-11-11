<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all();

        return response()->json([
            'success' => true,
            'data'    => $kategori
        ], 200);
    }
}