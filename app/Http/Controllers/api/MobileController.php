<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Model User
use App\Models\Reseller; // Model Reseller
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MobileController extends Controller
{
    public function login(Request $request)
    {
        // Validasi data yang dikirimkan
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string', // Tidak menggunakan hash untuk password
        ]);

        // Jika validasi gagal, kembalikan response error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Jika user tidak ditemukan, kembalikan response error
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!',
            ], 404);
        }

        // Periksa apakah password yang diberikan cocok
        // **Perhatikan bahwa ini tidak menggunakan hashing password, melainkan perbandingan langsung**
        if ($user->password !== $request->password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect password!',
            ], 401);
        }

        // Tentukan role dan kembalikan response sesuai dengan role
        if ($user->role == 'admin') {
            return response()->json([
                'status' => 'success',
                'role' => 'admin',
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);
        } elseif ($user->role == 'reseller') {
            // Ambil data reseller berdasarkan user_id
            $reseller = Reseller::where('user_id', $user->id_user)->first();

            if (!$reseller) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reseller data not found!',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'role' => 'reseller',
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'id_reseller' => $reseller->id_reseller,
                    'nama' => $reseller->nama,
                    'alamat' => $reseller->alamat,
                    'nomor_telepon' => $reseller->nomor_telepon,
                    'tanggal_lahir' => $reseller->tanggal_lahir,
                    'user_id' => $reseller->user_id,
                    'foto_profil' => $reseller->foto_profil,
                    'reseller_created_at' => $reseller->created_at,
                    'reseller_updated_at' => $reseller->updated_at,
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role!',
            ], 400);
        }
    }
}
