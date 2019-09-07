@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">@isset($role){{ __('Update Role') }}@else{{ __('New Role') }}@endisset</div>

                <div class="card-body">
                    <form method="POST" action="@isset($role){{ route('role.update', ['role' => $role->id, 'redirect=role.index']) }}@else{{ route('role.create', ['redirect=role.index']) }}@endisset">
                        @csrf

                        <div class="form-group row">
                            <label for="title" class="col-md-2 col-form-label text-md-right">{{ __('Title') }}</label>

                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', isset($role) ? $role->title : null) }}" required autocomplete="title" autofocus>

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="permissions" class="col-md-2 col-form-label text-md-right">{{ __('Permissions') }}</label>

                            <div class="col-md-8">
                                <select id="permissions" name="permissions[]" class="form-control @error('permissions') is-invalid @enderror" size="10" multiple required>
                                    @foreach ($permissions as $permission)
                                    <option value="{{ $permission->id }}"@isset($role) @if (in_array($permission->id, $role_permissions)) {{ 'selected' }} @endif @endisset>{{ $permission->title }}</option>
                                    @endforeach
                                </select>
                                <small id="permissionsHelpInline" class="text-muted">{{ __('You can assign multiple permissions by holding Ctrl key.') }}</small>

                                @error('permissions')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

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
