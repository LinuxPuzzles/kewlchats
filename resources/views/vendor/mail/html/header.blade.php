@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; font-size: 24px; font-weight: 800; letter-spacing: -0.02em; color: #1f2937; text-decoration: none;">
{{ $slot }}
</a>
</td>
</tr>
