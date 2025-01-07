<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:blog_categories|max:255',
        ]);

        BlogCategory::create([
            'name' => $request->name,
            'status' => 1,
            'click_count' => 0,
        ]);

        $result = $this->renderList();

        return response()->json([
            'message' => translate('Category added successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:blog_categories,name,' . $id . '|max:255',
        ]);

        $category = BlogCategory::findOrFail($id);
        $category->update([
            'name' => $request->name,
        ]);

        $result = $this->renderList();

        return response()->json([
            'message' => translate('Category updated successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function status($id, Request $request)
    {
        $category = BlogCategory::findOrFail($id);
        $category->update(['status' => $request->status]);

        $result = $this->renderList();

        return response()->json([
            'message' => translate('Status updated successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function delete($id, Request $request)
    {
        BlogCategory::findOrFail($id)->delete();

        $search = $request->input('search');
        $result = $this->renderList($search);

        return response()->json([
            'message' => translate('Category deleted successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');

        $result = $this->renderList($search);

        return response()->json([
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    private function renderList($search = null)
    {
        $categoryPriority =  helpers::get_business_settings('blog_category_priority_type') ?? 'latest';

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $categories = BlogCategory::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when($categoryPriority == 'latest', function ($query) {
                $query->latest();
            })
            ->when($categoryPriority == 'popularity', function ($query) {
                $query->orderBy('click_count', 'DESC');
            })
            ->when($categoryPriority == 'a_to_z', function ($query) {
                $query->orderBy('name', 'ASC');
            })
            ->when($categoryPriority == 'z_to_a', function ($query) {
                $query->orderBy('name', 'DESC');
            })
            ->when(!in_array($categoryPriority, $allowedPriorities), function ($query) {
                // Default to latest if priority is invalid
                $query->latest();
            })
            ->get();

        $html =  view('admin-views.blog.category.partials.table-rows', compact('categories'))->render();

        return [
            'html' => $html,
            'count' => $categories->count(),
        ];

    }

    public function countIncrement($id)
    {
        try {
            BlogCategory::findOrFail($id)->increment('click_count');
            return response()->json(['message' => 'Click count incremented successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found'], 404);
        }
    }
}
