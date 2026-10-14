@extends('frontend.layouts.app')

@section('content')
    <section class="shell flex min-h-[60vh] items-center py-20">
        <div class="mx-auto max-w-xl text-center">
            @if ($done)
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sunken text-accent">
                    <x-frontend.icon name="check" class="h-7 w-7" />
                </span>
                <h1 class="mt-8 font-display text-fluid-title text-ink">You have been unsubscribed</h1>
                <p class="mt-4 text-muted">We have removed <strong class="text-ink">{{ $email }}</strong> from the newsletter. We are sorry to see you go.</p>
                <a href="{{ route('home') }}" class="btn btn-outline mt-8">Back to home</a>
            @else
                <p class="eyebrow eyebrow-accent">Newsletter</p>
                <h1 class="mt-3 font-display text-fluid-title text-ink">Unsubscribe</h1>
                <p class="mt-4 text-muted">Enter the email address you subscribed with and we will remove it from our list.</p>

                <form action="{{ route('newsletter.unsubscribe') }}" method="post" class="mx-auto mt-8 max-w-md space-y-3 text-left">
                    @csrf
                    <div>
                        <label for="unsubscribe-email" class="field-label">Email address</label>
                        <input id="unsubscribe-email" name="email" type="email" required value="{{ $email }}" class="field" placeholder="you@example.com">
                        @error('email')
                            <p class="mt-2 text-sm text-accent">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-outline w-full">Unsubscribe me</button>
                </form>
            @endif
        </div>
    </section>
@endsection
