<x-app-layout>
<div id="autosaveOverlay5" class="autosave-overlay hidden">Saving…</div>
<form id="pds-form5" method="POST" action="{{ route('pds.submit') }}" class="w-full" enctype="multipart/form-data">
@csrf

<div class="max-w-6xl mx-auto p-4 flex justify-end">
  <a href="{{ route('pds.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">
    Download PDF
  </a>
</div>

<style>
textarea:focus { outline: none; box-shadow: none; }
[contenteditable]:focus { outline: none; margin: 0; padding: 0; }

body { margin: 0; }
.autosave-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; font-size: 20px; font-weight: 700; color: #111; }
.autosave-overlay.hidden { display: none; }

.signature-box {
  position: relative;
  background: repeating-linear-gradient(45deg, #f5f5f5, #f5f5f5 10px, #e5e5e5 10px, #e5e5e5 20px);
  border: 1px solid #d1d5db;
  border-radius: 6px;
  overflow: hidden;
}
.signature-box.signature-has-image {
  background: transparent;
  border-color: transparent;
}
.signature-box.signature-has-image img {
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
}

@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('pds-form5');
  const autosaveOverlay = document.getElementById('autosaveOverlay5');
  const submitBtn = document.getElementById('submit-pds-btn');
  if (!form || !submitBtn) return;

  const sessionData = @json(session('pds', []));
  const draftData = @json($data ?? []);
  const flat = {};

  const walk = (obj, prefix = '') => {
    if (obj === null || obj === undefined) return;
    if (typeof obj !== 'object') { if (prefix) flat[prefix] = obj; return; }
    if (Array.isArray(obj)) {
      obj.forEach((v, i) => walk(v, prefix ? `${prefix}[${i}]` : `${i}`));
    } else {
      Object.entries(obj).forEach(([k, v]) => walk(v, prefix ? `${prefix}[${k}]` : k));
    }
  };

  walk(sessionData);
  walk(draftData);

  const cssName = (name) => name.replace(/(["'\\])/g, '\\$1');
  const setField = (el, value) => {
    if (!el) return;
    if (el.type === 'checkbox' || el.type === 'radio') {
      el.checked = Array.isArray(value) ? value.map(String).includes(String(el.value)) : String(el.value) === String(value);
    } else {
      el.value = value;
      if (el.tagName === 'TEXTAREA') {
        el.dispatchEvent(new Event('input'));
      }
    }
  };

  Object.entries(flat).forEach(([name, value]) => {
    const exact = Array.from(document.querySelectorAll(`[name="${cssName(name)}"]`));
    if (exact.length) {
      exact.forEach(el => setField(el, value));
      return;
    }
    const matchIndex = name.match(/\[(\d+)\]$/);
    if (matchIndex) {
      const idx = parseInt(matchIndex[1], 10);
      const base = name.replace(/\[\d+\]$/, '[]');
      const arrFields = Array.from(document.querySelectorAll(`[name="${cssName(base)}"]`));
      if (arrFields[idx]) setField(arrFields[idx], value);
    }
  });

  const isNA = (val) => {
    const v = (val || '').trim().toUpperCase();
    return v === 'NA' || v === 'N/A' || v === 'NONE';
  };

  const isFilled = (el) => {
    if (el.type === 'file') return el.files && el.files.length > 0;
    if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
    const val = (el.value || '').trim();
    if (isNA(val)) return true;
    return val !== '';
  };

  const storageKey = 'pds_form_step5_' + ({{ auth()->id() ?? 0 }});
  const singleSelectCheckboxNames = new Set();
  const storageBase = "{{ asset('storage') }}/";
  const initialSignaturePath = @json($signaturePath ?? '');

  const signaturePreviewImg5 = document.getElementById('signaturePreviewImg5');
  const signaturePlaceholder5 = document.getElementById('signaturePlaceholder5');
  const signatureBox5 = document.getElementById('signatureBox5');
  const signatureDataInput5 = document.getElementById('signature_data5');
  const signaturePathInput5 = document.getElementById('signature_path5');

  const buildSignatureUrl5 = (path) => {
    if (!path) return '';
    const cleaned = path.replace(/^public\//, '');
    return storageBase + cleaned;
  };

  const updateSignaturePreview5 = () => {
    if (!signaturePreviewImg5 || !signaturePlaceholder5) return;
    const dataUrl = signatureDataInput5?.value;
    const pathVal = signaturePathInput5?.value || initialSignaturePath;
    const url = dataUrl || buildSignatureUrl5(pathVal);
    if (url) {
      signaturePreviewImg5.src = url;
      signaturePreviewImg5.classList.remove('hidden');
      signaturePlaceholder5.classList.add('hidden');
      signatureBox5?.classList.add('signature-has-image');
    } else {
      signaturePreviewImg5.classList.add('hidden');
      signaturePlaceholder5.classList.remove('hidden');
      signatureBox5?.classList.remove('signature-has-image');
    }
  };

  window.handleSignatureUpload5 = (file) => {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        const threshold = 240;
        for (let i = 0; i < data.length; i += 4) {
          if (data[i] > threshold && data[i + 1] > threshold && data[i + 2] > threshold) {
            data[i + 3] = 0;
          }
        }
        ctx.putImageData(imageData, 0, 0);

        const output = canvas.toDataURL('image/png');
        if (signatureDataInput5) signatureDataInput5.value = output;
        if (signaturePathInput5) signaturePathInput5.value = '';
        updateSignaturePreview5();
        saveCache();
        autoSaveToServer();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  };

  const ensureRemarkRows = (values = []) => {
    const tbody = document.getElementById('remarks-rows');
    const proto = document.getElementById('remarks-prototype');
    if (!tbody || !proto) return [];

    const existing = getRemarks();
    const targetCount = Math.max(values.length, existing.length || 1);

    const makeRow = (val = '') => {
      const clone = proto.cloneNode(true);
      clone.id = '';
      clone.value = val;
      attachReactive(clone);

      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.className = 'border-2 h-20 border-black relative';
      td.appendChild(clone);

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = '✕';
      btn.className = 'text-red-600 font-bold text-lg px-1';
      btn.style.position = 'absolute';
      btn.style.right = '-34px';
      btn.style.top = '10px';

      btn.onclick = () => {
        tbody.removeChild(tr);
        validateRequired();
        saveCache();
        autoSaveToServer();
      };

      td.appendChild(btn);
      tr.appendChild(td);
      tbody.appendChild(tr);
    };

    while (getRemarks().length < targetCount) {
      makeRow(values[getRemarks().length] ?? '');
    }

    return getRemarks();
  };

  const loadCache = (overrideData = null) => {
    let data = {};
    try {
      data = JSON.parse(localStorage.getItem(storageKey) || '{}');
    } catch (e) {
      data = {};
    }

    if (overrideData) {
      data = { ...data, ...overrideData };
    }

    if (overrideData?.signature_path && signaturePathInput5) {
      signaturePathInput5.value = overrideData.signature_path;
    }
    if (overrideData?.signature_data && signatureDataInput5) {
      signatureDataInput5.value = overrideData.signature_data;
    }

    Object.entries(data).forEach(([name, stored]) => {
      if (name === 'remarks[]' && Array.isArray(stored)) {
        const rows = ensureRemarkRows(stored);
        rows.forEach((el, idx) => {
          const val = stored[idx] ?? '';
          el.value = val;
          el.dispatchEvent(new Event('input', { bubbles: true }));
        });
        return;
      }

      const elements = Array.from(form.querySelectorAll(`[name="${name}"]`));

      if (name.endsWith('[]') && Array.isArray(stored)) {
        elements.forEach((el, idx) => {
          const val = stored[idx] ?? '';
          if (el.type === 'checkbox') {
            el.checked = Array.isArray(val) ? val.includes(el.value) : String(val) === el.value;
          } else if (el.type === 'radio') {
            el.checked = val === el.value;
          } else {
            if (!el.value) el.value = val;
          }
          if (el.tagName === 'TEXTAREA' || el.type === 'text') {
            el.dispatchEvent(new Event('input', { bubbles: true }));
          }
        });
        return;
      }

      elements.forEach(el => {
        if (el.type === 'checkbox') {
          if (Array.isArray(stored)) {
            el.checked = stored.includes(el.value);
          } else {
            el.checked = String(stored) === el.value;
          }
        }
        else if (el.type === 'radio') {
          el.checked = stored === el.value;
        }
        else {
          if (!el.value) {
            el.value = stored;
          }
        }

        if (el.tagName === 'TEXTAREA' || el.type === 'text') {
          el.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    });

    updateSignaturePreview5();
  };

  const saveCache = () => {
    const data = {};

    Array.from(form.elements).forEach(el => {
      if (!el.name || el.disabled) return;
      if (["button","submit","reset","file"].includes(el.type)) return;

      // Skip very large/base64 fields (e.g., signature data URLs) to avoid quota errors
      const rawValue = el.value || '';
      const isLargeDataUrl = typeof rawValue === 'string' && rawValue.length > 2000 && rawValue.startsWith('data:');
      const skipCacheFields = ['signature_data', 'signature_path', 'signature', 'signature_file'];
      if (skipCacheFields.includes(el.name) || isLargeDataUrl) {
        return;
      }

      const isArrayField = el.name.endsWith('[]');

      if (el.type === 'checkbox') {
        if (singleSelectCheckboxNames.has(el.name)) {
          if (el.checked) {
            data[el.name] = rawValue;
          } else if (!data[el.name]) {
            data[el.name] = '';
          }
        } else {
          if (!data[el.name]) data[el.name] = isArrayField ? [] : [];
          if (el.checked) data[el.name].push(rawValue);
        }
      }
      else if (el.type === 'radio') {
        if (el.checked) data[el.name] = rawValue;
      }
      else {
        if (isArrayField) {
          if (!data[el.name]) data[el.name] = [];
          data[el.name].push(rawValue);
        } else {
          data[el.name] = rawValue;
        }
      }
    });

    try {
      localStorage.setItem(storageKey, JSON.stringify(data));
    } catch (err) {
      console.warn('localStorage quota exceeded, skipping cache save', err);
    }
  };

  const autoSaveToServer = (() => {
    let timer;
    const retryDelay = 1200;
    const showOverlay = (flag) => {
      if (!autosaveOverlay) return;
      autosaveOverlay.classList.toggle('hidden', !flag);
    };
    const send = () => {
      const formData = new FormData(form);
      fetch('{{ route('pds.autosave') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          showOverlay(true);
          setTimeout(send, retryDelay);
          throw new Error('Auto-save failed');
        }
        return response.json();
      })
      .then(data => {
        const ok = data && data.status === 'ok';
        if (ok) {
          showOverlay(false);
        } else {
          showOverlay(true);
          setTimeout(send, retryDelay);
        }
      })
      .catch(() => {
        showOverlay(true);
        setTimeout(send, retryDelay);
      });
    };

    return () => {
      clearTimeout(timer);
      timer = setTimeout(send, 800);
    };
  })();

  const requiredFields = Array.from(form.querySelectorAll('[required]'));

  const getRemarks = () => Array.from(form.querySelectorAll('textarea[name="remarks[]"]'));

  const validateRequired = () => {
    const hasEmptyRemarks = getRemarks().some(t => !isFilled(t));

    const hasMissingRequired = requiredFields.some(el => {
      if (el.disabled || el.readOnly) return false;
      if (el.offsetParent === null) return false;
      return !isFilled(el);
    });

    const hasMissing = hasEmptyRemarks || hasMissingRequired;

    if (submitBtn) {
      submitBtn.disabled = hasMissing;
      if (hasMissing) {
        submitBtn.setAttribute('aria-disabled', 'true');
        submitBtn.classList.add('opacity-50','cursor-not-allowed','pointer-events-none');
      } else {
        submitBtn.removeAttribute('aria-disabled');
        submitBtn.classList.remove('opacity-50','cursor-not-allowed','pointer-events-none');
      }
    }
  };

  const attachReactive = (el) => {
    el.addEventListener('input', () => { validateRequired(); saveCache(); autoSaveToServer(); });
    el.addEventListener('change', () => { validateRequired(); saveCache(); autoSaveToServer(); });
  };

  // expose addCell for button onclick
  window.addCell = () => {
    const rows = ensureRemarkRows([...getRemarks()].map(r => r.value).concat(''));
    const newRow = rows[rows.length - 1];
    if (newRow) {
      newRow.value = '';
      newRow.dispatchEvent(new Event('input', { bubbles: true }));
    }
    validateRequired();
    saveCache();
    autoSaveToServer();
  };

  // Initial hooks
  getRemarks().forEach(attachReactive);
  Array.from(form.querySelectorAll('input[type="text"], textarea')).forEach(attachReactive);

  loadCache();
  updateSignaturePreview5();
  validateRequired();

  if (submitBtn) {
    submitBtn.addEventListener('click', (e) => {
      if (submitBtn.getAttribute('aria-disabled') === 'true') {
        e.preventDefault();
        e.stopPropagation();
        validateRequired();
      }
    });
  }

  // Check for master date from form1 and apply it
  function ensureMasterDateSeed() {
    const date5Input = document.querySelector('input[name="date5"]');
    const existing = localStorage.getItem('pds_master_date');
    const candidate = existing || date5Input?.value;
    if (candidate && !existing) {
      console.log('Seeding master date from form5/input value:', candidate);
      localStorage.setItem('pds_master_date', candidate);
    }
    return candidate || null;
  }

  function syncFromForm1() {
    const masterDate = ensureMasterDateSeed();
    if (masterDate) {
      console.log('Using master date:', masterDate);
      const date5Input = document.querySelector('input[name="date5"]');
      if (date5Input && date5Input.value !== masterDate) {
        console.log('Updating form5 date to:', masterDate);
        date5Input.value = masterDate;
        // Trigger change event to save to cache
        date5Input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
  }

  // Check for master date when page loads
  syncFromForm1();

  // Also check periodically in case user navigates back from form1
  setInterval(syncFromForm1, 1000);
});
</script>

<!-- =============================== -->
<!-- PAGE CONTENT -->
<!-- =============================== -->

<div class="max-w-6xl mx-auto p-4 font-serif text-sm">

<table>
  <th class="flex text-left font-['Arial_Narrow','Arial',sans-serif] italic font-semibold">
    Attachment to CS Form No. 212
  </th>
</table>

<table class="border-black w-full font-['Arial_Narrow','Arial',sans-serif] border-2">

<tr>
  <th class="text-base font-semibold italic bg-[#8a8a8a] text-white border border-black border-b-2">
    WORK EXPERIENCE SHEET
  </th>
</tr>

<tbody id="remarks-rows">

<tr>
<td class="border-t-2 h-20 p-3 text-base italic border-b-2 border-black">
<span class="p-1 font-semibold">Instructions:</span>
1. Include only the work experiences relevant to the position being applied to.
<p class="p-2 ml-20">
2. The duration should include start and finish dates, if known, month in abbreviated form, if known, and year in full. For the current position, use the word Present, e.g., 1998-Present. Work experience should be listed from most recent first.  
</p>
</td>
</tr>

<tr>
<td class="border-2 h-20 border-black relative">
<textarea
id="remarks-prototype"
name="remarks[]"
class="border-none w-full h-full p-5 resize-none text-sm focus:outline-none"
style="min-height:350px; white-space:pre-wrap;"
placeholder="Sample: If applying to Supervising Administrative Officer

•	Duration:  February 11, 2011 – present
•	Position:  Human Resource Management Officer III
•	Name of Office/Unit: Finance and Administrative Service
•	Immediate Supervisor: Maria Estrada
•	 Name of Agency/Organization and Location: Department of Human Resources, Metro Manila

•	List of Accomplishments and Contributions (if any)
 - Developed recruitment plan
 - Designed training program for retirees under EO 366
 
•	Summary of Actual Duties
  - Responsible for the management of the recruitment and selection process and the coordination of training activities of the Department; provides assistance in the management of the Division’s programs and activities and performs other related functions.
"
></textarea>
</td>
</tr>

</tbody>
</table>

<!-- SIGNATURE -->
<div class="w-full flex justify-end mt-[3cm] pr-6">
  <div class="w-[350px] text-center">
    <label id="signatureBox5" class="signature-box block h-36 w-full cursor-pointer">
      <input
        type="file"
        name="signature_file"
        id="signatureFileInput5"
        accept="image/*"
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        onchange="handleSignatureUpload5(this.files[0])"
      >
      <img
        id="signaturePreviewImg5"
        src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
        alt="Signature preview"
        class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
      >
      <div id="signaturePlaceholder5" class="absolute inset-0 flex items-center justify-center text-center text-xs text-gray-600 px-2">
        Upload signature here
      </div>
    </label>
    <input type="hidden" name="signature_path" id="signature_path5" value="{{ $signaturePath ?? '' }}">
    <input type="hidden" name="signature_data" id="signature_data5">
     <div class="w-100 border-b-2 border-black mb-1"></div>
    <div class="mt-2 text-sm">(Signature over Printed Name)</div>
  </div>
</div>

<!-- DATE -->
<div class="w-full flex justify-end mt-[1cm] pr-6 font-['Arial_Narrow','Arial',sans-serif]">
<div class="w-[350px] text-center relative">

<div class="border-b-2 border-black w-full absolute bottom-6 left-0"></div>

<div class="flex justify-center items-center h-10 relative">
<input type="date" name="date5" required class="w-full h-full text-center bg-transparent border-none text-base px-2 py-1 focus:outline-none focus:ring-0">
</div>

<div class="text-sm">DATE</div>
</div>
</div>

<div class="mt-5 flex justify-end mr-2 text-sm font-['Arial_Narrow','Arial',sans-serif]">
CS FORM 212 (Revised 2025), Page 5 of 5
</div>

<a href="{{ route('pds.form4') }}"
class="px-4 py-2 bg-blue-600 text-white rounded">
Previous Page</a>

<div class="mt-5 flex justify-end mr-2">
<button type="button"
class="px-4 py-2 bg-blue-600 text-white rounded"
onclick="addCell()">
Add Row
</button>
</div>

<div class="mt-3 flex justify-end">
<button id="submit-pds-btn"
  type="button"
  disabled
  data-submitpds-trigger
  data-submitpds-form="pds-form5"
  aria-disabled="true"
  class="px-5 py-2 bg-green-600 text-white rounded opacity-50 cursor-not-allowed pointer-events-none">
  Submit PDS
</button>
</div>

</div>
</form>
<x-submitpds />

<style>
/* Custom styling for date inputs - bigger calendar icon and middle text alignment */
input[type="date"] {
  color-scheme: light dark;
  font-size: 30px;
  text-align: center !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0 !important;
  margin-left: 50px;
}

/* Hide calendar icon since date is synced from form1 */
input[type="date"]::-webkit-calendar-picker-indicator {
  display: none;
}

input[type="date"]::-moz-calendar-picker-indicator {
  display: none;
}

/* Ensure text is vertically centered and black */
input[type="date"]::-webkit-datetime-edit-text {
  vertical-align: middle;
  color: #000000;
  font-size: 16px;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-month-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-day-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-year-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center !important;
}

/* Firefox date input text color and centering */
input[type="date"]::-moz-datetime-edit-text {
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-moz-datetime-edit-month-field {
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-moz-datetime-edit-day-field {
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-moz-datetime-edit-year-field {
  color: #000000;
  text-align: center !important;
}
</style>
</x-app-layout>
