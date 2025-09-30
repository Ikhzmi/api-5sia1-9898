<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // panggil semua data user dan simpan dalam variabel $user
        // method with() digunakan untuk mengikutsertakan realasi
        // realasi yang disebutkan sesuai dengan nama method pada model
        $users = User::query()->with('products')->get();
        // convert kedalam format JSON
        $json_users = json_encode($users);
        // berikan data (response) JSON ke aplikasi yang meminta (request)
        return $json_users;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
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
    }


    // cari user berdasarkan kemiripan nama atau email
    public function search(Request $request){
        // cari user berdasarkan string nama
    $users = User::where('name', 'like', '%' . $request->nama . '%')
        ->orWhere('email', 'like', '%' . $request->nama . '%')
        ->get();

    // SELECT * FROM users WHERE name OR email LIKE '%ahmad';
    return json_encode($users);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        // cari user
        // $user = User::find($request->id);
        $user = User::query()
        ->where('id', $request->id)
        ->with('products')
        ->get();
    // dd($user); //dump and die
    return json_encode($user);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    // Ubah Data User
    // parameter nama, surel, telp, sandi
    // method `PUT` atau `PATCH`
    // data user yang akan diubah dicari berdasarkan id yang dikirim
    // pada contoh ini, id akan langsung diasosiasikan ke model User
    public function update(Request $r, User $user)
    {
        try {
            // validasi ubah data
            $validated = $r->validate([
                'nama'      => 'max:255',
                'surel'     => 'email|unique:users,email,'.$user->id,
                'telp'      => 'unique:users,phone,'.$user->id,
                'sandi'     => 'min:6'
            ]);

            // ----------- cara yang sederhana
            // $user->update([
            //     'name'      => $r->nama ?? $user->name,
            //     'email'     => $r->surel ?? $user->email,
            //     'phone'     => $r->telp ?? $user->phone,
            //     'password'  => $r->sandi
            //                     ? Hash::make($r->sandi)
            //                     : $user->password
            // ]);

            // ----------- cara yang kompleks
            // salin data yang diterima ke variabel baru
            $data = $r->all();
            // jika ada data password pada array $data
            if (array_key_exists('sandi', $data)) {
                // replace isi `sandi` dengan hasil Hash `sandi`
                $data['sandi'] = Hash::make($data['sandi']);
            }

            // ubah data user
            $user->update([
                'name' => $data['nama'] ?? $user->name,
                'email'=> $data['surel'] ?? $user->email,
                'phone'=> $data['telp'] ?? $user->phone,
                'password'=> $data['sandi'] ?? $user->password
            ]);
            // ---------- berakhirnya cara yang kompleks


            // kembalikan data user yang sudah diubah beserta pesan sukses
            return response()->json([
                'pesan' => 'Sukses diubah!', 'user' => $user,
            ]);

        } catch (ValidationException $e) {
            return $e->validator->errors();
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    // Hapus Data User
    // method 'DELETE'
    // request dilakukan dengan menyertakan id user yang akan dihapus
    public function destroy(Request $r)
    {
         // temukan user berdasarkan id yang dikirim
    $user = User::find($r->id);
    // respon jika user tidak ditemukan
    if (!$user)
        return response()->json([
            'pesan' => 'Gagal! User tidak ditemukan.'
        ]);

    // hapus data user jika ada
    $user->delete();
    return response()->json([
        'pesan' => 'Sukses! User berhasil dihapus.'
    ]);
    }
}