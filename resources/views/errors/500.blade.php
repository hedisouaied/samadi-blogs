@extends('errors.minimal')

@section('title', 'Server error')

@section('content')
    <p class="font-display text-[6rem] leading-none text-line-strong md:text-[9rem]">500</p>
    <h1 class="mt-2 font-display text-fluid-title text-ink">Something went wrong on our end</h1>
    <p class="mx-auto mt-5 max-w-md text-muted">
        We hit an unexpected problem while preparing this page. Please try again in a moment.
    </p>

    <div class="mt-8 flex justify-center gap-3">
        <a href="{{ url('/') }}" class="btn btn-primary">Back home</a>
        <a href="{{ url('/blog') }}" class="btn btn-outline">Browse the journal</a>
    </div>
@endsection
