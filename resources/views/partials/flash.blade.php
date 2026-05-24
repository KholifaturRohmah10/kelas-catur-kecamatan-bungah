@if (session('success') || $errors->any())
    <div class="flash-stack" aria-live="polite" aria-atomic="true">
        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                Ada beberapa data yang perlu diperbaiki.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
