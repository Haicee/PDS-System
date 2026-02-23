<x-app-layout>
<form id="pds-form3" method="POST" action="{{ route('pds.saveStep', 3) }}" enctype="multipart/form-data">
@csrf
    <div class="max-w-6xl mx-auto p-4 flex justify-end">
        <a href="{{ route('pds.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">
            Download PDF
        </a>
    </div>
    <style>
  body { margin: 24px; }
  table {
    border-collapse: collapse;
    width: 100%;
 }
        td, th { padding: 4px; vertical-align: middle; }
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
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('#pds-form3');
            if (!form) return;

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

            // Uppercase enforcement
            document.querySelectorAll('textarea').forEach(el => {
                el.addEventListener('input', () => {
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const upper = el.value.toUpperCase();
                    if (el.value !== upper) {
                        el.value = upper;
                        el.setSelectionRange(start, end);
                    }
                });
            });

            // NA handling helpers
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

            // First-row logic per table
            const rowGroup = (namesArr) => namesArr.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`))).filter(arr => arr.length).map(arr => arr[0]);
            const voluntaryFirstRow = rowGroup(['voluntary_organization[]','voluntary_from[]','voluntary_to[]','voluntary_hours[]','voluntary_position_nature_of_work[]']);
            const learningFirstRow = rowGroup(['learning_title_of_ld[]','learning_from[]','learning_to[]','learning_hours[]','learning_type_of_ld[]','learning_conducted_sponsored_by[]']);
            const otherInfoFirstRow = rowGroup(['special_skills_hobbies[]','non_academic_distinctions_recognition[]','membership_in_association_organization[]']);

            const disableFollowingRows = (namesArr, disable) => {
                namesArr.forEach(n => {
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
                const voluntaryState = firstRowState(voluntaryFirstRow);
                disableFollowingRows(['voluntary_organization[]','voluntary_from[]','voluntary_to[]','voluntary_hours[]','voluntary_position_nature_of_work[]'], voluntaryState.allNA);

                const learningState = firstRowState(learningFirstRow);
                disableFollowingRows(['learning_title_of_ld[]','learning_from[]','learning_to[]','learning_hours[]','learning_type_of_ld[]','learning_conducted_sponsored_by[]'], learningState.allNA);

                const otherState = firstRowState(otherInfoFirstRow);
                disableFollowingRows(['special_skills_hobbies[]','non_academic_distinctions_recognition[]','membership_in_association_organization[]'], otherState.allNA);
            };

            [...voluntaryFirstRow, ...learningFirstRow, ...otherInfoFirstRow].forEach(f => {
                if (!f) return;
                f.addEventListener('input', refreshRows);
            });
            refreshRows();

            // Next button gating
            const nextBtn = document.getElementById('pds3-next');
            const requiredFields = Array.from(document.querySelectorAll('[required]'));

            const isFilled = (el) => {
                if (el.type === 'file') return el.files && el.files.length > 0;
                if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                const val = (el.value || '').trim();
                if (isNA(val)) return true;
                return val !== '';
            };

            const firstRowSets = [voluntaryFirstRow, learningFirstRow, otherInfoFirstRow];

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

            // Local cache + autosave/draft
            const storageKey = 'pds_form_step3_' + ({{ auth()->id() ?? 0 }});
            const singleSelectCheckboxNames = new Set();
            const storageBase = "{{ asset('storage') }}/";
            const initialSignaturePath = @json($signaturePath ?? '');

            const signaturePreviewImg3 = document.getElementById('signaturePreviewImg3');
            const signaturePlaceholder3 = document.getElementById('signaturePlaceholder3');
            const signatureBox3 = document.getElementById('signatureBox3');
            const signatureDataInput3 = document.getElementById('signature_data3');
            const signaturePathInput3 = document.getElementById('signature_path3');

            const buildSignatureUrl3 = (path) => {
                if (!path) return '';
                const cleaned = path.replace(/^public\//, '');
                return storageBase + cleaned;
            };

            const updateSignaturePreviewFromInputs3 = () => {
                if (!signaturePreviewImg3 || !signaturePlaceholder3) return;
                const dataUrl = signatureDataInput3?.value;
                const pathVal = signaturePathInput3?.value || initialSignaturePath;
                const url = dataUrl || buildSignatureUrl3(pathVal);
                if (url) {
                    signaturePreviewImg3.src = url;
                    signaturePreviewImg3.classList.remove('hidden');
                    signaturePlaceholder3.classList.add('hidden');
                    signatureBox3?.classList.add('signature-has-image');
                } else {
                    signaturePreviewImg3.classList.add('hidden');
                    signaturePlaceholder3.classList.remove('hidden');
                    signatureBox3?.classList.remove('signature-has-image');
                }
            };

            window.handleSignatureUpload3 = (file) => {
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
                        if (signatureDataInput3) signatureDataInput3.value = output;
                        if (signaturePathInput3) signaturePathInput3.value = '';
                        updateSignaturePreviewFromInputs3();
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
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

                if (overrideData?.signature_path && signaturePathInput3) {
                    signaturePathInput3.value = overrideData.signature_path;
                }
                if (overrideData?.signature_data && signatureDataInput3) {
                    signatureDataInput3.value = overrideData.signature_data;
                }
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
                updateSignaturePreviewFromInputs3();
            };

            const saveCache = () => {
                const data = {};

                Array.from(form.elements).forEach(el => {
                    if (!el.name || el.disabled) return;
                    if (["button","submit","reset","file"].includes(el.type)) return;

                    const isArrayField = el.name.endsWith('[]');

                    if (el.type === 'checkbox') {
                        if (singleSelectCheckboxNames.has(el.name)) {
                            if (el.checked) {
                                data[el.name] = el.value;
                            } else if (!data[el.name]) {
                                data[el.name] = '';
                            }
                        } else {
                            if (!data[el.name]) data[el.name] = isArrayField ? [] : [];
                            if (el.checked) data[el.name].push(el.value);
                        }
                    }
                    else if (el.type === 'radio') {
                        if (el.checked) data[el.name] = el.value;
                    }
                    else {
                        if (isArrayField) {
                            if (!data[el.name]) data[el.name] = [];
                            data[el.name].push(el.value);
                        } else {
                            data[el.name] = el.value;
                        }
                    }
                });

                try {
                    localStorage.setItem(storageKey, JSON.stringify(data));
                } catch (e) {}
            };

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

            fetch('{{ route('pds.draft') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(json => {
                    if (!json || !json.data) return;
                    loadCache(json.data);
                    updateSignaturePreviewFromInputs3();
                })
                .catch(() => {});

            form.addEventListener('input', () => { persist(); });
            form.addEventListener('change', () => { persist(); });
        });
    </script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">

    <table class="border border-black w-full font-['Arial_Narrow','Arial',sans-serif]">

      <colgroup>
        <col style="width: 29.5%;">
        <col style="width: 5%;">
        <col style="width: 5%;">
        <col style="width: 5%;">
        <col style="width: 20%;">
      </colgroup>
      
    <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold"colspan="5">
      VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENTAL / PEOPLE / VOLUNTARY ORGANIZATION 
    </th>

    <tr>
      <th class="border bg-[#e7e7e7]" rowspan="2">
        29. NAME & ADDRESS OF ORGANIZATION (Write in full)
      </th>

      <th class="border bg-[#e7e7e7]" colspan="2">
        INCLUSIVE DATES <p>(dd/mm/yyyy)</p>
      </th>


      <th class="border bg-[#e7e7e7]" rowspan="2">
        NUMBER OF <p>HOURS</p>
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        POSITION / NATURE OF WORK 
      </th>
    </tr>


    <tr>
    <th class="border text-center bg-[#e7e7e7]">FROM</th>
    <th class="border text-center bg-[#e7e7e7]">TO</th>
   </tr>

   @for ($i = 0; $i < 7; $i++)
      <tr>
      <td class="border h-10"><textarea rows="1" placeholder="Organization" name="voluntary_organization[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="From" name="voluntary_from[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="To" name="voluntary_to[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Hours" name="voluntary_hours[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Position/Nature of Work" name="voluntary_position_nature_of_work[]"></textarea></td>
      </tr>
   @endfor

    </table>
    

    <table class="border border-black font-['Arial_Narrow','Arial',sans-serif]">
      
      <colgroup>
        <col style="width: 45.5%;">
        <col style="width: 8%;">
        <col style="width: 7.5%;">
        <col style="width: 8%;">
        <col style="width: 12%;">
      </colgroup>

      <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      VII.  LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED
     </th>

     <tr class="border">

      <th rowspan="2" class="bg-[#e7e7e7] text-center">30. TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS <p>(Write in full)</p></th>

      <th colspan="2" class="border bg-[#e7e7e7]">
        INCLUSIVE DATES OF ATTENDANCE<p>(dd/mm/yyyy)</p>
      </th>

       <th rowspan="2" class="border bg-[#e7e7e7]">
        NUMBER OF HOURS
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
        Type of L&D
     <p>(Managerial/ Supervisory/
        Technical / etc) </p>
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
         CONDUCTED/ SPONSORED BY (Write in full)
      </th>
     </tr>

     <tr>
      <th class="border text-center font-light bg-[#e7e7e7]">FROM</th>
      <th class="border text-center font-light bg-[#e7e7e7]">TO</th>
     </tr>

    @for ($i = 0; $i < 27; $i++)
     <tr>
      <td class="border h-10"><textarea rows="1" placeholder="Title of L&D / Training" name="learning_title_of_ld[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="From" name="learning_from[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="To" name="learning_to[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Hours" name="learning_hours[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Type of L&D" name="learning_type_of_ld[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Conducted/Sponsored By" name="learning_conducted_sponsored_by[]"></textarea></td>
     </tr>
    @endfor

    </table>

    <table class="border border-black w-full font-['Arial_Narrow','Arial',sans-serif]">

      <colgroup>
        <col style="width: 5.11%;">
         <col style="width: 6.5%;">
          <col style="width: 4.39%;">
      </colgroup>
      <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="3">
        VIII.  OTHER INFORMATION
      </th>

      <tr class=" bg-[#e7e7e7]">
        <th class="border">
        SPECIAL SKILLS and HOBBIES
      </th>

      <th class="border">
        NON-ACADEMIC DISTINCTIONS / RECOGNITION <p>(Write in full)</p>
      </th>

      <th class="border" >
          MEMBERSHIP IN ASSOCIATION / ORGANIZATION <p>(Write in full)</p>
      </th>

  
      </tr>

    @for ($i = 0; $i < 7; $i++)
      <tr>
      <td class="border h-10"><textarea rows="1" placeholder="Special Skills and Hobbies" name="special_skills_hobbies[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Non-Academic Distinctions/Recognition" name="non_academic_distinctions_recognition[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Membership in Association/Organization" name="membership_in_association_organization[]"></textarea></td> 
      </tr>
    @endfor

    </table>

    <table class="border border-black w-full h-15 font-['Arial_Narrow','sans-serif']">
        <colgroup>
          <col style="width: 30.3%;">
           <col style="width: 20%;">
            <col style="width: 19.7%;">
             <col style="width: 10%;">
        </colgroup>
      <tr>
      <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

       <td class="border" colspan="2">
     <div class="h-full w-full p-2">
        <label id="signatureBox3" data-signature-cell class="signature-box block h-36 w-full cursor-pointer">
          <input
            type="file"
            name="signature_file"
            id="signatureFileInput3"
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onchange="handleSignatureUpload3(this.files[0])"
          >
          <img
            id="signaturePreviewImg3"
            src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
            alt="Signature preview"
            class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
          >
          <div id="signaturePlaceholder3" class="absolute inset-0 flex items-center justify-center text-center text-xs text-gray-600 px-3">
            Upload signature here
          </div>
        </label>
        <input type="hidden" name="signature_path" id="signature_path3" value="{{ $signaturePath ?? '' }}">
        <input type="hidden" name="signature_data" id="signature_data3">
      </div>
    </td>


      <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>

        <td colspan="2"
          class="border">
          <div class="h-full w-full">
         <textarea
      name="date3"
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

     <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 3 of 5
    </div>
    <div class="flex justify-between mt-4">
        <a id="pds3-prev" href="{{ route('pds.form2') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Previous Page</a>
        <button type="submit" id="pds3-next" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</button>
    </div>
    </div>
</form>
</x-app-layout>