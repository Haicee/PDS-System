<table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','sans-serif'; border:4px solid black; border-top:0;">
    <colgroup>
        <col style="width:32.2%;">
        <col style="width:15.7%;">
        <col style="width:14.72%;">
        <col style="width:10.4%;">
    </colgroup>
    <tr>
        <td class="border h-12 text-center text-xl font-bold italic align-middle">
            SIGNATURE
        </td>
        <td class="border" colspan="2" style="height: 80px;">
            <div style="height:80px; width:100%; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                @if($signatureUrl)
                  <img src="{{ $signatureUrl }}" alt="Signature" style="max-height:70px; max-width:100%; object-fit:contain;">
                @endif
            </div>
        </td>
        <td class="border text-center text-xl font-bold align-middle" colspan="2">
            DATE
        </td>
@include('pdsreview.partials.date-format-helper')
        <td colspan="3" class="border h-24">
          <div class="h-full w-full flex items-center justify-center text-lg text-center date-large-text" style="font-size:40px !important;">
            {{ format_pds_date($declaration->date_accomplished) ?? '—' }}
          </div>
        </td>
    </tr>
</table>
<div class="text-base w-full keep-base" style="margin-top: 10px; text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 3 of 5
</div>
