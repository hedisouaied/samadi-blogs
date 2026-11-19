@extends('errors.minimal')

@section('title', __('Server error'))

@section('content')
    <p class="font-display text-[6rem] leading-none text-line-strong md:text-[9rem]">500</p>
    <h1 class="mt-2 font-display text-fluid-title text-ink">{{ __('Something went wrong on our end') }}</h1>
    <p class="mx-auto mt-5 max-w-md text-muted">
        {{ __('We hit an unexpected problem while preparing this page. Please try again in a moment.') }}
    </p>

    <div class="mt-8 flex justify-center gap-3">
        <a href="{{ url('/') }}" class="btn btn-primary">{{ __('Back home') }}</a>
        <a href="{{ url('/blog') }}" class="btn btn-outline">{{ __('Browse the journal') }}</a>
    </div>
@endsection
