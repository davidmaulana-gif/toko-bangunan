<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RoleController extends Controller
{
    public function createRole(Request $request)
    {
        try {
            $request->validate([
                'role' => 'required|string|unique:roles,role'
            ]);

            $role = DB::table('roles')->insertGetId([
                'role' => $request->role,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json([
                'status' => true,
                'message' => 'data role berhasil di tambahkan',
                'data' => $role
            ], 200);
        } catch (\Exception $roleError) {
            return response()->json([
                'status' => false,
                'message' => 'data role gagal di tambahkan',
                'error' => $roleError->getMessage()
            ], 404);
        }
    }

    public function getRole()
    {
        try {
            $roleGet = DB::table('roles')
                ->select([
                    'role'
                ])
                ->whereNull('deleted_at')
                ->get();

            if ($roleGet->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'data user tidak di temukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'data user berhasil di lihat',
                'data' => $roleGet
            ], 200);
        } catch (\Exception $roleError) {
            return response()->json([
                'status' => false,
                'message' => 'data role tidak di temukan',
                'error' => $roleError->getMessage()
            ], 404);
        }
    }

    public function editRole(Request $request, $id)
    {
        try {
            $request->validate([
                'role' => 'required|string',
                $id
            ]);

            $role = DB::table('roles')->where('id', $id)->first();

            if (!$role) {
                return response()->json([
                    'status' => false,
                    'message' => 'data role tidak di temukan'
                ]);
            }

            DB::table('roles')
                ->where('id', $id)
                ->update([
                    'role' => $request->role,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => true,
                'message' => 'data role berhasil di ubah'
            ], 200);
        } catch (\Exception $roleError) {
            return response()->json([
                'status' => false,
                'message' => 'data role tidak di temukan',
                'error' => $roleError->getMessage()
            ], 404);
        }
    }

    public function searchRole(Request $request)
    {
        try {
            $data = DB::table('roles')
                ->select('role')
                ->where('role', 'ILIKE', '%' . $request->role . '%')
                ->whereNull('deleted_at')
                ->get();

            if ($data->isEmpty())
                return response()->json([
                    'status' => false,
                    'message' => 'data role tidak di temukan'
                ]);

            return response()->json([
                'status' => true,
                'message' => 'data role ditemukan',
                'data' => $data
            ]);
        } catch (\Exception $dataError) {
            return response()->json([
                'status' => false,
                'message' => 'data role tidak di temukan',
                'error' => $dataError->getMessage()
            ], 404);
        }
    }

    public function delete($id)
    {
        try {
            $role = DB::table('roles')
                ->where('id', $id)
                ->first();

            if (!$role) {
                return response()->json([
                    'status' => false,
                    'message' => 'data role tidak di temukan'
                ]);
            }
            $deleted = DB::table('roles')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'created_at' => now()
                ]);
            return response()->json([
                'status' => true,
                'message' => 'data role berhasil di hapus'
            ], 200);
        } catch (\Exception $roleError) {
            return response()->json([
                'status' => false,
                'message' => 'data role gagal dihapus',
                'error' => $roleError->getMessage()
            ]);
        }
    }
}
