@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body>
@else
<x-app-layout>
@endif
    <form method="POST" action="{{ route('pds.saveStep', 1) }}" enctype="multipart/form-data">
    @csrf
    @if (empty($pdfMode))
    <div class="max-w-6xl mx-auto p-4 flex justify-end gap-3">
      <a href="{{ route('pdsreview1.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">Preview PDF</a>
      <a href="{{ route('pds.pdf.download') }}" class="px-4 py-2 bg-slate-700 text-white rounded shadow border border-slate-800 hover:bg-slate-800">Download PDF</a>
    </div>
    @endif
   <style>
        /* Print-friendly, spreadsheet-like grid */
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 4px; vertical-align: middle; }
        /* Only apply borders where classes already exist */
        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        /* Keep header cells distinct and preserve colors for print */
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        /* Form controls styled as lined cells */
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 12px; height: 12px; }
    </style>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm" @if(!empty($pdfMode)) style="max-width:100%;" @endif>
  <!-- HEADER -->
  <header class="mb-4 flex items-start justify-between gap-4 w-full">
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
  <table class="align-middle w-full border border-black  table-fixed  font-['Arial_Narrow','sans-serif'] text-base">

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
            <div >
              {{ $personal->surname ?? '—' }}
            </div>
</td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7]  px-2 align-middle">
        2. FIRST NAME
      </td>
       <td class="border relative" colspan="2" style="height: 60px;">
            <div>
               {{ $personal->firstname ?? '—' }}
  </div>
</td>

      <td class="bg-[#e7e7e7] align-top border">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div>
           {{ $personal->name_extension ?? '—' }}
      </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-7 border-b-2" style="height: 60px;"> 
        MIDDLE NAME
      </td>
      <td colspan="3" class="border  border-black h-10 align-middle">
        <div>
          {{ $personal->middlename ?? '—' }}
        </div>
      </td>
    </tr>

    <!-- DATE OF BIRTH + CITIZENSHIP (match form1 layout) -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle" style="height: 60px;">
        3. DATE OF BIRTH
        <p class="text-xs font-normal ml-4">(dd/mm/yyyy)</p>
      </td>
      <td class="border h-10">
        <div class="py-2 text-lg">{{ $personal->date_of_birth ?? '—' }}</div>
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
            <label class="inline-flex items-center gap-2"><input type="checkbox" class="mt-1 mb-1" name="citizenship[]" value="filipino" {{ $personal->citizenship  == 'filipino' ? 'checked' : '' }} disabled> Filipino</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="dual_citizenship" {{ $personal->citizenship  == 'dual_citizenship' ? 'checked' : '' }} disabled> Dual Citizenship</label>
          </div>
          <div class="flex flex-wrap justify-center gap-6 ml-20">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="by_birth" {{ $personal->citizenship  == 'by_birth' ? 'checked' : '' }} disabled> by birth</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="by_naturalization" {{ $personal->citizenship  == 'by_naturalization' ? 'checked' : '' }} disabled> by naturalization</label>
          </div>
          <p class="py-3 flex justify-center align-middle">Pls. indicate country:</p>
          <div class="border mt-1 w-full text-center align-middle py-2 text-xl flex justify-center items-center" style="min-height: 38px; margin-bottom:10px;">
            {{ $personal->country ?? '—' }}
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
        <div class="py-2 text-lg">{{ $personal->place_of_birth ?? '—' }}</div>
      </td>
    </tr>

    <!-- SEX AT BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-2 border">
        5. SEX AT BIRTH
      </td>
      <td class="border px-2 text-base">
        <div class="flex items-center gap-6">
          <label class="inline-flex items-center gap-2">
            <input class="ml-10" type="checkbox" value="male" disabled {{ $personal->sex == 'male' ? 'checked' : '' }}>
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
      <td class="bg-[#e7e7e7] align-top py-5 px-2 border">6. CIVIL STATUS</td>
      <td class="border px-2 py-1 align-middle h-2">
    <div class="p-2">
      <div class="grid grid-cols-[100px_120px] gap-y-2 gap-x-4 text-base ">
        
       <label class="flex items-center gap-2">
    <input type="checkbox" name="civilstatus[]" value="single" class="w-3 h-3" disabled
         {{ $personal->civil_status == 'single' ? 'checked' : '' }}>
    Single
</label>

<label class="flex items-center gap-2">
    <input type="checkbox" name="civilstatus[]" value="married" disabled class="w-3 h-3"
         {{ $personal->civil_status == 'married' ? 'checked' : '' }}>
    Married
</label>

<label class="flex items-center gap-2">
    <input type="checkbox" name="civilstatus[]" value="widowed" disabled class="w-3 h-3"
         {{ $personal->civil_status == 'widowed' ? 'checked' : '' }}>
    Widowed
</label>

<label class="flex items-center gap-2">
    <input type="checkbox" name="civilstatus[]" value="separated" disabled class="w-3 h-3"
        {{ $personal->civil_status == 'separated' ? 'checked' : '' }}>
    Separated
</label>

<label class="flex items-center gap-2 col-span-2">
    <input type="checkbox" name="civilstatus[]" value="other/s" disabled class="w-3 h-3"
         {{ $personal->civil_status == 'Other/s' ? 'checked' : '' }}>
    Other/s:
</label>
      </div>
    </div>
  </td>
  <td rowspan="3" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7]" style="width:35%; border-right:1px solid #000;">
        <tr>
          <td class="px-2 py-1 align-top border-black">
            17. RESIDENTIAL ADDRESS
          </td>
        </tr>
        <tr class="h-10">
          <td class="text-center text-xl py-2">
            ZIP CODE
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white" style="width:65%; border-collapse:collapse;">

          <tr>
            <td class="h-auto align-top">
              <div class="grid grid-cols-2 mt-2 w-full text-center">
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->present_house_block_lot ?? '' }}</div>
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->present_street ?? '' }}</div>
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
<tr><td class="border-t border-black"></td></tr>
           <tr>
            <td class="h-auto align-top">
              <div class="grid grid-cols-2 mt-2 w-full text-center">
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->present_subdivision_village ?? '' }}</div>
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->present_barangay ?? '' }}</div>
              </div>
            </td>
          </tr>
          <tr class="h-2">
          <td class="border-t border-black/50 ">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>Subdivision/Village</p>
              <p>Baranggay</p>
            </div>
          </td>
        </tr>
        <tr><td class="border-t border-black"></td></tr>
          <tr>
            <td class="h-auto align-top">
              <div class="grid grid-cols-2 mt-2 w-full text-center">
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->present_city_municipality ?? '' }}</div>
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->present_province ?? '' }}</div>
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
        <tr>
          <td class="border-t border-black text-center py-2 text-base">
            {{ $address->present_zip_code ?? '' }}
          </td>
        </tr>
      </table>

    </div>
  </td>




    <tr>
      <td class="bg-[#e7e7e7] px-2 border font-['Arial_Narrow','Arial',sans-serif]">7. HEIGHT (m)</td>
      <td class="border px-2 h-10">
        {{ $personal->height ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">8. WEIGHT (kg)</td>
      <td class="border px-2 h-10">
        {{ $personal->weight ?? '—' }}
      </td>
    </tr>


      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8">9. BLOOD TYPE</td>
      <td class="border px-2 align-middle"> 
       {{ $personal->blood_type ?? '—' }}
      </td>


  <td rowspan="4" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7] border-b font-['Arial_Narrow','Arial',sans-serif] text-base" style="width:35%; border-right:1px solid #000;">
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
              <div class="grid grid-cols-2 mt-2 w-full text-center">
                  <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->permanent_house_block_lot ?? '' }}</div>
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->permanent_street ?? '' }}</div>
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
<tr><td class="border-t border-black"></td></tr>
           <tr>
            <td class="h-auto align-top">
              <div class="grid grid-cols-2 mt-2 w-full text-center">
                  <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->permanent_subdivision_village ?? '' }}</div>
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->permanent_barangay ?? '' }}</div>
              </div>
            </td>
          </tr>
          <tr class="h-2">
          <td class="border-t border-black/50 ">
            <div class="grid grid-cols-2 px-4 text-center">
              <p>Subdivision/Village</p>
              <p>Baranggay</p>
            </div>
          </td>
        </tr>
        <tr><td class="border-t border-black"></td></tr>
          <tr>
            <td class="h-auto align-top">
             <div class="grid grid-cols-2 mt-2 w-full text-center">
                  <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->permanent_city_municipality ?? '' }}</div>
                <div class="px-2 py-2 text-lg text-center whitespace-pre-wrap">{{ $address->permanent_province ?? '' }}</div>
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
        <tr>
          <td class="border-t border-black text-center py-2 text-base h-10">
          </td>
        </tr>
      </table>

    </div>
  </td>    

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">10. UMID ID NO.</td>
      <td class="border px-2 h-10">
        {{ $personal->umid_no ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">11. PAG-IBIG ID NO.</td>
      <td class="border px-2 h-10">
        {{ $personal->pagibig_no ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">12. PHILHEALTH NO.</td>
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
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif]  px-2 border h-8">13. PhilSys Number (PSN):</td>
      <td class="border px-2 h-10">
        {{ $personal->philsys_no ?? '—' }}
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
          <table class="bg-white border-l border-r border-t-0">
              <tr class="h-5">
                <td class="border-black border-l">
                  <div class="flex">
                 {{ $contact->telephone_no ?? '—' }}
                  </div>
                </td>
              </tr>
          </table>
        </div>
      </td>


    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">14. TIN ID</td>
      <td class="border px-2 h-10">{{ $personal->tin_no ?? '—' }}</td>

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
      <table class="bg-white border-l border-r border-t-0">
          <tr class="h-5">
            <td class="border-black border-l">
              <div class="flex">
           {{ $contact->mobile_no ?? '—' }}
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">15. AGENCY EMPLOYEE ID</td>
      <td class="border px-2 h-10">{{ $personal->agency_employee_no ?? '—' }}</td>

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
      <table class="bg-white border-l border-r border-t-0">
          <tr class="h-2">
            <td class="border-black border-l">
              <div>
             {{ $contact->email_address ?? '—' }}
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
        <div>
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
      <td class="bg-[#e7e7e7]  px-8 "> 
        MIDDLE NAME
      </td>
      <td colspan="3"
          class="border border-b-2 h-10">
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
      <td class="bg-[#e7e7e7]  px-2 border">EMPLOYER/BUSINESS NAME</td>
      
      <td colspan="3"
          class="border h-10">
          <div>
          {{ $spouse->employer ?? '—' }}
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
      <td class="bg-[#e7e7e7]  px-2 border">BUSINESS ADDRESS</td>
      
      <td colspan="3"
          class="border h-10">
          <div>
          {{ $spouse->business_address ?? '—' }}
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
      <td class="bg-[#e7e7e7]  px-2 border">TELEPHONE NO.</td>
      
       <td colspan="3"
          class="border h-10">
          <div>
          {{ $spouse->telephone_no ?? '—' }}
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
       <div>
        {{ $father->name_extension ?? '—' }}
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
         {{ $childDobs[$childIndex] ?? ' ' }}
       </div>
      </td>
      @php $childIndex++; @endphp
    </tr>



    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border-t-2" colspan="4">
        25. MOTHER'S MAIDEN NAME
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
          return ($rec && isset($rec->$field)) ? $rec->$field : '';
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
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('elementary','school_name') }}
          </div>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduCourse('elementary') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('elementary','from') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('elementary','to') }}
      </td>

      <td
            class="border h-10">
            <div class="h-full w-full px-2 text-center">
              {{ $eduVal('elementary','highest_level') }}
        </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('elementary','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduHonors('elementary') }}
      </td>
  </tr>


   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">SECONDARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('secondary','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduCourse('secondary') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('secondary','from') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('secondary','to') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('secondary','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('secondary','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduHonors('secondary') }}
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">VOCATIONAL / TRADE COURSE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('vocational','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduCourse('vocational') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('vocational','from') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('vocational','to') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('vocational','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('vocational','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduHonors('vocational') }}
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">COLLEGE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('college','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduCourse('college') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('college','from') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('college','to') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('college','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('college','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduHonors('college') }}
      </td>
  </tr>

  <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">GRADUATE STUDIES</td>

    <!-- EDITABLE CELL PATTERN -->
   <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('graduate_studies','school_name') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduCourse('graduate_studies') }}
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('graduate_studies','from') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('graduate_studies','to') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('graduate_studies','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduVal('graduate_studies','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full px-2 text-center">
            {{ $eduHonors('graduate_studies') }}
      </td>
  </tr>

 

  <tr>
    <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

    <td class="border" colspan="2">
      <div class="h-full w-full flex flex-col items-center justify-center p-2 space-y-2">
        @php $signatureUrl = !empty($signaturePath) ? asset('storage/'.$signaturePath) : null; @endphp
        @if($signatureUrl)
          <img src="{{ $signatureUrl }}" alt="Signature" class="max-h-28 object-contain">
        @else
          <div class="text-xs text-gray-600">No signature on file</div>
        @endif
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
        <a href="{{ route('pdsreview.pdsreview2') }}" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</a>
    </div>
    </div>
    </form>
@if(empty($pdfMode))
@endif
@if(!empty($pdfMode))
</body>
</html>
@else
</x-app-layout>
@endif
