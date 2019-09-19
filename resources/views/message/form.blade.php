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
                <div class="card-header">@isset($message){{ __('Update Message') }}@else{{ __('New Message') }}@endisset</div>

                <div class="card-body">
                    <form method="POST" action="@isset($message){{ route('message.update', ['message' => $message->id, 'redirect' => request('redirect')]) }}@else{{ route('message.create') }}@endisset">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', isset($message) ? $message->title : (isset($title) ? $title : null)) }}" placeholder="Title" required autocomplete="title" autofocus>

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <textarea id="content" class="form-control @error('content') is-invalid @enderror" name="content">{{ old('content', isset($message) ? $message->content : null) }}</textarea>

                                @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        @unless(isset($message))
                        <div class="form-group row">
                            <label for="receiver" class="col-md-1 col-form-label text-md-right">{{ __('To') }}</label>

                            <div class="col-md-11">
                                <select id="receiver" class="form-control @error('receiver') is-invalid @enderror" name="receiver" required>
                                    @foreach ($users as $user)
                                    <option value="{{ $user->id }}"{{ (isset($receiver_id) && $user->id == $receiver_id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>

                                @error('receiver')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        @endunless

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

        @unless(isset($message))
        $('#content').summernote('code', '');
        @endunless
    });
</script>
@endsection
