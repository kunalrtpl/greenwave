{{--
   The product block shared by both discontinued emails: what was
   discontinued, from when, and the admin's note.
--}}
<tr>
   <td class="section">
      <div class="section-label">Discontinued Product</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Product</td>
            <td class="info-row-value"><strong>{{ $product->product_name }}</strong></td>
         </tr>
         @if(!empty($product->product_code))
         <tr>
            <td class="info-row-label">Product Code</td>
            <td class="info-row-value">{{ $product->product_code }}</td>
         </tr>
         @endif
         <tr>
            <td class="info-row-label">Effective From</td>
            <td class="info-row-value"><strong style="color:#c62828;">{{ $discontinuedOn }}</strong></td>
         </tr>
      </table>
   </td>
</tr>

@if(!empty($remarks))
<tr>
   <td class="section">
      <div class="note"><strong>Note from Greenwave:</strong><br>{!! nl2br(e($remarks)) !!}</div>
   </td>
</tr>
@endif
