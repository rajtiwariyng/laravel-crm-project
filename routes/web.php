<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\MergeController;

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

// Default welcome route - redirect to contacts index for this app
Route::get("/", function () {
    // return view("welcome");
    return redirect()->route("contacts.index");
});

// Contact Management Routes (AJAX focused)
Route::prefix("contacts")->name("contacts.")->group(function () {
    Route::get("/", [ContactController::class, "index"])->name("index");
    Route::get("/fetch", [ContactController::class, "fetchContacts"])->name("fetch"); // AJAX fetch/filter
    Route::get("/create-data", [ContactController::class, "create"])->name("createData"); // AJAX get data for create modal
    Route::post("/", [ContactController::class, "store"])->name("store"); // AJAX store
    Route::get("/{contact}/edit-data", [ContactController::class, "edit"])->name("editData"); // AJAX get data for edit modal
    Route::put("/{contact}", [ContactController::class, "update"])->name("update"); // AJAX update (using PUT)
    Route::delete("/{contact}", [ContactController::class, "destroy"])->name("destroy"); // AJAX delete

    // Merge Routes (AJAX)
    Route::post("/merge/prepare", [MergeController::class, "prepareMerge"])->name("merge.prepare");
    Route::post("/merge/execute", [MergeController::class, "executeMerge"])->name("merge.execute");
});


// Admin Routes for Custom Field Management
// Add appropriate middleware (e.g., auth, admin role check) in a real application
Route::prefix("admin")->name("admin.")->group(function () {
    Route::resource("custom-fields", CustomFieldController::class);
});

// Basic Auth routes if needed (can be generated with php artisan ui bootstrap --auth)
// Auth::routes();
// Route::get("/home", [App\Http\Controllers\HomeController::class, "index"])->name("home");
