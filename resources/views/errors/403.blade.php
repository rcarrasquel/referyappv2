<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ReferyApp</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(109,190,69,0.20),_transparent_40%),radial-gradient(circle_at_20%_80%,_rgba(17,17,17,0.10),_transparent_45%)]"></div>

        <div class="relative w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-8 shadow-xl sm:p-10">
            <p class="text-xs font-semibold tracking-[0.18em] text-[#6DBE45]">REFERY.APP</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-[#111111]">Access denied</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                You do not have permission to access this section. If you believe this is an error, contact your administrator.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-[#6DBE45] px-4 py-2 text-sm font-semibold text-[#111111] transition hover:bg-[#8EDB63]">
                        Back to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-[#6DBE45] px-4 py-2 text-sm font-semibold text-[#111111] transition hover:bg-[#8EDB63]">
                        Go to Login
                    </a>
                @endauth
                <a href="https://xper.team" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Xperteam LLC
                </a>
            </div>

            <p class="mt-6 text-xs text-slate-500">
                A product of Xperteam LLC -
                <a href="https://xper.team" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-700 hover:text-slate-900">xper.team</a>
            </p>
        </div>
    </div>
</body>
</html>
