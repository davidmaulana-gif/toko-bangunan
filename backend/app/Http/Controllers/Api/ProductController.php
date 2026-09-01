<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'max_stock' => 'required|integer',
                'total_unit' => 'required|integer'
            ]);

            $lastCode = DB::table('products')
                ->orderByDesc('code')
                ->value('code');

            $sequence = $lastCode
                ? ((int) substr($lastCode, 3)) + 1
                : 1;

            $code = 'BRG' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            $uuid=Str::uuid();

            $data = DB::table('products')->insertGetId([
                'uuid'=>$uuid,
                'code' => $code,
                'name' => $request->name,
                'max_stock' => $request->max_stock,
                'total_unit' => $request->total_unit,
                'unit_id' => $request->unit_id,
                'category_id' => $request->category_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (!$data) {
                return response()->json([
                    'message' => 'data gagal ditambahkan.'
                ]);
            }

            return response()->json([
                'message' => 'data berhasil ditambahkan.',
                'data' => [
                    'uuid'=>$uuid,
                    'code' => $code,
                    'name' => $request->name,
                    'max_stock' => $request->max_stock,
                    'unit_id' => $request->unit_id,
                    'total_unit' => $request->total_unit,
                    'selling_price' => null,
                    'category_id' => $request->category_id
                ]
            ], 200);
        } catch (\Exception $errorProduct) {
            return response()->json([
                'message' => 'data gagal di tambahkan.',
                'error' => $errorProduct->getMessage()
            ], 500);
        }
    }

    public function getProduct()
    {
        try {
            $data = DB::table('products')
                ->select(
                    'code',
                    'name',
                    'selling_price',
                    'max_stock',
                    'total_unit'
                )
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->whereNull('categories.deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'data gagal di temukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil dilihat.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorProducts) {
            return response()->json([
                'message' => 'data gagal di lihat.',
                'error' => $errorProducts->getMessage()
            ]);
        }
    }

    public function editProduct(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string',
                'max_stock' => 'sometimes|integer',
                'total_unit' => 'sometimes|integer'
            ]);

            $data = DB::table('products')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'tidak ada data products'
                ], 500);
            }

            $updateData = $request->only([
                'name',
                'max_stock',
                'total_unit'
            ]);

            $updateData['updated_at'] = now();

            $update = DB::table('products')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update($updateData);

            return response()->json([
                'message' => 'data berhasil di tambahkan.',
                'data' => $update
            ], 200);
        } catch (\Exception $errorProducts) {
            return response()->json([
                'message' => 'data gagal di lihat.',
                'error' => $errorProducts->getMessage()
            ], 500);
        }
    }

    public function editSellingPrice(Request $request, $id)
    {
        try {
            $request->validate([
                'selling_price' => 'required|integer'
            ]);

            $data = DB::table('products')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data selling price gagal di tambahkan.'
                ],500);
            }

            $edit = DB::table('products')
                ->select('selling_price')
                ->where('id', $id)
                ->update([
                    'selling_price' => $request->selling_price,
                    'updated_at' => now(),
                    'created_at' => now()
                ]);

            return response()->json([
                'message' => 'data selling price berhasil di tambahkan.',
                'data' => [
                    'id' => $id,
                    'selling_price' => $request->selling_price
                ]
            ],200);
        } catch (\Exception $errorSellingPrice) {
            return response()->json([
                'message' => 'data selling price gagal di tambahkan.',
                'error' => $errorSellingPrice->getMessage()
            ],500);
        }
    }

    public function searchProduct(Request $request)
    {
        try {
            $data = DB::table('products')
                ->select(
                    'code',
                    'name',
                    'selling_price',
                    'max_stock',
                    'total_unit'
                )
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->whereNull('categories.deleted_at')
                ->where('name', 'ILIKE', '%' . $request->name . '%')
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data products tidak di temukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data products berhasil ditemukan.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorSearch) {
            return response()->json([
                'message' => 'data gagal di temukan.',
                'error' => $errorSearch->getMessage()
            ], 500);
        }
    }

    public function deleteProduct($id)
    {
        try {
            $data = DB::table('products')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            if (!$data) {
                return response()->json([
                    'message' => 'data tidak di temukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil di hapus.',
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data product gagal di hapus',
                'error' => $errorData->getMessage()
            ]);
        }
    }
}
