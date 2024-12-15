<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'promos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_promo'; // Menetapkan kolom 'id_promo' sebagai primary key

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
        'nama_promo',
        'deskripsi_promo',
        'tanggal_periode_awal',
        'tanggal_periode_akhir',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_periode_awal' => 'date',
        'tanggal_periode_akhir' => 'date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Menentukan apakah promo ini masih berlaku atau tidak.
     *
     * @return bool
     */
    public function isValid()
    {
        $currentDate = now();
        return $currentDate->between($this->tanggal_periode_awal, $this->tanggal_periode_akhir);
    }
}
