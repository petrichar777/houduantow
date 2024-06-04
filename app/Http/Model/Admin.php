<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = "admins"; // 确保数据库中的表名是复数形式
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $guarded = [];

    protected $fillable = [
        'admin_name',
        'major',
        'password',
    ];

    public static function authenticate($credentials)
    {
        $admin = self::where('admin_name', $credentials['admin_name'])->first();

        if ($admin && $admin->password === $credentials['password']) {
            return $admin;
        }

        return null;
    }

}
