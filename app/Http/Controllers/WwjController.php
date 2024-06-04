<?php

namespace App\Http\Controllers;

use App\Http\Model\Admin;
use App\Http\Model\events;
use App\Http\Model\Registration;
use App\Http\Model\Students;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class WwjController extends Controller
{
    public function register(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:255|unique:students',
            'major' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        // 调用模型中的静态方法进行注册
        $student = Students::registerStudent($validatedData);

        if ($student) {
            // 生成 token
            $token = JWTAuth::fromUser($student);
            return response()->json(['token' => $token], 201);
        } else {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('student_id', 'password');

        // 调用模型中的静态方法进行身份验证
        $user = Students::authenticate($credentials);

        if ($user) {
            // 验证成功，生成 token
            $token = JWTAuth::fromSubject($user);
            return response()->json([
                'message' => 'Login successful',
                'token' => $token
            ]);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function adminLogin(Request $request)
    {
        $validatedData = $request->validate([
            'admin_name' => 'required|string',
            'password' => 'required|string',
        ]);

        // 调用模型中的静态方法进行身份验证
        $admin = Admin::authenticate($validatedData);

        if ($admin) {
            return response()->json([
                'message' => 'Admin login successful',
            ]);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to log out, please try again'], 500);
        }
    }

    public function selectonestu(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|string',
        ]);

        $id = $validatedData['student_id'];

        // 调用模型中的静态方法获取学生报名信息
        $registrations = Registration::getStudentRegistrations($id);

        // 检查是否找到了学生报名信息
        if ($registrations->isEmpty()) {
            return response()->json(['message' => '该学生未报名任何比赛'], 404);
        }

        // 提取学生信息
        $studentInfo = $registrations->map(function ($registration) {
            return [
                'student_id' => $registration->student_id,
                'student_name' => $registration->student_name,
                'event_name' => $registration->event_name,
                'class' => $registration->class,
                'major' => $registration->major,
            ];
        });

        // 返回学生信息
        return response()->json($studentInfo, 200);
    }

    public function selectAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|string',
        ]);

        $id = $validatedData['student_id'];

        $student = Students::where('student_id', $id)->first();

        if (!$student) {
            return response()->json(['message' => '查询失败！该学生不存在'], 404);
        }

        // 查询学生报名的所有项目
        $registrations = Registration::getStudentRegistrations($id);

        // 计算每个项目的报名人数
        $signupCounts = Registration::getSignupCounts();

        // 将报名人数保存到 events 表中的 num_stu 字段
        events::updateSignupCounts($signupCounts);

        // 准备返回的学生信息
        $studentInfo = $registrations->map(function ($registration) use ($signupCounts) {
            return [
                'event_name' => $registration->event_name,
                'student_id' => $registration->student_id,
                'student_name' => $registration->student_name,
                'major' => $registration->major,
                'class' => $registration->class,
                'signup_count' => $signupCounts[$registration->event_name] ?? 0, // 获取该项目的报名人数
            ];
        });

        return response()->json([
            'Success' => true,
            'Message' => '查询成功',
            'Student_Info' => $studentInfo
        ], 200);
    }

    public function SxqAdminDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|string',
        ]);

        $studentId = $validatedData['student_id'];

        // 调用模型中的静态方法删除学生
        if (Students::deleteStudent($studentId)) {
            return response()->json(['success' => true, 'message' => '删除成功！'], 200);
        } else {
            return response()->json(['message' => '删除失败！该学生不存在'], 404);
        }
    }

    public function SxqstudentDelete(Request $request): JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|string',
            'student_name' => 'required|string',
            'class' => 'required|string',
            'major' => 'required|string',
        ]);

        $studentId = $validatedData['student_id'];
        $studentName = $validatedData['student_name'];
        $class = $validatedData['class'];
        $major = $validatedData['major'];

        // 调用模型中的静态方法删除学生
        if (Students::deleteStudentByDetails($studentId, $studentName, $class, $major)) {
            return response()->json(['success' => true, 'message' => '删除成功！'], 200);
        } else {
            return response()->json(['message' => '删除失败！该学生不存在'], 404);
        }
    }

    public function SxqreworkStudent(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|string',
            'student_name' => 'required|string',
            'class' => 'required|string',
            'major' => 'required|string',
            'event_name' => 'required|string',
        ]);

        // 调用模型中的静态方法更新学生信息
        if (Registration::updateStudentInfo($validatedData)) {
            return response()->json(['message' => '学生信息修改成功'], 200);
        } else {
            return response()->json(['message' => '无该学生信息'], 404);
        }
    }

    public function student_signup(Request $request): JsonResponse
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|integer',
            'student_name' => 'required|string',
            'class' => 'required|string',
            'major' => 'required|string',
            'event_name' => 'required|string',
        ]);

        // 调用模型中的静态方法进行报名操作
        $result = Registration::signupStudent($validatedData);

        return response()->json([
            'Success' => $result['success'],
            'Message' => $result['message']
        ]);
    }


}
