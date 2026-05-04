{{-- VIII. OTHER INFORMATION --}}
<table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','Arial',sans-serif; border:4px solid black; border-top:0;" border="1">
    <colgroup>
        <col style="width:5.13%;">
        <col style="width:6.5%;">
        <col style="width:4.30%;">
    </colgroup>
    <tr>
        <th colspan="3"
            style="background:#8a8a8a; color:#fff;
                   font-style:italic; font-size:18px;
                   text-align:left; padding:6px;
                   border:4px solid black;
                   -webkit-print-color-adjust:exact;
                   print-color-adjust:exact; font-size:23px;">
            VIII. OTHER INFORMATION
        </th>
    </tr>
    <tr style="background:#e7e7e7; -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
        <th style="border:1px solid black;">SPECIAL SKILLS and HOBBIES</th>
        <th style="border:1px solid black;">NON-ACADEMIC DISTINCTIONS / RECOGNITION <br> <span>(Write in full)</span></th>
        <th style="border:1px solid black;">MEMBERSHIP IN ASSOCIATION / ORGANIZATION <br> <span>(Write in full)</span></th>
    </tr>
    @php
        $otherCollection = $other ?? ($otherInfo ?? collect());
        $skills = $otherCollection->where('category', 'skills')->pluck('description')->values(); // keep all rows, don't filter
        $recognition = $otherCollection->where('category', 'recognition')->pluck('description')->values(); // keep all rows, don't filter
        $assoc = $otherCollection->where('category', 'association')->pluck('description')->values(); // keep all rows, don't filter
        $maxOther = 7; // Fixed 10 rows for other information
    @endphp
    @for ($i = 0; $i < $maxOther; $i++)
    <tr>
        <td style="border:1px solid black; text-align:center; vertical-align:top;">{{ $skills[$i] ?? ' ' }}</td>
        <td style="border:1px solid black; text-align:center; vertical-align:top;">{{ $recognition[$i] ?? ' ' }}</td>
        <td style="border:1px solid black; text-align:center; vertical-align:top;">{{ $assoc[$i] ?? ' ' }}</td>
    </tr>
    @endfor
</table>
