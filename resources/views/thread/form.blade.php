@extends('layouts.app')

@if (!isset($thread))
@section('scripts')
<!-- summernote js -->
<script src="{{ asset('third-party/summernote-0.8.12/summernote-bs4.min.js') }}" defer></script>
@endsection

@section('styles')
<!-- summernote css -->
<link href="{{ asset('third-party/summernote-0.8.12/summernote-bs4.css') }}" rel="stylesheet">
@endsection
@endif

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">@isset($thread){{ __('Update Thread') }}@else{{ __('New Thread') }}@endisset</div>

                <div class="card-body">
                    <form method="POST" action="@isset($thread){{ route('thread.update', ['thread' => $thread->id, 'redirect' => request('redirect')]) }}@else{{ route('thread.create', ['topic' => $topic_id, 'redirect=thread.show']) }}@endisset">
                        @csrf

                        <div class="form-group row">
                            <label for="title" class="col-md-2 col-form-label text-md-right">{{ __('Title') }}</label>

                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', isset($thread) ? $thread->title : null) }}" required autocomplete="title" autofocus>

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        @if (!isset($thread))
                        <div class="form-group row">
                            <label for="content" class="col-md-2 col-form-label text-md-right">{{ __('Content') }}</label>

                            <div class="col-md-8">
                                <textarea id="content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content', isset($thread) ? $thread->content : null) }}</textarea>

                                @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="form-group row">
                            <label for="topic" class="col-md-2 col-form-label text-md-right">{{ __('Topic') }}</label>

                            <div class="col-md-8">
                                <select id="topic" class="form-control @error('topic') is-invalid @enderror" name="topic" required>
                                    @foreach ($categories as $category)
                                    <option value="0">+ {{ $category->title }}</option>
                                    @foreach ($category->topics as $topic)
                                    <option value="{{ $topic->id }}"{{ (isset($thread) && $topic->id == $thread->topic_id) ? 'selected' : (isset($topic_id) && $topic->id == $topic_id) ? 'selected' : '' }}>{!! str_repeat('&nbsp;', 4) !!}- {{ $topic->title }}</option>
                                    @endforeach
                                    @endforeach
                                </select>

                                @error('topic')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-10 offset-md-2">
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

        @unless(isset($thread))
        $('#content').summernote('code', '');
        @endunless
    });
</script>
@endsection
