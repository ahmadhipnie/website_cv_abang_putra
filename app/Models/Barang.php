<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'barangs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_barang'; // Menyesuaikan dengan kolom 'id_barang' sebagai primary key

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'int'; // Tipe data primary key adalah integer

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_barang',
        'harga_barang',
        'stok_barang',
        'deskripsi_barang',
        'role',
        'kategori_id', // Kolom yang berhubungan dengan foreign key ke tabel kategoris
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Get the kategori that owns the Barang.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id_kategori');
    }

    /**
     * Get the gambar-barangs for the Barang.
     */
    public function gambarBarangs()
    {
        return $this->hasMany(GambarBarang::class, 'barang_id', 'id_barang');
    }
}
