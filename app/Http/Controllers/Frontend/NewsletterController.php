<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\SubscriberStatus;
use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        if (! (bool) setting('newsletter.newsletter_enabled', true)) {
            return $this->respond($request, false, 'Newsletter sign-ups are currently paused.', 423);
        }

        if ($request->filled('website')) {
            return $this->respond($request, true, 'Thanks for subscribing.');
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:100'],
            'consent' => ['accepted'],
        ]);

        $key = 'newsletter:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->respond($request, false, 'Too many requests. Please try again in a moment.', 429);
        }

        RateLimiter::hit($key, 60);

        $doubleOptIn = setting('newsletter.confirmation_policy', 'double_opt_in') === 'double_opt_in';

        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => strtolower(trim($data['email']))]);
        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->consent_at = now();
        $subscriber->consent_source = 'website';

        $newlyPending = $doubleOptIn && $subscriber->status !== SubscriberStatus::Subscribed;

        if ($newlyPending) {
            $subscriber->status = SubscriberStatus::Pending;
        } else {
            $subscriber->markSubscribed();
        }

        $subscriber->save();

        $message = $newlyPending
            ? 'Almost there — check your inbox to confirm your subscription.'
            : 'You are subscribed. Welcome aboard.';

        return $this->respond($request, true, $message);
    }

    public function unsubscribe(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'email' => ['required', 'email:rfc', 'max:255'],
            ]);

            $this->performUnsubscribe($data['email']);

            return $this->respond($request, true, 'You have been unsubscribed.');
        }

        if ($request->filled('email') && $request->hasValidSignature()) {
            $this->performUnsubscribe((string) $request->query('email'));

            return view('frontend.newsletter.unsubscribe', [
                'email' => (string) $request->query('email'),
                'done' => true,
                'seo' => Seo::make([
                    'title' => 'Unsubscribed',
                    'robots' => 'noindex,follow',
                ]),
            ]);
        }

        return view('frontend.newsletter.unsubscribe', [
            'email' => (string) $request->query('email', ''),
            'done' => false,
            'seo' => Seo::make([
                'title' => 'Unsubscribe',
                'robots' => 'noindex,follow',
            ]),
        ]);
    }

    protected function performUnsubscribe(string $email): void
    {
        $subscriber = NewsletterSubscriber::query()->where('email', strtolower(trim($email)))->first();

        if ($subscriber && $subscriber->status !== SubscriberStatus::Unsubscribed) {
            $subscriber->markUnsubscribed();
            $subscriber->save();
        }
    }

    protected function respond(Request $request, bool $ok, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : $status);
        }

        if (! $ok) {
            throw ValidationException::withMessages(['email' => $message]);
        }

        return back()->with('newsletter_status', $message);
    }
}
