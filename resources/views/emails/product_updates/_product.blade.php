{{--
   The product block shared by every product update email: which product,
   its new status, from when, and the admin's note.

   Expects: $product, $effectiveOn, $statusLabel, $statusColor, optional $remarks
--}}
<tr>
   <td class="section">
      <div class="section-label">Product Details</div>
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
            <td class="info-row-label">Status</td>
            <td class="info-row-value"><strong style="color:{{ $statusColor }};">{{ $statusLabel }}</strong></td>
         </tr>
         <tr>
            <td class="info-row-label">Effective From</td>
            <td class="info-row-value"><strong>{{ $effectiveOn }}</strong></td>
         </tr>
      </table>
   </td>
</tr>
