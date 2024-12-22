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

    public function getAllKategori()
    {
        try {
            // Menggunakan Facade DB untuk mengambil semua data dari tabel 'kategoris'
            $kategoris = DB::table('kategoris')->get();

            // Jika data kategori kosong
            if ($kategoris->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data kategori!',
                ], 404);
            }

            // Kembalikan response dengan data kategori
            return response()->json([
                'status' => 'success',
                'data' => $kategoris,
            ], 200);
        } catch (\Exception $e) {
            // Tangani error
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllBarang()
{
    try {
        // Menggunakan Facade DB untuk mengambil data barang beserta nama kategori dan image_url secara acak
        $barangs = DB::table('barangs')
            ->join('kategoris', 'barangs.kategori_id', '=', 'kategoris.id_kategori')  // Melakukan join dengan tabel kategoris
            ->leftJoin('gambar_barangs', 'barangs.id_barang', '=', 'gambar_barangs.barang_id')  // Melakukan left join dengan tabel gambar_barangs
            ->select('barangs.*', 'kategoris.nama_kategori', DB::raw('MAX(gambar_barangs.gambar_url) as gambar_url'))  // Memilih kolom dari tabel barangs, nama_kategori dari kategoris, dan gambar_url (ambil gambar pertama)
            ->groupBy('barangs.id_barang', 'kategoris.nama_kategori')  // Grouping berdasarkan barang_id dan nama_kategori
            ->inRandomOrder()  // Mengambil data secara acak
            ->get();

        // Jika data barang kosong
        if ($barangs->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada data barang!',
            ], 404);
        }

        // Kembalikan response dengan data barang
        return response()->json([
            'status' => 'success',
            'data_barang' => $barangs,
        ], 200);
    } catch (\Exception $e) {
        // Tangani error
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan!',
            'error' => $e->getMessage(),
        ], 500);
    }
}



}
