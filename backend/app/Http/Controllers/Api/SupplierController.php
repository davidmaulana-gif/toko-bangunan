<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function createSupplier(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'nib' => 'required|string|unique:suppliers,nib',
                'address' => 'required|string',
                'phone_number' => 'required|string|unique:suppliers,phone_number',
                'email' => 'required|email|unique:suppliers,email'
            ]);

            $uuid=Str::uuid();

            $data = DB::table('suppliers')->insertGetId([
                'name' => $request->name,
                'nib' => $request->nib,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'data berhasil di tambahkan.'
            ], 200);
        } catch (\Exception $errorSupplier) {
            return response()->json([
                'message' => 'data gagal di tambahkan.',
                'error' => $errorSupplier->getMessage()
            ]);
        }
    }

    public function getSupplier()
    {
        try {
            $dataSupplier = DB::table('suppliers')
                ->select(
                    'nib',
                    'name',
                    'address',
                    'phone_number',
                    'email'
                )
                ->get();

            if ($dataSupplier->isEmpty()) {
                return response()->json([
                    'message' => 'data tidak di temukan'
                ]);
            }

            return response()->json([
                'message' => 'data supplier di temukan.',
                'data' => $dataSupplier
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data gagal di lihat.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function searchSupplier(Request $request)
    {
        try {
            $data = DB::table('suppliers')
                ->select(
                    'nib',
                    'name',
                    'address',
                    'phone_number',
                    'email'
                )
                ->where('name', 'ILIKE', '%' . $request->name . '%')
                ->whereNull('deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'tidak ada data supplier.'
                ]);
            }

            return response()->json([
                'message' => 'data supplier berhasil telah di temukan',
                'data' => $data
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data gagal di temukan.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function editSupplier(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string',
                'address' => 'sometimes|string',
                'phone_number' => 'sometimes|string|unique:suppliers,phone_number,' . $id,
            ]);

            $data = DB::table('suppliers')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'Data supplier tidak ditemukan.'
                ], 404);
            }

            $updateData = $request->only([
                'name',
                'address',
                'phone_number'
            ]);

            $updateData['updated_at'] = now();

            $insert=DB::table('suppliers')
                ->select(
                    'name',
                    'address',
                    'phone_number',
                    'email'
                )
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update($updateData);

            $data = DB::table('suppliers')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            return response()->json([
                'message' => 'Data supplier berhasil di edit.',
                'data' => $insert
            ], 200);
        } catch (\Exception $errorData) {

            return response()->json([
                'message' => 'Data supplier gagal di edit.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function deleteSupplier($id)
    {
        try {
            $data = DB::table('suppliers')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data gagal di hapus.'
                ]);
            }

            $deleted = DB::table('suppliers')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'created_at' => now()
                ]);

            return response()->json([
                'message' => 'data supplier berhasil di hapus.'
            ]);
        } catch (\Exception $errorSupplier) {
            return response()->json([
                'message' => 'data tidak di temukan.',
                'error' => $errorSupplier->getMessage()
            ], 500);
        }
    }

}
