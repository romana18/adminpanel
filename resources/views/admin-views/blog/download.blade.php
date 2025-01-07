@extends('layouts.admin.app')

@section('title', translate('Download Button'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between gap-2 align-items-center flex-wrap mb-3">
            <div class="d-flex align-items-center gap-3 pb-2">
                <img width="24" src="{{asset('assets/admin/img/media/blog.png')}}" alt="{{ translate('business_setup') }}">
                <h2 class="page-header-title">{{translate('Download Button')}}</h2>
            </div>
        </div>

        <div class="inline-page-menu my-4">
            <ul class="list-unstyled">
                <li class=""><a href="{{route('admin.blog.index')}}">{{translate('Blog Page Setup')}}</a></li>
                <li class="active"><a href="{{route('admin.blog.download')}}">{{translate('Download Button')}}</a></li>
                <li class=""><a href="{{route('admin.blog.priority')}}">{{translate('Priority Setup')}}</a></li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header">
                    <span class="text-dark">
                        <i class="tio-browser-window"></i>
                        <strong>{{ translate('Download_App_Button') }}</strong>
                        <i class="tio-info-outined cursor-pointer" data-toggle="tooltip" data-placement="top" title="" data-original-title="Here you can setup the necessary information related to the app download option"></i>
                    </span>

                @php($blogDownloadAppButtonStatus = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_status'))
                <label class="toggle-switch toggle-switch-sm">
                    <input class="toggle-switch-input update-business-setting-status"
                           id="blog_download_app_button_status" type="checkbox"
                           name="blog_download_app_button_status"
                           data-name="blog_download_app_button_status"
                           data-url="{{ route('admin.business-settings.update-business-setting-status') }}"
                           data-icon="{{ asset('assets/admin/svg/components/info.svg')}}"
                           data-title="{{ ($blogDownloadAppButtonStatus ?? 0) == 1 ? translate('Are you sure to turn off the Download App Button Status?') . '? ' : translate('Are you sure to turn on the Download App Button Status') . '? ' }}"
                           data-sub-title="{{ ($blogDownloadAppButtonStatus ?? 0) == 1 ? translate('Once you turn off this Download App Button, It will not be visible to the Blog section for users.') : translate('Once you turn on this Download App Button, It will be visible to the Blog Section for users.') }}"
                           data-confirm-btn="{{ ($blogDownloadAppButtonStatus ?? 0) == 1 ? translate('Yes, Off') : translate('Yes, On') }}"
                        {{ isset($blogDownloadAppButtonStatus) && $blogDownloadAppButtonStatus == 1 ? 'checked' : '' }}>
                    <span class="toggle-switch-label text p-0">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                </label>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.blog.update-download') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="bg-light p-3 rounded">
                                @php($title = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_title'))
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2" for="title">{{ translate('Title') }}</label>
                                    <input type="text" name="blog_download_app_button_title" class="form-control" id="blog_download_app_button_title"
                                           value="{{ $title }}" placeholder="Enter Title" required="">
                                </div>

                                @php($subtitle = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_subtitle'))
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2" for="sub_title">{{ translate('Sub title') }}</label>
                                    <textarea name="blog_download_app_button_subtitle" id="blog_download_app_button_subtitle" class="form-control" rows="3" placeholder="Enter SUb Title">{{ $subtitle }}</textarea>
                                </div>
                            </div>
                            @php($landingDownloadStatus = \App\CentralLogics\helpers::get_business_settings('landing_download_section_status'))
                            @if($landingDownloadStatus == 1)
                                @if ($data['download_section']['data']['play_store_link'] != "" || $data['download_section']['data']['app_store_link'] != "")
                                    <div class="bg-light p-3 rounded mt-3">
                                        <h5>{{ translate('download_Button') }}</h5>
                                        <p>{{ translate('Please select the options that you would like to make visible to your users') }}</p>

                                        @php($playStore = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_android_button'))
                                        @php($appStore = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_apple_button'))

                                        <div class="d-flex gap-4 align-items-center flex-wrap mt-5">
                                            @if ($data['download_section']['data']['play_store_link'] != "")
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" name="blog_download_app_button_android_button" id="android" size="20"
                                                        {{ isset($playStore) && $playStore == 1 ? 'checked' : '' }}>
                                                    <img width="22" src="{{asset('assets/admin/img/android.png')}}" alt="{{ translate('android') }}">
                                                    <strong>{{ translate('Playstore_Button') }}</strong>
                                                </div>
                                            @endif
                                            @if ($data['download_section']['data']['app_store_link'] != "")
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" name="blog_download_app_button_apple_button" id="apple" size="20"
                                                        {{ isset($appStore) && $appStore == 1 ? 'checked' : '' }}>

                                                    <img width="22" src="{{asset('assets/admin/img/apple.png')}}" alt="{{ translate('apple') }}">
                                                    <strong>{{ translate('app_Store_Button') }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                @endif
                            @endif

                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex gap-5 justify-content-center flex-wrap">
                                @php($iconImage = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_icon'))
                                <div class="">
                                    <h5>{{ translate('Icon') }} <span class="text-danger">*</span></h5>
                                    <p class="mx-w180">JPG, JPEG, PNG Less Than 1MB <strong>(Ratio 1:1)</strong></p>
                                    <div class="upload-file auto profile-image-upload-file">
                                        <input type="file" name="blog_download_app_button_icon" class="upload-file__input"
                                               accept=".jpg, .jpeg, .png" @if(!isset($iconImage)) required @endif>
                                        <div class="upload-file__img border-gray d-flex justify-content-center align-items-center w-180 h-180 p-0 bg-light">
                                            <div class="upload-file__textbox text-center" @if($iconImage) style="display:none;" @endif>
                                                <img width="34" height="34"
                                                     src="{{asset('assets/admin/img/upload.svg')}}"
                                                     alt="" class="svg">
                                                <h6 class="mt-2 fw-semibold">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file__img__img h-100" @if($iconImage) style="display: block" @endif src="{{asset('storage/app/public/business/' . $iconImage)}}"
                                                width="180" height="180" loading="lazy" alt="">
                                        </div>
                                    </div>
                                </div>

                                @php($backgroundImage = \App\CentralLogics\helpers::get_business_settings('blog_download_app_button_background'))
                                <div class="">
                                    <h5>{{ translate('Background') }} <span class="text-danger">*</span></h5>
                                    <p class="mx-w180">{{ translate('JPG, JPEG, PNG Less Than 1MB') }} <strong>({{ translate('Ratio') }} 1:1)</strong></p>
                                    <div class="upload-file auto profile-image-upload-file">
                                        <input type="file" name="blog_download_app_button_background" class="upload-file__input"
                                               accept=".jpg, .jpeg, .png" @if(!isset($backgroundImage)) required @endif>
                                        <div class="upload-file__img border-gray d-flex justify-content-center align-items-center w-180 h-180 p-0 bg-light">
                                            <div class="upload-file__textbox text-center" @if($backgroundImage) style="display:none;" @endif>
                                                <img width="34" height="34"
                                                     src="{{asset('assets/admin/img/upload.svg')}}"
                                                     alt="" class="svg">
                                                <h6 class="mt-2 fw-semibold">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file__img__img h-100" @if($backgroundImage) style="display: block" @endif src="{{asset('storage/app/public/business/' . $backgroundImage)}}"
                                                 width="180" height="180" loading="lazy" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
