<?php

use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

//  default endpoint API: http://api-5sia1.test/api

// route ambil semua data user
// method: GET
Route::get('/users', function () {
    // panggil semua data user dan simpan dalam variabel $user
    // method with() digunakan untuk mengikutsertakan realasi
    // realasi yang disebutkan sesuai dengan nama method pada model
    $users = User::query()->with('products')->get();
    // convert kedalam format JSON
    $json_users = json_encode($users);
    // berikan data (response) JSON ke aplikasi yang meminta (request)
    return $json_users;
});

Route::get('/products/semuanya',[ProductController::class, 'index']);

Route::get('products/cari', [ProductController::class, 'search']);

// route cari user berdasarkan id
// method: Get
Route::get('/user/find', function (Request $request) {
    // cari user
    // $user = User::find($request->id);
    $user = User::query()
        ->where('id', $request->id)
        ->with('products')
        ->get();
    // dd($user); //dump and die
    return json_encode($user);
});

// route cari user berdasarkan kemiripan nama atau email
// method: Get
Route::get('/user/search', function (Request $request) {
    // cari user berdasarkan string nama
    $users = User::where('name', 'like', '%' . $request->nama . '%')
        ->orWhere('email', 'like', '%' . $request->nama . '%')
        ->get();

    // SELECT * FROM users WHERE name OR email LIKE '%ahmad';
    return json_encode($users);
});

// Registrasi User
// Parameter name, email, phone, password
// password harus dihash sebelum disimpan ke tabel
Route::post('/register', function (Request $r) {
    // Validasi data
    try {
        $validated = $r->validate([
            // params => rules
            'nama' => 'required|max:255',
            'surel' => 'required|email|unique:users,email',
            'telp' => 'required|unique:users,phone',
            'sandi' => 'required|min:6'
        ]);
        // Tambahkan data user baru
        $new_user = User::query()->create([
            // field => params
            'name' => $r->nama,
            'email' => $r->surel,
            'phone' => $r->telp,
            'password' => Hash::make($r->sandi)
        ]);
        // return data user
        return response()->json($new_user);
    } catch (ValidationException $e) {
        return $e->validator->errors();


    }


});

// Ubah Data User
// parameter nama, surel, telp, sandi
// method `PUT` atau `PATCH`
// data user yang akan diubah dicari berdasarkan id yang dikirim
// pada contoh ini, id akan langsung diasosiasikan ke model User
Route::put('/user/edit/{user}', function (Request $r, User $user) {

    try {
        // validasi ubah data
        $validated = $r->validate([
            // params => rules
            'nama' => 'max:255',
            'surel' => 'email|unique:users,email' . $user->id,
            'telp' => 'unique:users,phone' . $user->id,
            'sandi' => 'min:6'
        ]);
        // ----------------Cara Sederhana---------------------
        // $user->update([
        //     'name' => $r->nama ?? $user->name,
        //     'email' => $r->surel ?? $user->email,
        //     'phone' => $r->telp ?? $user->phone,
        //     'password' => $r->sandi
        //                     ? Hash::make($r->sandi)
        //                     : $user->password
        // ]);

        // ----------------Cara yang kompleks---------------------
        // salin data yang diterima ke variabel baru
        $data = $r->all();
        // Jika ada data passwowrd pada array $data
        if (array_key_exists('sandi', $data)) {
            // replace isi 'sandi' engan hasil Hash 'sandi'
            $data['sandi'] = Hash::make($data['sandi']);
        }
        // ubah data user
        $user->update([
            'name' => $data['nama'] ?? $user->name,
            'email' => $data['surel'] ?? $user->email,
            'phone' => $data['telp'] ?? $user->phone,
            'password' => $data['sandi'] ?? $user->password

        ]);
        // Kembalikan data user yang user yang sudah diubah beserta pesan sukses
        return response()->json([
            'pesan' => 'Sukses diubah!',
            'user' => $user,
        ]);

    } catch (ValidationException $e) {
        return $e->validator->errors();
    }

});

Route::delete('/user/delete', function (Request $r) {
    // temukan user berdasarkan id yang dikirim
    $user = User::find($r->id);
    // respon jika user tidak ditemukan
    if (!$user)
        return response()->json([
            'pesan' => 'Gagal! User tidak ditemukan.'
        ]);

    // hapus data user
    $user->delete();
    return response()->json([
        'pesan' => 'Sukses! User berhasil dihapus.'
    ]);


});


Route::post('/login', function (Request $request) {
    try {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => true,
                'message' => 'Login Success boyyy',
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->name
                ]
            ]);
        }return response()->json([
            'success' => false,
            'message' => 'Login Gagal bree ;('
        ], 500);
    } catch (ValidationException $e) {
        return $e->validator->errors();

    }


});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');