<?php

/**
 * API Routes Reference for Database Integration
 * 
 * Location: backend/routes/api.php
 * 
 * These routes are recommendations based on the database structure implemented.
 * Create corresponding controllers and implement these endpoints for full functionality.
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');                  // [POST] /api/auth/login
    Route::post('register', 'AuthController@register');            // [POST] /api/auth/register
    Route::post('logout', 'AuthController@logout')->middleware('auth:sanctum');
    Route::get('me', 'AuthController@me')->middleware('auth:sanctum');
});

// ============================================================================
// STUDENT ROUTES
// ============================================================================

Route::group(['prefix' => 'students', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', 'StudentController@index');                   // [GET] /api/students
    Route::get('{id}', 'StudentController@show');                 // [GET] /api/students/{id}
    Route::put('{id}', 'StudentController@update');               // [PUT] /api/students/{id}
    
    // Student Registration Progress
    Route::get('{id}/registration-progress', 'StudentController@getRegistrationProgress');
    Route::post('{id}/pay-registration', 'StudentController@payRegistration');
    Route::post('{id}/upload-documents', 'StudentController@uploadDocuments');
    Route::post('{id}/select-dorm-type', 'StudentController@selectDormType');
    Route::post('{id}/select-room', 'StudentController@selectRoom');
    Route::post('{id}/pickup-key', 'StudentController@pickupKey');
    
    // Student Academic Info
    Route::get('{id}/grades', 'StudentController@getGrades');     // [GET] /api/students/{id}/grades
    Route::get('{id}/attendance', 'StudentController@getAttendance');
    Route::get('{id}/meal-logs', 'StudentController@getMealLogs');
    Route::get('{id}/classes', 'StudentController@getClasses');
});

// ============================================================================
// DORM/ASRAMA ROUTES
// ============================================================================

Route::group(['prefix' => 'dorms'], function () {
    // Get all dorm buildings and types
    Route::get('buildings', 'DormController@getBuildings');       // [GET] /api/dorms/buildings
    Route::get('types', 'DormController@getTypes');               // [GET] /api/dorms/types
    
    // Dorm Rooms
    Route::get('rooms', 'DormController@getRooms');               // [GET] /api/dorms/rooms
    Route::get('rooms/{id}', 'DormController@getRoomDetail');     // [GET] /api/dorms/rooms/{id}
    Route::get('rooms/{id}/available', 'DormController@getAvailable');
    
    // Admin only
    Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
        Route::post('buildings', 'DormController@storeBuilding');
        Route::put('buildings/{id}', 'DormController@updateBuilding');
        Route::delete('buildings/{id}', 'DormController@deleteBuilding');
        
        Route::post('rooms', 'DormController@storeRoom');
        Route::put('rooms/{id}', 'DormController@updateRoom');
        Route::delete('rooms/{id}', 'DormController@deleteRoom');
    });
});

// ============================================================================
// CAFETERIA ROUTES
// ============================================================================

Route::group(['prefix' => 'cafeteria'], function () {
    // Public endpoints
    Route::get('menus', 'CafeteriaController@getMenus');          // [GET] /api/cafeteria/menus
    Route::get('menus/{date}', 'CafeteriaController@getMenuByDate');
    Route::get('my-logs', 'CafeteriaController@getMyLogs')->middleware('auth:sanctum');
    
    // Staff Kantin
    Route::group(['middleware' => ['auth:sanctum', 'staff.kantin']], function () {
        Route::post('menus', 'CafeteriaController@storeMenu');    // [POST] /api/cafeteria/menus
        Route::put('menus/{id}', 'CafeteriaController@updateMenu');
        Route::delete('menus/{id}', 'CafeteriaController@deleteMenu');
        
        Route::post('meals/scan', 'CafeteriaController@scanMeal'); // [POST] /api/cafeteria/meals/scan
    });
    
    // Admin
    Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
        Route::get('logs', 'CafeteriaController@getAllLogs');
        Route::get('logs/student/{studentId}', 'CafeteriaController@getStudentMealLogs');
    });
});

// ============================================================================
// ATTENDANCE ROUTES
// ============================================================================

Route::group(['prefix' => 'attendance', 'middleware' => 'auth:sanctum'], function () {
    // Teachers
    Route::group(['middleware' => 'teacher'], function () {
        Route::post('/', 'AttendanceController@record');          // [POST] /api/attendance
        Route::post('/bulk', 'AttendanceController@recordBulk');
    });
    
    // Anyone can view their own
    Route::get('my-records', 'AttendanceController@getMyRecords'); // [GET] /api/attendance/my-records
    
    // Admin/Teacher
    Route::group(['middleware' => 'admin_or_teacher'], function () {
        Route::get('student/{studentId}', 'AttendanceController@getStudentAttendance');
        Route::get('class/{classId}', 'AttendanceController@getClassAttendance');
    });
});

// ============================================================================
// GRADES ROUTES
// ============================================================================

Route::group(['prefix' => 'grades', 'middleware' => 'auth:sanctum'], function () {
    // Teachers - input grades
    Route::group(['middleware' => 'teacher'], function () {
        Route::post('/', 'GradeController@store');                 // [POST] /api/grades
        Route::put('{id}', 'GradeController@update');              // [PUT] /api/grades/{id}
        Route::delete('{id}', 'GradeController@delete');
    });
    
    // Students - view own grades
    Route::get('my-grades', 'GradeController@getMyGrades');       // [GET] /api/grades/my-grades
    Route::get('my-grades/{studentId}', 'GradeController@getStudentGrades');
    
    // Admin/Teacher
    Route::group(['middleware' => 'admin_or_teacher'], function () {
        Route::get('student/{studentId}', 'GradeController@getStudentGrades');
        Route::get('subject/{subjectId}', 'GradeController@getSubjectGrades');
        Route::get('class/{classId}', 'GradeController@getClassGrades');
    });
});

// ============================================================================
// CLASSES ROUTES
// ============================================================================

Route::group(['prefix' => 'classes'], function () {
    Route::get('/', 'ClassController@index');                     // [GET] /api/classes
    Route::get('{id}', 'ClassController@show');                   // [GET] /api/classes/{id}
    Route::get('{id}/students', 'ClassController@getStudents');   // [GET] /api/classes/{id}/students
    Route::get('{id}/schedules', 'ClassController@getSchedules');
    
    // Admin/Teacher
    Route::group(['middleware' => ['auth:sanctum', 'admin_or_teacher']], function () {
        Route::post('/', 'ClassController@store');
        Route::put('{id}', 'ClassController@update');
        Route::delete('{id}', 'ClassController@delete');
        
        // Class Schedules
        Route::post('{id}/schedules', 'ClassController@addSchedule');
        Route::put('schedules/{scheduleId}', 'ClassController@updateSchedule');
        Route::delete('schedules/{scheduleId}', 'ClassController@deleteSchedule');
    });
});

// ============================================================================
// SUBJECTS ROUTES
// ============================================================================

Route::group(['prefix' => 'subjects'], function () {
    Route::get('/', 'SubjectController@index');                   // [GET] /api/subjects
    Route::get('{id}', 'SubjectController@show');
    
    // Admin
    Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
        Route::post('/', 'SubjectController@store');
        Route::put('{id}', 'SubjectController@update');
        Route::delete('{id}', 'SubjectController@delete');
    });
});

// ============================================================================
// ACADEMIC YEAR ROUTES
// ============================================================================

Route::group(['prefix' => 'academic-years', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', 'AcademicYearController@index');
    Route::get('active', 'AcademicYearController@getActive');
    
    // Admin only
    Route::group(['middleware' => 'admin'], function () {
        Route::post('/', 'AcademicYearController@store');
        Route::put('{id}', 'AcademicYearController@update');
        Route::delete('{id}', 'AcademicYearController@delete');
        Route::put('{id}/activate', 'AcademicYearController@activate');
    });
});

// ============================================================================
// ADMIN/USER MANAGEMENT ROUTES
// ============================================================================

Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'admin']], function () {
    // User Management
    Route::get('users', 'AdminController@getUsers');              // [GET] /api/admin/users
    Route::get('users/{id}', 'AdminController@getUserDetail');
    Route::post('users', 'AdminController@createUser');           // [POST] /api/admin/users
    Route::put('users/{id}', 'AdminController@updateUser');       // [PUT] /api/admin/users/{id}
    Route::delete('users/{id}', 'AdminController@deleteUser');
    
    // Role Management
    Route::get('roles', 'AdminController@getRoles');
    Route::post('roles', 'AdminController@createRole');
    Route::put('roles/{id}', 'AdminController@updateRole');
    Route::delete('roles/{id}', 'AdminController@deleteRole');
    
    // Payment Verification
    Route::get('payments/pending', 'AdminController@getPendingPayments');
    Route::post('payments/{id}/verify', 'AdminController@verifyPayment');
    
    // Document Verification
    Route::get('documents/pending', 'AdminController@getPendingDocuments');
    Route::post('documents/{id}/verify', 'AdminController@verifyDocument');
    
    // Statistics
    Route::get('stats/dashboard', 'AdminController@getDashboardStats');
    Route::get('stats/students', 'AdminController@getStudentStats');
    Route::get('stats/dorms', 'AdminController@getDormStats');
});

// ============================================================================
// ROLE MANAGEMENT PATTERNS
// ============================================================================

/**
 * Middleware yang disarankan untuk dibuat di app/Http/Middleware/:
 * 
 * 1. admin
 *    - Check: can_manage_users
 * 
 * 2. teacher
 *    - Check: can_manage_nilai OR can_manage_absensi
 * 
 * 3. staff.kantin
 *    - Check: can_scan_meal_card OR can_manage_cafeteria_menu
 * 
 * 4. staff.asrama
 *    - Check: can_manage_asrama OR can_manage_dorm_rooms
 * 
 * 5. admin_or_teacher
 *    - Check: can_manage_users OR can_manage_nilai
 * 
 * Example Middleware Implementation:
 * 
 * public function handle(Request $request, Closure $next, string $permission): Response
 * {
 *     $user = $request->user();
 *     
 *     if (!$user || !$user->role) {
 *         return response()->json(['message' => 'Unauthorized'], 401);
 *     }
 *     
 *     if (!$user->role->hasPermission($permission)) {
 *         return response()->json(['message' => 'Forbidden'], 403);
 *     }
 *     
 *     return $next($request);
 * }
 */

// ============================================================================
// ERROR HANDLING
// ============================================================================

/**
 * Example Response Format:
 * 
 * Success (200):
 * {
 *     "success": true,
 *     "message": "Operation successful",
 *     "data": {...}
 * }
 * 
 * Error (4xx/5xx):
 * {
 *     "success": false,
 *     "message": "Error message",
 *     "errors": {...}
 * }
 */
