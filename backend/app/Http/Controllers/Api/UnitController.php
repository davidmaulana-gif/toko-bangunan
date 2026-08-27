<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function createUnit(Request $request)
    {
        try {
            $request->validate([
                'unit' => 'required|string'
            ]);

            $data = DB::table('units')->insertGetId([
                'unit' => $request->unit,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (!$data) {
                return response()->json([
                    'message' => 'gagal menambahkan data.'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil di tambahkan.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'gagal menambahkan data',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function getUnit()
    {
        try {
            $data = DB::table('units')
                ->select('unit')
                ->whereNull('deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'data unit tidak ditemukan.'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil di lihat.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data gagal di temukan.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }

    public function searchUnit(Request $request)
    {
        try {
            $data = DB::table('units')
                ->select('unit')
                ->where('unit', 'ILIKE', '%' . $request->unit . '%')
                ->whereNull('deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'data unit tidak di temukan.'
                ]);
            }

            return response()->json([
                'message' => 'data unit di temukan.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorUnit) {
            return response()->json([
                'message' => 'data unit gagal di temukan.',
                'error' => $errorUnit->getMessage()
            ], 500);
        }
    }

    public function editUnit(Request $request, $id)
    {
        try {
            $request->validate([
                'unit' => 'sometimes|string'
            ]);

            $unit = DB::table('units')
                ->where('id', $id)
                ->first();

            if (!$unit) {
                return response()->json([
                    'message' => 'data unit tidak ditemukan.'
                ], 500);
            }

            $data = DB::table('units')
                ->where('id', $id)
                ->update([
                    'unit' => $request->unit,
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'data unit berhasil di ubah'
            ], 200);
        } catch (\Exception $unitError) {
            return response()->json([
                'status' => false,
                'message' => 'data unit tidak di temukan',
                'error' => $unitError->getMessage()
            ], 404);
        }
    }

    public function deleteUnit($id)
    {
        try {
            $unit = DB::table('units')
                ->where('id', $id)
                ->first();

            if (!$unit) {
                return response()->json([
                    'status' => false,
                    'message' => 'data unit tidak di temukan'
                ]);
            }
            $deleted = DB::table('units')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);
            return response()->json([
                'status' => true,
                'message' => 'data unit berhasil di hapus'
            ], 200);
        } catch (\Exception $unitError) {
            return response()->json([
                'status' => false,
                'message' => 'data unit gagal dihapus',
                'error' => $unitError->getMessage()
            ]);
        }
    }

}
