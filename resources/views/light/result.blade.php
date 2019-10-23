@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Result</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $message }}<br /><a href="{{ $redirect }}">&larr; {{ __('Back') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
