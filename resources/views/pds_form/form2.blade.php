<x-app-layout>
    <style>
        body { margin: 24px; }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 4px; vertical-align: middle; }
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
    <th class="border text-center bg-[#e7e7e7]">FROM</th>
    <th class="border text-center bg-[#e7e7e7]">TO</th>
   </tr>

   @for ($i = 0; $i < 7; $i++)
      <tr>
        <td class="border h-10"><textarea rows="1" placeholder="Eligibility"></textarea></td>
        <td class="border h-10"><textarea rows="1" placeholder="Rating"></textarea></td>
        <td class="border h-10"><textarea rows="1" placeholder="Date"></textarea></td>
        <td class="border h-10"><textarea rows="1" placeholder="Place"></textarea></td>
        <td class="border h-10"><textarea rows="1" placeholder="License No."></textarea></td>
        <td class="border h-10"><textarea rows="1" placeholder="Validity"></textarea></td>
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
      <td class="border h-10"><textarea rows="1" placeholder="From"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="To"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Position Title"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Department/Agency/Office/Company"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Status"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Y/N"></textarea></td>
     </tr>
    @endfor
    <tr>
      <table class="border-black w-full h-15">
        <colgroup>
          <col style="width: 17%;">
           <col style="width: 20%;">
            <col style="width: 16.02%;">
             <col style="width: 15%;">
        </colgroup>
      <tr>
      <td class="text-center font-bold text-lg border">
          SIGNATURE
      </td>

       <td class="border" colspan="2">
      <div class="h-full w-full flex flex-col items-center justify-center p-2">
        <input type="file" name="signature_attachment_3" id="signature_attachment" accept="image/*,.pdf" class="text-sm">
      </div>
    </td>

      <td class="border text-center font-bold text-lg">
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
    CS FORM 212 (Revised 2025), Page 2 of 4
    </div>

    <div class="flex justify-between mt-4">
        <a href="{{ route('pds.form1') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Previous Page</a>
        <a href="{{ route('pds.form3') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</a>
    </div>
    </div>
</x-app-layout>