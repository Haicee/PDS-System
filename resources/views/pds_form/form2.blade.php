<x-app-layout>
<form id="pds-form2" method="POST" action="{{ route('pds.saveStep', 2) }}" enctype="multipart/form-data">
@csrf
    <div class="max-w-6xl mx-auto p-4 flex justify-end">
        <a href="{{ route('pds.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">
            Download PDF
        </a>
    </div>
    <style>
        body { margin: 24px; }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 4px; vertical-align: top; }

        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
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
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }

        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 12px; height: 12px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const autoSize = (el) => {
                el.style.height = 'auto';
                el.style.height = `${el.scrollHeight}px`;
            };

            document.querySelectorAll('textarea').forEach(el => {
                // uppercase enforcement
                el.addEventListener('input', () => {
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const upper = el.value.toUpperCase();
                    if (el.value !== upper) {
                        el.value = upper;
                        el.setSelectionRange(start, end);
                    }
                    autoSize(el);
                });

                // initial sizing
                requestAnimationFrame(() => autoSize(el));
            });

            // NA locking for any [] group on this page: disable only fields BELOW the first NA/N/A/NONE, keep existing values above
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            const names = new Set();
            document.querySelectorAll('input[name$="[]"], textarea[name$="[]"]').forEach(el => {
                const name = el.getAttribute('name');
                if (name) names.add(name);
            });

            names.forEach(name => {
                const selectorName = name.replace(/["'\\]/g, '\\$&');
                const fields = Array.from(document.querySelectorAll(`input[name="${selectorName}"]` + `, textarea[name="${selectorName}"]`));
                if (!fields.length) return;

                const refresh = () => {
                    const firstField = fields[0];
                    const firstIsNA = firstField ? isNA(firstField.value) : false;

                    fields.forEach((f, idx) => {
                        const shouldDisable = firstIsNA && idx > 0;
                        f.disabled = shouldDisable;
                        f.classList.toggle('bg-gray-200', shouldDisable);
                        f.classList.toggle('text-gray-500', shouldDisable);
                        f.classList.toggle('cursor-not-allowed', shouldDisable);
                        if (shouldDisable && f.tagName === 'TEXTAREA') {
                            f.value = '';
                        }
                    });
                };

                fields.forEach(f => f.addEventListener('input', refresh));
                refresh();
            });

            // First-row logic for eligibility and work tables
            const rowGroup = (names) => names.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`))).filter(arr => arr.length).map(arr => arr[0]);
            const eligibilityFirstRow = rowGroup(['eligibility[]','rating[]','date[]','place[]','license_no[]','validity[]']);
            const workFirstRow = rowGroup(['work_from[]','work_to[]','work_position_title[]','work_department[]','work_status[]','work_govt_service[]']);

            const disableFollowingRows = (names, disable) => {
                names.forEach(n => {
                    const fields = Array.from(document.querySelectorAll(`[name="${n}"]`));
                    fields.forEach((f, idx) => {
                        if (idx === 0) return;
                        f.disabled = disable;
                        f.classList.toggle('bg-gray-200', disable);
                        f.classList.toggle('text-gray-500', disable);
                        f.classList.toggle('cursor-not-allowed', disable);
                        if (disable && f.tagName === 'TEXTAREA') {
                            f.value = '';
                        }
                    });
                });
            };

            const firstRowState = (fields) => {
                const values = fields.map(f => (f?.value || '').trim());
                const allNA = values.length && values.every(v => isNA(v));
                const anyData = values.some(v => v !== '' && !isNA(v));
                return { allNA, anyData };
            };

            const refreshRows = () => {
                const eligState = firstRowState(eligibilityFirstRow);
                disableFollowingRows(['eligibility[]','rating[]','date[]','place[]','license_no[]','validity[]'], eligState.allNA);

                const workState = firstRowState(workFirstRow);
                disableFollowingRows(['work_from[]','work_to[]','work_position_title[]','work_department[]','work_status[]','work_govt_service[]'], workState.allNA);
            };

            [...eligibilityFirstRow, ...workFirstRow].forEach(f => {
                if (!f) return;
                f.addEventListener('input', refreshRows);
            });
            refreshRows();

            // Next button gating: required fields + first rows (allow NA as filled)
            const nextBtn = document.getElementById('next-btn');
            const requiredFields = Array.from(document.querySelectorAll('[required]'));

            const isFilled = (el) => {
                if (el.type === 'file') return el.files && el.files.length > 0;
                if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                const val = (el.value || '').trim();
                if (isNA(val)) return true;
                return val !== '';
            };

            const firstRowSets = [eligibilityFirstRow, workFirstRow];

            const validateRequired = () => {
                const hasMissingRequired = requiredFields.some(el => !el.disabled && !el.readOnly && !isFilled(el));

                const firstRowsIncomplete = firstRowSets.some(set => {
                    const state = firstRowState(set);
                    return !(state.allNA || state.anyData);
                });

                const hasMissing = hasMissingRequired || firstRowsIncomplete;

                if (!nextBtn) return;
                if (hasMissing) {
                    nextBtn.setAttribute('aria-disabled', 'true');
                    nextBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                } else {
                    nextBtn.removeAttribute('aria-disabled');
                    nextBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            };

            document.addEventListener('input', () => { refreshRows(); validateRequired(); }, true);
            document.addEventListener('change', () => { refreshRows(); validateRequired(); }, true);
            validateRequired();

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    if (nextBtn.getAttribute('aria-disabled') === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        validateRequired();
                    }
                });
            }

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

            // Local cache + draft fetch + autosave (copied from form1)
            const form = document.querySelector('#pds-form2');
            if (!form) return;

            const storageKey = 'pds_form_step2_' + ({{ auth()->id() ?? 0 }});
            const singleSelectCheckboxNames = new Set();
            const storageBase = "{{ asset('storage') }}/";
            const initialSignaturePath = @json($signaturePath ?? '');

            const signaturePreviewImg2 = document.getElementById('signaturePreviewImg2');
            const signaturePlaceholder2 = document.getElementById('signaturePlaceholder2');
            const signatureBox2 = document.getElementById('signatureBox2');
            const signatureDataInput2 = document.getElementById('signature_data2');
            const signaturePathInput2 = document.getElementById('signature_path2');

            const buildSignatureUrl2 = (path) => {
                if (!path) return '';
                const cleaned = path.replace(/^public\//, '');
                return storageBase + cleaned;
            };

            const updateSignaturePreviewFromInputs2 = () => {
                if (!signaturePreviewImg2 || !signaturePlaceholder2) return;
                const dataUrl = signatureDataInput2?.value;
                const pathVal = signaturePathInput2?.value || initialSignaturePath;
                const url = dataUrl || buildSignatureUrl2(pathVal);
                if (url) {
                    signaturePreviewImg2.src = url;
                    signaturePreviewImg2.classList.remove('hidden');
                    signaturePlaceholder2.classList.add('hidden');
                    signatureBox2?.classList.add('signature-has-image');
                } else {
                    signaturePreviewImg2.classList.add('hidden');
                    signaturePlaceholder2.classList.remove('hidden');
                    signatureBox2?.classList.remove('signature-has-image');
                }
            };

            window.handleSignatureUpload2 = (file) => {
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
                        if (signatureDataInput2) signatureDataInput2.value = output;
                        if (signaturePathInput2) signaturePathInput2.value = '';
                        if (signaturePreviewImg2) {
                            signaturePreviewImg2.src = output;
                            signaturePreviewImg2.classList.remove('hidden');
                        }
                        if (signaturePlaceholder2) signaturePlaceholder2.classList.add('hidden');
                        if (signatureBox2) signatureBox2.classList.add('signature-has-image');
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            };

            // LOAD CACHE (localStorage + optional override from draft)
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

                if (overrideData?.signature_path && signaturePathInput2) {
                    signaturePathInput2.value = overrideData.signature_path;
                }
                if (overrideData?.signature_data && signatureDataInput2) {
                    signatureDataInput2.value = overrideData.signature_data;
                }

                // For fields with [] names, ensure arrays apply in order
                Object.entries(data).forEach(([name, stored]) => {
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
            };

            // SAVE CACHE locally (preserve [] groups as arrays)
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

            // AUTOSAVE to server (throttled)
            const autoSaveToServer = (() => {
                let timer;
                return () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        const formData = new FormData(form);
                        fetch('{{ route('pds.autosave') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: formData
                        }).catch(() => {});
                    }, 800);
                };
            })();

            const persist = () => {
                saveCache();
                autoSaveToServer();
            };

            loadCache();
            updateSignaturePreviewFromInputs2();

            // If served via static view (no $data), fetch draft and hydrate once
            fetch('{{ route('pds.draft') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(json => {
                    if (!json || !json.data) return;
                    loadCache(json.data);
                    updateSignaturePreviewFromInputs2();
                })
                .catch(() => {});

            form.addEventListener('input', persist);
            form.addEventListener('change', persist);
        });
    </script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">

    <table class="border border-black w-full font-['Arial_Narrow','sans-serif']">

      <colgroup>
        <col style="width: 35%;">
        <col style="width: 10%;">
        <col style="width: 15%;">
        <col style="width: 15%;">
        <col style="width: 8%;">
        <col style="width: 8%;">
      </colgroup>
      
    <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      IV.  CIVIL SERVICE ELIGIBILITY
    </th>

    <tr>
      <th class="border bg-[#e7e7e7]" rowspan="2">
        27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        RATING <p>(If Applicable) </p>
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        DATE OF EXAMINATION / CONFERMENT
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        PLACE OF EXAMINATION / CONFERMENT
      </th>

       <th class="border bg-[#e7e7e7]" colspan="2">
        LICENSE (if applicable)
      </th>
    </tr>


    <tr>
    <th class="border text-center bg-[#e7e7e7]">NUMBER</th>
    <th class="border text-center bg-[#e7e7e7]">VALID UNTIL</th>
   </tr>

   @for ($i = 0; $i < 7; $i++)
      <tr>
        <td class="border align-top"><textarea rows="1" placeholder="Eligibility" name="eligibility[]" ></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="Rating" name="rating[]"></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="Date" name="date[]"></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="Place" name="place[]"></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="License No." name="license_no[]"></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="Validity" name="validity[]"></textarea></td>
      </tr>
   @endfor
    </table>
    

    <table class="border border-black font-['Arial_Narrow','sans-serif'] w-full">

      <colgroup>
        <col style="width: 8%;">
        <col style="width: 8%;">
        <col style="width: 20%;">
        <col style="width: 28%;">
        <col style="width: 20%;">
        <col style="width: 10%;">
      </colgroup>

      <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      V.  WORK EXPERIENCE 
      <p class="font-extralight text-lg">(Include private employment.  Start from your recent work.) Description of duties should be indicated in the attached Work Experience Sheet.</p>
     </th>

     <tr class="border">

         <th colspan="2" class="border bg-[#e7e7e7]">
        INCLUSIVE DATES OF ATTENDANCE<p>(dd/mm/yyyy)</p>
      </th>

      <th rowspan="2" class="bg-[#e7e7e7]">POSITION TITLE<p>(Write in full/Do not abbreviate)</p></th>

       <th rowspan="2" class="border bg-[#e7e7e7]">
        DEPARTMENT / AGENCY / OFFICE / COMPANY (Write in full/Do not abbreviate)
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
        STATUS OF APPOINTMENT
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
         GOV'T SERVICE (Y/ N)
      </th>
     </tr>

     <tr class=" bg-[#e7e7e7]">
      <th class="border text-center font-light bg-[#e7e7e7]">FROM</th>
      <th class="border text-center font-light bg-[#e7e7e7]">TO</th>
     </tr>

    @for ($i = 0; $i < 27; $i++)
     <tr>
      <td class="border align-top"><textarea rows="1" placeholder="From" name="work_from[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="To" name="work_to[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="Position Title" name="work_position_title[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="Department/Agency/Office/Company" name="work_department[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="Status" name="work_status[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="Y/N" name="work_govt_service[]"></textarea></td>
     </tr>
    @endfor
    <tr>
      <table class="border-black w-full h-15 font-['Arial_Narrow','sans-serif'] italic">
        <colgroup>
          <col style="width: 17%;">
           <col style="width: 20%;">
            <col style="width: 16.02%;">
             <col style="width: 15%;">
        </colgroup>
      <tr>
          <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

       <td class="border" colspan="2">
     <div class="h-full w-full p-2">
        <label id="signatureBox2" data-signature-cell class="signature-box block h-36 w-full cursor-pointer">
          <input
            type="file"
            name="signature_file"
            id="signatureFileInput2"
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onchange="handleSignatureUpload2(this.files[0])"
          >
          <img
            id="signaturePreviewImg2"
            src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
            alt="Signature preview"
            class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
          >
          <div id="signaturePlaceholder2" class="absolute inset-0 flex items-center justify-center text-center text-xs text-gray-600 px-3">
            Upload signature here
          </div>
        </label>
        <input type="hidden" name="signature_path" id="signature_path2" value="{{ $signaturePath ?? '' }}">
        <input type="hidden" name="signature_data" id="signature_data2">
      </div>
    </td>

      <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>

       <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="date2"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
      </tr>
    </table>

    </tr>
    </table>    

     
      <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 2 of 5
    </div>

        <div class="flex justify-between mt-4">
            <a href="{{ route('pds.form1') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded shadow border border-gray-300 hover:bg-gray-300">Previous Page</a>
            <button type="submit" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700">Next Page</button>
        </div>
</form>
</x-app-layout>