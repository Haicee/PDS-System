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
  <table class="align-middle w-full border-2  border-black  table-fixed  font-['Arial_Narrow','sans-serif'] text-base edu-table border-b-0">

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
            <div class="flex items-center gap-6>
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
        <div class="border text-xl flex justify-center items-center" 
            style="min-height:30px; padding:6px 8px; margin-bottom:10px; font-size:20px;">
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
      <table style="width:37.8%; padding:0px;">
        <tr>
          <td class="align-top border-black border-t-0" style="background-color:#e7e7e7;">
            17. RESIDENTIAL ADDRESS
          </td>
        </tr>
        <tr class="h-10">
          <td class="text-center text-lg py-2" style="background-color:#e7e7e7;">
            ZIP CODE
          </td>
        </tr>
      </table>
      <!-- RIGHT TABLE -->
      <table class="bg-white h-full border-l" style="width:65%; border-collapse:collapse;">
        <tr>
          <td class="align-top">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->present_house_block_lot ?? '—' }}</div>
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->present_street ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 text-center ">
              <p>House/Block/Lot No.</p>
              <p>Street</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->present_subdivision_village ?? '—' }}</div>
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->present_barangay ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 text-center">
              <p>Subdivision/Village</p>
              <p>Barangay</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->present_city_municipality ?? '—' }}</div>
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->present_province ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 text-center">
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
          <td class="px-2 py-1 align-top" style="background-color:#e7e7e7;">
            18. PERMANENT ADDRESS
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white border-l" style="width:65%; border-collapse:collapse;">
        <tr>
          <td class="align-top">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->permanent_house_block_lot ?? '—' }}</div>
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->permanent_street ?? '—' }}</div>
            </div>
          </td>
        </tr>
          
          <tr class="h-2">
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 text-center">
              <p>House/Block/Lot No.</p>
              <p>Street</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->permanent_subdivision_village ?? '—' }}</div>
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->permanent_barangay ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50 ">
            <div class="grid grid-cols-2 text-center">
              <p>Subdivision/Village</p>
              <p>Baranggay</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->permanent_city_municipality ?? '—' }}</div>
              <div class="py-1 text-base whitespace-pre-wrap">{{ $address->permanent_province ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr >
          <td class="border-t border-black/50">
            <div class="grid grid-cols-2 text-center">
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
      <td class="bg-[#e7e7e7] border font-['Arial_Narrow','Arial',sans-serif] px-2" style="background-color:#e7e7e7;">15. AGENCY EMPLOYEE ID</td>
      <td class="border px-2 align-middle">
         {{ $personal->agency_employee_no ?? '—' }}
      </td>
      <td class="bg-[#e7e7e7] px-2 align-middle border" style="background-color:#e7e7e7; width:35%;">21. E-MAIL ADDRESS (if any)</td>
      <td class="border px-2 align-middle" style="width:65%;">{{ $contact->email_address ?? '—' }}</td>
    </tr>

  </table>


   <table class="w-full border-2 border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">

    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:20%">
      <col style="width:10%">
      <col style="width:12%">
      <col style="width: 16%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="6" class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2 border-b-2">
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


<table class="w-full border-2 border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base border-t-0">

  @php
      $eduByLevel = $education->keyBy('level');
      // Raw accessor (no default); NA will display only if stored as such
    $eduValRaw = function(string $level, string $field) use ($education) {
        $rec = $education->firstWhere('level', $level);
        return $rec && isset($rec->$field) ? $rec->$field : '';
    };

    // School name: default to NA when empty
    $eduSchool = function(string $level) use ($eduValRaw) {
        $name = trim((string)$eduValRaw($level, 'school_name'));
        return $name === '' ? 'NA' : $name;
    };

    // Return raw value; if school is NA, hide other fields
    $eduVal = function(string $level, string $field) use ($eduValRaw) {
        $school = strtolower(trim((string)$eduValRaw($level, 'school_name')));
        if ($field !== 'school_name' && $school === 'na') {
            return '';
        }
        $value = $eduValRaw($level, $field);
        return ($value === null) ? '' : $value;
    };

    $eduCourse = function(string $level) use ($eduValRaw) {
        $school = strtolower(trim((string)$eduValRaw($level, 'school_name')));
        if ($school === 'na') {
            return '';
        }
        $course = $eduValRaw($level, 'degree_course');
        if ($course === '') {
            $course = $eduValRaw($level, 'basic_education');
        }
        return ($course === null) ? '' : $course;
    };

    $eduHonors = function(string $level) use ($eduValRaw) {
        $school = strtolower(trim((string)$eduValRaw($level, 'school_name')));
        if ($school === 'na') {
            return '';
        }
        $honors = $eduValRaw($level, 'academic_honors');
        if ($honors === '') {
            $honors = $eduValRaw($level, 'scholarship_acadhonors');
        }
        return ($honors === null) ? '' : $honors;
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
        class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2 border-b-2">
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
            {{ $eduSchool('elementary') }}
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
            {{ $eduSchool('secondary') }}
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
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('vocational') }}
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
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('college') }}
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
             <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('graduate_studies') }}
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

<div style="page-break-before: always;">
<table>
  <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      IV.  CIVIL SERVICE ELIGIBILITY
    </th>

    <tr>
      <th class="border bg-[#e7e7e7]" rowspan="2" style="width: 30%";>
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

  @php
    $rows = $eligibilities ?? collect();
    $maxRows = max(7, $rows->count()); // 7 display rows
@endphp

@for ($i = 0; $i < $maxRows; $i++)
  @php $row = $rows[$i] ?? null; @endphp
  <tr>
    <td class="border align-top text-center">{{ $row->eligibility ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->rating ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->exam_date ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->exam_place ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->license_no ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->validity ?? ' ' }}</td>
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

   @php
    // Fallback to $work when $workExperiences isn't passed, then sort ascending by start date
    $workRows = $workExperiences ?? ($work ?? collect());
    $workRows = $workRows->sortBy('from')->values();
    $maxRows = max(28, $workRows->count());
@endphp

@for ($i = 0; $i < $maxRows; $i++)
  @php $workRow = $workRows[$i] ?? null; @endphp
  <tr>
    <td class="border align-top text-center">{{ $workRow->from ?? " " }}</td>
    <td class="border align-top text-center">{{ $workRow->to ?? " " }}</td>
    <td class="border align-top text-center">{{ $workRow->position_title ?? " " }}</td>
    <td class="border align-top text-center">{{ $workRow->department ?? " " }}</td>
    <td class="border align-top text-center">{{ $workRow->status ?? " " }}</td>
    <td class="border align-top text-center">{{ $workRow->govt_service ?? " " }}</td>
  </tr>
@endfor
    <tr>
      <table class="border-black w-full h-15 font-['Arial_Narrow','sans-serif']">
        <colgroup>
          <col style="width: 17%;">
           <col style="width: 20%;">
            <col style="width: 16.02%;">
             <col style="width: 15%;">
        </colgroup>
      <tr>
      <td class="text-center font-bold text-xl border align-middle italic">
          SIGNATURE
      </td>

       <td class="border" colspan="2">
      <div class="h-full w-full flex flex-col items-center justify-center p-2">
        @if(empty($pdfMode))
        <input type="file" name="signature_attachment_3" id="signature_attachment" accept="image/*,.pdf" class="text-sm">
        @endif
      </div>
    </td>

      <td class="border text-center font-bold text-xl align-middle italic">
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

     
      <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 2 of 5
</div>
</div>

<div style="page-break-before: always;"></div>
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

    @php
    // Fallback to $voluntary when $voluntaryWorks isn't passed, then sort by start date ascending
    $volRows = $voluntaryWorks ?? ($voluntary ?? collect());
    $volRows = $volRows->sortBy('from')->values();
    $maxRows = max(7, $volRows->count());
@endphp

@for ($i = 0; $i < $maxRows; $i++)
  @php $row = $volRows[$i] ?? null; @endphp
  <tr>
    <td class="border align-top text-center">{{ $row->organization ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->from ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->to ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->hours ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $row->position ?? ' ' }}</td>
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

      @php
    // Fallback to $learning when $training isn't passed, then sort by start date ascending
    $trainingRows = $training ?? ($learning ?? collect());
    $trainingRows = $trainingRows->sortBy('from')->values();
    $maxTraining = max(21, $trainingRows->count());
@endphp

@for ($i = 0; $i < $maxTraining; $i++)
  @php $trow = $trainingRows[$i] ?? null; @endphp
  <tr>
    <td class="border align-top text-center">{{ $trow->title ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $trow->from ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $trow->to ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $trow->hours ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $trow->type_of_ld ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $trow->conducted_by ?? ' ' }}</td>
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

    @php
    $otherCollection = $other ?? ($otherInfo ?? collect());
    $skills = $otherCollection->where('category', 'skills')->pluck('description')->values();
    $recognition = $otherCollection->where('category', 'recognition')->pluck('description')->values();
    $assoc = $otherCollection->where('category', 'association')->pluck('description')->values();
    $maxOther = max(7, $skills->count(), $recognition->count(), $assoc->count());
@endphp

@for ($i = 0; $i < $maxOther; $i++)
  <tr>
    <td class="border align-top text-center">{{ $skills[$i] ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $recognition[$i] ?? ' ' }}</td>
    <td class="border align-top text-center">{{ $assoc[$i] ?? ' ' }}</td>
  </tr>
@endfor

    </table>

    <table class="border border-black w-full h-15 font-['Arial_Narrow','sans-serif'] italic">
        <colgroup>
          <col style="width: 31.95%;">
           <col style="width: 15.64%;">
            <col style="width: 15%;">
             <col style="width: 10%;">
        </colgroup>
      <tr>
      <td class="text-center font-bold text-xl border">
          SIGNATURE
      </td>

       <td class="border" colspan="2">
      <div class="h-full w-full flex flex-col items-center justify-center p-2">
        @if(empty($pdfMode))
        <input type="file" name="signature_attachment_3" id="signature_attachment" accept="image/*,.pdf" class="text-sm">
        @endif
      </div>
    </td>


      <td class="border text-center font-bold text-xl">
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

    <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 3 of 5
</div>
<div style="page-break-before: always;"></div>
 <table class="border-black w-full text-sm border-2 border-b-0">
    <tr>
      <td class="border w-2/3 align-top border-black">
        <div class="ml-4 mb-3 mt-3">
          34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the
          chief of bureau or office or to the person who has immediate supervision over you in the Office,
          Bureau or Department where you will be appointed,
          <p class="ml-10 mt-5">a. within the third degree?</p>
          <p class="ml-10 mt-3">b. within the fourth degree (for Local Government Unit – Career Employees)?</p>
        </div>
      </td>
      <td class="border px-2 align-top border-black">
        <div class="flex h-full gap-20 mt-20">
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_a" value="YES" Disabled @checked(($declaration->q34_a ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_a" value="NO" Disabled @checked(($declaration->q34_a ?? '') === 'NO')> NO</label>
        </div>
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_b" value="YES" Disabled @checked(($declaration->q34_b ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-3"><input type="checkbox" name="q34_b" value="NO" Disabled @checked(($declaration->q34_b ?? '') === 'NO')> NO</label>
        </div>
        <p class="mt-2">if yes, give details:</p>
        <input type="text" class="mb-2 w-full" name="q34_a_details" data-detail-for="q34_a" Disabled value="{{ $declaration->q34_a_details ?? '' }}">
        <input type="text" class="mb-2 w-full" name="q34_b_details" data-detail-for="q34_b" Disabled value="{{ $declaration->q34_b_details ?? '' }}">
      </td>
    </tr>

    <tr>
      <td class="border w-2/3 align-top border-b-0 border-black">
        <div class="ml-5 mb-3 mt-3">35. a. Have you ever been found guilty of any administrative offense?</div>
      </td>
      <td class="border px-2 align-top border-black">
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_a" Disabled value="YES" @checked(($declaration->q35_a ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_a" Disabled value="NO" @checked(($declaration->q35_a ?? '') === 'NO')> NO</label>
        </div>
        <p class="mt-2">if yes, give details:</p>
        <input type="text" class="mb-2 w-full" name="q35_a_details" data-detail-for="q35_a" Disabled value="{{ $declaration->q35_a_details ?? '' }}">
      </td>
    </tr>


    <tr>
      <td class="w-2/3 align-top border-t-0 border-black">
        <div class="ml-10 mb-3 mt-3">b. Have you been criminally charged before any court?</div>
      </td>
      <td class="border px-2 border-black">
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_b" Disabled value="YES" @checked(($declaration->q35_b ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_b" Disabled value="NO" @checked(($declaration->q35_b ?? '') === 'NO')> NO</label>
        </div>
        <p class="mt-2 mb-2">if yes, give details:</p>
        <span class="ml-9">Date Filed:</span> <input class="border-b mb-2 mr-40" type="text" Disabled data-detail-for="q35_b" name="q35_b_details_date" value="{{ $declaration->q35_b_details_date ?? '' }}">
        <span class="ml-1">Status of Case/s:</span> <input class="border-b mb-2" type="text" Disabled data-detail-for="q35_b" name="q35_b_details_status" value="{{ $declaration->q35_b_details_status ?? '' }}">
      </td>
    </tr>

 <tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
        </div>
        
      </td>
      
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-3">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q36" value="YES" Disabled @checked(($declaration->q36 ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q36" value="NO" Disabled @checked(($declaration->q36 ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" Disabled name="q36_details" data-detail-for="q36" value="{{ $declaration->q36_details ?? '' }}">

</tr>


<tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?
        </div>
      </td>
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-3">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q37" value="YES" Disabled @checked(($declaration->q37 ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q37" value="NO" Disabled @checked(($declaration->q37 ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" Disabled name="q37_details" data-detail-for="q37" value="{{ $declaration->q37_details ?? '' }}">

</tr>



<tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
        </div>
        
      </td>
      
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q38_a" value="YES" Disabled @checked(($declaration->q38_a ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q38_a" value="NO" Disabled @checked(($declaration->q38_a ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" Disabled name="q38_a_details" data-detail-for="q38_a" value="{{ $declaration->q38_a_details ?? '' }}">

</tr>


 <tr>
      <td class="border w-2/3 align-top border-t-0 border-black">
              <div class="ml-10 mb-3 mt-3">b. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?
    
        </div>
        
      </td>
      
     <td class="border  px-2  border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q38_b" value="YES" Disabled @checked(($declaration->q38_b ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q38_b" value="NO" Disabled @checked(($declaration->q38_b ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q38_b_details" data-detail-for="q38_b" value="{{ $declaration->q38_b_details ?? '' }}">   

</tr>



<tr>
      <td class="border w-2/3 align-top border-t-0 border-black border">
              <div class="ml-5 mb-3 mt-3">39. Have you acquired the status of an immigrant or permanent resident of another country?
        </div>
        
      </td>
      
     <td class="border  px-2 border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q39" value="YES" Disabled @checked(($declaration->q39 ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q39" value="NO" Disabled @checked(($declaration->q39 ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q39_details" data-detail-for="q39" value="{{ $declaration->q39_details ?? '' }}">   

</tr>


<tr>
      <td class="border w-2/3 align-top border-t-0 border-black">
              <div class="ml-5 mb-3 mt-3">40. Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:

                <p class="ml-6 mt-2 mb-18">a. Are you a member of any indigenous group?</p>
                <p class="ml-6 mb-18 mt-20">b. Are you a person with disability?</p>
                <p class="ml-6 flex mt-16">c. Are you a solo parent?</p>
        </div>
        
      </td>
      
     <td class="border  px-2 border-black">
    <div class="flex h-full gap-20 mt-12">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_a" value="YES" Disabled @checked(($declaration->q40_a ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_a" value="NO" Disabled @checked(($declaration->q40_a ?? '') === 'NO')> NO
      </label>
    </div>

      <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q40_a_details" data-detail-for="q40_a" value="{{ $declaration->q40_a_details ?? '' }}">

     <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_b" value="YES" @checked(($declaration->q40_b ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_b" value="NO" Disabled @checked(($declaration->q40_b ?? '') === 'NO')> NO
      </label>
    </div>

      <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q40_b_details" data-detail-for="q40_b" value="{{ $declaration->q40_b_details ?? '' }}">

     <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_c" value="YES" Disabled @checked(($declaration->q40_c ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_c" value="NO" Disabled @checked(($declaration->q40_c ?? '') === 'NO')> NO
      </label>
    </div>
  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" name="q40_c_details" Disabled data-detail-for="q40_c" value="{{ $declaration->q40_c_details ?? '' }}">
</td>
</tr>

    </table> 

    <table class="w-full h-full border-l-2 border-b-0 border-black font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="border-l-3 border border-t-2 border-r-2 border-b-3 border-black" colspan="3">
          <span class="ml-2">41. REFERENCES </span><span class="font-semibold">(Person not related by consanguinity or affinity to applicant / appointee)</span>
        </td>
        <td class="flex-1 w-1/4 align-top text-center border-l-0 border-r-2 border-t-2 border-black" rowspan="11">
  <div class="mt-12 flex flex-col items-center">

    <!-- PASSPORT PHOTO -->
    <label class="cursor-pointer">
      <div class="border-2 border-black w-[3.5cm] h-[4.5cm] flex items-center justify-center text-xs italic text-center relative overflow-hidden">
        <img id="photoPreview" class="absolute inset-0 w-full h-full object-cover hidden" />
        <div id="photoPlaceholder">
          Passport-sized unfiltered<br>
          picture taken within<br>
          the last 6 months<br>
          4.5 cm × 3.5 cm
        </div>
      </div>
      @if(empty($pdfMode))
      <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(event)" required>
      @endif
    </label>

    <div class="mt-2 text-xs">PHOTO</div>

    <!-- THUMB MARK -->
    <label class="cursor-pointer mt-6">
      <div class="mt-20 border-2 border-black w-[4.5cm] h-[4.5cm] flex items-center justify-center text-xs italic text-center relative overflow-hidden">
        <img id="thumbPreview" class="absolute inset-0 w-full h-full object-cover hidden" />
        <div id="thumbPlaceholder" class="mt-auto border-black border-t border-l-0 border-b-0 border-r-0 w-full">
          Right Thumbmark
        </div>
      </div>
      @if(empty($pdfMode))
      <input type="file" name="thumbmark" accept="image/*" class="hidden" onchange="previewThumb(event)" required>
      @endif
    </label>
  </div>
</td>
      </tr>
      <tr class="border border-r-0 border-black">
        <th class="border font-light w-24 border-l-3 border-black">NAME</th>
        <th class="border font-light border-black">OFFICE / RESIDENTIAL ADDRESS </th>
        <th class="border font-light w-52 border-r-2 border-black">CONTACT NO. AND / OR EMAIL</th>
      </tr>
      @php
        // Reindex to zero-based keys so array-style access works for all saved references
        $refRows = ($references ?? collect())->values();
        $maxRef = max(7, $refRows->count());
      @endphp
      @for ($i = 0; $i < $maxRef; $i++)
      @php $ref = $refRows[$i] ?? null; @endphp
      <tr class="border border-r-0 border-l-3 border-black align-top">
        <td class="border border-black align-top p-0 w-60 text-center" style="height:25px;">
          {{ $ref->name ?? '' }}
        </td>
        <td class="border border-black align-top p-0 text-center" style="height:25px;">
          {{ $ref->address ?? '' }}
        </td>
        <td class="border border-r-3 align-top p-0 border-r-2 border-black text-center" style="height:25px;">
          {{ $ref->contact ?? '' }}
        </td>
      </tr>
      @endfor
      <tr class="border justify-center">
        <td colspan="3" class="text-justify px-2 h-20 font-semibold border-t-2 border-black border-b-2 border-r-2">
          42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement pursuant to the provisions of pertinent laws, rules, and regulations of the Republic 
          <span class="px-4">of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein. I  agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.</span> 
        </td>
      </tr>
      <tr>
        <td class="pr-5 p-0 align-top w-[40%] border-l border-black border-r-0 flex-1">
        <table class="border-collapse text-xs border-2 ml-2 mt-2 h-[5.6cm] w-full">
  <!-- HEADER -->
  <tr>
    <td class="border px-2 py-1 font-semibold h-5 border-black" colspan="2">
      Government Issued ID (i.e. Passport, GSIS, SSS, PRC, Driver's License, etc.)<br>
      <span class="italic font-normal">
        PLEASE INDICATE ID Number and Date of Issuance
      </span>
    </td>
  </tr>

  <!-- ROW 1 -->
  <tr>
    <td class="border px-2 py-1 h-5 align-middle border-black">
      Government Issued ID:
    </td>
    <td class="border px-2 py-1 w-2/3 border-black">
      {{ $idInfo->gov_id ?? '' }}
    </td>
  </tr>

  <!-- ROW 2 -->
  <tr>
    <td class="border px-2 py-1 h-5 align-middle border-black">
      ID/License/Passport No.:
    </td>
    <td class="border px-2 py-1 border-black">
      {{ $idInfo->passport_licence_id?? '' }}
    </td>
  </tr>

  <!-- ROW 3 -->
  <tr>
    <td class="border px-2 py-1 h-5 align-middle border-black">
      Date/Place of Issuance:
    </td>
    <td class="border px-2 py-1 border-black">
      {{ $idInfo->date_place_issuance ?? '' }}
    </td>
  </tr>

</table>
        </td>
        <td class="p-0 align-top w-[35%] border-b-0 border-l-0 border-r-0 border-black" colspan="2">
          <table class="w-full border-collapse text-xs border-3 mt-2 border-2 mb-2">
            <tr>
              <td class="h-[3.06cm] border-black text-center align-middle italic text-red-600">
                (wet signature / e-signature / digital certificate)
              </td>
            </tr>
            <tr>
              <td class="border border-black text-center py-1">Signature (Sign inside the box)</td>
            </tr>
           <tr>
  <td>
    <div class="relative flex justify-center py-2">
      <div class="flex items-center space-x-1 relative">
        <input
          type="text"
          name="date4_month"
          maxlength="2"
          placeholder="MM"
          inputmode="numeric"
          class="text-center text-base bg-transparent border-none focus:outline-none"
        />
        <span class="text-base select-none">/</span>
        <input
          type="text"
          name="date4_day"
          maxlength="2"
          placeholder="DD"
          inputmode="numeric"
          class="text-center text-base bg-transparent border-none focus:outline-none"
        />
        <span class="text-base select-none">/</span>
        <input
          type="text"
          name="date4_year"
          maxlength="2"
          placeholder="YY"
          inputmode="numeric"
          class="text-center text-xl bg-transparent border-none focus:outline-none"
        />
      </div>
    </div>
  </td>
</tr>
            <tr>
              <td class="border border-black  text-center py-1">Date Accomplished</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table class="border-3 border-t-0 border-black w-full font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="p-2 text-center align-middle font-semibold text-sm">
          SUBSCRIBED AND SWORN to before me this _____________________________ , affiant exhibiting his/her validly issued government ID as indicated above.
        </td>
      </tr>
      <tr>
        <td class="p-2 align-top text-center">
          <table class="w-1/3 mx-auto h-full border-collapse text-xs border-3">
            <tr>
  <td class="border-black h-16 text-center align-middle italic text-red-600 relative">

    <!-- Placeholder / Text -->
    <div id="signaturePlaceholder">
      (wet signature / e-signature / digital certificate except for notary public)
    </div>

    <!-- File Input -->
    @if(empty($pdfMode))
    <input
      type="file"
      name="signature_file"
      accept=".jpg,.jpeg,.png,.pdf,.docx"
      class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
      onchange="handleSignaturePreview(event)"
      required
    />
    @endif

    <!-- Optional Preview -->
    <div id="signaturePreview" class="mt-1 text-xs text-gray-700"></div>

  </td>
</tr>
            <tr><td class="border-black border text-center py-2 font-semibold">Person Administering Oath</td></tr>
          </table>
        </td>
      </tr>
    </table>
   <div class="w-full" style="text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 4 of 5
</div>
<div style="page-break-before: always;"></div>
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

@php
  $remarkRows = ($remarks ?? collect())->values();
  $maxRemark = max(1, $remarkRows->count());
@endphp

@for ($i = 0; $i < $maxRemark; $i++)
@php $remark = $remarkRows[$i]->remarks ?? ''; @endphp
<tr>
<td class="border-2 h-20 border-black relative">
<textarea
id="remarks-prototype"
name="remarks[]"
class="border-none w-full h-full p-5 resize-none text-sm focus:outline-none"
style="min-height:350px; white-space:pre-wrap;"
placeholder="Sample: If applying to Supervising Administrative Officer

•\tDuration:  February 11, 2011 – present
•\tPosition:  Human Resource Management Officer III
•\tName of Office/Unit: Finance and Administrative Service
•\tImmediate Supervisor: Maria Estrada
•\t Name of Agency/Organization and Location: Department of Human Resources, Metro Manila

•\tList of Accomplishments and Contributions (if any)
 - Developed recruitment plan
 - Designed training program for retirees under EO 366
 
•\tSummary of Actual Duties
  - Responsible for the management of the recruitment and selection process and the coordination of training activities of the Department; provides assistance in the management of the Division’s programs and activities and performs other related functions.
"
>{{ $remark }}</textarea>
</td>
</tr>
@endfor

</tbody>
</table>

<!-- SIGNATURE -->
<div class="w-full flex justify-end mt-[3cm] pr-6">
<div class="w-[350px] text-center">
  <div class="border-b-2 border-black"></div>
  <div class="mt-1 text-sm">(Signature over Printed Name)</div>
</div>
</div>

<!-- DATE -->
<div class="w-full flex justify-end mt-[1cm] pr-6 font-['Arial_Narrow','Arial',sans-serif]">
<div class="w-[350px] text-center relative">

<div class="border-b-2 border-black w-full absolute bottom-6 left-0"></div>

<div class="flex justify-center space-x-1 relative">
@if(empty($pdfMode))
<input type="file" name="month" maxlength="2" placeholder="MM" class="w-12 text-center bg-transparent border-none text-base" style="display: none;">
@endif
<span class="mt-2">/</span>
@if(empty($pdfMode))
<input type="file" name="day" maxlength="2" placeholder="DD" class="w-12 text-center bg-transparent border-none" style="display: none;">
@endif
<span class="mt-2">/</span>
@if(empty($pdfMode))
<input type="file" name="year" maxlength="4" placeholder="YYYY" class="w-20 text-center bg-transparent border-none" style="display: none;">
@endif
<input type="text" name="month" maxlength="2" placeholder="MM" class="w-12 text-center bg-transparent border-none text-base">
<span class="mt-2">/</span>
<input type="text" name="day" maxlength="2" placeholder="DD" class="w-12 text-center bg-transparent border-none">
<span class="mt-2">/</span>
<input type="text" name="year" maxlength="4" placeholder="YYYY" class="w-20 text-center bg-transparent border-none">
</div>

<div class="text-sm">DATE</div>
</div>
</div>

<div class="mt-5 flex justify-end mr-2 text-sm font-['Arial_Narrow','Arial',sans-serif]">
CS FORM 212 (Revised 2025), Page 5 of 5
</div>
@if(!empty($pdfMode))
</body>
</html>
@endif
