<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function createCategories(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required|string'
            ]);

            $uuid=Str::uuid();

            $data = DB::table('categories')->insertGetId([
                'category' => $request->category,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (!$data) {
                return response()->json([
                    'message' => 'data gagal di tambahkan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil di tambahkan.',
                'data' => $data
            ]);
        } catch (\Exception $errorCategory) {
            return response()->json([
                'message' => 'data gagal di tambahkan',
                'error' => $errorCategory->getMessage()
            ], 500);
        }
    }

    public function getCategory()
    {
        try {
            $data = DB::table('categories')
                ->select('category','id')
                ->whereNull('deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'data tidak di temukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil di temukan.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorGet) {
            return response()->json([
                'message' => 'data gagal di temukan.',
                'error' => $errorGet->getMessage()
            ], 500);
        }
    }

    public function searchCategory(Request $request)
    {
        try {
            $data = DB::table('categories')
                ->select('category')
                ->where('category', 'ILIKE', '%' . $request->category . '%')
                ->whereNull('deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'data tidak di temukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data category berhasil di lihar.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorCategorie) {
            return response()->json([
                'message' => 'data gagal di temukan.',
                'error' => $errorCategorie->getMessage()
            ]);
        }
    }

    public function editCategory(Request $request, $id)
    {
        try {
            $request->validate([
                'category' => 'sometimes|string'
            ]);

            $categorie = DB::table('categories')
                ->where('id', $id)
                ->first();

            if (!$categorie) {
                return response()->json([
                    'message' => 'data gagal di edit.'
                ]);
            }

            $data = DB::table('categories')
                ->where('id', $id)
                ->update([
                    'category' => $request->category,
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'data berhasil di edit.',
            ]);
        } catch (\Exception $errorEdit) {
            return response()->json([
                'message' => 'data category gagal di edit.',
                'error' => $errorEdit->getMessage()
            ]);
        }
    }

    public function deleteCategory(Request $request, $id)
    {
        try {
            $data = DB::table('categories')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data gagal di hapus.'
                ]);
            }

            $delete = DB::table('categories')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);


            return response()->json([
                'message' => 'data category berhasil di hapus.'
            ], 200);
        } catch (\Exception $errorDelete) {
            return response()->json([
                'message' => 'data tidak di temukan.',
                'error' => $errorDelete->getMessage()
            ], 500);
        }
    }
}
