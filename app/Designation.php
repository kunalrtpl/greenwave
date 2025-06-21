<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Designation;
use DB;
class Designation extends Model
{
    //

    public function department(){
        return $this->belongsto('App\Department');
    }

    public static function DesignationTree($deptid,$parent_id = 'ROOT', $sub_mark = '',$selected_parent_id='ROOT'){
        $items = Designation::where('parent_id',$parent_id)->where('department_id',$deptid)->get();
        $items = json_decode(json_encode($items),true);
        foreach ($items as $key => $item) {
            $selected = "";
            if($item['id']==$selected_parent_id){
                $selected = "selected";
            }
            echo '<option value="'.$item['id'].'" '.$selected.'>'.$sub_mark.$item['designation'].'</option>';
            Designation::DesignationTree($deptid,$item['id'], $sub_mark.'»»',$selected_parent_id);
        }
    }

    public static function getReportDesignations($designationid){
        $designationIds = DB::select("SELECT T2.id
                FROM (
                    SELECT
                        @r AS _id,
                        (SELECT @r := parent_id FROM designations WHERE id = _id) AS parent_id,
                        @l := @l + 1 AS lvl
                    FROM
                        (SELECT @r := $designationid, @l := 0) vars,
                        designations h
                    WHERE @r <> 0) T1
                JOIN designations T2
                ON T1._id = T2.id
                ORDER BY T1.lvl DESC");
        $designationIds = array_flatten(json_decode(json_encode($designationIds),true));
        array_pop($designationIds);
        return $designationIds;
    }
}
