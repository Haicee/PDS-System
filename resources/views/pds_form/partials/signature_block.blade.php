<table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','sans-serif'; border:4px solid black; border-top:0;">
    <colgroup>
        <col style="width:32.2%;">
        <col style="width:15.7%;">
        <col style="width:14.72%;">
        <col style="width:10.4%;">
    </colgroup>
    <tr>
        <td class="border h-8 text-center align-middle" style="font-size:30px !important; font-weight:700 !important; font-style:italic !important;">
            SIGNATURE
        </td>
        <td class="border" colspan="2" style="height: 60px;">
            <div style="height:60px; width:100%; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                @if($signatureUrl)
                  <img src="{{ $signatureUrl }}" alt="Signature" style="max-height:55px; max-width:100%; object-fit:contain;">
                @endif
            </div>
        </td>
        <td class="border text-center align-middle" colspan="2" style="font-size:30px !important; font-weight:700 !important; font-style:italic !important;">
            DATE
        </td>
@include('pdsreview.partials.date-format-helper')
        <td colspan="3" class="border h-10">
          <div class="h-full w-full flex items-center justify-center text-center" style="font-size:25px !important; font-weight:400 !important;">
            {{ format_pds_date($declaration->date_accomplished) ?? '—' }}
          </div>
        </td>
    </tr>
</table>
