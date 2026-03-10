<x-guest-layout>
    <style>
        @keyframes otp-shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        .otp-shake {
            animation: otp-shake 0.4s ease;
        }
    </style>
    <div class="flex items-center justify-center px-4">
        <section class="w-full max-w-md rounded-3xl border border-white/10 bg-white/70 p-8 shadow-2xl backdrop-blur">
            <div class="mb-6 space-y-2 text-center">
                <img src="{{ asset('images/Bfar logo.png') }}" alt="BFAR" class="mx-auto h-14 w-auto object-contain drop-shadow-md">
                <h2 class="text-2xl font-semibold text-slate-900">Enter your one-time passcode</h2>
                <p class="text-sm text-slate-600">We've sent a 6-digit code to <span class="font-medium text-slate-800">{{ $email }}</span>.</p>
            </div>

            @if (session('status'))
                <div id="statusAlert" class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @else
                <div id="statusAlert" class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" style="display: none;"></div>
            @endif

            <form method="POST" action="{{ route('otp.verify', [], false) }}" class="space-y-5" id="otpForm">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700 block mb-2">One-time passcode</label>
                    <div id="otpBoxes" class="flex justify-between gap-2 {{ $errors->has('code') ? 'otp-shake' : '' }}">
                        @for ($i = 0; $i < 6; $i++)
                            <input type="text"
                                   maxlength="1"
                                   pattern="[0-9]*"
                                   inputmode="numeric"
                                   class="otp-input w-12 h-12 text-center text-lg font-semibold border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                   id="otp-{{ $i }}"
                                   data-index="{{ $i }}">
                        @endfor
                    </div>
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <input type="hidden" name="code" id="otpValue">

            </form>

            <div class="mt-4 text-center text-sm text-slate-600 space-y-3">
                <div>
                    <span id="countdown" style="display: none;">03:00</span>
                    <button id="resendBtn" class="ml-2 text-blue-600 underline hidden">Resend</button>
                </div>
                <form method="POST" action="{{ route('otp.cancel', [], false) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/80 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h2.008c.25 0 .48-.13.606-.341l.772-1.286A2 2 0 0 1 8.74 6h6.52a2 2 0 0 1 1.754 1.014l.772 1.286c.126.211.356.341.606.341H20.4c.887 0 1.6.716 1.6 1.6v5.4A2 2 0 0 1 20 18h-2.03c-.206 0-.407.064-.575.182l-1.492 1.05a2 2 0 0 1-1.15.368H9.247a2 2 0 0 1-1.15-.368l-1.492-1.05A.97.97 0 0 0 6.03 18H4a2 2 0 0 1-2-2v-5.4C2 9.716 2.716 9 3.6 9Z" />
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </section>
    </div>

    <script>
const otpInputs = document.querySelectorAll('.otp-input');
const otpValueInput = document.getElementById('otpValue');
const otpForm = document.getElementById('otpForm');
const resendBtn = document.getElementById('resendBtn');
const countdownEl = document.getElementById('countdown');
const otpBoxes = document.getElementById('otpBoxes');
const statusAlert = document.getElementById('statusAlert');
const COUNTDOWN_SEC = 180; // 3 minutes
const COUNTDOWN_MS = COUNTDOWN_SEC * 1000;
const RESEND_KEY = 'otp_resend_expires_at';
let countdownInterval;
let autoSubmitted = false;

// Autofocus first input
otpInputs[0].focus();

// Update hidden OTP field
function updateOtpValue() {
    let otp = '';
    otpInputs.forEach(input => otp += input.value);
    otpValueInput.value = otp;

    if (!autoSubmitted && otp.length === otpInputs.length && otp.match(/^\d{6}$/)) {
        autoSubmitted = true;
        otpForm.requestSubmit();
    }
}

function triggerOtpShake() {
    if (!otpBoxes) return;
    otpBoxes.classList.remove('otp-shake');
    void otpBoxes.offsetWidth; // restart animation
    otpBoxes.classList.add('otp-shake');
}

// Input navigation & paste handling
otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const value = e.target.value.replace(/[^0-9]/g, '');
        e.target.value = value;
        if (value && index < otpInputs.length - 1) otpInputs[index + 1].focus();
        updateOtpValue();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        pasteData.split('').forEach((num, i) => {
            if (otpInputs[i]) otpInputs[i].value = num;
        });
        updateOtpValue();
        if (pasteData.length < 6) otpInputs[pasteData.length]?.focus();
    });
});

// Shake on initial validation error
if (@json($errors->has('code'))) {
    triggerOtpShake();
}

// Format seconds as MM:SS
function formatTime(sec) {
    const m = String(Math.floor(sec / 60)).padStart(2, '0');
    const s = String(sec % 60).padStart(2, '0');
    return `${m}:${s}`;
}

// Start countdown using expiry timestamp
function startCountdown(expiryTs) {
    localStorage.setItem(RESEND_KEY, expiryTs.toString());
    resendBtn.classList.add('hidden');
    const remainingMsInitial = Math.max(0, expiryTs - Date.now());
    countdownEl.textContent = formatTime(Math.ceil(remainingMsInitial / 1000));
    countdownEl.style.display = 'inline';

    clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        const remainingMs = expiryTs - Date.now();
        const remainingSec = Math.max(0, Math.ceil(remainingMs / 1000));

        if (remainingMs <= 0) {
            clearInterval(countdownInterval);
            localStorage.removeItem(RESEND_KEY);
            countdownEl.style.display = 'none'; // hide countdown completely
            resendBtn.classList.remove('hidden'); // show resend
        } else {
            countdownEl.textContent = formatTime(remainingSec);
        }
    }, 1000);
}

function updateStatusMessage(message) {
    if (!statusAlert) return;
    statusAlert.textContent = message;
    statusAlert.style.display = 'block';
}

// Resend button click
resendBtn.addEventListener('click', () => {
    // Immediately hide the resend button and show countdown
    const expiryTs = Date.now() + COUNTDOWN_MS;
    countdownEl.style.display = 'inline';
    startCountdown(expiryTs);

    // Send the OTP request asynchronously
    fetch('{{ route("otp.resend", [], false) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(res => {
        if (!res.ok) {
            // Optional: show error to user
            alert('Failed to resend OTP. Please try again.');
        } else {
            updateStatusMessage('A new code has been sent to your email.');
        }
    }).catch(() => {
        alert('Failed to resend OTP. Please try again.');
    });
});

// Initialize countdown on page load
(() => {
    const storedExpiry = parseInt(localStorage.getItem(RESEND_KEY) || '0', 10);
    const now = Date.now();

    if (storedExpiry && storedExpiry > now) {
        startCountdown(storedExpiry);
    } else {
        localStorage.removeItem(RESEND_KEY);
        countdownEl.style.display = 'none';
        resendBtn.classList.remove('hidden');
    }
})();
</script>
</x-guest-layout>