<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('auctions')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Kategorija kreirana.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Kategorija ažurirana.');
    }

    public function destroy(Category $category)
    {
        if ($category->auctions()->exists()) {
            return back()->withErrors(['error' => 'Ne možete obrisati kategoriju koja ima aukcije.']);
        }

        $category->delete();
        return back()->with('success', 'Kategorija obrisana.');
    }
}
