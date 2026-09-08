<div class="letterhead-space" aria-hidden="true">
    <p class="head-gap">&nbsp;</p>
    <p class="head-gap">&nbsp;</p>
    <p class="head-gap">&nbsp;</p>
    <p class="head-gap">&nbsp;</p>
</div>

<div class="ref-block">
    <table class="ref-table">
        <tr>
            <td class="ref-label">Rujukan Kami</td>
            <td class="ref-colon">:</td>
            <td class="ref-value">{{ $referenceNo }}</td>
        </tr>
        <tr>
            <td class="ref-label">Tarikh</td>
            <td class="ref-colon">:</td>
            <td class="ref-value">{{ $letterDateMalay }}@if ($letterDateHijri !== '—')&nbsp;&nbsp;{{ $letterDateHijri }}@endif</td>
        </tr>
    </table>
</div>

<div class="recipient">
    <p class="name">{{ strtoupper($alpName) }}</p>
    <p class="address">{{ $alpAddress }}</p>
</div>

<p class="salutation">YAD Raja Dato'/YAD Dato' Setia/YM Datuk/YBhg. Dato' Sri/Datuk/Tuan/Puan,</p>

<p class="title"><strong>PEMAKLUMAN KEPUTUSAN PERMOHONAN SUMBANGAN AHLI LEMBAGA<br>PENASIHAT BANDARAYA KUALA LUMPUR BAGI PEMBANGUNAN<br>KOMUNITI</strong></p>

<p class="para">Dengan hormatnya saya merujuk kepada perkara di atas.</p>

@if ($approved)
    <p class="para"><span class="para-num">2.</span> Dimaklumkan bahawa permohonan sumbangan oleh <strong>{{ $recipientName }}</strong> telah <strong>DILULUSKAN</strong> oleh Dewan Bandaraya Kuala Lumpur.</p>
    <p class="para"><span class="para-num">3.</span> Penyaluran sumbangan akan dilaksanakan oleh Jabatan Kewangan dalam tempoh 14 hari bekerja. Status penyaluran boleh disemak melalui sistem <a href="{{ $dbayarUrl }}">{{ $dbayarUrl }}</a> menggunakan nombor pembekar yang dipaparkan dalam sistem.</p>
    <p class="para"><span class="para-num">4.</span> Pihak persatuan/pertubuhan juga hendaklah mengemukakan laporan penggunaan sumbangan kepada YAD Raja Dato'/YAD Dato' Setia/YM Datuk/YBhg. Dato' Sri/Datuk/Tuan/Puan dalam tempoh 30 hari dari tarikh program dilaksanakan mengikut format yang ditetapkan.</p>
    <p class="para"><span class="para-num">5.</span> Untuk sebarang pertanyaan lanjut, pihak YAD Raja Dato'/YAD Dato' Setia/YM Datuk/YBhg. Dato' Sri/Datuk/Tuan/Puan boleh menghubungi urus setia di talian {{ $contactPhone }}.</p>
@else
    <p class="para"><span class="para-num">2.</span> Dimaklumkan bahawa permohonan sumbangan oleh <strong>{{ $recipientName }}</strong> <strong>TIDAK DILULUSKAN</strong> oleh Dewan Bandaraya Kuala Lumpur.</p>
    <p class="para"><span class="para-num">3.</span> Keputusan ini adalah berdasarkan hasil semakan permohonan dan pematuhan terhadap kriteria yang telah ditetapkan.</p>
    <p class="para"><span class="para-num">4.</span> Untuk sebarang pertanyaan lanjut, pihak YAD Raja Dato'/YAD Dato' Setia/YM Datuk/YBhg. Dato' Sri/Datuk/Tuan/Puan boleh menghubungi urus setia di talian {{ $contactPhone }}.</p>
@endif

<p class="para">Sekian, terima kasih.</p>

<div class="motto">
    <p>“MALAYSIA MADANI”</p>
    <p>“BERKHIDMAT UNTUK NEGARA”</p>
    <p>“BERSEDIA MENYUMBANG, BANDAR RAYA CEMERLANG”</p>
</div>

<div class="sign">
    <p class="sign-closing">Saya yang menjalankan amanah,</p>
    <p class="sign-gap">&nbsp;</p>
    <p class="sign-gap">&nbsp;</p>
    <p class="sign-gap">&nbsp;</p>
    <p class="sign-name">({{ strtoupper($signatoryName) }})</p>
    <p>{{ $signatoryTitle }}</p>
    <p>{{ $signatoryOnBehalf }}</p>
</div>
