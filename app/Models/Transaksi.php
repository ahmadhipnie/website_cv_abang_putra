<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'barang_id',
        'jumlah_barang',
        'total_harga',
        'jenis_pengiriman',
        'alamat_pengiriman',
        'status',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id_barang');
    }
}
