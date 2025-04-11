<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\File;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::prefix('public')->group(function () {
    Route::get('/products', [PublicProductController::class, 'index']);
    Route::get('/products/{id}', [PublicProductController::class, 'show']);
});
Route::middleware('auth:sanctum')->get('/products', [ProductController::class, 'index']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('roles');
});
Route::prefix('auth')->middleware('auth:sanctum')->group(function () { 
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::get('/categories', [CategoryController::class, 'index']);
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});

/*
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'stats']);
    Route::apiResource('/products', AdminProductController::class);
    Route::apiResource('/users', UserController::class);
    Route::get('/stats', [AdminDashboardController::class, 'stats']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::get('/orders', [OrderController::class, 'adminIndex']);
});
// routes/api.php
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('products.show');

    Route::get('/storage/{path}', function ($path) {
        $path = storage_path('app/public/' . $path);
        
        if (!File::exists($path)) {
            abort(404);
        }
    
        return response()->file($path);
    })->where('path', '.*');
   
Route::prefix('subscription')->group(function () {
    Route::post('/create', [SubscriptionController::class, 'createSubscription'])
         ->middleware('auth:sanctum');
    
    Route::post('/webhook', [SubscriptionController::class, 'handleWebhook']);
});

