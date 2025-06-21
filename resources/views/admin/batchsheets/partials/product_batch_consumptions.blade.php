@if($batchConsumptions->isEmpty())
    <tr>
        <td colspan="7" class="text-center">No records found</td>
    </tr>
@else
    @foreach($batchConsumptions as $index => $batch)
        @if($batch->batchsheet->status == "Ready for Dispatch")
            <?php $color = "green";  ?>
        @else
            <?php $color = "black";  ?>
        @endif
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-center">{{$batch->batchsheet->batch_no}}</td>
            <td>
                <span style="color: {{ $color }};">{{$batch->batchsheet->status}}
                </span>
                  <br>
                <small>({{date('d M Y', strtotime($batch->status_date)) }})</small>
            </td>
            <td class="text-center">
                <b style="color: {{ $color }};">{{ formatQty($batch->final_material_filled) }} kg <br></b>
                <small>
                    @if(isset($batch->final_net_fill_size)) 
                        ({{ formatQty($batch->final_net_fill_size) }} kg) 
                    @endif
                </small>
            </td>
            <td>
                @if($batch->batchsheet->status == "Ready for Dispatch")
                    <input name="isse_qtys[]" type="number" class="form-control issue-qty"
                        placeholder="Enter Issue Qty"
                        data-final-net-fill="{{ $batch->final_net_fill_size }}"
                        data-max-qty="{{ $batch->final_material_filled }}"
                        oninput="calculatePacks(this)">
                @endif
            </td>
            <td class="num-of-packs">0</td>
        </tr>
    @endforeach
    <!-- Total Row -->
    <tr>
        <td colspan="4" class="text-right"><strong>Total Issued Qty:</strong></td>
        <td id="total-issued-qty"><strong>0</strong> kg</td>
        <td></td>
    </tr>
        @endif
<script type="text/javascript">
    function calculatePacks(input) {
    let issuedQty = parseFloat(input.value) || 0;
    let finalNetFillSize = parseFloat(input.getAttribute("data-final-net-fill")) || 1;
    let maxQty = parseFloat(input.getAttribute("data-max-qty")) || 0;

    // Prevent user from entering more than max available qty
    if (issuedQty > maxQty) {
        alert(`Issued quantity cannot exceed ${maxQty} kg.`);
        input.value = maxQty;
        issuedQty = maxQty;
    }

    // Calculate packs
    let packs = issuedQty / finalNetFillSize;

    // Formatting function to remove .00 if unnecessary
    function formatValue(value) {
        return value % 1 === 0 ? value.toFixed(0) : value.toFixed(2);
    }

    // Find the corresponding "No. of Packs" cell and update it
    let packsCell = input.closest("tr").querySelector(".num-of-packs");
    packsCell.textContent = formatValue(packs);

    // Update total issued quantity
    updateTotalIssuedQty();
}

function updateTotalIssuedQty() {
    let totalIssuedQty = 0;
    document.querySelectorAll(".issue-qty").forEach(input => {
        totalIssuedQty += parseFloat(input.value) || 0;
    });

    // Formatting function to remove .00 if unnecessary
    function formatValue(value) {
        return value % 1 === 0 ? value.toFixed(0) : value.toFixed(2);
    }

    // Update the total issued qty row
    document.getElementById("total-issued-qty").innerHTML = `<strong>${formatValue(totalIssuedQty)} kg</strong>`;
}


</script>
