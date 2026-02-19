@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body>
@endif
@if(empty($pdfMode))
<x-app-layout>
@endif
<form method="POST" action="{{ route('pds.submit') }}" class="w-full" enctype="multipart/form-data">
@csrf


<style>
textarea:focus { outline: none; box-shadow: none; }
[contenteditable]:focus { outline: none; margin: 0; padding: 0; }

@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<script>
// ===============================
// CHECK IF ALL REMARKS ARE FILLED
// ===============================
function checkRemarksFilled() {
  const submitBtn = document.getElementById('submit-pds-btn');
  const remarks = document.querySelectorAll('textarea[name="remarks[]"]');

  const hasEmpty = Array.from(remarks)
    .some(t => t.value.trim() === '');

  if (hasEmpty) {
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-50','cursor-not-allowed','pointer-events-none');
  } else {
    submitBtn.disabled = false;
    submitBtn.classList.remove('opacity-50','cursor-not-allowed','pointer-events-none');
  }
}

// ===============================
// ADD NEW TEXTAREA ROW
// ===============================
function addCell() {
  const tbody = document.getElementById('remarks-rows');
  const proto = document.getElementById('remarks-prototype');
  if (!tbody || !proto) return;

  const clone = proto.cloneNode(true);
  clone.id = '';
  clone.value = '';
  clone.addEventListener('input', checkRemarksFilled);

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
    checkRemarksFilled();
  };

  td.appendChild(btn);
  tr.appendChild(td);
  tbody.appendChild(tr);

  checkRemarksFilled();
}

// ===============================
// INITIAL LOAD
// ===============================
window.addEventListener('load', () => {
  document
    .querySelector('textarea[name="remarks[]"]')
    .addEventListener('input', checkRemarksFilled);

  checkRemarksFilled();
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
disabled
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
@if(empty($pdfMode))
<input type="file" name="day" maxlength="2" placeholder="DD" class="w-12 text-center bg-transparent border-none" style="display: none;">
@endif
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

@if(empty($pdfMode))
 <a href="{{ route('pdsreview.pdsreview4') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded shadow border border-gray-300 hover:bg-gray-300">Previous Page</a>
@endif

</div>
</form>
@if(empty($pdfMode))
</x-app-layout>
@endif
@if(!empty($pdfMode))
</body>
</html>
@endif
