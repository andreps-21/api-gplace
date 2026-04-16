<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    protected $table = 'variations';

    protected $fillable = ['grid_id','abbreviation','variation','is_enabled', 'representation'];

    protected $guarded=[];


    public function grid()
    {
        return $this->belongsTo(Grid::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public static function createCodVariation($params)
    {
        $quantidade = strlen($params);

        switch($quantidade)
        {
            case 1:
                return '00'.$params;
                break;
            case 2:
                return '00'.$params;
                break;
            case 3:
                return '0'.$params;
                break;

            default:
              return $params;
              break;
        }
    }

    public static function validatorVariation($array1 = null, $array2 = null, $array3 = null, $array4 = null )
    {
        $conjArray=[];
        if($array1 != null) {array_push($conjArray,$array1);}
        if($array2 != null) {array_push($conjArray,$array2);}
        if($array3 != null) {array_push($conjArray,$array3);}
        if($array4 != null) {array_push($conjArray,$array4);}

        // fazer um array para receber todos os arrays dentro dele
        $tam = count($conjArray);
        $array = [];
            //pecorrer o primeiro array add o array2 em cada index
        if($tam == 1) { $array = self::mountOneVariation($array1); }else{ $array = self::mountVariation($array1,$array2,$array3,$array4,$tam);}

        return collect($array);
    }

    private static function mountOneVariation($array)
    {
        $newarray = [] ;
       foreach ($array as $key => $value)
       {
        array_push($newarray, [$key => $value]);
        }
        return collect($newarray);
    }


    private static function mountVariation($array1,$array2,$array3,$array4,$tam)
    {
        $array = [];
        foreach ($array1 as $item1) {
            if(!empty($array2)){
                //pecorrer o array2 add os index do array1 com os do array2 em cada index
                foreach ($array2 as $item2) {
                    if ($tam == 2) {
                    $newarray = [] ;
                    array_push($newarray,$item1);
                    array_push($newarray,$item2);
                    array_push($array,$newarray);
                    }
                    if(!empty($array3)){
                        //pecorrer o array3 add os index do array1 com os do array2 com os do array3  em cada index
                        foreach ($array3 as $item3)
                            {
                                if($tam ==3)
                                {
                                $newarray = [];
                                array_push($newarray,$item1);
                                array_push($newarray,$item2);
                                array_push($newarray,$item3);
                                array_push($array,$newarray);
                                }
                                if(!empty($array4)){
                                    //pecorrer o array4 add os index do array1 com os do array2 com os do array3 e array4 em cada index
                                foreach ($array4 as $item4)
                                {
                                    if($tam ==4)
                                    {
                                        $newarray = [];
                                        array_push($newarray,$item1);
                                        array_push($newarray,$item2);
                                        array_push($newarray,$item3);
                                        array_push($newarray,$item4);
                                        array_push($array,$newarray);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return collect($array);
    }

}
