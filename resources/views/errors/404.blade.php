@extends('errors.minimal')

@section('title', 'Page not found')

@section('content')
    <p class="font-display text-[6rem] leading-none text-line-strong md:text-[9rem]">404</p>
    <h1 class="mt-2 font-display text-fluid-title text-ink">This page has gone missing</h1>
    <p class="mx-auto mt-5 max-w-md text-muted">
        The link may be broken, or the page may have been moved. Try the journal, or search for what you had in mind.
    </p>

    <form action="{{ url('/search') }}" method="get" role="search" class="mx-auto mt-8 flex max-w-sm items-center gap-2">
        <input type="search" name="q" placeholder="Search the archive…" aria-label="Search"
            class="field flex-1">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div class="mt-8 flex justify-center gap-3">
        <a href="{{ url('/') }}" class="btn btn-outline">Back home</a>
        <a href="{{ url('/blog') }}" class="btn btn-outline">Browse the journal</a>
    </div>
@endsection
