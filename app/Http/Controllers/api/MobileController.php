<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\GambarBarang;
use Illuminate\Http\Request;
use App\Models\User; // Model User
use App\Models\Reseller; // Model Reseller
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Promo;
use App\Models\GambarPromo;
use App\Models\Transaksi;

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
            // Menggunakan LEFT JOIN untuk memastikan kategori tanpa barang juga ditampilkan
            $kategoris = DB::table('kategoris')
                ->leftJoin('barangs', 'kategoris.id_kategori', '=', 'barangs.kategori_id')
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

    public function kirimFeedbackReseller(Request $request)
    {
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

    public function deletePromo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_promo' => 'required|integer|exists:promos,id_promo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_promo = $request->input('id_promo');

        DB::beginTransaction();
        try {
            // Delete associated images
            GambarPromo::where('promo_id', $id_promo)->delete();

            // Delete the promo
            Promo::where('id_promo', $id_promo)->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Promo and associated images deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete promo!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteBarang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_barang' => 'required|integer|exists:barangs,id_barang',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_barang = $request->input('id_barang');

        DB::beginTransaction();
        try {
            // Delete associated images
            GambarBarang::where('barang_id', $id_barang)->delete();

            // Delete the barang
            Barang::where('id_barang', $id_barang)->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Promo and associated images deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete promo!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStokBarang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_barang' => 'required|integer|exists:barangs,id_barang',
            'stok_barang' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_barang = $request->input('id_barang');
        $stok_barang = $request->input('stok_barang');

        DB::beginTransaction();
        try {
            // Update the stok_barang
            Barang::where('id_barang', $id_barang)->update(['stok_barang' => $stok_barang]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Stok barang updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update stok barang!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Handle the image upload
            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('foto_kategori', $imageName, 'public');
                $imageUrl = 'storage/' . $imagePath;
            }

            // Insert the new category
            DB::table('kategoris')->insert([
                'nama_kategori' => $request->input('nama_kategori'),
                'image_url' => $imageUrl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori added successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add kategori!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:users,id_user',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_user = $request->input('id_user');
        $password = $request->input('password');

        DB::beginTransaction();
        try {
            // Update the password
            DB::table('users')->where('id_user', $id_user)->update(['password' => $password]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update password!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addUserAndReseller(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'nama' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:15',
            'tanggal_lahir' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Insert the new user
            $userId = DB::table('users')->insertGetId([
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'role' => 'reseller',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert the new reseller
            DB::table('resellers')->insert([
                'nama' => $request->input('nama'),
                'nomor_telepon' => $request->input('nomor_telepon'),
                'tanggal_lahir' => $request->input('tanggal_lahir'),
                'alamat' => 'batam',
                'foto_profil' => 'storage/foto_profil_reseller/foto_profil_asd.jpeg',
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'User and reseller added successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add user and reseller!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addBarang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'harga_barang' => 'required|integer',
            'stok_barang' => 'required|integer',
            'deskripsi_barang' => 'required|string',
            'satuan' => 'required|string|max:50',
            'kategori_id' => 'required|integer|exists:kategoris,id_kategori',
            'gambar_url_1' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Insert the new barang
            $barangId = DB::table('barangs')->insertGetId([
                'nama_barang' => $request->input('nama_barang'),
                'harga_barang' => $request->input('harga_barang'),
                'stok_barang' => $request->input('stok_barang'),
                'deskripsi_barang' => $request->input('deskripsi_barang'),
                'satuan' => $request->input('satuan'),
                'kategori_id' => $request->input('kategori_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Handle the image uploads
            for ($i = 1; $i <= 3; $i++) {
                $imageKey = 'gambar_url_' . $i;
                if ($request->hasFile($imageKey)) {
                    $image = $request->file($imageKey);
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('foto_barang', $imageName, 'public');
                    $imageUrl = 'storage/' . $imagePath;

                    // Insert the new gambar_barang
                    DB::table('gambar_barangs')->insert([
                        'gambar_url' => $imageUrl,
                        'barang_id' => $barangId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Barang and images added successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add barang and images!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPromo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_promo' => 'required|string|max:255',
            'deskripsi_promo' => 'required|string',
            'tanggal_periode_awal' => 'required|date',
            'tanggal_periode_akhir' => 'required|date',
            'gambar_url_1' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Insert the new barang
            $promoId = DB::table('promos')->insertGetId([
                'nama_promo' => $request->input('nama_promo'),
                'deskripsi_promo' => $request->input('deskripsi_promo'),
                'tanggal_periode_awal' => $request->input('tanggal_periode_awal'),
                'tanggal_periode_akhir' => $request->input('tanggal_periode_akhir'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Handle the image uploads
            for ($i = 1; $i <= 3; $i++) {
                $imageKey = 'gambar_url_' . $i;
                if ($request->hasFile($imageKey)) {
                    $image = $request->file($imageKey);
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('foto_promo', $imageName, 'public');
                    $imageUrl = 'storage/' . $imagePath;

                    // Insert the new gambar_barang
                    DB::table('gambar_promos')->insert([
                        'gambar_url' => $imageUrl,
                        'promo_id' => $promoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Promo and images added successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add promo and images!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|integer|exists:kategoris,id_kategori',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_kategori = $request->input('id_kategori');

        DB::beginTransaction();
        try {
            // Get all barang ids related to the kategori
            $barangIds = DB::table('barangs')->where('kategori_id', $id_kategori)->pluck('id_barang');

            // Delete associated images
            DB::table('gambar_barangs')->whereIn('barang_id', $barangIds)->delete();

            // Delete the barangs
            DB::table('barangs')->where('kategori_id', $id_kategori)->delete();

            // Delete the kategori
            DB::table('kategoris')->where('id_kategori', $id_kategori)->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori and related data deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete kategori and related data!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteKategoriWithCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|integer|exists:kategoris,id_kategori',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_kategori = $request->input('id_kategori');

        try {
            // Cek apakah ada barang yang berelasi dengan kategori
            $barangCount = DB::table('barangs')->where('kategori_id', $id_kategori)->count();

            if ($barangCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori tidak dapat dihapus karena masih ada barang yang berelasi dengan kategori ini!',
                ], 400);
            }

            // Hapus kategori jika tidak ada barang yang berelasi
            DB::table('kategoris')->where('id_kategori', $id_kategori)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Kategori berhasil dihapus!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus kategori!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|integer|exists:kategoris,id_kategori',
            'nama_kategori' => 'required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_kategori = $request->input('id_kategori');

        DB::beginTransaction();
        try {
            // Ambil data kategori lama
            $kategori = DB::table('kategoris')->where('id_kategori', $id_kategori)->first();

            if (!$kategori) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori tidak ditemukan!',
                ], 404);
            }

            $imageUrl = $kategori->image_url; // Simpan URL gambar lama

            // Jika ada file baru yang diunggah
            if ($request->hasFile('image_url')) {
                $image = $request->file('image_url');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('foto_kategori', $imageName, 'public');
                $imageUrl = 'storage/' . $imagePath;

                // Hapus file lama jika ada
                if ($kategori->image_url && Storage::exists(str_replace('storage/', 'public/', $kategori->image_url))) {
                    Storage::delete(str_replace('storage/', 'public/', $kategori->image_url));
                }
            }

            // Update data kategori
            DB::table('kategoris')->where('id_kategori', $id_kategori)->update([
                'nama_kategori' => $request->input('nama_kategori'),
                'image_url' => $imageUrl,
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori berhasil diperbarui!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui kategori!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBarang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_barang' => 'required|integer|exists:barangs,id_barang',
            'nama_barang' => 'required|string|max:255',
            'harga_barang' => 'required|integer',
            'stok_barang' => 'required|integer',
            'deskripsi_barang' => 'required|string',
            'satuan' => 'required|string|max:50',
            'kategori_id' => 'required|integer|exists:kategoris,id_kategori',
            'gambar_url_1' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_barang = $request->input('id_barang');

        DB::beginTransaction();
        try {
            // Update data barang
            DB::table('barangs')->where('id_barang', $id_barang)->update([
                'nama_barang' => $request->input('nama_barang'),
                'harga_barang' => $request->input('harga_barang'),
                'stok_barang' => $request->input('stok_barang'),
                'deskripsi_barang' => $request->input('deskripsi_barang'),
                'satuan' => $request->input('satuan'),
                'kategori_id' => $request->input('kategori_id'),
                'updated_at' => now(),
            ]);

            // Hapus gambar lama dari tabel gambar_barangs
            $oldImages = DB::table('gambar_barangs')->where('barang_id', $id_barang)->get();
            foreach ($oldImages as $image) {
                if (Storage::exists(str_replace('storage/', 'public/', $image->gambar_url))) {
                    Storage::delete(str_replace('storage/', 'public/', $image->gambar_url));
                }
            }
            DB::table('gambar_barangs')->where('barang_id', $id_barang)->delete();

            // Tambahkan gambar baru jika ada
            for ($i = 1; $i <= 3; $i++) {
                $imageKey = 'gambar_url_' . $i;
                if ($request->hasFile($imageKey)) {
                    $image = $request->file($imageKey);
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $imagePath = $image->storeAs('foto_barang', $imageName, 'public');
                    $imageUrl = 'storage/' . $imagePath;

                    // Simpan gambar baru ke tabel gambar_barangs
                    DB::table('gambar_barangs')->insert([
                        'gambar_url' => $imageUrl,
                        'barang_id' => $id_barang,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Barang and images updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update barang and images!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePromo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_promo' => 'required|integer|exists:promos,id_promo',
            'nama_promo' => 'required|string|max:255',
            'deskripsi_promo' => 'required|string',
            'tanggal_periode_awal' => 'required|date',
            'tanggal_periode_akhir' => 'required|date',
            'gambar_url_1' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gambar_url_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id_promo = $request->input('id_promo');

        DB::beginTransaction();
        try {
            // Update data promo
            DB::table('promos')->where('id_promo', $id_promo)->update([
                'nama_promo' => $request->input('nama_promo'),
                'deskripsi_promo' => $request->input('deskripsi_promo'),
                'tanggal_periode_awal' => $request->input('tanggal_periode_awal'),
                'tanggal_periode_akhir' => $request->input('tanggal_periode_akhir'),
                'updated_at' => now(),
            ]);

            // Hapus gambar lama dari tabel gambar_promos
            $oldImages = DB::table('gambar_promos')->where('promo_id', $id_promo)->get();
            foreach ($oldImages as $image) {
                if (Storage::exists(str_replace('storage/', 'public/', $image->gambar_url))) {
                    Storage::delete(str_replace('storage/', 'public/', $image->gambar_url));
                }
            }
            DB::table('gambar_promos')->where('promo_id', $id_promo)->delete();

            // Tambahkan gambar baru jika ada
            for ($i = 1; $i <= 3; $i++) {
                $imageKey = 'gambar_url_' . $i;
                if ($request->hasFile($imageKey)) {
                    $image = $request->file($imageKey);
                    $imageName = $image->getClientOriginalName();
                    $imagePath = $image->storeAs('foto_promo', $imageName, 'public');
                    $imageUrl = 'storage/' . $imagePath;

                    // Simpan gambar baru ke tabel gambar_promos
                    DB::table('gambar_promos')->insert([
                        'gambar_url' => $imageUrl,
                        'promo_id' => $id_promo,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Promo and images updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update promo and images!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function addTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id_user',
            'barang_id' => 'required|integer|exists:barangs,id_barang',
            'jumlah_barang' => 'required|integer|min:1',
            'total_harga' => 'required|integer|min:0',
            'jenis_pengiriman' => 'required|string|max:255',
            'alamat_pengiriman' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Ambil barang
            $barang = Barang::find($request->barang_id);

            // Cek stok cukup
            if ($barang->stok_barang < $request->jumlah_barang) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stok barang tidak cukup!',
                ], 400);
            }

            // Kurangi stok
            $barang->stok_barang -= $request->jumlah_barang;
            $barang->save();

            // Simpan transaksi
            Transaksi::create([
                'user_id' => $request->user_id,
                'barang_id' => $request->barang_id,
                'jumlah_barang' => $request->jumlah_barang,
                'total_harga' => $request->total_harga,
                'jenis_pengiriman' => $request->jenis_pengiriman,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'status' => 'diproses',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan transaksi!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // API untuk mengubah status transaksi
    public function updateStatusTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:transaksis,id',
            'status' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $transaksi = Transaksi::find($request->id);
            $transaksi->status = $request->status;
            $transaksi->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Status transaksi berhasil diubah',
                'data' => $transaksi,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah status transaksi!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllTransaksi()
    {
        try {
            $transaksis = Transaksi::with(['user', 'barang'])->orderByDesc('created_at')->get();
            $resellers = \App\Models\Reseller::all()->keyBy('user_id');

            $data = $transaksis->map(function ($trx) use ($resellers) {
                $trxArr = $trx->toArray();

                // Pastikan array barang ada
                if (isset($trxArr['barang'])) {
                    // Tambahkan nama_reseller
                    $trxArr['barang']['nama_reseller'] = null;
                    if ($trx->user && $trx->user->role === 'reseller') {
                        $reseller = $resellers->get($trx->user->id_user);
                        $trxArr['barang']['nama_reseller'] = $reseller ? $reseller->nama : null;
                    }
                    // Tambahkan alamat_pengiriman
                    $trxArr['barang']['alamat_pengiriman'] = $trx->alamat_pengiriman;
                }

                return $trxArr;
            })->values();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data transaksi!',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Ambil transaksi berdasarkan user_id
    public function getTransaksiByUserId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id_user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $transaksis = Transaksi::with(['user', 'barang'])
                ->where('user_id', $request->user_id)
                ->orderByDesc('created_at')
                ->get();

            if ($transaksis->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada transaksi untuk user ini!',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $transaksis,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
