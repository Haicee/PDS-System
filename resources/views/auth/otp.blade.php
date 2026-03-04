<x-guest-layout>
    <div class="flex items-center justify-center px-4">
        <section class="w-full max-w-md rounded-3xl border border-white/10 bg-white/70 p-8 shadow-2xl backdrop-blur">
            <div class="mb-6 space-y-2 text-center">
                <img src="{{ asset('images/Bfar logo.png') }}" alt="BFAR" class="mx-auto h-14 w-auto object-contain drop-shadow-md">
                <h2 class="text-2xl font-semibold text-slate-900">Enter your one-time passcode</h2>
                <p class="text-sm text-slate-600">We've sent a 6-digit code to <span class="font-medium text-slate-800">{{ $email }}</span>.</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify', [], false) }}" class="space-y-5" id="otpForm">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700 block mb-2">One-time passcode</label>
                    <div class="flex justify-between gap-2">
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

                <button type="submit" class="group relative inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-500 via-sky-500 to-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-500">
                    <span class="absolute inset-0 rounded-2xl opacity-0 transition group-hover:opacity-20" style="background: linear-gradient(120deg, rgba(255,255,255,.7), rgba(255,255,255,0));"></span>
                    Verify and continue
                </button>
            </form>

            <div class="mt-4 text-center text-sm text-slate-600">
                <span id="countdown">03:00</span>
                <button id="resendBtn" class="ml-2 text-blue-600 underline hidden">Resend</button>
            </div>
        </section>
    </div>

    <script>
const otpInputs = document.querySelectorAll('.otp-input');
const otpValueInput = document.getElementById('otpValue');
const resendBtn = document.getElementById('resendBtn');
const countdownEl = document.getElementById('countdown');
const COUNTDOWN_SEC = 180; // 3 minutes
let remaining = COUNTDOWN_SEC;
let countdownInterval;

// Autofocus first input
otpInputs[0].focus();

// Update hidden OTP field
function updateOtpValue() {
    let otp = '';
    otpInputs.forEach(input => otp += input.value);
    otpValueInput.value = otp;
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

// Format seconds as MM:SS
function formatTime(sec) {
    const m = String(Math.floor(sec / 60)).padStart(2, '0');
    const s = String(sec % 60).padStart(2, '0');
    return `${m}:${s}`;
}

// Start countdown
function startCountdown() {
    remaining = COUNTDOWN_SEC;
    resendBtn.classList.add('hidden');
    countdownEl.style.display = 'inline';
    countdownEl.textContent = formatTime(remaining);

    clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
            clearInterval(countdownInterval);
            countdownEl.style.display = 'none'; // hide countdown completely
            resendBtn.classList.remove('hidden'); // show resend
        } else {
            countdownEl.textContent = formatTime(remaining);
        }
    }, 1000);
}

// Resend button click
// Resend button click
resendBtn.addEventListener('click', () => {
    // Immediately hide the resend button and show countdown
    resendBtn.classList.add('hidden');
    countdownEl.style.display = 'inline';
    startCountdown();

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
        }
    }).catch(() => {
        alert('Failed to resend OTP. Please try again.');
    });
});

// Initialize countdown on page load
startCountdown();
</script>
</x-guest-layout>