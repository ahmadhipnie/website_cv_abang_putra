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
                    //key => value
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
            $kategoris = DB::table('kategoris')
                ->join('barangs', 'kategoris.id_kategori', '=', 'barangs.kategori_id')
                ->select('kategoris.*', DB::raw('COUNT(barangs.kategori_id) as jumlah_barang'))
                ->groupBy('kategoris.id_kategori')
                ->get();

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

    public function getImagesBarangByIdBarang(Request $request)
    {
        try {
            // Validasi data yang dikirimkan
            $validator = Validator::make($request->all(), [
                'id_barang' => 'required|integer',
            ]);

            // Jika validasi gagal, kembalikan response error
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            // Menggunakan Facade DB untuk mengambil data gambar barang berdasarkan id_barang
            $gambar_barangs = DB::table('gambar_barangs')
                ->where('barang_id', $request->id_barang)
                ->get();

            // Jika data gambar barang kosong
            if ($gambar_barangs->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data gambar barang!',
                ], 404);
            }

            // Kembalikan response dengan data gambar barang
            return response()->json([
                'status' => 'success',
                'data_gambar_barang' => $gambar_barangs,
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

    public function getBarangsByKategori(Request $request)
    {
        try {
            // Validasi request
            $request->validate([
                'kategori_id' => 'required|integer|exists:kategoris,id_kategori',  // Pastikan kategori_id ada di tabel kategoris
            ]);

            // Menggunakan Facade DB untuk mengambil data barang berdasarkan kategori_id
            $barangs = DB::table('barangs')
                ->where('kategori_id', $request->kategori_id)  // Filter berdasarkan kategori_id
                ->join('kategoris', 'barangs.kategori_id', '=', 'kategoris.id_kategori')  // Melakukan join dengan tabel kategoris
                ->leftJoin('gambar_barangs', 'barangs.id_barang', '=', 'gambar_barangs.barang_id')  // Melakukan left join dengan tabel gambar_barangs
                ->select(
                    'barangs.*',
                    'kategoris.nama_kategori',
                    DB::raw('MAX(gambar_barangs.gambar_url) as gambar_url')  // Memilih gambar pertama untuk setiap barang
                )
                ->groupBy('barangs.id_barang', 'kategoris.nama_kategori')  // Pastikan groupBy sesuai dengan agregat MAX
                ->get();

            // Jika data barang kosong
            if ($barangs->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data barang untuk kategori ini!',
                    'data_barang' => [],
                ], 404);  // Kode 404 jika tidak ada data ditemukan
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
                'message' => 'Terjadi kesalahan saat mengambil data barang!',
                'error' => $e->getMessage(),
                'data_barang' => [],
            ], 500);  // Kode 500 untuk error internal server
        }
    }

    public function getBarangsBySearch(Request $request)
    {
        try {
            // Validasi request, memastikan nama_barang adalah string dan tidak kosong
            $request->validate([
                'nama_barang' => 'nullable|string|max:255',
                'kategori_id' => 'required|integer|exists:kategoris,id_kategori',  // Pastikan kategori_id ada di tabel kategoris

            ]);

            // Menyiapkan query untuk mencari barang berdasarkan nama_barang
            $barangs = DB::table('barangs')
                ->when($request->nama_barang, function ($query, $nama_barang) {
                    // Menambahkan kondisi pencarian berdasarkan nama_barang jika ada input
                    return $query->where('nama_barang', 'like', '%' . $nama_barang . '%');
                })
                ->where('kategori_id', $request->kategori_id)
                ->join('kategoris', 'barangs.kategori_id', '=', 'kategoris.id_kategori')  // Melakukan join dengan tabel kategoris
                ->leftJoin('gambar_barangs', 'barangs.id_barang', '=', 'gambar_barangs.barang_id')  // Melakukan left join dengan tabel gambar_barangs
                ->select(
                    'barangs.*',
                    DB::raw('MAX(gambar_barangs.gambar_url) as gambar_url')  // Memilih gambar pertama untuk setiap barang
                )
                ->groupBy('barangs.id_barang', 'kategoris.nama_kategori')  // Pastikan groupBy sesuai dengan agregat MAX
                ->get();

            // Jika data barang kosong
            if ($barangs->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data barang untuk nama barang ini!',
                    'data_barang' => [],
                ], 404);  // Kode 404 jika tidak ada data ditemukan
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
                'message' => 'Terjadi kesalahan saat mengambil data barang!',
                'error' => $e->getMessage(),
                'data_barang' => [],
            ], 500);  // Kode 500 untuk error internal server
        }
    }

    public function getAllGambarPromo()
    {
        try {




            // Menggunakan Facade DB untuk mengambil data gambar barang berdasarkan id_barang
            $gambar_promos = DB::table('gambar_promos')
                ->join('promos', 'gambar_promos.promo_id', '=', 'promos.id_promo')
                ->select('gambar_promos.*', 'promos.nama_promo')
                
                ->orderByDesc('gambar_promos.id_gambar_promo')
                ->get();

            // Jika data gambar barang kosong
            if ($gambar_promos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data gambar barang!',
                ], 404);
            }

            // Kembalikan response dengan data gambar barang
            return response()->json([
                'status' => 'success',
                'data_gambar_promo' => $gambar_promos,
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
