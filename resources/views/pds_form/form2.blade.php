<x-app-layout>
<form method="POST" action="{{ route('pds.saveStep', 2) }}" enctype="multipart/form-data">
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
                    const firstNAIndex = fields.findIndex(f => isNA(f.value));
                    fields.forEach((f, idx) => {
                        const shouldDisable = firstNAIndex !== -1 && idx > firstNAIndex;
                        f.disabled = shouldDisable;
                        f.classList.toggle('bg-gray-200', shouldDisable);
                        f.classList.toggle('text-gray-500', shouldDisable);
                        f.classList.toggle('cursor-not-allowed', shouldDisable);
                    });
                };

                fields.forEach(f => f.addEventListener('input', refresh));
                refresh();
            });

            // Next button gating: require all visible fields (treat NA/N/A/NONE as filled)
            const nextBtn = document.getElementById('next-btn');
            const visibleFields = () => Array.from(document.querySelectorAll('input:not([type="hidden"]), textarea, select'))
                .filter(el => !el.disabled && !el.readOnly && el.offsetParent !== null);

            const isFilled = (el) => {
                if (el.type === 'file') return el.files && el.files.length > 0;
                if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                const val = (el.value || '').trim();
                if (isNA(val)) return true;
                return val !== '';
            };

            const validateRequired = () => {
                const hasMissing = visibleFields().some(el => !isFilled(el));

                if (!nextBtn) return;
                if (hasMissing) {
                    nextBtn.setAttribute('aria-disabled', 'true');
                    nextBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                } else {
                    nextBtn.removeAttribute('aria-disabled');
                    nextBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            };

            document.addEventListener('input', validateRequired, true);
            document.addEventListener('change', validateRequired, true);
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
        <td class="border align-top"><textarea rows="1" placeholder="Eligibility" name="eligibility[]"></textarea></td>
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

    @for ($i = 0; $i < 8; $i++)
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
      <div class="h-full w-full flex flex-col items-center justify-center p-2">
        <input type="file" name="signature_attachment_3" id="signature_attachment" accept="image/*,.pdf" class="text-sm">
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
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  if (!form) return;
  const storageKey = 'pds_form_step2_' + ({{ auth()->id() ?? 0 }});

  const loadCache = () => {
    try {
      const cached = JSON.parse(localStorage.getItem(storageKey) || '{}');
      Object.entries(cached).forEach(([name, value]) => {
        const field = form.elements[name];
        if (!field) return;
        if (field.type === 'checkbox' || field.type === 'radio') {
          field.checked = !!value;
        } else {
          field.value = value;
          if (field.tagName === 'TEXTAREA') field.dispatchEvent(new Event('input'));
        }
      });
    } catch (e) {}
  };

  const saveCache = () => {
    const data = {};
    Array.from(form.elements).forEach(el => {
      if (!el.name || el.disabled) return;
      if (['button','submit','reset','file'].includes(el.type)) return;
      if (el.type === 'checkbox' || el.type === 'radio') {
        data[el.name] = el.checked;
      } else {
        data[el.name] = el.value;
      }
    });
    try { localStorage.setItem(storageKey, JSON.stringify(data)); } catch (e) {}
  };

  loadCache();
  form.addEventListener('input', saveCache);
  form.addEventListener('change', saveCache);
  form.addEventListener('submit', () => { localStorage.removeItem(storageKey); });
});
</script>

</x-app-layout>