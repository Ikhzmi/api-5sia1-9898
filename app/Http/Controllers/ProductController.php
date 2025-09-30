<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Tampilkan semua data produk.
     * Beserta pemiliknya (user)
     */
    public function index()
    {
        // untuk memanggil relasi terkait, sebutkan
        // nama method relasi yang ada di model tersebut
        // Gunakan  method with() untuk menyertakn relasi table
        // pada data yang di panggil
        $products = Product::query()
            ->where('is_available', true)
            ->with('user')
            ->get();
        // Format respon ada status (sukses/gagal) dan data
        return response()->json([
            'status' => 'Sukses',
            'data' => $products
        ]);
    }

    /**
     * Cari produk berdasarkan 'name'
     * dan ikutkan relasinya
     */
    public function search(Request $req)
    {
        // valdasi minimal 3 huruf untuk pencarian
        try {
            $validated = $req->validate([
                'teks' => 'required|min:3'
            ], [
                // Pesan Error Custom
                'teks.required' => ':Attribute jangan dikosongkan lah!',
                'teks.min' => ':Ini :attribute kurang Bos!!',
            ], [
                // custom attributes
                'teks' => 'huruf'
            ]);

            // proses pencarian produk berdasarkan teks yang akan dikirim
            $products = Product::query()
                ->where('name', 'like', '%' . $req->teks . '%')
                ->with('user')
                ->get();
            // Format respon ada status (sukses/gagal) dan data
            return response()->json([
                'status' => 'Sukses',
                'data' => $products
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                'pesan' => 'Gagal!',
                'data' => $ex->getMessage(),
            ], );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_available' => 'nullable|boolean',
                'stock' => 'nullable|integer|min:0',
                'price' => 'nullable|integer|min:0',
                'description' => 'nullable|string'
            ], [
                'name.required' => 'Nama produk wajib diisi!',
                'name.max' => 'Nama produk maksimal 255 karakter!',
                'image.image' => 'File harus berupa gambar!',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif!',
                'image.max' => 'Ukuran gambar maksimal 2MB!',
                'user_id.exists' => 'User tidak ditemukan!',
                'stock.min' => 'Stok tidak boleh kurang dari 0!',
                'price.min' => 'Harga tidak boleh kurang dari 0!'
            ]);

            // Handle upload gambar jika ada
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // Buat produk baru
            $product = Product::create([
                'user_id' => $validated['user_id'] ?? auth()->id(),
                'name' => $validated['name'],
                'image_path' => $imagePath,
                'is_available' => $validated['is_available'] ?? true,
                'stock' => $validated['stock'] ?? 1,
                'price' => $validated['price'] ?? 0,
                'description' => $validated['description'] ?? null,
            ]);

            // Load relasi user
            $product->load('user');

            return response()->json([
                'status' => 'Sukses',
                'pesan' => 'Produk berhasil ditambahkan!',
                'data' => $product
            ], 201);
        } catch (ValidationException $ex) {
            return response()->json([
                'status' => 'Gagal',
                'pesan' => 'Validasi gagal!',
                'errors' => $ex->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('user')->find($id);

        if (!$product) {
            return response()->json([
                'status' => 'Gagal',
                'pesan' => 'Produk tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'status' => 'Sukses',
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'Gagal',
                'pesan' => 'Produk tidak ditemukan!'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'name' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_available' => 'nullable|boolean',
                'stock' => 'nullable|integer|min:0',
                'price' => 'nullable|integer|min:0',
                'description' => 'nullable|string'
            ], [
                'name.max' => 'Nama produk maksimal 255 karakter!',
                'image.image' => 'File harus berupa gambar!',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif!',
                'image.max' => 'Ukuran gambar maksimal 2MB!',
                'user_id.exists' => 'User tidak ditemukan!',
                'stock.min' => 'Stok tidak boleh kurang dari 0!',
                'price.min' => 'Harga tidak boleh kurang dari 0!'
            ]);

            // Handle upload gambar baru
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $validated['image_path'] = $request->file('image')->store('products', 'public');
            }

            // Update produk
            $product->update(array_filter($validated, function ($value) {
                return $value !== null;
            }));

            // Load relasi user
            $product->load('user');

            return response()->json([
                'status' => 'Sukses',
                'pesan' => 'Produk berhasil diupdate!',
                'data' => $product
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                'status' => 'Gagal',
                'pesan' => 'Validasi gagal!',
                'errors' => $ex->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $r)
    {
        $product = Product::find($r->id);

        if (!$product) {
            return response()->json([
                'status' => 'Gagal',
                'pesan' => 'Produk tidak ditemukan!'
            ], 404);
        }

        // Hapus gambar jika ada
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'status' => 'Sukses',
            'pesan' => 'Produk berhasil dihapus.'
        ]);
    }
}