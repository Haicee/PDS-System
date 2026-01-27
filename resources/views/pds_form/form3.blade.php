<x-app-layout>
<style>
  body { margin: 24px; }
  table {
    border-collapse: collapse;
    width: 100%;
 }
        td, th { padding: 4px; vertical-align: middle; }
        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
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
      <td class="border h-10"><textarea rows="1" placeholder="Organization"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="From"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="To"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Hours"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Position/Nature of Work"></textarea></td>
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

    @for ($i = 0; $i < 8; $i++)
     <tr>
      <td class="border h-10"><textarea rows="1" placeholder="Title of L&D / Training"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="From"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="To"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Hours"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Type of L&D"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Conducted/Sponsored By"></textarea></td>
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
      <td class="border h-10"><textarea rows="1" placeholder="Special Skills and Hobbies"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Non-Academic Distinctions/Recognition"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="Membership in Association/Organization"></textarea></td> 
      </tr>
    @endfor

    </table>

    <table class="border border-black w-full h-15">
        <colgroup>
          <col style="width: 30.3%;">
           <col style="width: 20%;">
            <col style="width: 19.7%;">
             <col style="width: 10%;">
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
    CS FORM 212 (Revised 2025), Page 3 of 4
    </div>
    <div class="flex justify-between mt-4">
        <a href="{{ route('pds.form2') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Previous Page</a>
        <a href="{{ route('pds.form4') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</a>
    </div>
    </div>
</x-app-layout>