{{--
   The closing "what this means for you" box.
   Expects: $steps (list of strings), $tone ('warn' | 'good')
--}}
<tr>
   <td class="section section-last">
      @if(($tone ?? 'warn') === 'good')
      <div class="good" style="background:#f1f8e9; border:1px solid #c5e1a5; border-left:5px solid #43a047; border-radius:10px; padding:16px 18px;">
      @else
      <div class="notice" style="background:#fff4f2; border:1px solid #ffccbc; border-left:5px solid #e64a19; border-radius:10px; padding:16px 18px;">
      @endif
         <div class="notice-title">What this means for you</div>
         <table role="presentation" class="steps" width="100%">
            @foreach($steps as $i => $text)
            <tr><td class="step-dot">{{ $i + 1 }}.</td><td>{{ $text }}</td></tr>
            @endforeach
         </table>
      </div>
   </td>
</tr>
