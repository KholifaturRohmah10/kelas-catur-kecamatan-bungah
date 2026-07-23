@if (session('success') || $errors->any())
    <div id="floating-flash-stack" aria-live="polite" aria-atomic="true" style="position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; display: flex; flex-direction: column; gap: 12px; min-width: 320px; max-width: 90vw; pointer-events: none;">
        @if (session('success'))
            <div class="alert alert-success floating-alert" role="status" style="box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); margin-bottom: 0; pointer-events: auto;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error floating-alert" role="alert" style="box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); margin-bottom: 0; pointer-events: auto;">
                Ada beberapa data yang perlu diperbaiki.
                <ul style="margin-top: 8px; margin-bottom: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.floating-alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 3000);
    </script>
@endif
