<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\quest_of_test;
use App\Models\questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Controller;
use App\Models\grade;
use App\Models\level;
use App\Models\status;
use App\Models\student;
use App\Models\students;
use App\Models\subjects;
use App\Models\tests;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class Admincontroller extends Controller
{
    public function getAdmin()
    {
        $data = admin::get();
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No admin found!',
            ], 400);
        }
        return response()->json([
            'data'    => $data,
        ]);
    }

    public function getInfo($username)
    {
        $admin = Admin::select('admins.admin_id', 'admins.username', 'admins.avatar', 'admins.email', 'admins.name', 'admins.last_login', 'admins.birthday', 'permissions.permission_detail', 'genders.gender_detail', 'genders.gender_id')
            ->join('permissions', 'admins.permission', '=', 'permissions.permission')
            ->join('genders', 'admins.gender_id', '=', 'genders.gender_id')
            ->where('admins.username', '=', $username)
            ->first();
    if ($admin) {
        //đẩy view ở đây nha!!
        //return view('admin.info', ['admin' => $admin]);
        return response()->json(['admin' => $admin], 200);
    }
        return response()->json(['message' => 'Admin không tồn tại!'], 404);
    }

    public function updateProfile(Request $request)
    {
        $data['id'] = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'gender_id' => 'required',
            'birthday' => 'nullable|date',
            'password' => 'required|min:6|max:20',
            'email' => 'nullable|email|unique:admins,email,'.$data['id'].',admin_id',
        ], [
            'name.required' => 'Vui lòng nhập tên!',
            'name.min' => 'Tên cần ít nhất 3 ký tự!',
            'name.max' => 'Tên dài nhất 255 ký tự!',
            'gender_id.required' => 'Vui lòng chon giới tính!',
            'birthday.date' => 'Ngày sinh chưa đúng định dạng!',
            'password.required' => 'Vui lòng nhập mật khẩu!',
            'password.min' => 'Vui nhap it nhat 6 ky tu!',
            'email.email' => 'Vui long nhap email hop le!',
            'email.unique' => 'Email da ton tai!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $me = Admin::find($request->id);
        $me->update([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'gender_id' => $request['gender_id'],
                    'birthday' => $request['birthday'],
                    'password' => bcrypt($request['password']),
                    'last_login' => Carbon::now(CarbonTimeZone::createFromHourOffset(7 * 60))->timezone('Asia/Ho_Chi_Minh'),
                ]);
        return response()->json([
            'status' => true,
            'message' => "Cập nhập tài khoản cá nhân thành công!"
        ]);
    }
    public function updateAvatarProfile(Request $request)
    {
        $user = $request->user('admins');

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'avatar.required' => 'Vui lòng chọn hình ảnh đại diện',
            'avatar.image' => 'Vui lòng chọn hình ảnh đại diện',
            'avatar.mimes' => 'Vui lòng chọn hình ảnh đúng định dạng (jpeg, png, jpg, gif, svg)',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2048KB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = $image->store('images/admin');

        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

            $user->avatar = $path;
            $user->save();

            return response()->json(['message' => 'Tải lên thành công', 'path' => $path], 200);
        }
            return response()->json(['message' => 'Không có tệp nào được tải lên'], 404);
    }
    public function indexLogin()
    {
        return view('loginTest');
    }

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['submitLogin']]);
    // }

    public function submitLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'username' => 'required|string|exists:admins,username',
            'email'    => 'required|email',
            'password' => 'required|string|max:20|min:6',
        ], [
            // 'username.required' => 'Tên đăng nhập là bắt buộc!',
            // 'username.exists'   => 'Tên đăng nhập không tồn tại!',
            'email.required'    => 'Email là bắt buộc!',
            'email.email'       => 'Email phải là định dạng hợp lệ!',
            'password.required' => 'Mật khẩu là bắt buộc!',
            'password.min'      => 'Mật khẩu tối thiểu 6 kí tự!',
            'password.max'      => 'Mật khẩu tối đa 20 kí tự!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $result = [];

        if ($request->has('email') && $request->has('password')) {
            $email = $request->input('email');
            $password = $request->input('password');


            $admin = DB::table('admins')
                ->select('permission')
                ->where('email', $email)
                ->orWhere('email', $password)
                ->first();

            if ($admin) {
                $permission = $admin->permission;
            }

            $token  = Auth::guard('api')->attempt([
                'email'    => $email,
                'password'    => $password,
            ]);
            // dd($token);
            if ($token) {
                return response()->json([
                    'result' =>  "Đăng nhập thành công",
                    'access_token' => $token,
                    'permission' => $permission,
                    'expires_in' => JWTAuth::factory()->getTTL() * 6000
                ]);
            } else {
                return response()->json([
                    'mesage' =>  "Tài khoản hoặc mật khẩu không đúng!",
                ], 403);
            }
        }
    }


    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        return redirect('api/admin/login');
    }


    public function check_add_admin_via_file(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:6|max:50',
            'username' => 'required|string|min:6|max:50|unique:admins,username',
            'email' => 'nullable|email|unique:admins,email',
            'password' => 'required|string|min:6|max:20',
            'birthday' => 'nullable|date',
            'gender' => 'required|string|in:Nam,Nữ,Khác',
            'permission' => 'nullable|string',
            'file' => 'required|file|mimes:xlsx',
        ], [
            'name.min' => 'Tên Admin tối thiểu 6 kí tự!',
            'name.required' => 'Tên Admin không được để trống!',
            'username.required' => 'Username không được để trống!',
            'username.unique' => 'Username đã tồn tại!',
            'password.required' => 'Password không được để trống!',
            'password.min' => 'Password tối thiểu 6 kí tự!',
            'email.email' => 'Email không đúng định dạng!',
            'email.unique' => 'Email đã được sử dụng!',
            'birthday.date' => 'Ngày Sinh phải là một ngày hợp lệ!',
            'file.required' => 'Vui lòng chọn tệp để tiếp tục.',
            'file.mimes' => 'Chỉ chấp nhận tệp với định dạng xlsx.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $result = [];

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $count = 0;
            $errList = [];

            foreach ($sheetData as $key => $row) {
                if ($key < 4) {
                    continue;
                }

                if (empty($row['A'])) {
                    continue;
                }

                $name = $row['B'];
                $username = $row['C'];
                $email = $row['D'];
                $password = bcrypt($row['E']);
                $birthday = $row['F'];
                $gender = ($row['G'] == 'Nam') ? 2 : (($row['G'] == 'Nữ') ? 3 : 1);
                $admin = new Admin([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'birthday' => $birthday,
                    'gender_id' => $gender,
                    'last_login' => now(),
                ]);

                if ($admin->saveQuietly()) {
                    $count++;
                } else {
                    $errList[] = $row['A'];
                }
            }

            unlink($filePath);

            if (empty($errList)) {
                $result['status_value'] = "Thêm thành công " . $count . " tài khoản!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm tài khoản có STT: " . implode(', ', $errList) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Không tìm thấy tệp được tải lên!";
            $result['status'] = 0;
        }

        return response()->json($result);
        // return response()->json([
        //     'result' => $result,
        // ]);
    }

    public function indexAdmin()
    {
        return view('admin.CRUD');
    }
    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|min:3|max:50|unique:admins,name',
            'username'      => 'required|string|min:6|max:50|unique:admins,username',
            'gender_id'     => 'required|integer',
            'password'      => 'required|string|min:6|max:20',
            'email'         => 'nullable|email|unique:admins,email',
            'permission'    => 'nullable',
            'birthday'      => 'nullable|date',
        ], [
            'name.min'           => 'Tên Admin tối thiểu 3 kí tự!',
            'name.max'            => 'Ten Admin dài nhất 50 ký tự!',
            'name.unique'         => 'Ten Admin đã tồn tại!',
            'name.required'         => 'Tên Admin không được để trống!',
            'username.required'     => 'Username không được để trống!',
            'username.unique'       => 'Username đã tồn tại!',
            'password.required'     => 'Password không được để trống!',
            'password.min'          => 'Password tối thiểu 6 kí tự!',
            'email.email'           => 'Email không đúng định dạng!',
            'email.unique'          => 'Email đã được sử dụng!',
            'birthday.date'         => 'Ngày Sinh phải là một ngày hợp lệ!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $password = bcrypt($request->password);
       $admin = admin::create(array_merge(request()->all(),$password));
        return response()->json([
            'message'   => 'Thêm Admin thành công!',
            'admin'   => $admin,
        ]);
    }

    public function deleteAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,admin_id'
        ], [
            'admin_id.*' => 'Admin không tồn tại!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $admin = admin::find($request->id);

        if (!$admin) {
            return response()->json([
                'message'   => 'Admin không tồn tại!'
            ], 400);
        }
        $admin->delete();
        return response()->json([
            'message'   => 'Xóa Admin thành công!',
            "admin" => $admin
        ]);
    }


    public function updateAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'sometimes|exists:admins,admin_id',
            'name' => 'sometimes|string|min:6|max:50',
            'gender_id' => 'sometimes|integer',
            'birthday' => 'sometimes|date',
            'password' => 'sometimes|string|min:6|max:20',
        ], [
            'admin_id.required' => 'Admin không được để trống!',
            'admin_id.exists' => 'Admin không tồn tại!',
            'name.min' => 'Tên Admin tối thiểu 6 kí tự!',
            'name.required' => 'Tên Admin không được để trống!',
            'gender_id.required' => 'Giới tính không được để trống!',
            'birthday.date' => 'Ngày Sinh phải là một ngày hợp lệ!',
            'password.min' => 'Mật khẩu tối thiểu 6 kí tự!',
            'password.max' => 'Mật khẩu không được quá 20 kí tự!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $admin = Admin::find($request->admin_id);
        $data = $request->only(['name', 'username', 'gender_id', 'birthday', 'password', 'permission',]);

        if (!$admin) {
            return response()->json([
                'status'    => false,
                'message'   => 'Tài khoản không tồn tại!'
            ], 400);
        } else if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $admin->fill($data)->save();

        return response()->json([
            'message'   => 'Cập nhật thông tin thành công!',
            'admin'   => $admin,
        ]);
    }

    public function getQuestion()
    {
        $data = questions::with('teacher')->get();
        if (!$data) return response()->json([
            'message' => 'No question found!',
        ], 400);
        return response()->json([
            'data' => $data,
        ]);
    }

    public function getLevels()
    {
        $level = level::get();

        return response()->json([
            'level' => $level,
        ]);
    }

    public function getGrades()
    {
        $grade = grade::get();

        return response()->json([
            'grade' => $grade,
        ]);
    }

    public function getStatus()
    {
        $status = status::get();

        return response()->json([
            'status' => $status,
        ]);
    }

    public function getSubjects()
    {
        $subjects = subjects::get();

        return response()->json([
            'subjects' => $subjects,
        ]);
    }


    public function checkAddQuestionViaFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id'        => 'required|integer|exists:subjects,subject_id',
            'question_content'  => 'required|string',
            'level_id'          => 'required|integer|exists:levels,level_id',
            'answer_a'          => 'required|string',
            'answer_b'          => 'required|string',
            'answer_c'          => 'required|string',
            'answer_d'          => 'required|string',
            'correct_answer'    => 'required|string|in:A,B,C,D',
            'grade_id'          => 'required|integer|exists:grades,grade_id',
            'unit'              => 'required|string',
            'suggest'           => 'nullable|string',
            'status_id'         => 'required|integer|in:1,2,3',
            'teacher_id'        => 'nullable|integer|exists:teachers,teacher_id',
            'file' => 'required|file|mimes:xlsx',
        ], [
            'subject_id.required'          => 'Mã môn học không được để trống!',
            'subject_id.exists'            => 'Mã môn học không tồn tại!',
            'question_content.required'    => 'Nội dung câu hỏi không được để trống!',
            'level_id.required'            => 'Mã cấp độ không được để trống!',
            'level_id.exists'              => 'Mã cấp độ không tồn tại!',
            'answer_a.required'            => 'Câu trả lời A không được để trống!',
            'answer_b.required'            => 'Câu trả lời B không được để trống!',
            'answer_c.required'            => 'Câu trả lời C không được để trống!',
            'answer_d.required'            => 'Câu trả lời D không được để trống!',
            'correct_answer.required'      => 'Câu trả lời đúng không được để trống!',
            'correct_answer.in'            => 'Câu trả lời đúng phải là A, B, C hoặc D!',
            'grade_id.required'            => 'Mã khối học không được để trống!',
            'grade_id.integer'             => 'Mã khối học phải là số nguyên!',
            'grade_id.exists'              => 'Mã khối học không tồn tại!',
            'unit.required'                => 'Đơn vị không được để trống!',
            'suggest.string'                => 'Gợi ý phải là chuỗi!',
            'status_id.required'           => 'Trạng thái không được để trống!',
            'status_id.in'                 => 'Trạng thái không hợp lệ!',
            'teacher_id.exists'            => 'Mã giáo viên không tồn tại!',
            'file.required'                => 'Vui lòng chọn tệp để tiếp tục!',
            'file.mimes'                   => 'File phải là xlsx!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $result = [];

        $subjectId = $request->subject_id;
        // $subjectId = 10;
        $inputFileType = 'Xlsx';
        $count = 0;
        $errList = [];

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            foreach ($sheetData as $key => $row) {
                if ($key < 4 || empty($row['A'])) {
                    continue;
                }

                $answers = [];
                $stt = $row['A'];
                $questionContent = $row['B'];
                $levelId = $row['C'];
                $answerA = $row['D'];
                $answerB = $row['E'];
                $answerC = $row['F'];
                $answerD = $row['G'];
                $correctAnswer = $row['H'];
                $gradeId = $row['I'];
                $unit = $row['J'];
                $suggest = $row['K'];
                $teacherId = null;
                switch ($correctAnswer) {
                    case "A":
                        $answer = $answerA;
                        break;
                    case "B":
                        $answer = $answerB;
                        break;
                    case "C":
                        $answer = $answerC;
                        break;
                    default:
                        $answer = $answerD;
                }

                if (!empty($questionContent) && $teacherId == null) {
                    $question = new questions([
                        'subject_id' => $subjectId,
                        'question_content' => $questionContent,
                        'level_id' => $levelId,
                        'answer_a' => $answerA,
                        'answer_b' => $answerB,
                        'answer_c' => $answerC,
                        'answer_d' => $answerD,
                        'correct_answer' => $answer,
                        'grade_id' => $gradeId,
                        'unit' => $unit,
                        'suggest' => $suggest,
                        'status_id' => 3,
                        'teacher_id' => $teacherId,
                    ]);

                    // Lưu câu hỏi vào cơ sở dữ liệu
                    if ($question->saveQuietly()) {
                        $count++;
                    } else {
                        $errList[] = $stt;
                    }
                }
            }

            unlink($filePath);

            if (empty($errList)) {
                $result['status_value'] = "Thêm thành công " . $count . " câu hỏi!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm câu hỏi có STT: " . implode(', ', $errList) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Không tìm thấy tệp được tải lên!";
            $result['status'] = 0;
        }
        return response()->json($result);
    }

    public function checkAddQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id'        => 'required|integer|exists:subjects,subject_id',
            'question_content'  => 'required|string',
            'grade_id'          => 'required|integer|exists:grades,grade_id',
            'level_id'          => 'required|integer|exists:levels,level_id',
            'unit'              => 'required|string',
            'answer_a'          => 'required|string',
            'answer_b'          => 'required|string',
            'answer_c'          => 'required|string',
            'answer_d'          => 'required|string',
            'correct_answer'    => 'required|in:A,B,C,D',
            'status_id'         => 'required|integer|in:1,2,3',
            'suggest'           => 'nullable|string',
        ], [
            'subject_id.required'           => 'Mã môn học không được để trống!',
            'subject_id.exists'             => 'Mã môn học không tồn tại!',
            'question_content.required'     => 'Nội dung câu hỏi không được để trống!',
            'grade_id.required'             => 'Mã khối học không được để trống!',
            'grade_id.exists'               => 'Mã khối học không tồn tại!',
            'level_id.required'             => 'Mã cấp độ không được để trống!',
            'level_id.exists'               => 'Mã cấp độ không tồn tại!',
            'unit.required'                 => 'Đơn vị không được để trống!',
            'answer_a.required'             => 'Câu trả lời A không được để trống!',
            'answer_b.required'             => 'Câu trả lời B không được để trống!',
            'answer_c.required'             => 'Câu trả lời C không được để trống!',
            'answer_d.required'             => 'Câu trả lời D không được để trống!',
            'correct_answer.required'       => 'Câu trả lời đúng không được để trống!',
            'correct_answer.in'             => 'Câu trả lời đúng phải là A, B, C hoặc D!',
            'status_id.required'            => 'Trạng thái không được để trống!',
            'status_id.in'                  => 'Trạng thái không hợp lệ!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = request()->only(['question_content', 'level_id', 'answer_a', 'answer_b', 'subject_id', 'answer_c', 'answer_d', 'correct_answer', 'grade_id', 'unit', 'suggest', 'status_id', 'teacher_id']);
        $question =  questions::create($data);
        return response()->json(['question' => $question]);
    }


    public function updateQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id'       => 'required|integer|exists:questions,question_id',
            'question_content'  => 'nullable|string',
            'level_id'          => 'nullable|integer|exists:levels,level_id',
            'answer_a'          => 'nullable|string',
            'answer_b'          => 'nullable|string',
            'answer_c'          => 'nullable|string',
            'answer_d'          => 'nullable|string',
            'correct_answer'    => 'nullable|in:A,B,C,D',
            'grade_id'          => 'nullable|integer|exists:grades,grade_id',
            'unit'              => 'nullable|string',
            'suggest'           => 'nullable|string',
            'status_id'         => 'nullable|integer|in:1,2,3',
            'teacher_id'        => 'nullable|integer|exists:teachers,teacher_id',
        ], [
            'question_id.required'      => 'ID câu hỏi là bắt buộc!',
            'question_id.exists'        => 'Không tìm thấy câu hỏi với ID đã chọn!',
            'level_id.exists'           => 'Không tìm thấy level với ID đã chọn!',
            'grade_id.exists'           => 'Không tìm thấy grade với ID đã chọn!',
            'status_id.in'              => 'Cấp độ không được để trống!',
            'teacher_id.integer'        => 'Teacher ID phải là số nguyên!',
            'teacher_id.exists'         => 'Không tìm thấy giáo viên với ID đã chọn!',
            'correct_answer.in'         => 'Đáp án đúng phải là A, B, C hoặc D!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $question = questions::find(request()->question_id);
        if (empty($question)) {
            return response()->json(["message" => "Không tìm thấy câu hỏi!"], 400);
        }
        // $validator = Validator::make($request->all(), [
        //     'question_content' => 'string|max:255',
        //     'level_id' => 'numeric',
        //     'answer_a' => 'string',
        //     'answer_b' => 'string',
        //     'answer_c' => 'string',
        //     'answer_d' => 'string',
        //     'correct_answer' => 'numeric',
        //     'grade_id' => 'numeric',
        //     'unit' => 'numeric',
        //     'suggest' => 'string',
        //     'status_id' => 'numeric',
        //     'teacher_id' => 'numeric',
        //     'subject_id' => 'numeric',
        // ]);
        // if ($validator->fails()) {
        //     return response($validator->errors()->all(), 400);
        // }
        $data = $request->only(['question_content', 'level_id', 'answer_a', 'answer_b', 'subject_id', 'answer_c', 'answer_d', 'correct_answer', 'grade_id', 'unit', 'suggest', 'status_id', 'teacher_id']);
        $question->fill($data);
        $question->fill($data)->save();
        return response()->json(["question" => $question]);
    }



    public function deleteQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,question_id'
        ], [
            'question_id.exists' => 'Câu hỏi có vẻ không tồn tại!',
            'question_id.required' => 'ID câu hỏi là bắt buộc!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $question_id = $request->question_id;
        $question = questions::find($question_id);

        if (!$question) {
            return response()->json([
                'status'    => false,
                'message'   => 'Xoá câu hỏi không thành công!',
            ]);
        }
        $question->delete();
        return response()->json([
            'status'    => true,
            'message'   => 'Xoá câu hỏi thành công!',
        ]);
        // return response()->json($question_id);
    }


    public function checkAddTest(Request $request)
    {

        $result = [];

        $testName   = $request->test_name;
        $password   = bcrypt($request->password);
        $gradeId    = $request->grade_id;
        $subjectId  = $request->subject_id;
        $levelId    = $request->level_id;
        $totalQuestions = $request->total_questions;
        $questionEasy = $request->question_easy;
        $questionAverage = $request->question_average;
        $questionDifficult = $request->question_difficult;
        $timeToDo   = $request->time_to_do;
        $note       = $request->note;

        $testCode   = rand(100000, 999999);
        $teacher    = new admin();
        $total      = $teacher->getCountQuestions($subjectId, $gradeId);

        if (empty($testName) || empty($timeToDo) || empty($password)) {
            $result['status_value'] = "Không được bỏ trống các trường nhập!";
            $result['status'] = 0;
        } else {
            if ($totalQuestions != null) {
                if ($totalQuestions > $total->question_count) {
                    $result['status_value'] = "Số lượng câu hỏi môn " . $total->subject_detail . " " . $total->grade_detail . " không đủ! Vui lòng nhập số lượng tối đa " . $total->question_count . " câu hỏi!";
                    $result['status'] = 0;
                    if ($total->question_count == 0) {
                        $result['status_value'] = "Không có câu hỏi nào trong ngân hàng câu hỏi cho môn " . $total->subject_detail . " " . $total->grade_detail . "!";
                    }
                } else {
                    $test = new Tests([
                        'test_name' => $testName,
                        'password' => $password,
                        'subject_id' => $subjectId,
                        'grade_id' => $gradeId,
                        'level_id' => $levelId,
                        'total_questions' => $totalQuestions,
                        'time_to_do' => $timeToDo,
                        'note' => $note,
                        'status_id' => 3,
                    ]);
                    $test->saveQuietly();

                    if ($test) {
                        $result['status_value'] = "Thêm thành công!";
                        $result['status'] = 1;

                        $adminModel = new Admin();
                        $limit = $adminModel->calculateQuestionLevel($totalQuestions, $levelId);
                        foreach ($limit as $levelId => $limitQuest) {
                            $listQuest = $adminModel->getListQuestByLevel($gradeId, $subjectId, $levelId, $limitQuest);
                            foreach ($listQuest as $quest) {
                                $adminModel->addQuestToTest($test->test_code, $quest->question_id);
                            }
                        }
                    } else {
                        $result['status_value'] = "Thêm thất bại!";
                        $result['status'] = 0;
                    }
                }
            } else {
                $totalQuestions = $questionEasy + $questionAverage + $questionDifficult;
                if ($totalQuestions > $total->question_count) {
                    $result['status_value'] = "Số lượng câu hỏi môn " . $total->subject_detail . " " . $total->grade_detail . " không đủ! Vui lòng nhập số lượng tối đa " . $total->question_count . " câu hỏi!";
                    $result['status'] = 0;
                    if ($total->question_count == 0) {
                        $result['status_value'] = "Không có câu hỏi nào trong ngân hàng câu hỏi cho môn " . $total->subject_detail . " " . $total->grade_detail . "!";
                    }
                } else {
                    $test = new Tests([
                        'test_name' => $testName,
                        'password' => $password,
                        'subject_id' => $subjectId,
                        'grade_id' => $gradeId,
                        'level_id' => $levelId,
                        'total_questions' => $totalQuestions,
                        'time_to_do' => $timeToDo,
                        'note' => $note,
                        'status_id' => 3,
                    ]);
                    $test->saveQuietly();
                    if ($test) {
                        $result['status_value'] = "Thêm thành công!";
                        $result['status'] = 1;
                        //Tạo bộ câu hỏi cho đề thi
                        $adminModel = new admin();
                        $limit = $adminModel->caculatorQuestionNormal($questionEasy, $questionAverage, $questionDifficult);
                        foreach ($limit as $levelId => $limitQuest) {
                            $listQuest = $adminModel->getListQuestByLevel($gradeId, $subjectId, $levelId, $limitQuest);
                            foreach ($listQuest as $quest) {
                                $adminModel->addQuestToTest($test->test_code, $quest->question_id);
                            }
                        }
                    } else {
                        $result['status_value'] = "Thêm thất bại!";
                        $result['status'] = 0;
                    }
                }
            }
        }
        return response()->json([
            'result' => $result,

        ]);
    }
    public function getTest()
    {
        $data = tests::get();
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No test found!',
            ], 400);
        }
        return response()->json([
            'data'    => $data,
        ]);
    }
      public function getTestDetail( $test_code)
    {
        $data = tests::find($test_code)->questions()->get();
        return response()->json([
            'data'    => $data,
        ]);
    }


    public function addTest(Request $request)
    {
        $result   = [];
        $student  = new student();
        $testCode = $request->test_code;
        $password = md5($request->password);
        $check = Auth::guard('apiStudents')->user();
        $id = $check->student_id;
        if ($password != $student->getTest($testCode)->password) {
            $result['status_value'] = "Sai mật khẩu";
            $result['status'] = 0;
        } else {
            $listQuest =  $student->getQuestOfTest($testCode);
            if ($listQuest !== null) {
                foreach ($listQuest as $quest) {
                    $array = array();
                    $array[0] = $quest->answer_a;
                    $array[1] = $quest->answer_b;
                    $array[2] = $quest->answer_c;
                    $array[3] = $quest->answer_d;
                    $ID = rand(1, time()) + rand(100000, 999999);
                    $time = $student->getTest($testCode)->time_to_do . ':00';
                    if (is_array($array) && count($array) >= 4) {
                        $student->addStudentQuest($id, $ID, $testCode, $quest->question_id, $array[0], $array[1], $array[2], $array[3]);
                    } else {
                        $result['status_value'] = "Không có đáp án";
                        $result['status'] = 0;
                    }
                    $student->updateStudentExam($testCode, $time, $id);
                }
                $result['status_value'] = "Thành công. Chuẩn bị chuyển trang!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Không có câu hỏi cho bài kiểm tra này";
                $result['status'] = 0;
            }
        }


        return response()->json([
            'result' => $result,
        ]);
    }
}
