<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $promoImages = [];
        $imgPath = public_path('img');
        if (File::isDirectory($imgPath)) {
            $promoImages = collect(File::files($imgPath))
                ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp', 'gif', 'avif'], true))
                ->sortBy(fn ($file) => strtolower($file->getFilename()))
                ->map(fn ($file) => asset('img/' . $file->getFilename()))
                ->values()
                ->all();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'promoImages' => $promoImages,
            'locale' => app()->getLocale(),
            'flash' => [
                'status' => $request->session()->get('status'),
            ],
        ];
    }
}
