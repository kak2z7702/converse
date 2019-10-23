@extends(config('theme.layout'))

@section('content')
<div class="container">
    <h1 class="mt-3">Welcome!</h1>
    <p>{!! __('Install your community by going to <a href=":url">/install</a> route!', ['url' => route('install')]) !!}</p>
</div>
@endsection
