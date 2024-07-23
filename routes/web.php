<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Admin\PostController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/print-lckb/{monthYear}', [PrintController::class, 'document']);
Route::get('/preview-lckb/{monthYear}/{user_id}', [PrintController::class, 'preview']);

Route::get('/logout_all', function () {
    \App\Models\User::each(function ($u) {
        Auth::login($u);
        Auth::logout();
    });
});

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/get-password', function () {
    return bcrypt('12345678');
});

Route::get('/all-users', function () {
    $users = \App\Models\User::all()->pluck('nip_name')->sort();
    // $users = $users->toArray();
    // return $users;

    $html = '';
    foreach ($users as $user) {

        $bod = substr($user, 0, 8);
        $dob = DateTime::createFromFormat('Ymd', $bod);
        // return $ymd;
        $today   = new DateTime('today');
        $year = $dob->diff($today)->y;
        $month = $dob->diff($today)->m;
        $day = $dob->diff($today)->d;

        $tgllahir = $year." tahun"." ".sprintf('%02d', $month)." bulan"." ".sprintf('%02d', $day)." hari";

        $html .= $tgllahir . '    -    ' .$user . '<br>';
    }

    return $html;
});

Route::middleware('auth')->group(function () {

    Route::post('/api/post/status/switch', function (Request $request) {


        $data = $request->input();
        $message = '';
        $success = false;




        try {

            $user = Auth::user();
            if(Hash::check($data['password'], $user->password)) {
                if (isset($request->switch_status)) {
                    $post = \App\Models\Post::find($data['id_post']);
                    $post->status = $data['string_status'];
                    // if($request->string_status == 'published') {
                    //     $post->editor = Auth::user()->name;
                    // }
                    $post->save();
                    $success = true;
                }
            }

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()
            ->json(['success' => $success, 'message' => $message]);
    })->name('post.status.switch');

    Route::get('/api/master', [MasterController::class, 'index']);

    Route::get('/api/reports', [ReportController::class, 'index']);

    Route::get('/api/stats/all', [DashboardController::class, 'all']);

    Route::get('/api/stats/reports', [DashboardController::class, 'reports']);
    Route::get('/api/fusers', [UserController::class, 'fetch']);

    Route::get('/api/fetch-orgs', [OrganizationController::class, 'fetch']);
    Route::get('/api/orgs', [OrganizationController::class, 'index']);
    Route::post('api/orgs', [OrganizationController::class, 'store']);
    Route::put('api/orgs/{org}', [OrganizationController::class, 'update']);
    Route::delete('/api/orgs/{org}', [OrganizationController::class, 'destroy']);
    Route::delete('/api/orgs', [OrganizationController::class, 'bulkDelete']);


    Route::get('/api/users', [UserController::class, 'index']);
    Route::post('api/users', [UserController::class, 'store']);
    Route::put('api/users/{user}', [UserController::class, 'update']);
    Route::delete('/api/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/api/users/{user}/change-role', [UserController::class, 'changeRole']);
    Route::delete('/api/users', [UserController::class, 'bulkDelete']);

    Route::get('/api/reports', [ReportController::class, 'index']);
    Route::post('/api/reports/create', [ReportController::class, 'store']);
    Route::get('/api/reports/{work}/edit', [ReportController::class, 'edit']);
    Route::put('/api/reports/{work}/edit', [ReportController::class, 'update']);
    Route::delete('/api/reports/{work}', [ReportController::class, 'destroy']);

    Route::delete('/api/parent-reports/{report}', [ReportController::class, 'destroyParent']);

    Route::get('/api/posts', [PostController::class, 'index']);
    Route::post('/api/posts/create', [PostController::class, 'store']);
    Route::get('/api/posts/{post}/edit', [PostController::class, 'edit']);
    Route::put('/api/posts/{post}/edit', [PostController::class, 'update']);
    Route::delete('/api/posts/{post}', [PostController::class, 'destroy']);

    Route::get('/api/settings', [SettingController::class, 'index']);
    Route::post('/api/settings', [SettingController::class, 'update']);

    Route::get('/api/profile', [ProfileController::class, 'index']);
    Route::put('/api/profile', [ProfileController::class, 'update']);
    Route::post('/api/upload-profile-image', [ProfileController::class, 'uploadImage']);
    Route::post('/api/change-user-password', [ProfileController::class, 'changePassword']);
});

Route::get('{view}', ApplicationController::class)->where('view', '(.*)')->middleware('auth');
