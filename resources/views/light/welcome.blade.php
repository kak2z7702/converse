@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="jumbotron mt-3">
        <h1 class="display-4">{{ __('Welcome!') }}</h1>
        <p class="lead">{{ __('Thank you for choosing Converse as your community manager. Although Converse is a fairly simple system, you will not be disappointed by its capabilites. Let\'s get started by installing Converse via the installation wizard.') }}</p>
        <hr class="my-4">
        <p>{!! __('Click on Install button down below to head to the installation wizard or simply goto <a href=":url">/install</a> route.', ['url' => route('install')]) !!}</p>
        <a class="btn btn-primary btn-lg" href="{{ route('install') }}" role="button">Install</a>
    </div>
</div>
@endsection
