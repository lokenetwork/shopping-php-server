<?php

class Map {

  function __construct(){
  }

  function get_distance($longitude1,$latitude1,$longitude2,$latitude2){
    //将角度转为狐度
    $radLat1=deg2rad($latitude1);//deg2rad()函数将角度转换为弧度
    $radLat2=deg2rad($latitude2);
    $radLng1=deg2rad($longitude1);
    $radLng2=deg2rad($longitude2);
    $a=$radLat1-$radLat2;
    $b=$radLng1-$radLng2;
    $distance = 2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
    $distance = round($distance);
    if( $distance > 1000 ){ ;
      $distance = sprintf("%.2f", ($distance/1000));
      if( $distance > 100 ){
        $distance_str = ">100km";
      }else{
        $distance_str = $distance.'km';
      }
    }else{
      $distance_str = ($distance).'m';
    }

    return $distance_str;
  }

  function get_nearby_shop($longitude,$latitude){
    //var_dump($longitude);
    //var_dump($latitude);
    $longitude = floatval($longitude);
    $latitude = floatval($latitude);

    $shop_table_name = Yaf_Application::app()->getConfig()->mysql->table_prefix."shop_info";
    $shop_sql = "SELECT shop_id,name,longitude,latitude,
        (POWER(MOD(ABS(longitude - {$longitude}),360),2) + POWER(ABS(latitude - {$latitude}),2)) AS distance
        FROM {$shop_table_name}
        ORDER BY distance LIMIT 100";
    //var_dump($shop_sql);
    $shop_info = [];
    $shop_info['list'] = r_db()->query($shop_sql)->fetchAll();
    $shop_info['all_shop_ids'] = [];
    foreach($shop_info['list'] as $item){
      $shop_info['all_shop_ids'][] = $item['shop_id'];
    }
    return($shop_info);

  }

}
?>