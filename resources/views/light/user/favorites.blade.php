@extends(config('theme.layout'))

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Favorites') }}
                </div>

                <div class="card-body">
                    @forelse ($favorites as $favorite)
                    <div class="row @if (!$loop->last){{ 'mb-3' }}@endif">
                        <div class="col-10">
                            <a href="{{ route('thread.show', ['category_slug' => $favorite->thread->topic->category->slug, 'topic_slug' => $favorite->thread->topic->slug, 'thread_slug' => $favorite->thread->slug]) }}" target="_blank"><h5 class="mt-2 mb-1">{{ $favorite->thread->title }}</h5></a>
                        </div>
                        <div class="col-2 pt-1">
                            <a href="{{ route('thread.unfavorite', ['thread' => $favorite->thread->id, 'redirect=user.favorites']) }}" class="btn btn-warning btn-sm float-right">{{ __('Unfavorite') }}</a>
                        </div>
                    </div>
                    @empty
                    {{ __('You have no favorites.') }}
                    @endforelse
                    @if ($favorites->hasMorePages())
                    <div class="row mt-3">
                        <div class="col-12">
                        {{ $favorites->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
