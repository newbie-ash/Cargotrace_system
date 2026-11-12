<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'company_name' => $request->user()->company_name,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'notifications' => [
                'unread_count' => fn () => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
                'recent' => fn () => $request->user() ? $request->user()->notifications()->take(6)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'read' => ! is_null($n->read_at),
                    'data' => $n->data,
                    'created_at' => $n->created_at?->diffForHumans() ?? 'Baru saja',
                ]) : [],
            ],
            'appName' => config('app.name', 'Delixie'),
        ]);
    }
}
