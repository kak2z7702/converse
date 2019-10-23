@extends(config('theme.layout'))

@section('scripts')
<!-- bootstrap-colorpicker js -->
<script src="{{ asset('third-party/bootstrap-colorpicker-3.1.2/dist/js/bootstrap-colorpicker.js') }}" defer></script>
@endsection

@section('styles')
<!-- bootstrap-colorpicker css -->
<link href="{{ asset('third-party/bootstrap-colorpicker-3.1.2/dist/css/bootstrap-colorpicker.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Options') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('options', ['redirect' => request('redirect')]) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-2 col-form-label text-md-right">{{ __('Community Name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', config('app.name')) }}" required autocomplete="name">

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <hr />

                        <div class="form-group row">
                            <label for="theme" class="col-md-2 col-form-label text-md-right">{{ __('Theme') }}</label>

                            <div class="col-md-8">
                                <select id="theme" class="form-control @error('theme') is-invalid @enderror" name="theme" required>
                                    @foreach ($themes as $theme)
                                    <option value="{{ $theme->id }}"{{ ($theme->id == old('theme', config('app.theme'))) ? 'selected' : '' }}>{{ $theme->name }} ({{ __('By') }} {{ $theme->author }}) {{ __('Version') }} {{ $theme->version }}</option>
                                    @endforeach
                                </select>

                                @error('theme')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="backgroundColor" class="col-md-2 col-form-label text-md-right">{{ __('Background Color') }}</label>

                            <div class="col-md-8">
                                <input id="backgroundColor" name="background_color" type="text" class="form-control @error('background_color') is-invalid @enderror" value="{{ old('background_color', config('app.background.color')) }}">

                                @error('background_color')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="backgroundImage" class="col-md-2 col-form-label text-md-right">{{ __('Background Image') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input id="backgroundImage" name="background_image" type="file" class="custom-file-input @error('background_image') is-invalid @enderror">
                                    <label for="backgroundImage" class="custom-file-label" data-browse="{{ __('Browse') }}">@if (config('app.background.image')){!! config('app.background.image') !!}@else{{ __('Choose an image...') }}@endif</label>
                                    <small class="text-muted">{{ __('Must be an image (jpeg, png, bmp, gif, svg, or webp) and less than equal 1 MB.') }}</small>
                                </div>

                                @error('background_image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        @if (config('app.background.image'))
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="no_background_image" id="noBackgroundImage" {{ old('no_background_image') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="noBackgroundImage">{{ __('No Background Image') }}</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <hr />

                        <div class="form-group row">
                            <div class="col-md-10 offset-md-2">
                                <div class="form-check">
                                    <input type="hidden" name="display_cookie_consent" value="off" /> <!-- This field will be sent as check box value when it's not checked -->
                                    <input class="form-check-input" type="checkbox" name="display_cookie_consent" id="display_cookie_consent" {{ old('display_cookie_consent', config('app.display_cookie_consent')) ? 'checked' : '' }}>

                                    <label class="form-check-label" for="display_cookie_consent">{{ __('Display Cookie Consent') }}</label>
                                    <small class="text-muted">{{ __('Display a notice regarding compliance with the EU Cookies Directive.') }}</small>
                                </div>
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
            $('#backgroundColor').colorpicker();

            $('#backgroundColor').on('colorpickerChange', function(event) {
                $('body').css('background-color', event.color.toString());
            });
        });
    });
</script>
@endsection
