@extends('layouts.app')

@section('scripts')
<!-- summernote js -->
<script src="{{ asset('third-party/summernote-0.8.12/summernote-bs4.min.js') }}" defer></script>
@endsection

@section('styles')
<!-- summernote css -->
<link href="{{ asset('third-party/summernote-0.8.12/summernote-bs4.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">@isset($page){{ __('Update Page') }}@else{{ __('New Page') }}@endisset</div>

                <div class="card-body">
                    <form method="POST" action="@isset($page){{ route('page.update', ['page' => $page->id, 'redirect' => request('redirect')]) }}@else{{ route('page.create') }}@endisset">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', isset($page) ? $page->title : null) }}" placeholder="Title" required autocomplete="title" autofocus>

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <textarea id="content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content', isset($page) ? $page->content : null) }}</textarea>

                                @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="hidden" name="can_have_comments" value="off" /> <!-- This field will be sent as check box value when it's not checked -->
                                    <input class="form-check-input" type="checkbox" name="can_have_comments" id="can_have_comments" {{ old('can_have_comments', isset($page) ? $page->can_have_comments : false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="can_have_comments">{{ __('Can Have Comments') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                <a href="{{ $redirect }}" class="btn btn-link">{{ __('Cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        $('#content').summernote({
            height: 300,
            placeholder: {!! '\'' . __('Write here...') . '\'' !!},
            codeviewFilter: true,
            maximumImageFileSize: 1024 * 1024, // 1 MB 
            callbacks: { 
                onImageUploadError: function (msg) { alert(msg + " {{ __('Choose images less than equal to 1 MB.') }}"); }
            }
        });

        @unless(isset($page))
        $('#content').summernote('code', '');
        @endunless
    });
</script>
@endsection
