<?php

namespace App\Http\Controllers;

use App\Models\TransactionCategory;
use Illuminate\Http\Request;

class TransactionCategoryController extends Controller
{
    public function index()
    {
        $categories = TransactionCategory::orderBy('type')->get();
        return view('backoffice.transactions.categories.index', compact('categories'));
    }
    public function create()
    {
        return view('backoffice.transactions.categories.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate(
            [
                'type' => ['required', 'in:income,expense'],
                'category' => ['required', 'string', 'max:100'],
            ],
            [
                'type.required' => 'Jenis kategori wajib dipilih',
                'type.in' => 'Jenis kategori tidak valid',
                'category.required' => 'Nama kategori wajib diisi',
                'category.max' => 'Nama kategori maksimal 100 karakter',
            ]
        );

        // 2. Generate slug
        $slug = Str()->slug($validated['category']);

        // 3. Cegah duplikat kategori (per user + type)
        $exists = TransactionCategory::where('type', $validated['type'])
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['category' => 'Kategori sudah tersedia'])
                ->withInput();
        }

        // 4. Simpan kategori
        TransactionCategory::create([
            'name' => $validated['category'],
            'slug' => $slug,
            'type' => $validated['type'],
        ]);

        // 5. Redirect sukses
        return redirect()
            ->route('transactions-categories.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }
    public function edit($id)
    {
        $category = TransactionCategory::findOrFail($id);

        return view('backoffice.transactions.categories.edit', compact('category'));
    }
    public function update(Request $request, $id)
    {
        // 1. Ambil kategori milik user login
        $category = TransactionCategory::where('id', $id)
            ->firstOrFail();

        // 2. Validasi input
        $validated = $request->validate(
            [
                'type' => ['required', 'in:income,expense'],
                'category' => ['required', 'string', 'max:100'],
            ],
            [
                'type.required' => 'Jenis kategori wajib dipilih',
                'type.in' => 'Jenis kategori tidak valid',
                'category.required' => 'Nama kategori wajib diisi',
                'category.max' => 'Nama kategori maksimal 100 karakter',
            ]
        );

        // 3. Generate slug baru
        $slug = Str()->slug($validated['category']);

        // 4. Cegah duplikat (exclude diri sendiri)
        $exists = TransactionCategory::where('type', $validated['type'])
            ->where('slug', $slug)
            ->where('id', '!=', $category->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['category' => 'Kategori dengan nama ini sudah ada'])
                ->withInput();
        }

        // 5. Update data
        $category->update([
            'name' => $validated['category'],
            'slug' => $slug,
            'type' => $validated['type'],
        ]);

        // 6. Redirect sukses
        return redirect()
            ->route('transactions-categories.index')
            ->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy($id)
    {

        $category = TransactionCategory::findOrFail($id);

        $category->delete();

        return redirect()
            ->route('transactions-categories.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
