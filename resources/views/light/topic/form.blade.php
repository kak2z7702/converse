@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">@isset($topic){{ __('Update Topic') }}@else{{ __('New Topic') }}@endisset</div>

                <div class="card-body">
                    <form method="POST" action="@isset($topic){{ route('topic.update', ['topic' => $topic->id, 'redirect' => request('redirect')]) }}@else{{ route('topic.create', ['redirect' => request('redirect')]) }}@endisset" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="title" class="col-md-2 col-form-label text-md-right">{{ __('Title') }}</label>

                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', isset($topic) ? $topic->title : null) }}" required autocomplete="title" autofocus>

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="description" class="col-md-2 col-form-label text-md-right">{{ __('Description') }}</label>

                            <div class="col-md-8">
                                <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description', isset($topic) ? $topic->description : null) }}</textarea>

                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="photo" class="col-md-2 col-form-label text-md-right">{{ __('Photo') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input id="photo" name="photo" type="file" class="custom-file-input @error('photo') is-invalid @enderror">
                                    <label for="photo" class="custom-file-label" data-browse="{{ __('Browse') }}">@if (isset($topic) && isset($topic->photo)){!! $topic->photo !!}@else{{ __('Choose an image...') }}@endif</label>
                                    <small class="text-muted">{{ __('Must be a 64px by 64px image (jpeg, png, bmp, gif, svg, or webp) and less than equal 1 MB.') }}</small>
                                </div>

                                @error('photo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div id="photoPreviewRow" class="form-group row d-none">
                            <label class="col-md-2 col-form-label text-md-right">{{ __('New Photo') }}</label>

                            <div class="col-md-8">
                                <img id="photoPreview" src="#" class="rounded-circle img-border" alt="Preview" width="64" height="64">
                            </div>
                        </div>

                        @if (isset($topic) && isset($topic->photo))
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label text-md-right">{{ __('Current Photo') }}</label>
                        
                            <div class="col-md-8">
                                <img src="{{ asset('storage') . '/' . $topic->photo }}" class="rounded-circle img-border" alt="{{ $topic->title }}" width="64" height="64">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="no_photo" id="noPhoto" {{ old('no_photo') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="noPhoto">{{ __('No Photo') }}</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row">
                            <label for="category" class="col-md-2 col-form-label text-md-right">{{ __('Category') }}</label>

                            <div class="col-md-8">
                                <select id="category" class="form-control @error('category') is-invalid @enderror" name="category" required>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"{{ (isset($topic) && $category->id == $topic->category_id) ? 'selected' : (isset($category_id) && $category->id == $category_id) ? 'selected' : '' }}>{{ $category->title }}</option>
                                    @endforeach
                                </select>

                                @error('category')
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
        $(function () {
            $('#photo').on('change', function(event) {
                let blob = URL.createObjectURL(event.target.files[0]);

                $('#photoPreviewRow').removeClass('d-none');
                $('#photoPreview').attr('src', blob);

                URL.revokeObjectURL(blob);
            });
        });
    });
</script>
@endsection
