<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $appends = ['medias','certificates','product_gst'];

    protected $casts = [
        'additional_information' => 'array',
    ];
    
    public function pro_checklist(){
        return $this->hasMany('App\ProductChecklist','product_id');
    }

    public function qty_discounts(){
        return $this->hasMany('App\QtyDiscount','product_id');
    }

    public function packing_type(){
        return $this->belongsto('App\PackingType','packing_type_id','id');
    }

    public function latestProductPricing(){
        return $this->hasOne('App\ProductPricing','product_id','id')->OrderBy('price_date','DESC');
    }

    public function getProductGstAttribute(){
        return 18;
    }

    public function getMediasAttribute(){
        $technical_literature_url ="";
        $msds_url ="";
        $gots_certification_url ="";
        $zdhc_certification_url = "";
        if(!empty($this->technical_literature)){
            $technical_literature_url = url('images/ProductDocuments/'.$this->technical_literature);
        }
        if(!empty($this->msds)){
            $msds_url = url('images/ProductDocuments/'.$this->msds);
        }
        if(!empty($this->gots_certification)){
            $gots_certification_url = url('images/ProductDocuments/'.$this->gots_certification);
        }
        if(!empty($this->zdhc_certification)){
            $zdhc_certification_url = url('images/ProductDocuments/'.$this->zdhc_certification);
        }
        return array('technical_literature_url'=>$technical_literature_url,'msds_url'=>$msds_url,'gots_certification_url'=>$gots_certification_url,'zdhc_certification_url'=>$zdhc_certification_url,'gots_certificate_url'=>url('/GOTS-certificate.pdf'));
    }

    public function getCertificatesAttribute(){
        return [
                    [
                        'name' => 'GOTS Certificate',
                        'key' => 'gots',
                        'logo' => url('/certificates/gots.jpeg'),
                        'pdf' => url('/certificates/gots.pdf')
                    ],
                    [
                        'name' => 'ZDHC Certificate',
                        'key' => 'zdhc',
                        'logo' => url('/certificates/zdhc.jpeg'),
                        'pdf' => url('/certificates/zdhc.pdf')
                    ],
                    [
                        'name' => 'Oekotex Certificate',
                        'key' => 'oekotex',
                        'logo' => url('/certificates/oeko.jpeg'),
                        'pdf' => url('/certificates/oeko.pdf')
                    ]

                ];
    }
        
    public function weightages(){
        return $this->hasMany('App\ProductWeightage');
    }


    public function raw_materials(){
    	return $this->hasMany('App\ProductRawMaterial');
    }

    public function productpacking(){
    	return $this->belongsto('App\PackingSize','packing_size_id','id');
    }

    public function pricings(){
    	return $this->hasMany('App\ProductPricing');
    }

    public function latest_pro_pricing(){
        return $this->hasOne('App\ProductPricing','product_id','id')->orderby('price_date','DESC');
    }

    public function product_stages(){
        return $this->hasMany('App\ProductStage');
    }

    public function product_weightages(){
        return $this->hasMany('App\ProductWeightage');
    }

    public function customerDiscounts()
    {
        return $this->hasOne(\App\CustomerDiscount::class, 'product_id');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_products',
            'product_id',
            'user_id'
        );
    }
}
