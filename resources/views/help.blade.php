@extends('layouts.app')

@section('content')
<div class="container">
    <p>{!! __('Install your community by going to <a href=":url">/install</a> route!', ['url' => route('install')]) !!}</p>
</div>
@endsection
