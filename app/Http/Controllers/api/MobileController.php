<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Model User
use App\Models\Reseller; // Model Reseller
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


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
    public function getAllPromo()
    {
        try {
            // Menggunakan Facade DB untuk mengambil data promo dan image_url secara acak
            $promos = DB::table('promos')
                ->leftJoin('gambar_promos', 'promos.id_promo', '=', 'gambar_promos.promo_id')  // Melakukan left join dengan tabel gambar_barangs
                ->select('promos.*', DB::raw('MAX(gambar_promos.gambar_url) as gambar_url'))  // Memilih kolom dari tabel barangs, nama_kategori dari kategoris, dan gambar_url (ambil gambar pertama)
                ->groupBy('promos.id_promo')  // Grouping berdasarkan barang_id dan nama_kategori
                ->get();

            // Jika data promo kosong
            if ($promos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data promo!',
                ], 404);
            }

            // Kembalikan response dengan data barang
            return response()->json([
                'status' => 'success',
                'data_promo' => $promos,
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
   

    public function getAllFeedback()
{
    try {
        // Mengambil data dari tabel feedback
        $feedback = DB::table('feedback')
            ->select('feedback.*')
            ->get();

        // Jika data kosong
        if ($feedback->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada data feedback',
            ], 404);
        }

        // Mengembalikan response berhasil
        return response()->json([
            'status' => 'success',
            'data_feedback' => $feedback,
        ], 200);
    } catch (\Exception $e) {
        // Menangani error
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage() ?: 'Terjadi kesalahan yang tidak diketahui!',
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

    public function getBarangsBySearchAdmin(Request $request)
    {
        try {
            // Validasi request, memastikan nama_barang adalah string dan tidak kosong
            $request->validate([
                'nama_barang' => 'nullable|string|max:255',

            ]);

            // Menyiapkan query untuk mencari barang berdasarkan nama_barang
            $barangs = DB::table('barangs')
                ->when($request->nama_barang, function ($query, $nama_barang) {
                    // Menambahkan kondisi pencarian berdasarkan nama_barang jika ada input
                    return $query->where('nama_barang', 'like', '%' . $nama_barang . '%');
                })
                ->leftJoin('gambar_barangs', 'barangs.id_barang', '=', 'gambar_barangs.barang_id')  // Melakukan left join dengan tabel gambar_barangs
                ->select(
                    'barangs.*',
                    DB::raw('MAX(gambar_barangs.gambar_url) as gambar_url')  // Memilih gambar pertama untuk setiap barang
                )
                ->groupBy('barangs.id_barang')
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
    public function getPromosBySearchAdmin(Request $request)
    {
        try {
            // Validasi request, memastikan nama_barang adalah string dan tidak kosong
            $request->validate([
                'nama_promo' => 'nullable|string|max:255',

            ]);

            // Menyiapkan query untuk mencari barang berdasarkan nama_promo
            $promos = DB::table('promos')
                ->when($request->nama_promo, function ($query, $nama_promo) {
                    // Menambahkan kondisi pencarian berdasarkan nama_barang jika ada input
                    return $query->where('nama_promo', 'like', '%' . $nama_promo . '%');
                })
                ->leftJoin('gambar_promos', 'promos.id_promo', '=', 'gambar_promos.promo_id')  // Melakukan left join dengan tabel gambar_promos
                ->select(
                    'promos.*',
                    DB::raw('MAX(gambar_promos.gambar_url) as gambar_url')  // Memilih gambar pertama untuk setiap promo
                )
                ->groupBy('promos.id_promo')
                ->get();

            // Jika data promo kosong
            if ($promos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data promo untuk nama promo ini!',
                    'data_promo' => [],
                ], 404);  // Kode 404 jika tidak ada data ditemukan
            }

            // Kembalikan response dengan data promo
            return response()->json([
                'status' => 'success',
                'data_promo' => $promos,
            ], 200);
        } catch (\Exception $e) {
            // Tangani error
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data promo!',
                'error' => $e->getMessage(),
                'data_promo' => [],
            ], 500);  // Kode 500 untuk error internal server
        }
    }

    public function getAllGambarPromo()
    {
        try {
            // Menggunakan Facade DB untuk mengambil data gambar barang berdasarkan id_barang
            $gambar_promos = DB::table('gambar_promos')
                ->join('promos', 'gambar_promos.promo_id', '=', 'promos.id_promo')
                ->select('gambar_promos.*', 'promos.nama_promo', 'promos.deskripsi_promo', 'promos.tanggal_periode_awal', 'promos.tanggal_periode_akhir')

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

    public function getImagesPromoByIdPromo(Request $request)
    {
        try {
            // Validasi data yang dikirimkan
            $validator = Validator::make($request->all(), [
                'id_promo' => 'required|integer',
            ]);

            // Jika validasi gagal, kembalikan response error
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            // Menggunakan Facade DB untuk mengambil data gambar promo berdasarkan id_promo
            $gambar_promos = DB::table('gambar_promos')
            ->join('promos', 'gambar_promos.promo_id', '=', 'promos.id_promo')
                ->select('gambar_promos.*', 'promos.nama_promo', 'promos.deskripsi_promo', 'promos.tanggal_periode_awal', 'promos.tanggal_periode_akhir')

                ->where('promo_id', $request->id_promo)
                ->get();

            // Jika data gambar barang kosong
            if ($gambar_promos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data gambar promo!',
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

    public function kirimFeedbackReseller(Request $request) {
        try {
            // Validasi data yang dikirimkan
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'nama_reseller' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
                'isi_feedback' => 'required|string',
            ]);

            // Jika validasi gagal, kembalikan response error
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            // Menyimpan data ke tabel feedback menggunakan DB facade
            $inserted = DB::table('feedback')->insert([
                'email' => $request->email,
                'nama_reseller' => $request->nama_reseller,
                'rating' => $request->rating,
                'isi_feedback' => $request->isi_feedback,
                'created_at' => now(),
                'updated_at' => now(), // Menambahkan updated_at jika diperlukan
            ]);

            // Cek apakah data berhasil disimpan
            if ($inserted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Feedback berhasil disimpan!',
                ], 201);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan feedback!',
                ], 500);
            }

        } catch (\Exception $e) {
            // Tangani error
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateUserReseller(Request $request)
    {
        try {
            // Validasi data yang dikirimkan
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|integer|exists:users,id_user',
                'email' => 'required|string|email|unique:users,email,' . $request->id_user . ',id_user',
                'password' => 'required|string',
                'nama' => 'required|string',
                'alamat' => 'required|string',
                'nomor_telepon' => 'required|string',
                'foto_profil' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Jika validasi gagal, kembalikan response error
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            DB::beginTransaction();

            // Update data di tabel users
            $userUpdated = DB::table('users')->where('id_user', $request->id_user)->update([
                'email' => $request->email,
                'password' => $request->password,
                'updated_at' => now(),
            ]);

            if (!$userUpdated) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate data user',
                ], 500);
            }

            // Proses file foto_profil
            $fotoProfilPath = null;
            if ($request->hasFile('foto_profil')) {
                $fotoProfilFile = $request->file('foto_profil');

                // Hapus file foto_profil sebelumnya jika ada
                $oldFotoProfil = DB::table('resellers')->where('user_id', $request->id_user)->value('foto_profil');
                if ($oldFotoProfil && Storage::exists($oldFotoProfil)) {
                    Storage::delete($oldFotoProfil);
                }

                // Simpan file baru di folder public/storage/foto_profil_reseller
                $fotoProfilPath = $fotoProfilFile->storeAs(
                    'public/foto_profil_reseller',
                    uniqid() . '.' . $fotoProfilFile->getClientOriginalExtension()
                );

                // Format path untuk disimpan di database
                $fotoProfilPath = str_replace('public/', 'storage/', $fotoProfilPath);
            }

            // Update data di tabel resellers
            $resellerData = [
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
                'updated_at' => now(),
            ];

            if ($fotoProfilPath) {
                $resellerData['foto_profil'] = $fotoProfilPath;
            }

            $resellerUpdated = DB::table('resellers')->where('user_id', $request->id_user)->update($resellerData);

            if (!$resellerUpdated) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate data reseller',
                ], 500);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data user dan reseller berhasil diperbarui',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
