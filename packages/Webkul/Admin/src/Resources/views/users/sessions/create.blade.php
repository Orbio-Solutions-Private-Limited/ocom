<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.sessions.title')
    </x-slot>

    <div class="admin-login">
        <aside class="admin-login-brand" aria-hidden="true">
            <span class="admin-login-orbit"></span>
            <span class="admin-login-orbit admin-login-orbit--inner"></span>

            <img
                class="admin-login-mark"
                src="{{ bagisto_asset('images/mark.svg') }}"
                alt=""
            >

            <p class="admin-login-wordmark">PLUTO</p>
        </aside>

        <section class="admin-login-panel">
            <div class="admin-login-form">
                <h1 class="admin-login-title">
                    @lang('admin::app.users.sessions.title')
                </h1>

                <form
                    method="POST"
                    action="{{ route('admin.session.store') }}"
                >
                    @csrf

                    <div class="mb-4">
                        <label
                            for="email"
                            class="required mb-1.5 flex items-center gap-1 text-xs font-normal uppercase tracking-widest text-gray-600"
                        >
                            @lang('admin::app.users.sessions.email')
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            required
                            autofocus
                            class="admin-login-input"
                            placeholder="@lang('admin::app.users.sessions.email')"
                        >

                        @error('email')
                            <p class="mt-1 text-xs italic text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative mb-4">
                        <label
                            for="password"
                            class="required mb-1.5 flex items-center gap-1 text-xs font-normal uppercase tracking-widest text-gray-600"
                        >
                            @lang('admin::app.users.sessions.password')
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="admin-login-input ltr:pr-10 rtl:pl-10"
                            placeholder="@lang('admin::app.users.sessions.password')"
                        >

                        <button
                            type="button"
                            class="icon-view absolute top-10.5 -translate-y-2/4 cursor-pointer border-0 bg-transparent p-0 text-2xl text-gray-500 ltr:right-2 rtl:left-2"
                            id="visibilityIcon"
                            onclick="switchVisibility()"
                            aria-label="@lang('admin::app.users.sessions.password')"
                        ></button>

                        @error('password')
                            <p class="mt-1 text-xs italic text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 flex flex-col gap-4">
                        <button
                            type="submit"
                            class="admin-login-submit"
                            aria-label="{{ trans('admin::app.users.sessions.submit-btn') }}"
                        >
                            @lang('admin::app.users.sessions.submit-btn')
                        </button>

                        <a
                            class="text-center text-xs font-normal leading-6 text-[#4E6A24] hover:underline"
                            href="{{ route('admin.forget_password.create') }}"
                        >
                            @lang('admin::app.users.sessions.forget-password-link')
                        </a>
                    </div>
                </form>

                <p class="admin-login-credit">
                    @lang('admin::app.users.sessions.powered-by-description')
                </p>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            function switchVisibility() {
                let passwordField = document.getElementById("password");
                let visibilityIcon = document.getElementById("visibilityIcon");

                passwordField.type = passwordField.type === "password" ? "text" : "password";
                visibilityIcon.classList.toggle("icon-view-close");
            }
        </script>
    @endpush
</x-admin::layouts.anonymous>
