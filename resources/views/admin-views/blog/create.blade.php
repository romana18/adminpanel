@extends('layouts.admin.app')

@section('title', translate('Blog Setup'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between gap-2 align-items-center flex-wrap">
            <div class="d-flex flex-wrap align-items-center gap-3 pb-2">
                <img width="24" src="{{asset('assets/admin/img/media/blog.png')}}" alt="{{ translate('business_setup') }}">
                <h2 class="page-header-title">{{translate('Create New Blog')}}</h2>
            </div>
        </div>

        <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="blog-form">
            @csrf
            <div class="card mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="d-flex gap-2 align-items-baseline justify-content-between">
                                    <label class="input-label">{{ translate('category') }}</label>
                                    <button type="button" class="btn btn-link text-decoration-underline p-0" data-toggle="offcanvas">
                                        <i class="tio-add"></i>
                                        {{ translate('manage_Category') }}
                                    </button>
                                </div>
                                <select name="category_id" id="category_id" class="form-control">
                                    <option value="" selected disabled>{{ translate('Select Category') }}</option>
                                    @foreach($categories->where('status', 1) as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{ translate('writer') }}</label>
                                <input type="text" name="writer" class="form-control" value="{{ old('writer') }}" placeholder="Ex: John Milar" maxlength="50">
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{ translate('publish_Date') }}</label>
                                <input type="date" name="publish_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <label class="text-dark mb-0">{{ translate('Thumbnail') }}<span class="text-danger"> *</span></label>

                                    <p class="mx-w180">{{ translate('JPG, JPEG, PNG Less Than 1MB') }} <strong>(Ratio 2:1)</strong></p>
                                    <div class="upload-file auto profile-image-upload-file">
                                        <input type="file" name="image" class="upload-file__input"
                                            accept=".jpg, .jpeg, .png" required>
                                        <div class="upload-file__img banner border-gray d-flex justify-content-center align-items-center mw-100 w-360 h-180 p-0 bg-light">
                                            <div class="upload-file__textbox text-center">
                                                <img height="34"
                                                    src="{{asset('assets/admin/img/upload.svg')}}"
                                                    alt="" class="svg ratio-2">
                                                <h6 class="mt-2 fw-semibold">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file__img__img h-100 ratio-2" width="180" height="180" loading="lazy" alt="">
                                        </div>
                                        <a href="javascript:void(0)" class="remove-img-icon d-none">
                                            <i class="tio-clear"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-4 bg-soft-secondary rounded">

                        <div class="form-group">
                            <label class="input-label" for="title">{{translate('title')}}<span class="text-danger"> *</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}"  placeholder="{{translate('title')}}" maxlength="255" required>
                        </div>

                        <div class="">
                            <label class="input-label" for="title">{{translate('description')}}<span class="text-danger"> *</span></label>
                            <div id="editor" class="min-h200 bg-white">{{ translate('Type the description of your blog...') }}</div>
                            <textarea class="editor form-control d-none" name="description" id="hiddenArea"></textarea>
                        </div>
                    </div>

                    <!-- Hidden input fields to store status and draft -->
                    <input type="hidden" name="status" id="status" value="1"> <!-- Default status -->
                    <input type="hidden" name="is_draft" id="is_draft" value="0"> <!-- Default draft -->

                    <div class="d-flex justify-content-end gap-3 mt-3 flex-wrap">
                        <button type="reset" class="btn btn-secondary"><i class="fa fa-repeat mr-1" aria-hidden="true"></i>{{ translate('reset') }}</button>
                        <button type="button" class="btn btn-outline-primary demo-form-submit save-draft"> <i class="fa fa-save mr-1" aria-hidden="true"></i> {{ translate('Save_to_Draft') }}</button>
                        <button type="submit" class="btn btn-primary demo-form-submit publish"><i class="fa fa-check-circle-o mr-1" aria-hidden="true"></i>{{ translate('publish') }}</button>
                    </div>
                </div>
            </div>
        </form>

        @include('admin-views.blog.category.index')
    </div>


@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/image-upload.js')}}"></script>
    <script src="{{ asset('assets/admin/js/quill-editor.js') }}"></script>

    <script>

        $(document).ready(function () {
            var bn_quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                        ['blockquote', 'code-block'],
                        ['link', 'image', 'video', 'formula'],

                        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                        [{ 'direction': 'rtl' }],                         // text direction

                        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                        [{ 'font': [] }],
                        [{ 'align': [] }],

                        ['clean']
                    ]
                }
            });

            $('#blog-form').on('submit', function () {
                var myEditor = document.querySelector('#editor');
                $('#hiddenArea').val(myEditor.children[0].innerHTML);
            });
        });


        $(function () {
            'use strict'

            $('[data-toggle="offcanvas"]').on('click', function () {
                $('body').append('<div class="offcanvas-overlay active"></div>')
                $('.offcanvas-collapse').toggleClass('open')
            });
            $('body').on('click', '.offcanvas-overlay, .offcanvas-close', function () {
                $('body').find('.offcanvas-overlay').remove();
                $('.offcanvas-collapse').removeClass('open')
            });
        })

        // add or update category
        $('.category-form-submit').on('click', function (e) {
            e.preventDefault();

            $('#datatableSearch').val('');

            let categoryName = $('#category_name').val();
            let categoryId = $('#category_name').data('id');
            let url = categoryId
                ? `/admin/blog/category/update/${categoryId}` // Update route if ID exists
                : '{{ route("admin.blog.category.store") }}'; // Store route for new category
            let method = categoryId ? 'PUT' : 'POST'; // Use PUT for update, POST for create

            $.ajax({
                url: url,
                method: method,
                data: {
                    name: categoryName,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    $('#category_name').val('');
                    $('#category_name').removeData('id');
                    $('.category-form-submit').text('Submit');

                    toastr.success(response.message);

                    $('table tbody').html(response.html);

                    let countCategory = $('.category-count');
                    countCategory.text(response.count);
                },
                error: function (response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function (key) {
                            toastr.error(errors[key][0]); // Show each validation error
                        });
                    } else {
                        toastr.error(response.responseJSON.message || 'Something went wrong!');
                    }
                }
            });
        });

        // delete category
        $(document).on('click', '.delete-category-btn', function () {
            let categoryId = $(this).data('id');

            // Set the category ID in a global variable or pass it directly during confirmation
            $('#confirmDelete').data('id', categoryId);
        });

        $('#confirmDelete').on('click', function () {
            let categoryId = $(this).data('id');
            let searchQuery = $('#datatableSearch').val();

            $.ajax({
                url: '/admin/blog/category/delete/' + categoryId,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    search: searchQuery
                },
                success: function (response) {
                    $('#deleteModal').modal('hide');

                    toastr.success(response.message);

                    $('table tbody').html(response.html);

                    let countCategory = $('.category-count');
                    countCategory.text(response.count);
                },
                error: function (xhr) {
                    $('#deleteModal').modal('hide');
                    toastr.error('Failed to delete the category.');
                }
            });
        });

        //edit category
        $(document).on('click', '.edit-category-btn', function () {
            let categoryId = $(this).data('id');
            let categoryName = $(this).data('name');

            $('#category_name').val(categoryName);
            $('#category_name').data('id', categoryId);
            $('.category-form-submit').text('Update');

            // Scroll to the top of the offcanvas div
            $('.offcanvas-collapse').animate({ scrollTop: 0 }, 'slow');
            $('#category_name').focus();
        });

        // search category
        let debounceTimer;
        $('#datatableSearch').on('keyup', function () {
            clearTimeout(debounceTimer);
            let searchQuery = $(this).val();

            debounceTimer = setTimeout(function () {
                $.ajax({
                    url: '{{ route("admin.blog.category.search") }}',
                    method: 'GET',
                    data: { search: searchQuery },
                    success: function (response) {
                        $('table tbody').html(response.html);

                        let countCategory = $('.category-count');
                        countCategory.text(response.count);
                    },
                    error: function () {
                        toastr.error('Failed to fetch categories!');
                    }
                });
            }, 300); // Wait for 300ms before triggering the request
        });


        // category status change
        $(document).on('change', '.change-category-status', function () {
            statusChange(this)
        });

        function statusChange(obj){
            let categoryId = $(obj).data('id');
            let iconContent = $(obj).data('icon');
            let titleContent = $(obj).data('title');
            let subTitleContent = $(obj).data('sub-title');
            let confirmBtnContent = $(obj).data('confirm-btn');
            let cancelBtnContent = $(obj).data('cancel-btn');

            let checkbox = $(obj);  // Store the checkbox element
            let initialStatus = checkbox.prop('checked') === true ? 0 : 1; // Store the initial status (checked or unchecked)
            let status = initialStatus === 1 ? 0 : 1;  // Toggle the status (opposite of the initial one)

            // Show custom modal
            const modalElement = document.getElementById('categoryStatusModal');
            let bootstrapModal = new bootstrap.Modal(modalElement);
            bootstrapModal.show();

            if (iconContent) {
                $("#icon").attr('src', iconContent);
            }
            if (titleContent) {
                $("#modalTitle").html(titleContent);
            }
            if (subTitleContent) {
                $("#subTitle").html(subTitleContent);
            }
            if (confirmBtnContent) {
                $("#modalConfirmBtn").html(confirmBtnContent);
            }
            if (cancelBtnContent) {
                $("#modalCancelBtn").html(cancelBtnContent);
            }

            let confirmBtn = document.getElementById("modalConfirmBtn");
            let cancelBtn = document.getElementById("modalCancelBtn");


            confirmBtn.onclick = function () {
                $.ajax({
                    url: '/admin/blog/category/status/' + categoryId,
                    data: {status: status},
                    success: function (response) {
                        toastr.success(response.message);

                        $('table tbody').html(response.html);
                    },
                    error: function () {
                        resetCheckbox();
                        toastr.error("{{ translate('status_change_failed') }}");
                    }
                });
                bootstrapModal.hide();
            }

            // When the user clicks on Cancel button
            cancelBtn.onclick = function () {
                bootstrapModal.hide();
                resetCheckbox();
            }
            modalElement.addEventListener('hidden.bs.modal', function () {
                resetCheckbox();
            });

            function resetCheckbox() {
                checkbox.prop('checked', initialStatus);
            }
        }

        $(document).ready(function () {
            // Handle save to draft button
            $('.save-draft').on('click', function () {
                $('#status').val(0); // Set status to 0 for drafts
                $('#is_draft').val(1); // Mark as draft
                $('#blog-form').submit(); // Submit the form
            });

            // Handle publish button
            $('.publish').on('click', function () {
                $('#status').val(1); // Set status to 1 for published
                $('#is_draft').val(0); // Ensure it's not a draft
                $('#blog-form').submit(); // Submit the form
            });
        });

    </script>

@endpush
