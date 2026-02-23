<x-app-layout>
    <form id="pds-form1" method="POST" action="{{ route('pds.saveStep', 1) }}" enctype="multipart/form-data">
    @csrf
    <style>
        /* Print-friendly, spreadsheet-like grid */
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 4px; vertical-align: top; }
        /* Only apply borders where classes already exist */
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
        /* Form controls styled as lined cells */
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 12px; height: 12px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Prefill from session cache (pds) so going back restores values
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
            // Draft data takes precedence, then session cache (will be refreshed below via API if route is static view)
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
            // Keep textarea input uppercase
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

            // NA handling: only disable fields BELOW the first NA in each [] group; do not clear existing values
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            const names = new Set();
            document.querySelectorAll('input[name$="[]"], textarea[name$="[]"]').forEach(el => {
                const name = el.getAttribute('name');
                if (name) names.add(name);
            });
            // Also include education rows (not []-suffixed) for per-row NA locking
            document.querySelectorAll('textarea[name*="[school_name]"]').forEach(el => {
                const name = el.getAttribute('name');
                if (name) names.add(name);
            });

            // Special handling for children rows: allow only one NA on first row, disable rest
            const childrenNameFields = Array.from(document.querySelectorAll('textarea[name="children_familybg[]"]'));
            const childrenDobFields = Array.from(document.querySelectorAll('textarea[name="children_dateofbirth_familybg[]"]'));

            const refreshChildren = () => {
                const firstName = childrenNameFields[0];
                const firstDob = childrenDobFields[0];
                if (!firstName || !firstDob) return;

                const firstIsNA = isNA(firstName.value) && isNA(firstDob.value);

                if (firstIsNA) {
                    firstName.value = 'NA';
                    firstDob.value = 'NA';
                }

                childrenNameFields.forEach((f, idx) => {
                    const shouldDisable = firstIsNA && idx > 0;
                    if (idx > 0) {
                        f.value = firstIsNA ? '' : (isNA(f.value) ? '' : f.value);
                    }
                    f.disabled = shouldDisable;
                    f.readOnly = shouldDisable;
                    f.classList.toggle('bg-gray-200', shouldDisable);
                    f.classList.toggle('text-gray-500', shouldDisable);
                    f.classList.toggle('cursor-not-allowed', shouldDisable);
                    f.classList.toggle('pointer-events-none', shouldDisable);
                });

                childrenDobFields.forEach((f, idx) => {
                    const shouldDisable = firstIsNA && idx > 0;
                    if (idx > 0) {
                        f.value = firstIsNA ? '' : (isNA(f.value) ? '' : f.value);
                    }
                    f.disabled = shouldDisable;
                    f.readOnly = shouldDisable;
                    f.classList.toggle('bg-gray-200', shouldDisable);
                    f.classList.toggle('text-gray-500', shouldDisable);
                    f.classList.toggle('cursor-not-allowed', shouldDisable);
                    f.classList.toggle('pointer-events-none', shouldDisable);
                });
            };

            childrenNameFields.forEach(f => f.addEventListener('input', refreshChildren));
            childrenDobFields.forEach(f => f.addEventListener('input', refreshChildren));
            refreshChildren();

            names.forEach(name => {
                // Skip children fields; handled above
                if (name === 'children_familybg[]' || name === 'children_dateofbirth_familybg[]') {
                    return;
                }

                const selectorName = name.replace(/["'\\]/g, '\\$&');
                const fields = Array.from(document.querySelectorAll(`input[name="${selectorName}"]` + `, textarea[name="${selectorName}"]`));
                if (!fields.length) return;

                // Default: existing logic for pure [] groups
                const refreshArray = () => {
                    const firstNAIndex = fields.findIndex(f => isNA(f.value));
                    fields.forEach((f, idx) => {
                        const shouldDisable = firstNAIndex !== -1 && idx > firstNAIndex;
                        f.disabled = shouldDisable;
                        f.classList.toggle('bg-gray-200', shouldDisable);
                        f.classList.toggle('text-gray-500', shouldDisable);
                        f.classList.toggle('cursor-not-allowed', shouldDisable);
                    });
                };

                // Special handling for education rows: when school_name is NA, disable ONLY siblings in same row
                if (selectorName.includes('education[') && selectorName.endsWith('[school_name]')) {
                    const schoolFields = fields;
                    const rowSelectors = [
                        '[basic_education]',
                        '[from]',
                        '[to]',
                        '[highest_level]',
                        '[year_graduated]',
                        '[scholarship_acadhonors]'
                    ];

                    const refreshRow = () => {
                        schoolFields.forEach(schoolField => {
                            const isRowNA = isNA(schoolField.value);
                            // derive row key prefix like education[elementary]
                            const rowPrefix = schoolField.name.replace(/\[school_name\]$/, '');
                            rowSelectors.forEach(sel => {
                                const targetName = `${rowPrefix}${sel}`;
                                const targets = document.querySelectorAll(`textarea[name="${targetName}"], input[name="${targetName}"]`);
                                targets.forEach(target => {
                                    target.disabled = isRowNA;
                                    target.readOnly = isRowNA;
                                    target.classList.toggle('bg-gray-200', isRowNA);
                                    target.classList.toggle('text-gray-500', isRowNA);
                                    target.classList.toggle('cursor-not-allowed', isRowNA);
                                    target.classList.toggle('pointer-events-none', isRowNA);
                                });
                            });
                        });
                    };

                    schoolFields.forEach(f => f.addEventListener('input', refreshRow));
                    refreshRow();
                    return;
                }

                fields.forEach(f => f.addEventListener('input', refreshArray));
                refreshArray();
            });

            // Next button gating: require all required fields on this page + at least one checked per checkbox group
            const nextBtn = document.getElementById('next-btn');
            const requiredFields = Array.from(document.querySelectorAll('input[required], textarea[required], select[required]'));
            const checkboxGroups = [
                'sex[]',
                'civilstatus[]',
                'citizenship[]'
            ];

            const validateRequired = () => {
                const missingRequiredInputs = requiredFields.some(el => {
                    if (el.disabled || el.readOnly) return false;
                    if (el.type === 'file') return !(el.files && el.files.length > 0);
                    return !((el.value || '').trim());
                });

                const missingCheckboxGroup = checkboxGroups.some(name => {
                    const boxes = Array.from(document.querySelectorAll(`input[type="checkbox"][name="${name}"]`));
                    if (!boxes.length) return false;
                    return !boxes.some(b => b.checked);
                });

                const hasMissing = missingRequiredInputs || missingCheckboxGroup;

                if (!nextBtn) return;
                if (hasMissing) {
                    nextBtn.setAttribute('aria-disabled', 'true');
                    nextBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                } else {
                    nextBtn.removeAttribute('aria-disabled');
                    nextBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            };

            requiredFields.forEach(el => {
                el.addEventListener('input', validateRequired);
                el.addEventListener('change', validateRequired);
            });

            checkboxGroups.forEach(name => {
                document.querySelectorAll(`input[type="checkbox"][name="${name}"]`).forEach(box => {
                    box.addEventListener('change', validateRequired);
                });
            });

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
        });
    </script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">
  <!-- HEADER -->
  <header class="mb-4 flex items-start justify-between gap-4">
    <div class="text-sm font-bold italic font-['Arial_Narrow','sans-serif']">CS Form No. 212
      <br>
    <span class="font-light text-sm italic font-['Arial_Narrow','sans-serif']">Revised 2025</span>
    </div>
    <h1 class="font-extrabold text-4xl text-center mb-4 font-['Arial_Black','sans-serif'] flex-1">
      PERSONAL DATA SHEET
    </h1>
    <div class="flex items-center">
      <a href="{{ route('pds.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">
        Download PDF
      </a>
    </div>
  </header>

  <p class=" font-['Arial','sans-serif'] text-base italic font-bold mb-1">
    WARNING: Any misrepresentation made in the Personal Data Sheet shall cause the filing of administrative/criminal case/s.
  </p>
  <p class=" font-['Arial','sans-serif'] text-base text-s italic font-bold mb-2">
    READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEET (PDS) BEFORE ACCOMPLISHING THE PDS FORM.
  </p>

  <p class="  font-['Arial_Narrow','sans-serif'] text-base mb-3">
    Print legibly if accomplished through own handwriting. Tick appropriate boxes and use separate sheet if necessary. Indicate <span class="font-bold">N/A</span> if not applicable. <span class="font-bold">DO NOT ABBREVIATE.</span>
  </p>

  <!-- MAIN TABLE -->
  <table class="align-middle w-full border border-black  table-fixed  font-['Arial_Narrow','sans-serif'] text-base w-[100%]" >

    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:8%">
      <col style="width:9%">
      <col style="width:10%">
      <col style="width:10%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="4" class="font-['Arial_Narrow','Arial',sans-serif] bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold">
        I. PERSONAL INFORMATION
      </td>
    </tr>

    <!-- SURNAME -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle">
        1.  SURNAME
     </td>
          <td class="border relative" colspan="3" style="height: 60px;">
            <div class="absolute inset-0 flex items-center">
    <textarea
      name="surname"
      id="surname"
      required
      rows="1"
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Surname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
  </div>
</td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7]  px-2 align-middle">
        2. FIRST NAME
      </td>
       <td class="border relative" colspan="2" style="height: 60px;">
            <div class="absolute inset-0 flex items-center">
    <textarea
      name="firstname"
      id="firstname"
      rows="1"
      required
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Firstname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
  </div>
</td>

      <td class="bg-[#e7e7e7] align-top border">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
           text-lg">
           <textarea
      name="employee_name_extension"
      id="name_extension"
      required
      rows="1"
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Name Extension"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-7 border-b-2"> 
        MIDDLE NAME
      </td>
      <td colspan="3" class="border border-b-2 h-10 align-middle">
        <div  class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
           text-lg">
           <textarea
      name="middlename"
      id="middlename"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      placeholder="Enter Middle Name"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
      </td>
    </tr>

    <!-- DATE OF BIRTH + CITIZENSHIP -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border">
        3. DATE OF BIRTH
        <p class="text-xs font-normal ml-4">(dd/mm/yyyy)</p>
      </td>
      <td class="border h-10">
        <div 
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
           py-2
           text-lg">
           <textarea
      name="date_of_birth"
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Date of Birth"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>

      <td rowspan="3" class="bg-[#e7e7e7] px-2 align-top border-l-5">
        16. CITIZENSHIP
        <p class="text-base mt-8 text-center">
          If holder of dual citizenship, <br>
          please indicate the details.
        </p>
      </td>



      <td rowspan="3" class="border px-2 align-top text-base">

        <div class="flex flex-col items-center text-center w-full space-y-1">
          <div class="flex flex-wrap justify-center gap-6 mr-20">
            <label class="inline-flex items-center gap-2"><input type="checkbox" class="mt-1 mb-1" name="citizenship[]" value="filipino"> Filipino</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="dual_citizenship"> Dual Citizenship</label>
          </div>
          <div class="flex flex-wrap justify-center gap-6 ml-20">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="by_birth"> by birth</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="by_naturalization"> by naturalization</label>
          </div>
          <p class="py-3 flex justify-center">Pls. indicate country:</p>
          <div class="border mb-2 mt-1 w-full text-center" style="min-height: 38px;">
            <textarea
      name="country"
      id="country"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden text-center"
      placeholder="Enter Country"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
          </div>
        </div>
      </td>
    </tr>

    <!-- PLACE OF BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 border align-middle">
        4. PLACE OF BIRTH
      </td>
      <td class="border px-2 h-10">
        <div 
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
            py-2
           text-lg">
    <textarea
      name="place_of_birth"
      id="place_of_birth"
      required
      rows="1"
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Place of Birth"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
    </tr>

    <!-- SEX AT BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-2 border">
        5. SEX AT BIRTH
      </td>
      <td class="border px-2 text-base">
        <label class="mr-10 ml-2 mt-2">
          <input type="checkbox" name="sex[]" value="male"> Male
        </label>
        <label>
          <input type="checkbox" name="sex[]" value="female" class="ml-7"> Female
        </label>
      </td>
    </tr>

    <!-- SIMPLE ROW TEMPLATE -->
      <td class="bg-[#e7e7e7] align-top py-5 px-2 border">6. CIVIL STATUS</td>
      <td class="border px-2 py-1 align-middle h-2">
    <div class="p-2">
      <div class="grid grid-cols-[100px_120px] gap-y-2 gap-x-4 text-base ">
        
        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="single" class="w-3 h-3">
          Single
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="married" class="w-3 h-3">
          Married
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="widowed" class="w-3 h-3">
          Widowed
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="separated" class="w-3 h-3">
          Separated
        </label>

        <label class="flex items-center gap-2 col-span-2">
          <input type="checkbox" name="civilstatus[]" value="other/s" class="w-3 h-3">
          Other/s:
        </label>

      </div>
    </div>
  </td>


  <td rowspan="3" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7]" style="width:35%;">
        <tr>
          <td class="px-2 py-1 align-top border-black border-r">
            17. RESIDENTIAL ADDRESS
          </td>
        </tr>
        <tr class="h-10">
          <td class="text-center text-xl py-2 border-r border-black">
            ZIP CODE
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white" style="width:65%; border-collapse:collapse;">

          <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="house_block_lot"
                  placeholder="House/Block/Lot No."
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  placeholder="Street"
                  name="street"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          
          <tr class="h-2">
          <td class="border-t border-black/50">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">House/Block/Lot No.</p>
              <p class="flex-1 text-center ml-5">Street</p>
            </div>
          </td>
        </tr>
<tr><td class="border-t border-black"></td></tr>
           <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="subdivision_village"
                  placeholder="Subdivision/Village"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="baranggay"
                  placeholder="Barangay"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          <tr class="h-2">
          <td class="border-t border-black/50 ">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">Subdivision/Village</p>
              <p class="flex-1 text-center ml-5">Baranggay</p>
            </div>
          </td>
        </tr>
        <tr><td class="border-t border-black"></td></tr>
          <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="city_municipality"
                  placeholder="City/Municipality"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="province"
                  placeholder="Province"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>

          <tr >
          <td class="border-t border-black/50">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">City/Municipality</p>
              <p class="flex-1 text-center ml-4">Province</p>
            </div>
          </td>
        </tr>
        <tr class="h-2">
          <td>
            <div class="flex">
            </div>
          </td>
        </tr>
        <tr class="h-2">
          <td class="border-t border-black">
            <div>
            
            </div>
          </td>
        </tr>

        <tr class="h-2">
          <td>
            <div>
            
            </div>
          </td>
        </tr>

        
        <tr class="h-2">
          <td>
             <div class="border-none h-8 mb-2 mt-1 text-center">
          <textarea
      name="zip_code"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden text-center align-top"
      placeholder="Enter Zip Code"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
          </td>
        </tr>
      </table>

    </div>
  </td>




    <tr>
      <td class="bg-[#e7e7e7] px-2 border font-['Arial_Narrow','Arial',sans-serif]">7. HEIGHT (m)</td>
      <td class="border px-2 h-10">
        <textarea
      name="height"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Height"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">8. WEIGHT (kg)</td>
      <td class="border px-2 h-10">
        <textarea
      name="weight"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Weight"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>


      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8">9. BLOOD TYPE</td>
      <td class="border px-2 align-middle"> 
        <textarea
      name="blood_type"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Blood Type"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>


  <td rowspan="4" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7] border-b font-['Arial_Narrow','Arial',sans-serif] text-base" style="width:35%;">
        <tr>
          <td class="px-2 py-1 align-top border-r border-black">
            18. PERMANENT ADDRESS
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white" style="width:65%; border-collapse:collapse;">

            <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_house_block_lot"
                  placeholder="House/Block/Lot No."
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_street"
                  placeholder="Street"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          
          <tr class="h-2">
          <td class="border-t border-black/50">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">House/Block/Lot No.</p>
              <p class="flex-1 text-center ml-5">Street</p>
            </div>
          </td>
        </tr>
<tr><td class="border-t border-black"></td></tr>
           <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_subdivision_village"
                  placeholder="Subdivision/Village"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_baranggay"
                  placeholder="Baranggay"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          <tr class="h-2">
          <td class="border-t border-black/50 ">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">Subdivision/Village</p>
              <p class="flex-1 text-center ml-5">Baranggay</p>
            </div>
          </td>
        </tr>
        <tr><td class="border-t border-black"></td></tr>
          <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_city_municipality"
                  placeholder="City/Municipality"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_province"
                  placeholder="Province"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>

          <tr >
          <td class="border-t border-black/50">
            <div class="flex">
              <p class="text-center w-full">City/Municipality</p>
              <p class="text-center w-full ml-4">Province</p>
            </div>
          </td>
        </tr>
        <tr class="h-10">
          <td class="border-t border-black">
            <div>
            
            </div>
          </td>
        </tr>
      </table>

    </div>
  </td>    

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">10. UMID ID NO.</td>
      <td class="border px-2 h-10">

        <textarea
      name="umid_id_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter UMID ID NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">11. PAG-IBIG ID NO.</td>
      <td class="border px-2 h-10">
        <textarea
      name="pagibig_id_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter PAGIBIG ID NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">12. PHILHEALTH NO.</td>
      <td class="border px-2 h-10">
        <textarea
      name="philhealth_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter PHILHEALTH NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    
    <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif]  px-2 border h-8">13. PhilSys Number (PSN):</td>
      <td class="border px-2 align-middle">
          <textarea
      name="philsys_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter PhilSys Number (PSN)"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    
  
    <td rowspan="1" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="border-l bg-[#e7e7e7] border-b-0 border-r-0 h-10 font-['Arial_Narrow','Arial',sans-serif]" style="width: 53.4%;">

        <tr>
          <td class="px-2 align-middle w-full">
            19. TELEPHONE NO.
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white w-300 border-l border-r border-t-0">
          <tr class="h-5">
            <td class="border-black border-l">
              <div class="flex">
             <textarea
      name="telephone_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top px-2"
      placeholder="Enter Telephone NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>
       
    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">14. TIN ID</td>
      <td class="border px-2 h-10">
        <textarea
      name="tin_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter TIN ID"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

       <td rowspan="1" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class=" border-l bg-[#e7e7e7] border-b-0 border-r-0 h-10 font-['Arial_Narrow','Arial',sans-serif]" style="width: 53.4%;">
        <tr>
          <td class="px-2 py-1 align-middle">
            20. MOBILE NO.
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white w-300 border-l border-r border-t-0">
          <tr class="h-5">
            <td class="border-black border-l">
              <div class="flex">
            <textarea
      name="mobile_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top px-2"
      placeholder="Enter Mobile NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">15. AGENCY EMPLOYEE ID</td>
      <td class="border px-2 h-10">
         <textarea
      name="agency_employee_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Agency Employee ID"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

       <td rowspan="1" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="border-l  bg-[#e7e7e7] border-b-0 border-r-0 h-10 font-['Arial_Narrow','Arial',sans-serif]" style="width: 53.4%;">
        <tr>
          <td class="px-2 align-middle">
           21. E-MAIL ADDRESS (if any)
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white w-300 border-l border-r border-t-0 h-10">
          <tr class="h-2">
            <td class="border-black border-l">
              <div>
             <textarea
      name="email_address"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top px-2"
      placeholder="Enter Email Address"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>

      
    </tr>

  </table>


   <table class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">

    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:20%">
      <col style="width:10%">
      <col style="width:12%">
      <col style="width: 16%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="6" class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black border-t">
        II. FAMILY BACKGROUND
      </td>
    </tr>


     <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle">
        22. SPOUSE'S SURNAME
      </td>
      <td colspan="3"
          class="border">
           <textarea
      name="spouse_surname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Spouse's Surname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td class="border text-center bg-[#e7e7e7] ">
        23. NAME of CHILDREN  (Write full name and list all)
      </td>

      <td class="border text-center bg-[#e7e7e7]">
        DATE OF BIRTH (dd/mm/yyyy) 
      </td>

    </tr>

    @php
        $draftChildrenNames = $data['children_familybg'] ?? [];
        $draftChildrenDobs = $data['children_dateofbirth_familybg'] ?? [];

        $childNames = collect(old('children_familybg', $draftChildrenNames));
        $childDobs = collect(old('children_dateofbirth_familybg', $draftChildrenDobs));
        $childRowCount = max(14, $childNames->count(), $childDobs->count());
        while ($childNames->count() < $childRowCount) { $childNames->push(''); }
        while ($childDobs->count() < $childRowCount) { $childDobs->push(''); }
    @endphp

    


    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        FIRST NAME
      </td>
       <td colspan="2"
          class="border">
           <textarea
      name="spouse_firstname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Firstname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td class="bg-[#e7e7e7] align-top">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div>
            <textarea
      name="spouse_name_extension"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Name Extension"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7]  px-8 "> 
        MIDDLE NAME
      </td>
      <td colspan="3"
          class="border border-b-2 h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Middle Name"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7] px-2 border border-t-2">OCCUPATION</td>
       
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_occupation"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Occupation"
    ></textarea>
      </td>

    @php
        $childName = $childNames->shift();
        $childDob = $childDobs->shift();
    @endphp
    <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">EMPLOYER/BUSINESS NAME</td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_employer_business_name"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Enmployer/Business Name"
    ></textarea>
      </td>


      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
        <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">BUSINESS ADDRESS</td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_business_address"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Business Address"
    ></textarea>
      </td>


    @php
        $childName = $childNames->shift();
        $childDob = $childDobs->shift();
    @endphp
     <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">TELEPHONE NO.</td>
      
       <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_telephone_no"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Telephone NO."
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>


    <tr>
      <td class="bg-[#e7e7e7]  px-2 align-middle">
        24. FATHER'S SURNAME
      </td>
      

       <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="father_surname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Father's Surname"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
       <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        FIRST NAME
      </td>

       <td colspan="2"
          class="border">
           <textarea
      name="father_firstname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Firstname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td class="bg-[#e7e7e7] align-top">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div>
            <textarea
      name="father_name_extension"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Name Extension"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
      </td>
      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
       <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7]  px-8"> 
        MIDDLE NAME
      </td>

      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="father_middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Middle Name"
    ></textarea>
      </td>

     @php
         $childName = $childNames->shift();
         $childDob = $childDobs->shift();
     @endphp
     <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>



    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border-t-2" colspan="4">
        25. MOTHER'S MAIDEN NAME
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle  px-8"> 
        SURNAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="mother_surname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Surname"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>


    <tr>
      <td class="bg-[#e7e7e7]  px-8 align-middle">
       FIRST NAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="mother_firstname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter First Name"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        MIDDLE NAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="mother_middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Middle Name"
    ></textarea>
      </td>

      
      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
       <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <textarea
      name="children_dateofbirth_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childDob }}</textarea>
       </div>
      </td>
    </tr>
  </table>


<table class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">

  <!-- EXACT COLUMN GRID (8 columns) -->
  <colgroup>
    <col style="width:27%"> <!-- LEVEL -->
    <col style="width:30%"> <!-- SCHOOL -->
    <col style="width:33%"> <!-- COURSE -->
    <col style="width:10%"> <!-- FROM -->
    <col style="width:10%"> <!-- TO -->
    <col style="width:17%"> <!-- HIGHEST -->
    <col style="width:15%"> <!-- YEAR -->
    <col style="width:17.5%"> <!-- HONORS -->
  </colgroup>

  <tr>
    <td colspan="8"
        class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black">
      III. EDUCATIONAL BACKGROUND
    </td>
  </tr>

  <!-- HEADER ROW 1 -->
  <tr>
    <th class="border font-light" rowspan="2">26. LEVEL</th>
    <th class="border font-light" rowspan="2">NAME OF SCHOOL<p>(Write in Full)</p></th>
    <th class="border font-light" rowspan="2">BASIC EDUCATION / DEGREE / COURSE<p>(Write in full)</p></th>
    <th class="border text-center font-light" colspan="2">PERIOD OF ATTENDANCE</th>
    <th class="border font-light" rowspan="2">
      HIGHEST LEVEL/<br>UNITS EARNED<br>
      <span class="text-base">(if not graduated)</span>
    </th>
    <th class="border font-light" rowspan="2">YEAR GRADUATED</th>
    <th class="border font-light" rowspan="2">SCHOLARSHIP / ACADEMIC<br>HONORS RECEIVED</th>
  </tr>

  <!-- HEADER ROW 2 -->
  <tr>
    <th class="border text-center font-light">FROM</th>
    <th class="border text-center font-light">TO</th>
  </tr>

  <!-- DATA ROW -->
  <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">ELEMENTARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
            class="border h-10">
            <div class="h-full w-full">
          <textarea
        name="education[elementary][highest_level]"
        required
        rows="1"
        class="w-full h-full text-lg resize-none
              focus:outline-none focus:ring-0
              whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
        oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      ></textarea>
        </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
  </tr>


   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">SECONDARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">VOCATIONAL / TRADE COURSE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">COLLEGE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">GRADUATE STUDIES</td>

    <!-- EDITABLE CELL PATTERN -->
   <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
  </tr>

 

  <tr>
    <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

    <td class="border" colspan="2">
      <div class="h-full w-full p-2">
        <label id="signatureBox" data-signature-cell class="signature-box block h-36 w-full cursor-pointer">
          <input
            type="file"
            name="signature_file"
            id="signatureFileInput"
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onchange="handleSignatureUpload(this.files[0])"
          >
          <img
            id="signaturePreviewImg"
            src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
            alt="Signature preview"
            class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
          >
          <div id="signaturePlaceholder" class="absolute inset-0 flex items-center justify-center text-center text-xs text-gray-600 px-3">
            Upload signature here
          </div>
        </label>

        <input type="hidden" name="signature_path" id="signature_path" value="{{ $signaturePath ?? '' }}">
        <input type="hidden" name="signature_data" id="signature_data">
      </div>
    </td>

    <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>

    <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="date1"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
</table>

<table class="bg-transparent">
    <div class="flex justify-end mr-2 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 1 of 5
    </div>
</table>

    <div class="flex justify-end mt-4">
        <button type="submit" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</button>
    </div>
    </div>
    </form>
<script>
        document.addEventListener('DOMContentLoaded', () => {

  const form = document.querySelector('#pds-form1');
  if (!form) {
    console.error('Form element #pds-form1 not found!');
    return;
  }
  console.log('Form element found:', form);

  const storageKey = 'pds_form_step1_' + ({{ auth()->id() ?? 0 }});
  const singleSelectCheckboxNames = new Set(['sex[]','civilstatus[]','citizenship[]']);
  const storageBase = "{{ asset('storage') }}/";
  const initialSignaturePath = @json($signaturePath ?? '');

  const signaturePreviewImg = document.getElementById('signaturePreviewImg');
  const signaturePlaceholder = document.getElementById('signaturePlaceholder');
  const signatureBox = document.getElementById('signatureBox');
  const signatureDataInput = document.getElementById('signature_data');
  const signaturePathInput = document.getElementById('signature_path');
  const signatureFileName = document.getElementById('signatureFileName');

  const buildSignatureUrl = (path) => {
    if (!path) return '';
    const cleaned = path.replace(/^public\//, '');
    return storageBase + cleaned;
  };

  const updateSignaturePreviewFromInputs = () => {
    if (!signaturePreviewImg || !signaturePlaceholder) return;
    const dataUrl = signatureDataInput?.value;
    const pathVal = signaturePathInput?.value || initialSignaturePath;
    const url = dataUrl || buildSignatureUrl(pathVal);
    if (url) {
      signaturePreviewImg.src = url;
      signaturePreviewImg.classList.remove('hidden');
      signaturePlaceholder.classList.add('hidden');
      signatureBox?.classList.add('signature-has-image');
    } else {
      signaturePreviewImg.classList.add('hidden');
      signaturePlaceholder.classList.remove('hidden');
      signatureBox?.classList.remove('signature-has-image');
    }
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

    if (overrideData?.signature_path && signaturePathInput) {
      signaturePathInput.value = overrideData.signature_path;
    }
    if (overrideData?.signature_data && signatureDataInput) {
      signatureDataInput.value = overrideData.signature_data;
    }

    // For fields with [] names, ensure arrays apply in order
    const arrayBucket = {};
    Object.entries(data).forEach(([name, stored]) => {
      if (name.endsWith('[]') && Array.isArray(stored)) {
        arrayBucket[name] = stored;
      }
    });

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

  // --- Signature upload with auto background removal (single source for all forms) ---
  window.handleSignatureUpload = (file) => {
    if (!file) return;
    if (signatureFileName) signatureFileName.textContent = file.name;

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
        const threshold = 240; // treat near-white as background
        for (let i = 0; i < data.length; i += 4) {
          if (data[i] > threshold && data[i + 1] > threshold && data[i + 2] > threshold) {
            data[i + 3] = 0; // transparent
          }
        }
        ctx.putImageData(imageData, 0, 0);

        const output = canvas.toDataURL('image/png');
        signatureDataInput.value = output;
        signaturePathInput.value = '';
        signaturePreviewImg.src = output;
        signaturePreviewImg.classList.remove('hidden');
        signaturePlaceholder.classList.add('hidden');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  };

  // AUTOSAVE to server (throttled)
  const autoSaveToServer = (() => {
    let timer;
    return () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        const formData = new FormData(form);
        console.log('Auto-saving to server...');
        fetch('{{ route('pds.autosave') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            console.error('Auto-save failed:', response.status, response.statusText);
            throw new Error('Auto-save failed');
          }
          console.log('Auto-save successful');
          return response.json();
        })
        .then(data => {
          console.log('Auto-save response:', data);
        })
        .catch(error => {
          console.error('Auto-save error:', error);
        });
      }, 800);
    };
  })();

  const persist = () => {
    console.log('Persist function called');
    saveCache();
    autoSaveToServer();
  };

  loadCache();
  updateSignaturePreviewFromInputs();

  // Debug: Check if form element exists
  console.log('Form element:', form);
  console.log('Form event listener attached');

  // Enforce single select behavior for specific checkbox groups
  document.querySelectorAll('input[type="checkbox"]').forEach(box => {
    if (!singleSelectCheckboxNames.has(box.name)) return;
    box.addEventListener('change', () => {
      if (!box.checked) return;
      document.querySelectorAll(`input[type="checkbox"][name="${box.name}"]`).forEach(other => {
        if (other !== box) other.checked = false;
      });
      saveCache();
    });
  });

  // If served via static view (no $data), fetch draft and hydrate once
  fetch('{{ route('pds.draft') }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.ok ? r.json() : null)
    .then(json => {
      if (!json || !json.data) return;
      loadCache(json.data);
      updateSignaturePreviewFromInputs();
    })
    .catch(() => {});

  form.addEventListener('input', (e) => {
    console.log('Form input event triggered on:', e.target.name, e.target.type);
    persist();
  });
  form.addEventListener('change', (e) => {
    console.log('Form change event triggered on:', e.target.name, e.target.type);
    persist();
  });

});
</script>


</x-app-layout>