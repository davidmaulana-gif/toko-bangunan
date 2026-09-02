<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

// use Egulias\EmailValidator\Result\Reason\RFCWarnings;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|unique:users,username|regex:/^[A-Za-z0-9_]+$/',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string'
            ]);

            $uuid = Str::uuid();

            $user = DB::table('users')->insertGetId([
                'uuid' => $uuid,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'data user berhasil di tambahkan',
                'data' => $request->username
            ], 200);
        } catch (\Exception $userError) {
            return response()->json([
                'status' => false,
                'message' => 'data user gagal di tambahkan',
                'error' => $userError->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string'
            ]);

            $delete = DB::table('users')
                ->where('deleted_at')
                ->first();

            if (!$delete) {
                return response()->json([
                    'message' => 'user telah di hapus'
                ], 500);
            }

            $login = $request->login;

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $login)->first();
            } else {
                $user = User::where('username', $login)->first();
            }

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'email atau username tidak di temukan.'
                ], 500);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Password salah',
                ], 401);
            }

            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'login berhasil',
                'token' => $token,
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ], 200);
        } catch (\Exception $loginError) {
            return response()->json([
                'status' => false,
                'message' => 'login gagal',
                'error' => $loginError->getMessage()
            ], 401);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'anda berhasil logout.'
            ], 200);
        } catch (\Exception $errorLogout) {
            return response()->json([
                'status' => false,
                'message' => 'data tidak ditemukan',
                'error' => $errorLogout->getMessage()
            ], 404);
        }
    }

    public function edit(Request $request)
    {
        try {
            $request->validate([
                'username' => 'string|unique:users,username|regex:/^[A-Za-z0-9_]+$/',
                'email' => 'email|unique:users,email',
                'password' => 'string'
            ]);

            $data = DB::table('users')
                ->where('id', Auth::id())
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'data tidak ditemukan'
                ], 404);
            }

            $dataUser = DB::table('users')
                ->where('id', Auth::id())
                ->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'data user berhasil di edit'
            ], 200);
        } catch (\Exception $userError) {
            return response()->json([
                'message' => 'data tidak ditemukan',
                'error' => $userError->getMessage()
            ], 500);
        }
    }

    public function delete($uuid)
    {
        try {
            $user = DB::table('users')
                ->where('uuid', $uuid)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            if (!$user) {
                return response()->json([
                    'message' => 'data gagal di hapus 1'
                ], 500);
            }

            $id = DB::table('users')
                ->where('uuid', $uuid)
                ->value('id');


            $personalData = DB::table('personal_datas')
                ->where('user_id',  $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            dd($personalData);

            if (!$personalData) {
                return response()->json([
                    'message' => 'data gagal di hapus'
                ], 500);
            }

            return response()->json([
                'message' => 'data berhasil di hapus.'
            ], 200);
        } catch (\Exception $errorUser) {
            return response()->json([
                'message' => 'data tidak di temukan.',
                'error' => $errorUser->getMessage()
            ], 500);
        }
    }
}
