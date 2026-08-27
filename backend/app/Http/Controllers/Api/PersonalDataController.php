<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class PersonalDataController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:personal_datas,name',
                'address' => 'required|string',
                'picture' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'phone_number' => 'required|string|unique:personal_datas,phone_number',
                'gender' => 'required|string',
            ]);

            $picture = $request->file('picture')->store('/personal-data', 'public');

            $createdAt = now();

            $lastNip = DB::table('personal_datas')
                ->whereYear('created_at', $createdAt->year)
                ->orderByDesc('nip')
                ->value('nip');

            $sequence = $lastNip
                ? ((int) substr($lastNip, 4)) + 1
                : 1;

            $nip = $createdAt->format('Y') . str_pad($sequence, 6, '0', STR_PAD_LEFT);

            $insert = DB::table('personal_datas')->insertGetId([
                'name' => $request->name,
                'address' => $request->address,
                'picture' => $picture,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'nip' => $nip,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $data = DB::table('personal_datas')
                ->where('id', $insert)
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'personal data berhasil di tambahkan.',
                'data' => [
                    'name' => $data->name,
                    'address' => $data->address,
                    'picture' => asset('storage/' . $data->picture),
                    'phone_number' => $data->phone_number,
                    'gender' => $data->gender,
                    'nip' => $data->nip,
                    'user_id' => $data->user_id
                ]
            ], 200);
        } catch (\Exception $dataError) {
            return response()->json([
                'status' => false,
                'message' => 'data tidak di temukan',
                'error' => $dataError->getMessage()
            ], 404);
        }
    }

    public function getPersonalData()
    {
        try {
            $userId = Auth::id();

            $data = DB::table('personal_datas')
                ->select(
                    'name',
                    'address',
                    'picture',
                    'phone_number',
                    'gender',
                    'nip'
                )
                ->whereNull('deleted_at')
                ->where('user_id', $userId)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'data' => $data
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'status' => false,
                'message' => 'data tidak di temukan',
                'error' => $errorData->getMessage()
            ], 404);
        }
    }

    public function editPersonalData(Request $request)
    {
        try {

            // dd($request->all());
            $request->validate([
                'name' => 'sometimes|string',
                'address' => 'sometimes|string',
                'picture' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
                'gender' => 'sometimes|string',
                'phone_number' => 'sometimes|string|unique:personal_datas,phone_number',
            ]);

            $id = Auth::id();

            $data = DB::table('personal_datas')
                ->where('user_id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            $updateData = $request->only([
                'name',
                'address',
                'phone_number',
                'gender'
            ]);

            if ($request->hasFile('picture')) {

                $picture = $request->file('picture')->store('/personal-data', 'public');
                $updateData['picture'] = $picture;
            }

            $updateData['updated_at'] = now();

            $updated = DB::table('personal_datas')
                ->where('user_id', $id)
                ->whereNull('deleted_at')
                ->update($updateData);

            $data = DB::table('personal_datas')
                ->select(
                    'name',
                    'address',
                    'picture',
                    'phone_number',
                    'gender',
                    'nik',
                    'user_id'
                )
                ->where('user_id', $id)
                ->whereNull('deleted_at')
                ->first();

            return response()->json([
                'message' => 'Data berhasil diubah.',
                'update' => $data
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diubah.',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function searchPersonalData(Request $request)
    {
        try {
            $data = DB::table('personal_datas')
                ->where('name', 'ILIKE', '%' . $request->search . '%')
                ->where('deleted_at')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'gagal menemukan data.'
                ], 404);
            }

            return response()->json([
                'message' => ' data telah di temukan.',
                'data' => $data
            ], 200);
        } catch (\Exception $errorData) {
            return response()->json([
                'message' => 'data tidak di temukan.',
                'error' => $errorData->getMessage()
            ], 500);
        }
    }
}
