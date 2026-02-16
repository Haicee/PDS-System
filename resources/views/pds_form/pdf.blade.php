@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PDS PDF</title>
@if(empty($pdfMode))
  @vite('resources/css/app.css')
@endif
<style>
        /* Print-friendly, spreadsheet-like grid tuned to fit on one A4 page */
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: 'Arial', sans-serif; font-size: 8px; margin: 0 auto; max-width: 100%; width: 100%; }
        html, body { background: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        table { width: 100%; table-layout: fixed; border-collapse: collapse; background: #fff !important; }
        td, th { padding: 5px; word-wrap: break-word; overflow: visible; line-height: 1.1; vertical-align: middle; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        tr { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        td:not(.bg-\[#e7e7e7\]), th:not(.bg-\[#e7e7e7\]) { min-height: 22px; height: 22px; }
        /* Preserve colors in Browsershot print/PDF */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        .table-wrapper { width: 100%; }
        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        /* Keep header cells distinct and preserve colors for print */
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        }
        /* Form controls styled as lined cells */
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 12px; height: 12px;}
        @if (!empty($pdfMode))
        /* Minimal utility shims for Dompdf rendering */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .items-start { align-items: flex-start; }
        .items-end { align-items: flex-end; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-2 { gap: 0.5rem; }
        .flex-1 { flex: 1 1 0; }
        .w-full { width: 100%; }
        .w-1\/2 { width: 50%; }
        .w-1\/3 { width: 33.333%; }
        .w-2\/3 { width: 66.666%; }
        .w-1\/4 { width: 25%; }
        .w-3\/4 { width: 75%; }
        .w-1\/5 { width: 20%; }
        .w-1\/6 { width: 16.666%; }
        .w-60 { width: 15rem; }
        .w-40 { width: 10rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .text-center { text-align: center; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .p-4 { padding: 1rem; }
        .p-2 { padding: 0.5rem; }
        .p-1 { padding: 0.25rem; }
        .ml-2 { margin-left: 0.5rem; }
        .mr-2 { margin-right: 0.5rem; }
        .ml-4 { margin-left: 1rem; }
        .mr-4 { margin-right: 1rem; }
        .leading-tight { line-height: 1.2; }
        .whitespace-nowrap { white-space: nowrap; }
        .bg-gray-200 { background: #e5e7eb; }
        .bg-gray-300 { background: #d1d5db; }
        .max-w-9xl, .max-w-7xl, .max-w-6xl { max-width: 100%; }
        body { font-family: 'Arial', sans-serif; }
        /* Grid helpers used in the table layout */
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-cols-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        .grid-cols-4 { grid-template-columns: repeat(4, minmax(0,1fr)); }
        .grid-cols-[100px_120px] { grid-template-columns: 100px 120px; }
        .grid-cols-[200px_1fr] { grid-template-columns: 200px 1fr; }
        .table-fixed { table-layout: fixed; }
        /* Border/color utilities frequently used */
        .border-black { border-color: #000 !important; }
        .border-b-0 { border-bottom: 0 !important; }
        .border-b-2 { border-bottom: 2px solid #000 !important; }
        .border-t { border-top: 1px solid #000 !important; }
        .border-l { border-left: 1px solid #000 !important; }
        .border-r { border-right: 1px solid #000 !important; }
        .border-black\/50 { border-color: rgba(0,0,0,0.5) !important; }
        .bg-\[#e7e7e7\] { background: #e7e7e7 !important; background-color: #e7e7e7 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .bg-\[#8a8a8a\] { background: #8a8a8a !important; background-color: #8a8a8a !important; color: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .bg-\[#8a8a8a\] * { color: #fff !important; }
        /* Text sizing fallbacks */
        .text-xl { font-size: 1.1rem; }
        .text-2xl { font-size: 1.25rem; }
        .text-3xl { font-size: 1.4rem; }
        .text-4xl { font-size: 1.6rem; }
        .text-base { font-size: 1rem; }
        .text-lg { font-size: 1.05rem; }
        /* Width helpers */
        .w-300 { width: 300px; }
        .w-200 { width: 200px; }
        /* Misc */
        .space-y-1 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.25rem; }
        @endif
    </style>
</head>
<body>
@endif

<div class="p-0 font-serif text-sm" @if(!empty($pdfMode)) style="width:100%;max-width:100%;" @endif>
  <!-- HEADER -->
  <header class="mb-2 flex items-start justify-between gap-4 w-full">
    <span class="text-sm font-bold italic font-['Arial_Narrow','sans-serif']">CS Form No. 212
      <br>
    <span class="font-light text-sm italic font-['Arial_Narrow','sans-serif']">Revised 2025</span>
    </span>
    <h1 class=" font-extrabold text-4xl text-center mb-4 font-['Arial_Black','sans-serif'] flex-1">
      PERSONAL DATA SHEET
    </h1>
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
  <table class="align-middle w-full border border-black  table-fixed  font-['Arial_Narrow','sans-serif'] text-base edu-table">

    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:8%">
      <col style="width:9%">
      <col style="width:10%">
      <col style="width:12%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="4" class="font-['Arial_Narrow','Arial',sans-serif] bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" style="background-color:#8a8a8a;color:white;">
        I. PERSONAL INFORMATION
      </td>
    </tr>

    <!-- SURNAME -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle" style="background-color:#e7e7e7;">
        1.  SURNAME
     </td>
          <td class="border relative" colspan="3">
            <div >
              {{ $personal->surname ?? '—' }}
            </div>
</td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle" style="background-color:#e7e7e7;">
        2. FIRST NAME
      </td>
       <td class="border relative" colspan="2">
            <div>
               {{ $personal->firstname ?? '—' }}
  </div>
</td>

      <td class="bg-[#e7e7e7] align-top border" style="background-color:#e7e7e7;">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div class="ml-2">
           {{ $personal->name_extension ?? '—' }}
      </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle" style="padding-left: 26px;background-color:#e7e7e7;">
        MIDDLE NAME
    </td>
      <td colspan="3" class="border border-black h-10 align-middle">
        <div>
          {{ $personal->middlename ?? '—' }}
        </div>
      </td>
    </tr>

    <!-- DATE OF BIRTH + CITIZENSHIP -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border-black border" style="background-color:#e7e7e7;">
        3. DATE OF BIRTH
        <p class="text-xs font-normal ml-4">(dd/mm/yyyy)</p>
      </td>
      <td class="border">
        <div>
          {{ $personal->date_of_birth ?? '—' }}
      </div>
      </td>

    <td rowspan="3" class="px-2 align-top border-l-5 border-t border-black" style="background-color:#e7e7e7;">
        16. CITIZENSHIP
        <p class="text-base mt-8 text-center">
          If holder of dual citizenship, <br>
          please indicate the details.
        </p>
      </td>



      <td rowspan="3" class="border px-2 align-top text-base">
      <table class="w-full text-base" style="border-collapse:collapse;">
        <tr>
          <td class="py-1">
            <div class="flex items-center gap-6">
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle" name="citizenship[]" value="filipino" {{ $personal->citizenship  == 'filipino' ? 'checked' : '' }} disabled> Filipino</label>
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle" name="citizenship[]" value="dual_citizenship" {{ $personal->citizenship  == 'dual_citizenship' ? 'checked' : '' }} disabled> Dual Citizenship</label>
            </div>
          </td>
        </tr>
        <tr>
          <td class="py-1">
            <div class="flex items-center gap-6 ml-4">
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle" name="citizenship[]" value="by_birth" {{ $personal->citizenship  == 'by_birth' ? 'checked' : '' }} disabled> by birth</label>
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle" name="citizenship[]" value="by_naturalization" {{ $personal->citizenship  == 'by_naturalization' ? 'checked' : '' }} disabled> by naturalization</label>
            </div>
          </td>
        </tr>
        <tr>
          <td class="py-2 text-center">Pls. indicate country:</td>
        </tr>
        <tr>
          <td class="py-1">
            <div class="border text-center" style="min-height:32px; padding:4px 6px; margin-bottom: 10px;">
              {{ $personal->country ?? '—' }}
            </div>
          </td>
        </tr>
      </table>
    </td>
    </tr>

    <!-- PLACE OF BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 border align-middle" style="background-color:#e7e7e7;">
        4. PLACE OF BIRTH
      </td>
      <td class="border px-2 h-10">
        <div 
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
            py-2
           text-base">
    {{ $personal->place_of_birth ?? '—' }}
      </div>
      </td>
    </tr>

    <!-- SEX AT BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-2 border" style="background-color:#e7e7e7;">
        5. SEX AT BIRTH
      </td>
      <td class="border px-2 text-base">
        <div class="flex items-center justify-center gap-6">
          <label class="inline-flex items-center gap-2">
            <input class="ml-10"  type="checkbox" value="male" disabled {{ $personal->sex == 'male' ? 'checked' : '' }}>
            Male
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" value="female" disabled {{ $personal->sex == 'female' ? 'checked' : '' }}>
            Female
          </label>
        </div>
      </td>
    </tr>
    <!-- SIMPLE ROW TEMPLATE -->
      <td class="bg-[#e7e7e7] align-top py-5 px-2 border" style="background-color:#e7e7e7;">6. CIVIL STATUS</td>
      <td class="border px-2 py-1 align-middle h-2">
        <div class="p-1">
          <div class="grid grid-cols-2 gap-y-2 gap-x-6 text-base">
            <label class="flex items-center gap-2">
              <input type="checkbox" name="civilstatus[]" value="single" class="w-3 h-3" disabled {{ $personal->civil_status == 'single' ? 'checked' : '' }}>
              Single
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" name="civilstatus[]" value="married" disabled class="w-3 h-3" {{ $personal->civil_status == 'married' ? 'checked' : '' }}>
              Married
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" name="civilstatus[]" value="widowed" disabled class="w-3 h-3" {{ $personal->civil_status == 'widowed' ? 'checked' : '' }}>
              Widowed
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" name="civilstatus[]" value="separated" disabled class="w-3 h-3" {{ $personal->civil_status == 'separated' ? 'checked' : '' }}>
              Separated
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" name="civilstatus[]" value="other/s" disabled class="w-3 h-3" {{ $personal->civil_status == 'Other/s' ? 'checked' : '' }}>
              Other/s:
            </label>
          </div>
        </div>
      </td>
    
<td rowspan="3" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table style="width:38%;">
        <tr>
          <td class="px-2 align-top border-black border-t-0 border-r" style="background-color:#e7e7e7;">
            17. RESIDENTIAL ADDRESS
          </td>
        </tr>
        <tr class="h-10">
          <td class="text-center text-lg py-2 border-r" style="background-color:#e7e7e7;">
            ZIP CODE
          </td>
        </tr>
      </table>
      <!-- RIGHT TABLE -->
      <table class="bg-white h-full" style="width:65%; border-collapse:collapse;">
        <tr>
          <td class="h-auto align-top">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->present_house_block_lot ?? '—' }}</div>
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->present_street ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>House/Block/Lot No.</p>
              <p>Street</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->present_subdivision_village ?? '—' }}</div>
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->present_barangay ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>Subdivision/Village</p>
              <p>Barangay</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->present_city_municipality ?? '—' }}</div>
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->present_province ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>City/Municipality</p>
              <p>Province</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black text-center py-2 text-base border-b-0">
            {{ $address->present_zip_code ?? '—' }}
          </td>
        </tr>
      </table>

    </div>
  </td>




    <tr>
      <td class="px-2 border font-['Arial_Narrow','Arial',sans-serif]" style="background-color:#e7e7e7;">7. HEIGHT (m)</td>
      <td class="border px-2 h-10">
        {{ $personal->height ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">8. WEIGHT (kg)</td>
      <td class="border px-2 h-10">
        {{ $personal->weight ?? '—' }}
      </td>
    </tr>


      <td class="font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8" style="background-color:#e7e7e7;">9. BLOOD TYPE</td>
      <td class="border px-2 align-middle"> 
       {{ $personal->blood_type ?? '—' }}
      </td>


  <td rowspan="4" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7] border-b font-['Arial_Narrow','Arial',sans-serif] text-base" style="width:38%;">
        <tr>
          <td class="px-2 py-1 align-top border-r" style="background-color:#e7e7e7;">
            18. PERMANENT ADDRESS
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white" style="width:65%; border-collapse:collapse;">
        <tr>
          <td class="h-auto align-top">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->permanent_house_block_lot ?? '—' }}</div>
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->permanent_street ?? '—' }}</div>
            </div>
          </td>
        </tr>
          
          <tr class="h-2">
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>House/Block/Lot No.</p>
              <p>Street</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->permanent_subdivision_village ?? '—' }}</div>
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->permanent_barangay ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50 ">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>Subdivision/Village</p>
              <p>Baranggay</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->permanent_city_municipality ?? '—' }}</div>
              <div class="px-1 py-1 text-base whitespace-pre-wrap">{{ $address->permanent_province ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr >
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>City/Municipality</p>
              <p>Province</p>
            </div>
          </td>
        </tr>
      </table>

    </div>
  </td>    

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">10. UMID ID NO.</td>
      <td class="border px-2 h-10">
        {{ $personal->umid_no ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">11. PAG-IBIG ID NO.</td>
      <td class="border px-2 h-10">
        {{ $personal->pagibig_no ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">12. PHILHEALTH NO.</td>
      <td class="border px-2 h-10">
         {{ $personal->philhealth_no ?? '—' }}
      </td>
    </tr>

    @php
        $childNames = $children->pluck('firstname')->toArray();
        $childDobs = $children->pluck('date_of_birth')->toArray();
        $childRowCount = max(14, count($childNames), count($childDobs));
        $childNames = array_pad($childNames, $childRowCount, '');
        $childDobs = array_pad($childDobs, $childRowCount, '');
        $childIndex = 0;
    @endphp

    
    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8" style="background-color:#e7e7e7;">13. PhilSys Number (PSN):</td>
      <td class="border px-2 align-middle">
          {{ $personal->philsys_no ?? '—' }}
      </td>
      <td class="px-2 align-middle border" style="background-color:#e7e7e7; width:35%;">19. TELEPHONE NO.</td>
      <td class="border px-2 align-middle"  style="width:65%;">{{ $contact->telephone_no ?? '—' }}</td>
    </tr>
   
    <tr>
      <td class="font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">14. TIN ID</td>
      <td class="border px-2 align-middle">
        {{ $personal->tin_no ?? '—' }}
      </td>
      <td class="px-2 align-middle border" style="background-color:#e7e7e7; width:35%;">20. MOBILE NO.</td>
      <td class="border px-2 align-middle" style="width:65%;">{{ $contact->mobile_no ?? '—' }}</td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">15. AGENCY EMPLOYEE ID</td>
      <td class="border px-2 align-middle">
         {{ $personal->agency_employee_no ?? '—' }}
      </td>
      <td class="bg-[#e7e7e7] px-2 align-middle border" style="background-color:#e7e7e7; width:35%;">21. E-MAIL ADDRESS (if any)</td>
      <td class="border px-2 align-middle" style="width:65%;">{{ $contact->email_address ?? '—' }}</td>
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
          <div>{{ $spouse->surname ?? '—' }}</div>
      </td>

      <td class="border text-center bg-[#e7e7e7] ">
        23. NAME of CHILDREN  (Write full name and list all)
      </td>

      <td class="border text-center bg-[#e7e7e7]">
        DATE OF BIRTH (dd/mm/yyyy) 
      </td>

    </tr>

    


    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        FIRST NAME
      </td>
       <td colspan="2"
          class="border">
          <div>
 {{ $spouse->firstname ?? '—' }}
          </div>
      </td>

      <td class="bg-[#e7e7e7] align-top">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div class="ml-2">
            {{ $spouse->name_extension ?? '—' }}
        </div>
      </td>
      
      <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7]  px-8"> 
        MIDDLE NAME
      </td>
      <td colspan="3"
          class="border h-10">
          <div>
         {{ $spouse->middlename ?? '—' }}
      </div>
      </td>

      <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

      <tr>
      <td class="bg-[#e7e7e7] px-2 border border-t-2">OCCUPATION</td>
       
      <td colspan="3"
          class="border h-10">
          <div>
        {{ $spouse->occupation ?? '—' }}
      </td>

    <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">EMPLOYER/BUSINESS NAME</td>
      
      <td colspan="3"
          class="border h-10">
          <div>
          {{ $spouse->employer ?? '—' }}
      </td>


        <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">BUSINESS ADDRESS</td>
      
      <td colspan="3"
          class="border h-10">
          <div>
          {{ $spouse->business_address ?? '—' }}
      </td>


     <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">TELEPHONE NO.</td>
      
       <td colspan="3"
          class="border h-10">
          <div>
          {{ $spouse->telephone_no ?? '—' }}
      </td>


        <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>


    <tr>
      <td class="bg-[#e7e7e7]  px-2 align-middle">
        24. FATHER'S SURNAME
      </td>
      

       <td colspan="3"
          class="border h-10">
          <div>
        {{ $father->surname ?? '—' }}
      </td>


        <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
         {{ $childDobs[$childIndex] ?? ' ' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        FIRST NAME
      </td>

       <td colspan="2"
          class="border">
          <div>
        {{ $father->firstname ?? '—' }}
      </div>
      </td>
      </td>

      <td class="bg-[#e7e7e7] align-top">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
       <div class="ml-2">
        {{ $father->name_extension ?? '—' }}
      </div>
      </td>

       <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? '' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? '' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7]  px-8"> 
        MIDDLE NAME
      </td>

      <td colspan="3"
          class="border h-10">
          <div>
        {{ $father->middlename ?? '—' }}
      </div>
      </td>

    <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? ' ' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>



    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border-t-2">
        25. MOTHER'S MAIDEN NAME
      </td>

      <td colspan="3"
          class="border-t-2 border h-10">
       <div>
        {{ $mother->maiden_name ?? '—' }}
      </div>
      </td>

       <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? ' ' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? ' ' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle  px-8"> 
        SURNAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div>
        {{ $mother->surname ?? '—' }}
      </div>
      </td>


      <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? ' ' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? ' ' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>


    <tr>
      <td class="bg-[#e7e7e7]  px-8 align-middle">
       FIRST NAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div>
        {{ $mother->firstname ?? '—' }}
      </div>
      </td>

       <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? ' ' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? ' ' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        MIDDLE NAME
      </td>
      
      <td colspan="3"
          class="border h-10">
           <div>
        {{ $mother->middlename ?? '—' }}
      </div>
      </td>

      
        <td class="border">
       <div class="h-full w-full px-2 text-center">
         {{ $childNames[$childIndex] ?? ' ' }}
       </div>
      </td>

      <td class="border">
       <div  class="h-full w-full px-2 text-center">
         {{ $childDobs[$childIndex] ?? ' ' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>
  </table>


<table class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">

  @php
      $eduByLevel = $education->keyBy('level');
      $eduVal = function(string $level, string $field) use ($eduByLevel) {
          $rec = $eduByLevel->get($level);
          return $rec && isset($rec->$field) ? $rec->$field : '';
      };
      $eduCourse = function(string $level) use ($eduVal) {
          $course = $eduVal($level, 'degree_course');
          if ($course === '') {
              $course = $eduVal($level, 'basic_education');
          }
          return $course;
      };
      $eduHonors = function(string $level) use ($eduVal) {
          $honors = $eduVal($level, 'academic_honors');
          if ($honors === '') {
              $honors = $eduVal($level, 'scholarship_acadhonors');
          }
          return $honors;
      };
  @endphp

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
        class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2  border-black border-2 border-t-0">
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
          class="border h-10 align">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduVal('elementary','school_name') }}
          </div>
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduCourse('elementary') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('elementary','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('elementary','to') }}
      </td>

      <td
            class="border h-10">
            <div class="edu-cell h-full w-full text-center">
              {{ $eduVal('elementary','highest_level') }}
        </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('elementary','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('elementary') }}
      </td>
  </tr>


   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">SECONDARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduVal('secondary','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduCourse('secondary') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('secondary') }}
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">VOCATIONAL / TRADE COURSE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('vocational','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduCourse('vocational') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full  text-center">
            {{ $eduVal('vocational','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('vocational','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('vocational','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full   text-center">
            {{ $eduVal('vocational','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full   text-center">
            {{ $eduHonors('vocational') }}
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">COLLEGE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="edu-cell h-full w-full px-2 text-center">
            {{ $eduVal('college','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full px-2 text-center">
            {{ $eduCourse('college') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('college','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('college','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full   text-center">
            {{ $eduVal('college','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('college','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('college') }}
      </td>
  </tr>

  <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">GRADUATE STUDIES</td>

    <!-- EDITABLE CELL PATTERN -->
   <td
          class="border h-10">
          <div class="edu-cell h-full w-full px-2 text-center">
            {{ $eduVal('graduate_studies','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full px-2 text-center">
            {{ $eduCourse('graduate_studies') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('graduate_studies') }}
      </td>
  </tr>

 

  <tr>
    <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

    <td class="border" colspan="2">
      <div class="h-full w-full flex flex-col items-center justify-center p-2">
        <input type="file" name="signature_attachment_1" id="signature_attachment" disabled accept="image/*,.pdf" class="text-sm">
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
      disabled
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
</table>

<div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 1 of 5
</div>
</div>

@if(!empty($pdfMode))
    <!-- Page 1 Complete -->

    <!-- Page 2: Civil Service Eligibility & Work Experience -->
    <div style="page-break-after: always;">
        <h2 class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white italic text-xl px-2 border-2 border-black font-bold" style="background-color:#8a8a8a !important; color:white !important;">
          IV.  CIVIL SERVICE ELIGIBILITY
        </h2>

        <table class="w-full border border-black" style="border-collapse: collapse;">
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;" rowspan="2">
                    27. ELIGIBILITY NAME
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;" rowspan="2">
                    RATING
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;" rowspan="2">
                    DATE OF EXAMINATION
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;" rowspan="2">
                    PLACE OF EXAMINATION
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;" colspan="2">
                    LICENSE
                </th>
            </tr>
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">NUMBER</th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">VALID UNTIL</th>
            </tr>
            @php
                $eligibilityRows = $eligibilities ?? collect();
                $maxRows = max(7, $eligibilityRows->count()); // Show 7 rows minimum
            @endphp

            @for ($i = 0; $i < $maxRows; $i++)
                @php $row = $eligibilityRows[$i] ?? null; @endphp
                <tr>
                    <td class="border p-2 align-top text-center">{{ $row->eligibility ?? ' ' }}</td>
                    <td class="border p-2 align-top text-center">{{ $row->rating ?? ' ' }}</td>
                    <td class="border p-2 align-top text-center">{{ $row->exam_date ?? ' ' }}</td>
                    <td class="border p-2 align-top text-center">{{ $row->exam_place ?? ' ' }}</td>
                    <td class="border p-2 align-top text-center">{{ $row->license_no ?? ' ' }}</td>
                    <td class="border p-2 align-top text-center">{{ $row->validity ?? ' ' }}</td>
                </tr>
            @endfor
        </table>

        <h2 class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white italic text-xl px-2 border-2 border-black font-bold mt-4" style="background-color:#8a8a8a !important; color:white !important;">
          V.  WORK EXPERIENCE
        </h2>

        <table class="w-full border border-black" style="border-collapse: collapse;">
            <tr>
                <th colspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">INCLUSIVE DATES</th>
                <th rowspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">POSITION TITLE</th>
                <th rowspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">DEPARTMENT / AGENCY / COMPANY</th>
                <th rowspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">STATUS</th>
                <th rowspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">GOV'T SERVICE</th>
            </tr>
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">FROM</th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">TO</th>
            </tr>

            @php
                $workRows = $work ?? collect();
                $maxWorkRows = max(10, $workRows->count()); // Show at least 10 work experience rows
            @endphp

            @for ($i = 0; $i < $maxWorkRows; $i++)
                @php $workRow = $workRows[$i] ?? null; @endphp
                <tr>
                    <td class="border p-2 align-top text-center">{{ $workRow->from ?? " " }}</td>
                    <td class="border p-2 align-top text-center">{{ $workRow->to ?? " " }}</td>
                    <td class="border p-2 align-top text-center">{{ $workRow->position_title ?? " " }}</td>
                    <td class="border p-2 align-top text-center">{{ $workRow->department ?? " " }}</td>
                    <td class="border p-2 align-top text-center">{{ $workRow->status ?? " " }}</td>
                    <td class="border p-2 align-top text-center">{{ $workRow->govt_service ?? " " }}</td>
                </tr>
            @endfor
        </table>

        <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
            CS FORM 212 (Revised 2025), Page 2 of 5
        </div>
    </div>

    <!-- Page 3: Voluntary Work & Learning Development -->
    <div style="page-break-after: always;">
        <h2 class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white italic text-xl px-2 border-2 border-black font-bold" style="background-color:#8a8a8a !important; color:white !important;">
          VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE / VOLUNTARY ORGANIZATIONS
        </h2>

        <table class="w-full border border-black" style="border-collapse: collapse;">
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    NAME & ADDRESS OF ORGANIZATION
                </th>
                <th colspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    INCLUSIVE DATES
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    HOURS
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    POSITION / NATURE OF WORK
                </th>
            </tr>
            <tr>
                <th class="border p-1 text-center">&nbsp;</th>
                <th class="border p-1 text-center bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">FROM</th>
                <th class="border p-1 text-center bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">TO</th>
                <th class="border p-1 text-center">&nbsp;</th>
                <th class="border p-1 text-center">&nbsp;</th>
            </tr>

            @php
                $voluntaryRows = $voluntary ?? collect();
                $maxVoluntaryRows = max(5, $voluntaryRows->count()); // Show at least 5 rows
            @endphp

            @for ($i = 0; $i < $maxVoluntaryRows; $i++)
                @php $volRow = $voluntaryRows[$i] ?? null; @endphp
                <tr>
                    <td class="border p-2">{{ $volRow->name_address ?? '' }}</td>
                    <td class="border p-2 text-center">{{ $volRow->from ?? '' }}</td>
                    <td class="border p-2 text-center">{{ $volRow->to ?? '' }}</td>
                    <td class="border p-2 text-center">{{ $volRow->hours ?? '' }}</td>
                    <td class="border p-2">{{ $volRow->position ?? '' }}</td>
                </tr>
            @endfor
        </table>

        <h2 class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white italic text-xl px-2 border-2 border-black font-bold mt-4" style="background-color:#8a8a8a !important; color:white !important;">
          VII. LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED
        </h2>

        <table class="w-full border border-black" style="border-collapse: collapse;">
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS
                </th>
                <th colspan="2" class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    INCLUSIVE DATES
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    HOURS
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    TYPE OF LD
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">
                    CONDUCTED/SPONSORED BY
                </th>
            </tr>
            <tr>
                <th class="border p-1 text-center">&nbsp;</th>
                <th class="border p-1 text-center bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">FROM</th>
                <th class="border p-1 text-center bg-[#e7e7e7]" style="background-color:#e7e7e7 !important;">TO</th>
                <th class="border p-1 text-center">&nbsp;</th>
                <th class="border p-1 text-center">&nbsp;</th>
                <th class="border p-1 text-center">&nbsp;</th>
            </tr>

            @php
                $trainingRows = $training ?? collect();
                $maxTrainingRows = max(5, $trainingRows->count());
            @endphp

            @for ($i = 0; $i < $maxTrainingRows; $i++)
                @php $trainRow = $trainingRows[$i] ?? null; @endphp
                <tr>
                    <td class="border p-2">{{ $trainRow->title ?? '' }}</td>
                    <td class="border p-2 text-center">{{ $trainRow->from ?? '' }}</td>
                    <td class="border p-2 text-center">{{ $trainRow->to ?? '' }}</td>
                    <td class="border p-2 text-center">{{ $trainRow->hours ?? '' }}</td>
                    <td class="border p-2">{{ $trainRow->type ?? '' }}</td>
                    <td class="border p-2">{{ $trainRow->conducted_by ?? '' }}</td>
                </tr>
            @endfor
        </table>

        <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
            CS FORM 212 (Revised 2025), Page 3 of 5
        </div>
    </div>

    <!-- Page 4: Other Information -->
    <div style="page-break-after: always;">
        <h2 class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white italic text-xl px-2 border-2 border-black font-bold" style="background-color:#8a8a8a !important; color:white !important;">
          VIII. OTHER INFORMATION
        </h2>

        <table class="w-full border border-black" style="border-collapse: collapse;">
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="width:33%; background-color:#e7e7e7 !important;">
                    SPECIAL SKILLS and HOBBIES
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="width:33%; background-color:#e7e7e7 !important;">
                    NON-ACADEMIC DISTINCTIONS / RECOGNITION
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="width:34%; background-color:#e7e7e7 !important;">
                    MEMBERSHIP IN ASSOCIATION/ORGANIZATION
                </th>
            </tr>

            @php
                $otherInfoRows = $otherInfo ?? collect();
                $maxOtherRows = max(7, $otherInfoRows->count());
            @endphp

            @for ($i = 0; $i < $maxOtherRows; $i++)
                @php
                    $skills = $otherInfoRows->where('type', 'skills')->values()->get($i) ?? null;
                    $recognitions = $otherInfoRows->where('type', 'recognitions')->values()->get($i) ?? null;
                    $memberships = $otherInfoRows->where('type', 'memberships')->values()->get($i) ?? null;
                @endphp
                <tr>
                    <td class="border p-2">{{ $skills->details ?? '' }}</td>
                    <td class="border p-2">{{ $recognitions->details ?? '' }}</td>
                    <td class="border p-2">{{ $memberships->details ?? '' }}</td>
                </tr>
            @endfor
        </table>

        <h3 class="font-['Arial_Narrow','Arial',sans-serif] text-center font-bold mt-4">
          REFERENCES
        </h3>

        <table class="w-full border border-black" style="border-collapse: collapse;">
            <tr>
                <th class="border p-2 bg-[#e7e7e7]" style="width:30%; background-color:#e7e7e7 !important;">
                    NAME
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="width:40%; background-color:#e7e7e7 !important;">
                    ADDRESS
                </th>
                <th class="border p-2 bg-[#e7e7e7]" style="width:30%; background-color:#e7e7e7 !important;">
                    TEL. NO. / EMAIL
                </th>
            </tr>

            @php
                $referenceRows = $references ?? collect();
                $maxRefRows = max(3, $referenceRows->count());
            @endphp

            @for ($i = 0; $i < $maxRefRows; $i++)
                @php $ref = $referenceRows[$i] ?? null; @endphp
                <tr>
                    <td class="border p-2">{{ $ref->name ?? '' }}</td>
                    <td class="border p-2">{{ $ref->address ?? '' }}</td>
                    <td class="border p-2">{{ $ref->tel_no ?? '' }}</td>
                </tr>
            @endfor
        </table>

        <h3 class="font-['Arial_Narrow','Arial',sans-serif] text-left font-bold mt-4">
          34. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct and complete statement pursuant to the provisions of pertinent laws, rules and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein. I agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.
        </h3>

        <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
            CS FORM 212 (Revised 2025), Page 4 of 5
        </div>
    </div>

    <!-- Page 5: Remarks -->
    <div>
        <h2 class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white italic text-xl px-2 border-2 border-black font-bold" style="background-color:#8a8a8a !important; color:white !important;">
          REMARKS
        </h2>
        
        @php
            $remarksRows = $remarks ?? collect();
            $maxRemarks = max(5, $remarksRows->count());
        @endphp
        
        <div class="border border-black p-4 min-h-[400px]">
            @foreach ($remarksRows as $remark)
                <p class="mb-2">{{ $remark->content ?? '' }}</p>
            @endforeach
        </div>

        <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif'; margin-top: 1rem;">
            CS FORM 212 (Revised 2025), Page 5 of 5
        </div>
    </div>
@endif

@if(!empty($pdfMode))
</body>
</html>
@endif
