@extends('emails.layout')

@section('title', __('email.welcome.subject', [], $locale))

@section('content')
<h1>{{ __('email.welcome.greeting', ['name' => $user->firstname ?? $user->name], $locale) }}</h1>

<div class="content">
    <p>{{ __('email.welcome.body', [], $locale) }}</p>

    <div class="details-box">
        <h3>{{ __('email.welcome.next_steps', [], $locale) }}</h3>
        <ul>
            <li>{{ __('email.welcome.verify_email', [], $locale) }}</li>
            <li>{{ __('email.welcome.complete_profile', [], $locale) }}</li>
            <li>{{ __('email.welcome.browse_projects', [], $locale) }}</li>
        </ul>
    </div>

    <center>
        <a href="{{ config('app.frontend_url') }}/login" class="button">
            {{ __('email.common.button.login', [], $locale) }}
        </a>
    </center>

    <p>{{ __('email.welcome.footer', [], $locale) }}</p>
</div>
@endsection
