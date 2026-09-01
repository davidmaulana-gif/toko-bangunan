<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function createSales(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'nik' => 'required|string|unique:sales,nik',
                'phone_number' => 'required|string|unique:sales,phone_number',
                'email' => 'required|string|unique:sales,email',
                'picture' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            $picture = $request->file('picture')->store('/sales', 'public');

            $uuid=Str::uuid();

            $insert = DB::table('sales')->insertGetId([
                'name' => $request->name,
                'nik' => $request->nik,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'picture' => $picture,
                'supplier_id' => $request->supplier_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $data = DB::table('sales')
                ->where('id', $insert)
                ->first();

            return response()->json([
                'message' => 'berhasil menambahkan data sales.',
                'data' => [
                    'name' => $data->name,
                    'nik' => $data->nik,
                    'phone_number' => $data->phone_number,
                    'email' => $data->email,
                    'picture' => asset('storage/' . $data->picture),
                    'supplier_id' => $data->supplier_id
                ]
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data gagal di tambahkan.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function getSales()
    {
        try {
            $data = DB::table('sales')
                ->select(
                    'name',
                    'nik',
                    'phone_number',
                    'email',
                    'picture',
                    'supplier_id'
                )
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'data tidak di temukan.'
                ]);
            }

            return response()->json([
                'message' > 'data sales berhasil di lihat.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorSales) {
            return response()->json([
                'message' => 'data sales gagal di temukan.',
                'error' => $errorSales->getMessage()
            ], 500);
        }
    }

    public function searchSales(Request $request)
    {
        try {
            $insert = DB::table('sales')
                ->select(
                    'name',
                    'nik',
                    'phone_number',
                    'email',
                    'picture',
                    'supplier_id'
                )
                ->where('name', 'ILIKE', '%' . $request->name . '%')
                ->whereNull('deleted_at')
                ->get();

            if ($insert->isEmpty()) {
                return response()->json([
                    'message' => 'data sales tidak di temukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data sales ditemukan.',
                'data' => $insert
            ], 200);
        } catch (\Exception $errorSales) {
            return response()->json([
                'message' => 'data gagal di temukan.',
                'error' => $errorSales->getMessage()
            ], 500);
        }
    }

    public function editSales(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string',
                'phone_number' => 'sometimes|string|unique:sales,phone_number',
                'picture' => 'sometimes|string|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            $data = DB::table('sales')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data sales gagal di edit.'
                ]);
            }

            $updated = $request->only([
                'name',
                'phone_number',
                'picture'
            ]);

            if ($request->hasFile('picture')) {

                $picture = $request->file('picture')->store('/sales', 'public');
                $updateData['picture'] = $picture;
            }

            $updated['updated_at'] = now();

            DB::table('sales')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update($updated);

            $data = DB::table('sales')
                ->select(
                    'nik',
                    'name',
                    'phone_number',
                    'email',
                    'picture',
                    'supplier_id'
                )
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            return response()->json([
                'message' => 'data sales berhasil di edit.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data tidak di temukan.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function deleteSales($id)
    {
        try {
            $data = DB::table('sales')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data sales tidak di temukan.'
                ], 500);
            }

            $updated = DB::table('sales')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'message' => 'data sales berhasil di hapus',
            ], 200);
        } catch (\Exception $errorSales) {
            return response()->json([
                'message' => 'data sales gagal di temukan.',
                'error' => $errorSales->getMessage()
            ], 500);
        }
    }
}
