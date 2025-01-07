<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    private $categoryPriorityType;

    public function __construct(
        public Blog $blog
    ) {
        $this->categoryPriorityType = helpers::get_business_settings('blog_category_priority_type') ?? 'latest';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $blogPriority =  helpers::get_business_settings('blog_priority_type') ?? 'latest';
        $allowedPriorities = ['latest', 'a_to_z', 'z_to_a'];

        $blogs = Blog::with('blogCategory')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('writer', 'like', "%{$search}%")
                        ->orWhereHas('blogCategory', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($blogPriority == 'latest', function ($query) {
                $query->latest();
            })
            ->when($blogPriority == 'popularity', function ($query) {
                $query->orderBy('click_count', 'DESC');
            })
            ->when($blogPriority == 'a_to_z', function ($query) {
                $query->orderBy('title', 'ASC');
            })
            ->when($blogPriority == 'z_to_a', function ($query) {
                $query->orderBy('title', 'DESC');
            })
            ->when(!in_array($blogPriority, $allowedPriorities), function ($query) {
                $query->latest();
            })
            ->paginate(Helpers::pagination_limit());

        return view('admin-views.blog.index', compact('blogs'));
    }

    public function create()
    {
        $categoryPriority = $this->categoryPriorityType;

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $categories = BlogCategory::query()
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

        return view('admin-views.blog.create', compact('categories'));
    }

    public function priority()
    {
        return view('admin-views.blog.priority');
    }

    public function download()
    {
        $data = [
            'download_section' => [
                'data' => Helpers::get_business_settings('download_section'),
            ],
        ];
        return view('admin-views.blog.download', compact('data'));
    }

    public function updateDownload(Request $request)
    {
        BusinessSetting::updateOrInsert(['key' => 'blog_download_app_button_title'], [
            'value' => $request->blog_download_app_button_title
        ]);

        BusinessSetting::updateOrInsert(['key' => 'blog_download_app_button_subtitle'], [
            'value' => $request->blog_download_app_button_subtitle
        ]);

        BusinessSetting::updateOrInsert(['key' => 'blog_download_app_button_android_button'], [
            'value' => $request->input('blog_download_app_button_android_button') ? 1 : 0
        ]);

        BusinessSetting::updateOrInsert(['key' => 'blog_download_app_button_apple_button'], [
            'value' => $request->input('blog_download_app_button_apple_button') ? 1 : 0
        ]);

        $currentIcon = BusinessSetting::where(['key' => 'blog_download_app_button_icon'])->first() ?? '';
        if ($request->has('blog_download_app_button_icon')) {
            $iconName = Helpers::update('business/', $currentIcon->value ?? '', 'png', $request->file('blog_download_app_button_icon'));
        } else {
            $iconName = $currentIcon->value ?? '';
        }

        BusinessSetting::updateOrInsert(['key' => 'blog_download_app_button_icon'], [
            'value' => $iconName
        ]);

        $currentBackground = BusinessSetting::where(['key' => 'blog_download_app_button_background'])->first() ?? '';
        if ($request->has('blog_download_app_button_background')) {
            $backgroundImageName = Helpers::update('business/', $currentBackground->value ?? '', 'png', $request->file('blog_download_app_button_background'));
        } else {
            $backgroundImageName = $currentBackground->value ?? '';
        }

        BusinessSetting::updateOrInsert(['key' => 'blog_download_app_button_background'], [
            'value' => $backgroundImageName
        ]);

        Toastr::success(translate('Updated successfully'));
        return back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
            'writer' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:blog_categories,id',
            'publish_date' => 'nullable|date',
        ]);

        // Handle the image upload
        $imagePath = $request->hasFile('image')
            ? Helpers::upload('blog/', 'png', $request->file('image'))
            : null;

        // Prepare draft data if saving as a draft
        $draftData = $request->is_draft
            ? array_merge($request->except(['image', '_token']), ['image' => $imagePath])
            : null;

        $blog = $this->blog;
        $blog->title = $request->title;
        $blog->description = $request->description;
        $blog->writer = $request->writer;
        $blog->category_id = $request->category_id;
        $blog->publish_date = $request->publish_date ?? now();
        $blog->status = $request->status ?? 1;
        $blog->is_draft = $request->is_draft ?? 0;
        $blog->click_count = 0;
        $blog->image = $imagePath;
        $blog->draft_data = $draftData;
        $blog->is_published  = $request->is_draft ? 0 : 1;

        $blog->save();

        Toastr::success($request->is_draft ? translate('Blog drafted successfully') :translate('Blog published successfully'));
        return redirect()->route('admin.blog.index');
    }

    public function delete(Request $request)
    {
        $blog = $this->blog->find($request->id);

        if (!$blog) {
            Toastr::error(translate('Blog not found.'));
            return back();
        }

        Helpers::delete('blog/' . $blog['image']);
        $blog->delete();

        return response()->json([
            'message' => translate('Blog deleted successfully.'),
        ]);
    }

    public function status(Request $request)
    {
        $blog = $this->blog->find($request->id);
        $blog->status = $request->status;
        $blog->save();

        return response()->json([
            'message' => translate('Status update successfully')
        ]);
    }

    public function preview($id)
    {
        $data = [
            'download_section' => [
                'data' => Helpers::get_business_settings('download_section'),
                'status' => BusinessSetting::where('key', 'landing_download_section_status')->value('value'),
                'header_title' => null
            ],
            'blog_download_app_button_status' => helpers::get_business_settings('blog_download_app_button_status'),
            'blog_download_app_button_title' => helpers::get_business_settings('blog_download_app_button_title'),
            'blog_download_app_button_subtitle' => helpers::get_business_settings('blog_download_app_button_subtitle'),
            'play_store_status' => helpers::get_business_settings('blog_download_app_button_android_button'),
            'app_store_status' => helpers::get_business_settings('blog_download_app_button_apple_button'),
            'icon_image' => helpers::get_business_settings('blog_download_app_button_icon'),
            'background_image' => helpers::get_business_settings('blog_download_app_button_background'),
        ];
        $blog = $this->blog->with('blogCategory')->find($id);

        $description = $blog->description;

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($description);
        libxml_clear_errors();

        $articleTags = $dom->getElementsByTagName('h2');
        $articleLinks = [];
        foreach ($articleTags as $index => $articleTag) {
            $articleContent = $articleTag->textContent;
            $id = 'article-section-' . $index;
            $articleTag->setAttribute('id', $id); // Set an ID for each <h1>
            $articleLinks[] = [
                'id' => $id,
                'text' => $articleContent
            ];
        }
        $updatedDescription = $dom->saveHTML();

        return view('admin-views.blog.preview', compact('data', 'blog', 'updatedDescription', 'articleLinks'));
    }

    public function draftPreview($id)
    {
        $data = [
            'download_section' => [
                'data' => Helpers::get_business_settings('download_section'),
                'status' => BusinessSetting::where('key', 'landing_download_section_status')->value('value'),
                'header_title' => null
            ],
            'blog_download_app_button_status' => helpers::get_business_settings('blog_download_app_button_status'),
            'blog_download_app_button_title' => helpers::get_business_settings('blog_download_app_button_title'),
            'blog_download_app_button_subtitle' => helpers::get_business_settings('blog_download_app_button_subtitle'),
            'play_store_status' => helpers::get_business_settings('blog_download_app_button_android_button'),
            'app_store_status' => helpers::get_business_settings('blog_download_app_button_apple_button'),
            'icon_image' => helpers::get_business_settings('blog_download_app_button_icon'),
            'background_image' => helpers::get_business_settings('blog_download_app_button_background'),
        ];
        $blog = $this->blog->with('blogCategory')->find($id);

        $description = $blog->draft_data['description'];

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($description);
        libxml_clear_errors();

        $articleTags = $dom->getElementsByTagName('h2');
        $articleLinks = [];
        foreach ($articleTags as $index => $articleTag) {
            $articleContent = $articleTag->textContent;
            $id = 'article-section-' . $index;
            $articleTag->setAttribute('id', $id); // Set an ID for each <h1>
            $articleLinks[] = [
                'id' => $id,
                'text' => $articleContent
            ];
        }
        $updatedDescription = $dom->saveHTML();

        return view('admin-views.blog.draft-preview', compact('data', 'blog', 'updatedDescription', 'articleLinks'));
    }

    public function edit($id)
    {
        $categoryPriority = $this->categoryPriorityType;

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $blog = $this->blog->with('blogCategory')->find($id);

        $categories = BlogCategory::active()
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

        return view('admin-views.blog.edit', compact('blog', 'categories'));
    }

    public function draftEdit($id)
    {
        $categoryPriority = $this->categoryPriorityType;

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $blog = $this->blog->with('blogCategory')->find($id);

        $categories = BlogCategory::active()
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

        return view('admin-views.blog.draft-edit', compact('blog', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'mimes:jpeg,jpg,png,gif|max:10000',
            'writer' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:blog_categories,id',
            'publish_date' => 'nullable|date',
        ]);

        $blog = $this->blog->find($id);

        if ($request->clear_draft == 1){
            if ($blog->is_published){
                $blog->draft_data = null; // Clear draft data on publish
                $blog->is_draft = 0;
                $blog->save();
            }else{
                $blog->delete();
            }
            Toastr::success(translate('Blog draft cleared successfully'));
            return redirect()->route('admin.blog.index');
        }

        // Prepare draft data if saving as a draft
        if ($request->is_draft) {
            $draftImage = $blog?->draft_data['image'] ?? null; // Safely access the draft image
            $imagePath = $request->hasFile('image')
                ? Helpers::update('blog/', $draftImage, 'png', $request->file('image'))
                : $blog->image;
            $draftData = array_merge(
                $request->except(['image', '_token']),
                ['image' => $imagePath]
            );
            $blog->draft_data = $draftData;
            $blog->is_draft = 1;
        }else{
            $imagePath = $request->hasFile('image')
                ? Helpers::update('blog/', $blog->image, 'png', $request->file('image'))
                : $blog->image;

            $blog->title = $request->title;
            $blog->description = $request->description;
            $blog->writer = $request->writer;
            $blog->category_id = $request->category_id;
            $blog->publish_date = $request->publish_date ?? now();
            $blog->image = $imagePath;
            $blog->is_draft = 0;
            $blog->draft_data = null; // Clear draft data on publish
            $blog->is_published  = 1;
            $blog->status  = 1;
        }

        $blog->update();

        Toastr::success($request->is_draft ? translate('Blog drafted successfully') :translate('Blog published successfully'));
        return redirect()->route('admin.blog.index');
    }

}
