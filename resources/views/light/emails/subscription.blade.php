<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Converse') }}</title>

    <style>
        body {
            margin:0;
            font-family:Nunito,sans-serif;
            font-size:.9rem;
            font-weight:400;
            line-height:1.6;
            color:#212529;
            text-align:left;
            background-color:#f8fafc
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin-top:0;
            margin-bottom:.5rem
        }

        p {
            margin-top:0;
            margin-bottom:1rem
        }

        small {
            font-size:80%
        }

        a {
            color:#3490dc;
            text-decoration:none;
            background-color:transparent;
            -webkit-text-decoration-skip:objects
        }

        a:hover {
            color:#1d68a7;
            text-decoration:underline
        }

        hr {
            margin-top:1rem;
            margin-bottom:1rem;
            border:0;
            border-top:1px solid rgba(0,0,0,.1)
        }

        .container {
            width:100%;
            padding-right:15px;
            padding-left:15px;
            margin-right:auto;
            margin-left:auto;
            max-width:1140px
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="width: 640px; margin: 30px auto">
            <h1>{{ config('app.name', 'Converse') }}</h1>
            <h2>{{ __('Subscription Notification') }}</h2>
            <p>There has been a new comment posted on this thread.</p>
            <h3>
                <a href="{{ route('thread.show', ['category_slug' => $comment->thread->topic->category->slug, 'topic_slug' => $comment->thread->topic->slug, 'thread_slug' => $comment->thread->slug]) }}">
                    {{ $comment->thread->title }}
                </a>
            </h3>
            <hr />
            <small style="color: #6c757d">
                Due to your subscription to this thread, you're being notified about new comments posted on this thread.
                In order to stop receiving these emails, unsubscribe from the thread in thread's Options panel.
            </small>
        </div>
    </div>
</body>
</html>

