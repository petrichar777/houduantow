<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Students extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = "students"; // 确保数据库中的表名是复数形式
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $guarded = [];

    protected $fillable = [
        'student_id',
        'password',
        'student_name',
        'major',
        'class',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * 验证用户的用户名和密码。
     *
     * @param  string  $student_id
     * @param  string  $password
     * @return bool
     */
    public static function validateCredentials($student_id, $password)
    {
        $user = self::where('student_id', $student_id)->first();

        if ($user && Hash::check($password, $user->password)) {
            // 如果密码正确，返回 true
            return true;
        }

        // 如果用户不存在或密码不匹配，返回 false
        return false;
    }

    /**
     * 注册新学生
     *
     * @param  array  $validatedData
     * @return Students
     */
    public static function registerStudent($validatedData)
    {
        $credentials = [
            'student_name' => $validatedData['student_name'],
            'student_id' => $validatedData['student_id'],
            'major' => $validatedData['major'],
            'class' => $validatedData['class'],
            'password' => Hash::make($validatedData['password']), // 对密码进行哈希处理
        ];

        return self::create($credentials);
    }

    public static function authenticate($credentials)
    {
        $user = self::where('student_id', $credentials['student_id'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            return $user;
        }

        return null;
    }

    public static function deleteStudent($studentId)
    {
        $student = self::find($studentId);

        if ($student) {
            return $student->delete();
        }

        return false;
    }

    public static function deleteStudentByDetails($studentId, $studentName, $class, $major)
    {
        $student = self::where('student_id', $studentId)
            ->where('student_name', $studentName)
            ->where('class', $class)
            ->where('major', $major)
            ->first();

        if ($student) {
            return $student->delete();
        }

        return false;
    }
}
