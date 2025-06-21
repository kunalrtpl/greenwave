<?php $regions = array(); $subregionArr = array(); $userDeptRegions =array(); $selProductsArr = array(); $userProductsArr = array(); $selCustNameArr = array(); $selCustIdsArr = array();?>
@foreach($subRegions as $subRegion)
    <?php $regions[] = $subRegion['parent_region']['region'];
        $subregionArr[] = $subRegion['region'];
        $userDeptRegions[] = $subRegion['parent_region']['id'].'#'.$subRegion['id'];
    ?>
@endforeach

@if(isset($products))
    <?php $selProductsArr =  array_column($products, 'product_code'); ?>
    <?php $userProductsArr =  array_column($products, 'id'); ?>
@endif
@if(isset($customers))
    <?php $selCustNameArr =  array_column($customers, 'name'); ?>
    <?php $selCustIdsArr =  array_column($customers, 'id'); ?>
@endif
<?php $regions = array_unique($regions); ?>
<tr>
    <td>{{$departmentInfo['department']}}</td>
    <?php /*<td>{{$designationInfo['designation']}}</td>*/?>
    <td>{{$reportToInfo['name']}}</td>
    <?php /*<td>
        <?php echo implode(', ', $selProductsArr); ?>
    </td>
    <td>
        <?php echo implode(', ', $regions); ?>
    </td>*/?>
    <td>
        <?php echo  implode(', ', $subregionArr); ?>
    </td>
    <td>
        @if(!empty($subregionArr))
            <?php $getCities = \App\RegionCity::cities($subregionArr);?>
            {{$getCities}}
        @endif
    </td>
    <!-- <td>
        <?php echo  implode(', ', $selCustNameArr); ?> 
    </td> -->
    <td>
        <button type="button" class="btn btn-xs btn-danger removeRow" href="javascript:;">
            <i class="fa fa-times"></i>
        </button>
    </td>
    <?php 
        //$userDeptJson['department_id']  =  $designationInfo['department']['id'];
        //$userDeptJson['designation_id'] = $designationInfo['id'];
        $userDeptJson['department_id']  = $departmentInfo['id'];
        $userDeptJson['report_to']      = $reportToInfo['id'];
        $userDeptJson['dept_regions']   = $userDeptRegions;
        $userDeptJson['products']       = $userProductsArr;
        $userDeptJson['customer_ids']   = $selCustIdsArr;
    ?>
    <input type="hidden" name="user_depts[]" value="{{json_encode($userDeptJson)}}">
</tr>