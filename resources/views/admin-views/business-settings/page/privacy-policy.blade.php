@extends('layouts.admin.app')

@section('title', translate('Privacy Policy'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2 mb-3">
            <img width="24" src="{{asset('assets/admin/img/media/compliant.png')}}" alt="{{translate('privacy_policy')}}">
            <h2 class="mb-0 text-capitalize">{{translate('privacy_policy')}}</h2>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-3">
            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-intro">Section View <i class="tio-document-text"></i></button>

            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.pages.privacy-policy')}}" method="post" id="tnc-form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="input-label" for="title">{{translate('title')}}</label>
                                <input type="text" name="privacy_policy_title" value="{{$title->value}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                <input type="text" value="{{$subTitle->value}}" name="privacy_policy_sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div id="editor" class="min-h-200px">{!! $privacyPolicy['value']??'' !!}</div>
                                <textarea class="ckeditor form-control" name="privacy_policy" id="hiddenArea" style="display: none">{!! $privacyPolicy['value']??'' !!}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

            <div class="modal fade" id="section-view-modal-intro" tabindex="-1" aria-labelledby="section-view-modalLabel-intro" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-center">
                                <img width="100%" src="{{asset('public/assets/landing/img/section-view/privacy.png')}}" alt="{{translate('privacy_policy')}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="modal fade" id="notes-view-modal" tabindex="-1" aria-labelledby="notes-view-modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-center mb-4">
                            <img width="58" src="{{asset('public/assets/landing/img/media/notes.png')}}" alt="{{translate('note')}}">
                        </div>

                        <h5 class="mb-3">{{translate('For Title and Headline')}} </h5>

                        <ul class="list-unstyled d-flex flex-column gap-2">
                            <li>{{translate('1. To include a text color just use  ** around the text **  you want to use colour')}}</li>
                            <li>{{translate('2. To include a text background just use  ## around the text ##  you want to use background colour')}}</li>
                            <li>{{translate('3. To include a text bold just use  @@ around the text @@  you want to use bold')}}</li>
                            <li>{{translate('4. If you want to break the line just use  %%  from where you want to break')}}</li>
                        </ul>

                        <div class="d-flex justify-content-center mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-center">{{translate('Choose')}} <span class="bg-primary text-white">{{ Helpers::get_business_settings('business_name') }}</span> for <br>{{translate('Secure And Convenient Digital Payments')}}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('script_2')
    <script src="{{ asset('assets/admin/js/quill-editor.js') }}"></script>
    <script>
        $(document).ready(function () {
            var bn_quill = new Quill('#editor', {
                theme: 'snow'
            });

            $('#tnc-form').on('submit', function () {
                var myEditor = document.querySelector('#editor');
                $('#hiddenArea').val(myEditor.children[0].innerHTML);
            });
        });
    </script>
@endpush
