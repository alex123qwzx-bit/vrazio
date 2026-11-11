<x-filament-panels::page.simple>
    @push('scripts')
        {{-- Firebase App & Auth SDK for Google Identity --}}
        <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const firebaseConfig = {
                    apiKey: '{{ config('services.firebase.api_key') }}',
                    authDomain: '{{ config('services.firebase.auth_domain') }}',
                    projectId: '{{ config('services.firebase.project_id') }}',
                    storageBucket: '{{ config('services.firebase.storage_bucket') }}',
                    messagingSenderId: '{{ config('services.firebase.messaging_sender_id') }}',
                    appId: '{{ config('services.firebase.app_id') }}'
                };

                if (!firebase.apps.length) {
                    firebase.initializeApp(firebaseConfig);
                }

                const googleBtn = document.getElementById('admin-google-signin-btn');
                const alertBox = document.getElementById('admin-google-alert');

                if (googleBtn) {
                    googleBtn.addEventListener('click', async () => {
                        googleBtn.disabled = true;
                        googleBtn.style.opacity = '0.6';
                        googleBtn.innerText = 'Connecting to Google Secure Vault...';
                        alertBox.style.display = 'none';

                        try {
                            const provider = new firebase.auth.GoogleAuthProvider();
                            provider.setCustomParameters({ prompt: 'select_account' });

                            const result = await firebase.auth().signInWithPopup(provider);
                            const idToken = await result.user.getIdToken();

                            googleBtn.innerText = 'Verifying Administrative Clearance...';

                            const res = await fetch('{{ route('admin.auth.google') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ id_token: idToken })
                            });

                            const data = await res.json();

                            if (res.ok && data.success) {
                                alertBox.style.display = 'block';
                                alertBox.style.backgroundColor = '#ECFDF5';
                                alertBox.style.borderColor = '#10B981';
                                alertBox.style.color = '#065F46';
                                alertBox.innerHTML = '<strong>Clearance Approved:</strong> ' + data.message + ' Redirecting...';
                                window.location.href = data.redirect_url || '{{ url('/admin') }}';
                            } else {
                                googleBtn.disabled = false;
                                googleBtn.style.opacity = '1';
                                googleBtn.innerHTML = '<svg style="width:18px;height:18px;margin-right:8px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg> Sign in with Google (Authorized Staff Only)';
                                alertBox.style.display = 'block';
                                alertBox.style.backgroundColor = '#FEF2F2';
                                alertBox.style.borderColor = '#EF4444';
                                alertBox.style.color = '#991B1B';
                                alertBox.innerHTML = '<strong>Access Denied:</strong> ' + (data.message || 'Administrative verification failed.');
                            }
                        } catch (err) {
                            googleBtn.disabled = false;
                            googleBtn.style.opacity = '1';
                            googleBtn.innerHTML = '<svg style="width:18px;height:18px;margin-right:8px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg> Sign in with Google (Authorized Staff Only)';
                            if (err.code !== 'auth/popup-closed-by-user') {
                                alertBox.style.display = 'block';
                                alertBox.style.backgroundColor = '#FEF2F2';
                                alertBox.style.borderColor = '#EF4444';
                                alertBox.style.color = '#991B1B';
                                alertBox.innerHTML = '<strong>Authentication Error:</strong> ' + (err.message || 'Google identity verification failed.');
                            }
                        }
                    });
                }
            });
        </script>
    @endpush

    <div style="margin-bottom: 24px; text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: rgba(212, 175, 55, 0.12); border: 1px solid #D4AF37; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #D4AF37; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10B981; box-shadow: 0 0 6px #10B981;"></span>
            High-Security Concierge Clearance &bull; Layer 2
        </div>
        <p style="font-size: 12px; color: #6B7280; margin: 0; line-height: 1.5;">
            Super Administration &bull; Sub-Manager Operational Portal
        </p>
    </div>

    {{-- Google Sign In Container --}}
    <div style="margin-bottom: 24px;">
        <div id="admin-google-alert" style="display: none; padding: 12px 14px; border: 1px solid; border-radius: 6px; font-size: 12px; margin-bottom: 14px; text-align: left; line-height: 1.5;"></div>

        <button type="button" 
                id="admin-google-signin-btn"
                style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 18px; background-color: #FFFFFF; color: #0E261C; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; border: 2px solid #D4AF37; border-radius: 6px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.2s ease;">
            <svg style="width: 20px; height: 20px; shrink-0;" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Sign in with Google (Authorized Staff Only)</span>
        </button>

        <div style="display: flex; align-items: center; text-align: center; margin: 20px 0 16px 0;">
            <div style="flex: 1; border-bottom: 1px solid #E5E7EB;"></div>
            <span style="padding: 0 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.12em; color: #9CA3AF; font-weight: 600;">Or Email &amp; Security Passcode</span>
            <div style="flex: 1; border-bottom: 1px solid #E5E7EB;"></div>
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #F3F4F6; text-align: center; font-size: 11px; color: #9CA3AF;">
        <span>All administrative logins are cryptographically logged and audited.</span>
        <br>
        <span style="color: #6B7280;">Sub-Manager accounts require Super Administrator approval before access is granted.</span>
    </div>
</x-filament-panels::page.simple>
