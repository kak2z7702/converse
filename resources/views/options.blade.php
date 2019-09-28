@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Options') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('options', ['redirect' => request('redirect')]) }}">
                        @csrf

                        <div class="form-group row">
                            <label for="communityName" class="col-md-2 col-form-label text-md-right">{{ __('Community Name') }}</label>

                            <div class="col-md-8">
                                <input id="communityName" type="text" class="form-control @error('community_name') is-invalid @enderror" name="community_name" value="{{ old('community_name', isset($options) ? $options->community->name : null) }}" required autocomplete="community_name">

                                @error('community_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <hr />

                        <div class="form-group row">
                            <div class="col-md-10 offset-md-2">
                                <div class="form-check">
                                    <input type="hidden" name="display_cookie_consent" value="off" /> <!-- This field will be sent as check box value when it's not checked -->
                                    <input class="form-check-input" type="checkbox" name="display_cookie_consent" id="display_cookie_consent" {{ old('display_cookie_consent', $options->display_cookie_consent) ? 'checked' : '' }}>

                                    <label class="form-check-label" for="display_cookie_consent">{{ __('Display cookie consent') }}</label>
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
@endsection
