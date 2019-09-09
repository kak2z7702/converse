@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">@isset($user){{ __('Update User') }}@else{{ __('New User') }}@endisset</div>

                <div class="card-body">
                    <form method="POST" action="@isset($user){{ route('user.update', ['user' => $user->id, 'redirect=user.index']) }}@else{{ route('user.create', ['redirect=user.index']) }}@endisset" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-2 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', isset($user) ? $user->name : null) }}" required autocomplete="name" autofocus>

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-2 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-8">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', isset($user) ? $user->email : null) }}" required autocomplete="email">

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="photo" class="col-md-2 col-form-label text-md-right">{{ __('Avatar') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input id="photo" name="photo" type="file" class="custom-file-input @error('photo') is-invalid @enderror">
                                    <label for="photo" class="custom-file-label" data-browse="{{ __('Browse') }}">{{ __('Choose an image...') }}</label>
                                    <small id="photoHelpInline" class="text-muted">{{ __('Must be a 64px by 64px image (jpeg, png, bmp, gif, svg, or webp) and less than equal 1 MB.') }}</small>
                                </div>

                                @error('photo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        @if(isset($user) && isset($user->photo))
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
                                <img src="{{ asset('storage') . '/' . $user->photo }}" class="rounded-circle img-border" alt="Avatar" width="64" height="64">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="no_photo" id="no_photo" {{ old('no_photo') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="no_photo">
                                        {{ __('No Photo') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row">
                            <label for="password" class="col-md-2 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-8">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" @if (!isset($user)){{ 'required' }}@endif autocomplete="new-password">
                                @isset($user)
                                <small class="text-muted">{{ __('Enter password if you want to set a new password otherwise leave empty.') }}</small>
                                @endisset

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        @unless (isset($user) && $user->is_admin)
                        <div class="form-group row">
                            <label for="roles" class="col-md-2 col-form-label text-md-right">{{ __('Roles') }}</label>

                            <div class="col-md-8">
                                <select id="roles" name="roles[]" class="form-control @error('roles') is-invalid @enderror" multiple required>
                                    @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"@isset($user) @if (in_array($role->id, $user_roles)) {{ 'selected' }} @endif @endisset>{{ $role->title }}</option>
                                    @endforeach
                                </select>
                                <small id="rolesHelpInline" class="text-muted">{{ __('You can assign multiple roles by holding Ctrl key.') }}</small>

                                @error('roles')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        @endunless

                        <div class="form-group row mb-0">
                            <div class="col-md-12 offset-md-2">
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