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
                <div class="card-header">{{ __('Update Comment') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('comment.update', ['comment' => $comment->id, 'redirect=thread.show']) }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-12">
                                <textarea id="content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content', isset($comment) ? $comment->content : null) }}</textarea>

                                @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
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
            placeholder: 'Write here...',
            codeviewFilter: true,
            maximumImageFileSize: 1024 * 1024, // 1 MB 
            callbacks: { 
                onImageUploadError: function (msg) { alert(msg + " Choose images less than equal to 1 MB."); }
            }
        });
    });
</script>
@endsection
