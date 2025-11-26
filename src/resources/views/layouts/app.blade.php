<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH ATTENDANCE</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
    @stack('css')
</head>

<body>
    <header>
        <a href="{{ auth()->check() && auth()->user()->role === 0 ? '/' : route('admin.index') }}">
            <img src="{{ asset('logo.svg') }}" alt="COACHTECH">
        </a>
        @if (!request()->is('login') && !request()->is('register') && !request()->is('admin/login'))
        <nav class="{{ auth()->check() && auth()->user()->role === 1 ? 'header-nav-admin' : 'header-nav' }}">
            @if(auth()->check() && auth()->user()->role === 0)
            {{-- 一般ユーザー用ナビ --}}
            <a class="header-nav__link" href="/">勤怠</a>
            <a class="header-nav__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a class="header-nav__link" href="{{ route('attendance.request.index') }}">申請</a>
            @else
            {{-- 管理者用ナビ --}}
            <a class="header-nav-admin__link" href="{{ route('admin.index') }}">勤怠一覧</a>
            <a class="header-nav-admin__link" href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
            <a class="header-nav-admin__link" href="{{ route('attendance.request.index') }}">申請一覧</a>
            @endif

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="header-nav__button">ログアウト</button>
            </form>
        </nav>
        @endif
    </header>
    <main>
        @yield('content')
    </main>

    @if (session('status'))
    <script>
        alert("{{ session('status') }}");
    </script>
    @endif

    @if (session('error'))
    <script>
        alert("エラー: {{ session('error') }}");
    </script>
    @endif
</body>

</html>