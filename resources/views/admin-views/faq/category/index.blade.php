{{-- Offcanvas --}}
<div class="navbar-collapse offcanvas-collapse">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap p-3">
        <h5 class="m-0">{{ translate('category_Setup') }}</h5>
        <button class="navbar-toggler p-0 border-0 offcanvas-close" type="button" data-toggle="offcanvas">
            <i class="tio-clear"></i>
        </button>
    </div>

    <div class="p-3">
        <div class="p-4 bg-light rounded">
            <form action="#" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2" for="category_name">{{ translate('Category_Name') }}</label>
                    <input type="text" name="name" class="form-control" id="category_name" placeholder="{{ translate('type_Category_Name') }}" required>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-outline-danger">{{translate('reset')}}</button>
                    <button type="submit" class="btn btn-primary category-form-submit">{{translate('submit')}}</button>
                </div>
            </form>
        </div>

        <div class="mt-5">
            <div class="d-flex justify-content-between gap-3 align-items-center flex-wrap mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{ translate('category_List') }}</h5>
                    <span class="badge badge-soft-secondary text-dark category-count"> {{ $categories->count() }}</span>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <form id="category-search-form" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="Search category" aria-label="Search" value="" autocomplete="off">
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive border rounded overflow-hidden category-table">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{ translate('category') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($categories as $key => $category)
                        <tr id="category-row-{{ $category->id }}">
                            <td>{{  $loop->iteration }}</td>
                            <td>
                                <a href="#" class="media gap-3 align-items-center text-dark">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td>
                                <label class="switcher">
                                    <input type="checkbox" name="status" class="switcher_input change-category-status"
                                           data-id="{{ $category->id }}"
                                           data-icon="{{ asset('assets/admin/svg/components/info.svg')}}"
                                           data-title="{{ $category->status == 1 ? translate('Are you sure to turn off the category') . '? ' : translate('Are you sure to turn on the category status?') . '? ' }}"
                                           data-sub-title="{{ $category->status == 1 ? translate('Once you turn off, it will not be accessed when selecting the category') : translate('When you turn on this category, It can be accessed and visible for selection') }}"
                                           data-confirm-btn="{{ $category->status == 1 ? translate('Yes, Off') : translate('Yes, On') }}"

                                        {{ $category->status == 1 ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-info edit-category-btn" data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                    <button class="action-btn btn btn-outline-danger delete-category-btn" data-id="{{ $category->id }}" data-toggle="modal" data-target="#deleteModal">
                                        <i class="tio-add-to-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="d-flex flex-column gap-2 align-items-center py-8 px-3 rounded">
                                    <img width="44" class="mb-2" src="{{asset('assets/admin/svg/components/faq.svg')}}" alt="{{ translate('unavailable') }}">
                                    <p class="text-muted">{{ translate('Currently no FAQ Category available in this list') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true" style="z-index: 999999">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <img src="{{ asset('assets/admin/svg/components/delete.svg') }}" alt="Delete Icon" style="width: 50px;">
                </div>
                <h4>{{ translate('Are you sure you want to delete this category') }}?</h4>
                <p class="text-muted">{{ translate('Once you delete it, this will permanently remove it from the category list and can not be accessed') }}.</p>
            </div>
            <div class="d-flex justify-content-center gap-3 my-3 flex-wrap">
                <button type="button" id="confirmDelete" class="btn btn-danger">{{ translate('Yes') }}, {{ translate('Delete') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Not Now') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="categoryStatusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true" style="z-index: 999999">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto">
                    <div>
                        <div class="text-center">
                            <img alt="" class="mb-4" id="category-icon"
                                 src="{{asset('public/assets/admin-module/img/svg/blocked_customer.svg')}}">
                            <h5 class="modal-title mb-3" id="categoryModalTitle">{{translate("Are you sure?")}}</h5>
                        </div>
                        <div class="text-center mb-4 pb-2">
                            <p id="categorySubTitle">{{translate("Want to change status")}}</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-primary min-w-120" id="categoryModalConfirmBtn">{{translate('Ok')}}</button>
                        <button type="button" class="btn btn-secondary min-w-120" id="categoryModalCancelBtn">{{translate('Not Now')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
