<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use App\PackingType;
use App\Label;
use App\Product;
use App\ProductStock;
use App\Services\ProductStockService;
class ProductInventory extends Model
{
    /** Finished product received ready for dispatch — the RFD batches. */
    const TYPE_RFD = 'RFDM';

    /** Sale return material. */
    const TYPE_SALE_RETURN = 'SRM';

    //
	public function osp_checklists(){
		return $this->hasMany('App\ProductChecklist','product_id','product_id')->with('checklist');
	}

	public function product(){
		return $this->belongsto('App\Product','product_id','id');
	}

	public function osp_history(){
		return $this->hasMany('App\ProductInventoryHistory','product_inventory_id','id');
	}

	public function osp_histories(){
		return $this->hasMany('App\ProductInventoryHistory','product_inventory_id','id');
	}

	public function addedby(){
		return $this->belongsTo('App\User','added_by','id')->select('id','name');
	}

	public function medias(){
		return $this->hasMany('App\ProductInventoryMedia','product_inventory_id','id');
	}

	public function ospinv_checklists(){
		return $this->hasMany('App\ProductInventoryChecklist','product_id','product_id')->with('checklist');
	}

    public static function createOSPInventory($data){
    	$srno ="";
    	if($data['type']=="SRM"){
    		$lastRMI = ProductInventory::where('type','SRM')->orderby('id','DESC')->select('serial_no')->first();
	    	if(isset($lastRMI->serial_no)){
	    		$explodeSRNO = explode('SRM-', $lastRMI->serial_no);
	    		$srno = $explodeSRNO[1] + 1;
	    		$srno = 'SRM-'.$srno;
	    	}else{
	    		$srno = 'SRM-1000';
	    	}
    	}
    	$createOSPinv = new ProductInventory;
    	$createOSPinv->type = $data['type'];
    	$createOSPinv->incoming_date = $data['incoming_date'];
		$createOSPinv->product_id = $data['product_id'];
		$createOSPinv->serial_no    = $srno;
		//$createOSPinv->no_of_packs    = $data['no_of_packs'];
		$createOSPinv->stock    = $data['stock'];
		$createOSPinv->remaining_stock    = $data['stock'];
		$createOSPinv->remarks  = $data['remarks'];
		$createOSPinv->supplier_batch_no = $data['supplier_batch_no'];
		//$createOSPinv->price    = $data['raw_material_price'];
		$createOSPinv->added_by = Auth::user()->id;
		$createOSPinv->status   = "Incoming Material";
		if($data['type']=="RFDM"){
            /*
             | Where this batch may be used. Add Incoming Material asks only
             | for a Finished Product entry, and posts a hidden 0 beside each
             | box so an unticked one still arrives. Anything that does not
             | send them at all keeps the column default, which is true.
             */
            if(array_key_exists('used_in_sample', $data)){
                $createOSPinv->used_in_sample = !empty($data['used_in_sample']) ? 1 : 0;
            }
            if(array_key_exists('used_in_dispatch_order', $data)){
                $createOSPinv->used_in_dispatch_order = !empty($data['used_in_dispatch_order']) ? 1 : 0;
            }

            /*
             | The packing / label section of the form is commented out for now
             | (resources/views/admin/inventories/append-material-details.blade.php).
             | Everything below still works exactly as before the moment those
             | inputs come back — until then the columns are simply saved empty.
             */
            $packingTypeId   = isset($data['final_packing_type']) ? $data['final_packing_type'] : null;
            $noOfPacks       = isset($data['final_no_of_packs']) ? $data['final_no_of_packs'] : 0;
            $netFillSize     = isset($data['final_net_fill_size']) ? $data['final_net_fill_size'] : 0;
            $packsConsumed   = isset($data['packs_consumed']) ? $data['packs_consumed'] : 0;
            $labelId         = isset($data['label_id']) ? $data['label_id'] : 0;
            $labelsConsumed  = isset($data['labels_consumed']) ? $data['labels_consumed'] : 0;

			$createOSPinv->packing_type_id = $packingTypeId;
            $createOSPinv->no_of_packs = $noOfPacks;
            $createOSPinv->net_fill_size = $netFillSize;
            $createOSPinv->material_fill = $noOfPacks * $netFillSize;
            $createOSPinv->packs_consumed = $packsConsumed;
            $createOSPinv->label_id = $labelId;
            $createOSPinv->labels_consumed = $labelsConsumed;
            if($packingTypeId && $packsConsumed >0){
                PackingType::where('id',$packingTypeId)->decrement('stock',$packsConsumed);
            }
            if($labelId && $labelsConsumed >0){
                Label::where('id',$labelId)->decrement('stock',$labelsConsumed);
            }
		}
		$createOSPinv->save();
		$ospinvid = $createOSPinv->id;
		if($data['type']=="SRM"){
			$historyData['product_inventory_id'] = $ospinvid;
			$historyData['status'] = 'Incoming Material';
			$historyData['remarks'] = $data['remarks'];
			ProductInventoryHistory::createHistory($historyData);
		}else{
			DB::table('products')->where('id',$data['product_id'])->increment('current_stock',$data['stock']);

			// New ledger-backed stock. products.current_stock above is left exactly
			// as it was; this is the table that will take over from it.
			ProductStockService::credit($data['product_id'], $data['stock'], [
				'bucket'       => ProductStock::BUCKET_INCOMING,
				'source'       => ProductStockService::SOURCE_INCOMING_MATERIAL,
				'reference'    => $createOSPinv,
				'reference_no' => $createOSPinv->serial_no,
				'batch_no'     => $createOSPinv->supplier_batch_no,
				'notes'        => isset($data['remarks']) ? $data['remarks'] : null,
			]);
		}
		return $ospinvid;
    }

    /* ───────────────────────── RFD batches ───────────────────────── */

    public function scopeRfd($query)
    {
        return $query->where('type', self::TYPE_RFD);
    }

    /** Batches that still have something left in them. */
    public function scopeWithStock($query)
    {
        return $query->where('remaining_stock', '>', 0);
    }

    public function getRemainingQtyAttribute()
    {
        return round((float) $this->remaining_stock, 3);
    }

    public static function qty($value)
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }
}
