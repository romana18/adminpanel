<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Models\FAQCategory;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    private $categoryPriorityType;

    public function __construct(
        public FAQ $faq
    ) {
        $this->categoryPriorityType = helpers::get_business_settings('faq_category_priority_type') ?? 'latest';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $categoryPriority = $this->categoryPriorityType;

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $categories = FAQCategory::query()
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

        $faqPriority =  helpers::get_business_settings('faq_priority_type') ?? 'latest';
        $allowedPriorities = ['latest', 'a_to_z', 'z_to_a'];

        $faqs = FAQ::with('faqCategory')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%")
                        ->orWhereHas('faqCategory', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($faqPriority == 'latest', function ($query) {
                $query->latest();
            })
            ->when($faqPriority == 'a_to_z', function ($query) {
                $query->orderBy('question', 'ASC');
            })
            ->when($faqPriority == 'z_to_a', function ($query) {
                $query->orderBy('question', 'DESC');
            })
            ->when(!in_array($faqPriority, $allowedPriorities), function ($query) {
                $query->latest();
            })
            ->paginate(Helpers::pagination_limit());

        return view('admin-views.faq.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        $categoryPriority = $this->categoryPriorityType;

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $categories = FAQCategory::query()
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

        return view('admin-views.faq.create', compact('categories'));
    }

    public function priority()
    {
        return view('admin-views.faq.priority');
    }

    public function download()
    {
        $data = [
            'download_section' => [
                'data' => Helpers::get_business_settings('download_section'),
            ],
        ];
        return view('admin-views.faq.download', compact('data'));
    }

    public function updateDownload(Request $request)
    {
        BusinessSetting::updateOrInsert(['key' => 'faq_download_app_button_title'], [
            'value' => $request->faq_download_app_button_title
        ]);

        BusinessSetting::updateOrInsert(['key' => 'faq_download_app_button_subtitle'], [
            'value' => $request->faq_download_app_button_subtitle
        ]);

        BusinessSetting::updateOrInsert(['key' => 'faq_download_app_button_android_button'], [
            'value' => $request->input('faq_download_app_button_android_button') ? 1 : 0
        ]);

        BusinessSetting::updateOrInsert(['key' => 'faq_download_app_button_apple_button'], [
            'value' => $request->input('faq_download_app_button_apple_button') ? 1 : 0
        ]);

        $currentIcon = BusinessSetting::where(['key' => 'faq_download_app_button_icon'])->first() ?? '';
        if ($request->has('faq_download_app_button_icon')) {
            $iconName = Helpers::update('business/', $currentIcon->value ?? '', 'png', $request->file('faq_download_app_button_icon'));
        } else {
            $iconName = $currentIcon->value ?? '';
        }

        BusinessSetting::updateOrInsert(['key' => 'faq_download_app_button_icon'], [
            'value' => $iconName
        ]);

        $currentBackground = BusinessSetting::where(['key' => 'faq_download_app_button_background'])->first() ?? '';
        if ($request->has('faq_download_app_button_background')) {
            $backgroundImageName = Helpers::update('business/', $currentBackground->value ?? '', 'png', $request->file('faq_download_app_button_background'));
        } else {
            $backgroundImageName = $currentBackground->value ?? '';
        }

        BusinessSetting::updateOrInsert(['key' => 'faq_download_app_button_background'], [
            'value' => $backgroundImageName
        ]);

        Toastr::success(translate('Updated successfully'));
        return back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:1000',
            'category_id' => 'nullable|integer|exists:faq_categories,id',
        ]);

        $faq = $this->faq;
        $faq->category_id = $request->category_id;
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->save();

        Toastr::success(translate('FAQ added successfully'));
        return redirect()->route('admin.faq.index');
    }

    public function delete($id)
    {
        $faq = $this->faq->find($id);

        if (!$faq) {
            Toastr::error(translate('faq not found.'));
            return back();
        }

        $faq->delete();

        return response()->json([
            'message' => translate('FAQ deleted successfully.'),
        ]);
    }

    public function status(Request $request)
    {
        $faq = $this->faq->find($request->id);
        $faq->status = $request->status;
        $faq->save();

        return response()->json([
            'message' => translate('Status update successfully')
        ]);
    }

    public function edit($id)
    {
        $categoryPriority = $this->categoryPriorityType;

        // Define allowed priority types for validation
        $allowedPriorities = ['latest', 'popularity', 'a_to_z', 'z_to_a'];

        $faq = $this->faq->findOrFail($id);

        $categories = FAQCategory::active()
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

        return view('admin-views.faq.edit-form', compact('faq', 'categories'));
    }
    public function details($id)
    {
        $faq = $this->faq->findOrFail($id);
        return view('admin-views.faq.detail', compact('faq'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:1000',
            'category_id' => 'nullable|integer|exists:faq_categories,id',
        ]);

        $faq = $this->faq->find($id);
        $faq->category_id = $request->category_id;
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->update();

        Toastr::success(translate('FAQ updated successfully'));
        return back();

    }
}
