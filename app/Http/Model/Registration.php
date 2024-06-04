<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Registration extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $table = 'registrations'; // 数据库中的表名
    protected $primaryKey = 'id'; // 主键
    public $timestamps = true; // 自动管理 created_at 和 updated_at

    // 允许批量赋值的字段
    protected $fillable = [
        'student_id',
        'event_name',
        'student_name',
        'class',
        'major',
    ];

    // 设置日期字段的格式
    protected $dates = [
        'registered_at',
    ];

    // 定义与 Event 的关系
    public function event()
    {
        return $this->belongsTo(events::class);
    }

    // 定义与 User 的关系 (假设有 User 模型)
    public function Students()
    {
        return $this->belongsTo(Students::class);
    }

    public static function getStudentRegistrations($studentId)
    {
        return self::where('student_id', $studentId)->get();
    }

    public static function getSignupCounts()
    {
        return self::select('event_name')
            ->selectRaw('count(*) as signup_count')
            ->groupBy('event_name')
            ->pluck('signup_count', 'event_name');
    }

    public static function updateStudentInfo($validatedData)
    {
        $student = self::where('student_id', $validatedData['student_id'])
            ->where('student_name', $validatedData['student_name'])
            ->first();

        if ($student) {
            $student->student_id = $validatedData['student_id'];
            $student->student_name = $validatedData['student_name'];
            $student->class = $validatedData['class'];
            $student->major = $validatedData['major'];
            $student->event_name = $validatedData['event_name'];
            return $student->save();
        }

        return false;
    }

    public static function signupStudent($validatedData)
    {
        $studentId = $validatedData['student_id'];

        // 检查该学生已经报名的项目数量
        $projectsCount = self::where('student_id', $studentId)->count();

        if ($projectsCount >= 4) {
            return [
                'success' => false,
                'message' => '每个学生只能报名不超过4个项目',
            ];
        } else {
            // 进行报名操作，可以将报名信息存储到数据库中
            self::create([
                'student_id' => $validatedData['student_id'],
                'student_name' => $validatedData['student_name'],
                'class' => $validatedData['class'],
                'major' => $validatedData['major'],
                'event_name' => $validatedData['event_name'],
            ]);

            return [
                'success' => true,
                'message' => '报名成功',
            ];
        }
    }

    public static function adminSignupStudent($validatedData)
    {
        // 创建新的学生记录
        $student = new self();
        $student->student_id = $validatedData['student_id'];
        $student->student_name = $validatedData['student_name'];
        $student->class = $validatedData['class'];
        $student->major = $validatedData['major'];
        $student->event_name = $validatedData['event_name'];

        return $student->save();
    }

    public function Admin_signup(Request $request): JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|integer',
            'student_name' => 'required|string',
            'class' => 'required|string',
            'major' => 'required|string',
            'event_name' => 'required|string',
        ]);

        // 调用模型中的静态方法进行添加操作
        $result = Registration::adminSignupStudent($validatedData);

        if ($result) {
            return response()->json(['Success' => true, 'Message' => '学生添加成功']);
        } else {
            return response()->json(['Success' => false, 'Message' => '学生添加失败']);
        }
    }
}
