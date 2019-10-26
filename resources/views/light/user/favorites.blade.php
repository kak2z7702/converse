@extends(config('theme.layout'))

@section('css')
#search {
    -webkit-border-top-right-radius: 0 !important;
    -webkit-border-bottom-right-radius: 0 !important;
    -moz-border-radius-topright: 0 !important;
    -moz-border-radius-bottomright: 0 !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    min-width: 50px;
}
@endsection

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('index') }}">{{ __('Home') }}</a> >
                    <a href="{{ route('user.show', ['user' => auth()->user()->id]) }}">{{ __('User') }}</a> >
                    <a href="{{ route('user.favorites') }}">{{ __('Favorites') }}</a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <form action="{{ route('user.favorites') }}" method="get">
                                <div class="btn-group float-right" role="group" aria-label="Search query">
                                    <input id="search" name="q" type="text" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', request()->filled('q') ? request()->q : '') }}" placeholder="Thread..." autofocus>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @forelse ($threads as $thread)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-10">
                            <a href="{{ route('thread.show', ['category_slug' => $thread->topic->category->slug, 'topic_slug' => $thread->topic->slug, 'thread_slug' => $thread->slug]) }}" target="_blank"><h5 class="mt-2 mb-1">{{ $thread->title }}</h5></a>
                        </div>
                        <div class="col-2 pt-1">
                            <a href="{{ route('thread.unfavorite', ['thread' => $thread->id, 'redirect=user.favorites']) }}" class="btn btn-warning btn-sm float-right">{{ __('Unfavorite') }}</a>
                        </div>
                    </div>
                    @empty
                    {{ __('There are no favorite threads to display.') }}
                    @endforelse
                    @if ($threads->lastPage() > 1)
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $threads->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
