<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class events extends Model
{
    use HasFactory;

    protected $table = 'events'; // 数据库中的表名
    protected $primaryKey = 'id'; // 主键
    public $timestamps = true; // 自动管理 created_at 和 updated_at

    // 允许批量赋值的字段
    protected $fillable = [
        'event_name',
        'num_stu',
        'num',
    ];

    public static function updateSignupCounts($signupCounts)
    {
        foreach ($signupCounts as $event_name => $signup_count) {
            self::where('event_name', $event_name)->update(['num_stu' => $signup_count]);
        }
    }
}
