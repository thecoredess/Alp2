@props(['url'])
{{--
    Logo dirujuk sebagai cid:alp-logo dan dilekapkan oleh pendengar MessageSending
    (AppServiceProvider). Imej terlekap muncul walaupun penerima di luar rangkaian
    DBKL dan tidak boleh mencapai apps.dbkl.gov.my.
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<img src="cid:alp-logo" class="logo" alt="Logo DBKL">
<span class="header-title">{!! $slot !!}</span>
<span class="header-subtitle">Dewan Bandaraya Kuala Lumpur</span>
</a>
</td>
</tr>
