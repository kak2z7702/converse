@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Profile') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('user.profile', ['redirect' => request('redirect')]) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-2 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', auth()->user()->name) }}" required autocomplete="name" autofocus>

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
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="email">

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
                                <div class="form-check">
                                    <input type="hidden" name="show_email" value="off" /> <!-- This field will be sent as check box value when it's not checked -->
                                    <input class="form-check-input" type="checkbox" name="show_email" id="show_email" {{ old('show_email', auth()->user()->show_email) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_email">{{ __('Show Email') }}</label>
                                    <small class="text-muted">{{ __('Display your email in different sections of community.') }}</small>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <div class="form-group row">
                            <label for="photo" class="col-md-2 col-form-label text-md-right">{{ __('Avatar') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input id="photo" name="photo" type="file" class="custom-file-input @error('photo') is-invalid @enderror">
                                    <label for="photo" class="custom-file-label" data-browse="{{ __('Browse') }}">@if (isset(auth()->user()->photo)){!! auth()->user()->photo !!}@else{{ __('Choose an image...') }}@endif</label>
                                    <small class="text-muted">{{ __('Must be a 64px by 64px image (jpeg, png, bmp, gif, svg, or webp) and less than equal 1 MB.') }}</small>
                                </div>

                                @error('photo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div id="photoPreviewRow" class="form-group row" style="display: none">
                            <label class="col-md-2 col-form-label text-md-right">{{ __('New Avatar') }}</label>

                            <div class="col-md-8">
                                <img id="photoPreview" src="#" class="rounded-circle img-border" alt="Preview" width="64" height="64">
                            </div>
                        </div>

                        @if (isset(auth()->user()->photo))
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label text-md-right">{{ __('Current Avatar') }}</label>

                            <div class="col-md-8">
                                <img src="{{ asset('storage') . '/' . auth()->user()->photo }}" class="rounded-circle img-border" alt="Avatar" width="64" height="64">
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

                        <hr />

                        <div class="form-group row">
                            <label for="bio" class="col-md-2 col-form-label text-md-right">{{ __('Bio') }}</label>

                            <div class="col-md-8">
                                <textarea id="bio" class="form-control @error('bio') is-invalid @enderror" name="bio">{{ old('bio', auth()->user()->bio) }}</textarea>
                                <small class="text-muted">{{ __('Lay it on us.') }}</small>

                                @error('bio')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <hr />

                        <div class="form-group row">
                            <label for="newPassword" class="col-md-2 col-form-label text-md-right">{{ __('New Password') }}</label>

                            <div class="col-md-8">
                                <input id="newPassword" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" autocomplete="new-password">
                                <small class="text-muted">{{ __('Enter password if you want to set a new password otherwise leave empty.') }}</small>

                                @error('new_password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="newPasswordConfirm" class="col-md-2 col-form-label text-md-right">{{ __('Confirm New Password') }}</label>

                            <div class="col-md-8">
                                <input id="newPasswordConfirm" type="password" class="form-control" name="new_password_confirmation" autocomplete="new-password">
                                <small class="text-muted">{{ __('Confirm new password if you want to set a new password otherwise leave empty.') }}</small>
                            </div>
                        </div>

                        <hr />

                        <div class="form-group row">
                            <label for="password" class="col-md-2 col-form-label text-md-right">{{ __('Current Password') }}</label>

                            <div class="col-md-8">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="password" required>
                                <small class="text-muted">{{ __('For any changes to take place, enter your current password to see if it\'s really you.') }}</small>

                                @error('password')
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

                $('#photoPreviewRow').show();
                $('#photoPreview').attr('src', blob);

                URL.revokeObjectURL(blob);
            });
        });
    });
</script>
@endsection
