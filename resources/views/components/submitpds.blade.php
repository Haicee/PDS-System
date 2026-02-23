<div id="submitpds-confirm" class="fixed inset-0 z-50 hidden bg-slate-900/50 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-slate-900">Submit PDS?</h3>
      <button type="button" class="p-2 text-slate-500 hover:text-slate-700" data-submitpds-cancel>✕</button>
    </div>
    <div class="px-6 py-5 space-y-4">
      <p class="text-slate-600">Are you sure you want to submit?</p>
      <div class="flex gap-3">
        <button type="button" class="flex-1 rounded-xl border px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300" data-submitpds-cancel>Cancel</button>
        <button type="button" class="flex-1 rounded-xl px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500" id="submitpds-confirm-ok">OK</button>
      </div>
    </div>
  </div>
</div>

<div id="submitpds-success" class="fixed inset-0 z-50 hidden bg-slate-900/50 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 px-6 py-6 text-center space-y-3">
    <div class="mx-auto h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
      </svg>
    </div>
    <h3 class="text-lg font-semibold text-slate-900">Submitted successfully</h3>
    <p class="text-slate-600 text-sm">Redirecting to review…</p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const confirmModal = document.getElementById('submitpds-confirm');
  const successModal = document.getElementById('submitpds-success');
  const confirmOk = document.getElementById('submitpds-confirm-ok');
  const cancelButtons = Array.from(document.querySelectorAll('[data-submitpds-cancel]'));
  const triggers = Array.from(document.querySelectorAll('[data-submitpds-trigger]'));
  let submitting = false;

  const openConfirm = () => confirmModal?.classList.remove('hidden');
  const closeConfirm = () => confirmModal?.classList.add('hidden');
  const showSuccess = () => successModal?.classList.remove('hidden');

  const submitForm = async (form) => {
    if (!form || submitting) return;
    submitting = true;
    closeConfirm();
    try {
      const formData = new FormData(form);
      const response = await fetch(form.getAttribute('action'), {
        method: form.getAttribute('method') || 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
        body: formData,
      });

      if (!response.ok) throw new Error('Submit failed');

      showSuccess();
      setTimeout(() => {
        window.location.href = '{{ route('pdsreview.pdsreview1') }}';
      }, 900);
    } catch (err) {
      alert('Submission failed. Please try again.');
      submitting = false;
    }
  };

  triggers.forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      const formId = trigger.getAttribute('data-submitpds-form');
      const form = formId ? document.getElementById(formId) : trigger.closest('form');
      if (!form) return;

      // Respect disabled state on the button
      if (trigger.getAttribute('aria-disabled') === 'true' || trigger.disabled) return;

      // Store form reference for confirm OK handler
      confirmOk.dataset.submitpdsTarget = form.id || '';
      openConfirm();
    });
  });

  cancelButtons.forEach(btn => btn.addEventListener('click', closeConfirm));

  if (confirmOk) {
    confirmOk.addEventListener('click', () => {
      const formId = confirmOk.dataset.submitpdsTarget;
      const form = formId ? document.getElementById(formId) : document.querySelector('form');
      submitForm(form);
    });
  }
});
</script>
