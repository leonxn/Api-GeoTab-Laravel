use Geotab;

// FUNCION PARA OBTENER DEVICE CODIGO

 public function getDevice()
    {
      $api = new Geotab\API("USUARIO", "CLAVE", "SERVIDOR", "my.geotab.com");
      $api->authenticate();
      $vehi=VehiculoModelo::where('status_deleted','=','1')->get();
      foreach($vehi as  $obj)
          {
            $geoserial=$obj->geotab_serial;
            $idv=$obj->id;
     
            //Doing mehtod API call
            $parameters1 = [
              "credentials"  => $api->authenticate(),
              "typeName"     => "Device", 
              "search"      => [
                "serialNumber"=> $obj->geotab_serial 
              ]
          ];
          $api->call("Get", $parameters1, function ($devicess) use($idv){ 
              // dd(json_encode($devicess)); 
              foreach($devicess as  $key => $value)
              {
                $serial=$value['serialNumber'];
                $placa=$value['licensePlate'];
                $id=$value['id'];
    
                echo "Serial: ".$value['serialNumber']."<br>";
                echo "id: ".$value['id']."<br>";
    
                //echo $value['licensePlate']."<br>"; 
                if( $value['serialNumber'] <> '000-000-0000'){
                  $vehiculo = VehiculoModelo::findOrFail($idv);
                  $vehiculo->identificador_geotab = $id; 
                  $vehiculo->save();
                   //$this->searchCoordenada($id,$serial,$placa,$idv); 
                }      
              }
          }, function ($error) {
              var_dump($error);
          });
        }
        //End object API get
    }


// FUNCION OBTENER COORDENADAS
 public function searchCoordenada()
    { 
      $api = new Geotab\API("USUARIO", "CLAVE", "SERVIDOR", "my.geotab.com");
      $api->authenticate(); 

      $vehi=VehiculoModelo:where('status_deleted','=','1')->get();
      foreach($vehi as  $obj)
          {
            $geoserial=$obj->geotab_serial;
            $idv=$obj->identificador_geotab;
            $id=$obj->id;
    
         //Doing mehtod API call
         $parameters1 = [
          "credentials"  => $api->authenticate(),
          "typeName"     => "DeviceStatusInfo",
          "search"       => [
            "deviceSearch"   => [
              "id"   => $idv, 
          ]
        ]
      ]; 
      $api->call("Get", $parameters1, function ($statusInfos) use($api,$idv,$id) { 
       
        //$idd=Input::get('serial');
        $longitud=$statusInfos[0]['longitude'];
        $latitud=$statusInfos[0]['latitude'];
        $isdrive=$statusInfos[0]['isDriving'];   
        echo "longitud: ".$longitud."<br>";
        echo "latitud: ".$latitud."<br>";
        echo "Encendido: ".$isdrive."<br>";
      
       
     
        $api->call("GetAddresses",  
        [ "coordinates" => [ ["x" => $longitud, "y" => $latitud] ], 
          "movingAddresses" => true ], function ($resp_coord) use ($idv,$longitud,$latitud,$isdrive,$id){
          foreach($resp_coord as  $key => $value)
          { 
            $country=$value['country'];
            $city=$value['city'];
            $region=$value['region'];
            $direccion=$value['formattedAddress']; 
    
            echo "ID VEHICULO: ".$idv."<br>";  
            echo "ID : ".$id."<br>";   
     
            echo "Dirección: ".$direccion."<br>"; 
            echo "Region: ".$region."<br>"; 
            echo "Country: ".$country."<br>";  
            echo "city: ".$city."<br>";  
            echo "<hr>";
    
            $vehiculo = Vehiculo::findOrFail($id);
            $vehiculo->longitud = $longitud;
            $vehiculo->latitud = $latitud;
            $vehiculo->isDriving = $isdrive;
            $vehiculo->coordenadas = $latitud.','.$longitud;
            $vehiculo->ubicacion = $direccion;
            $vehiculo->region = $region;
            $vehiculo->city = $city;
            $vehiculo->country = $country; 
            $vehiculo->save(); 
    
          }
    
          //print_r($resp_coord);
        }, function ($error) {
            var_dump($error);
        });
    
      }, function ($error) {
          var_dump($error);
      });
    }
    }

// FUNCION PARA OBTENER UBICACIÓN

    public function updateUbicacion($serial)
    {
      $api = new Geotab\API("USUARIO", "CLAVE", "SERVIDOR", "my.geotab.com");
      $api->authenticate();
      $vehi=VehiculoModelo::where('geotab_serial','=',$serial)->get();
      foreach($vehi as  $obj)
      { 
          //Doing mehtod API call
          $parameters1 = [
            "credentials"  => $api->authenticate(), 
            "typeName"     => "GetAddresses",
            "coordinates" => ["x" => $obj->longitud, "y" => $obj->latitud],  
              "movingAddresses" => true
          
        ];
     
        $api->call("Get", $parameters1,  function ($device) {
          foreach($geo as  $key => $value)
          { 
            $country=$value['country'];
            $city=$value['city'];
            $region=$value['region'];
            $dirección=$value['formattedAddress']; 
      
            echo "Dirección: ".$dirección."<br>"; 
            echo "Region: ".$region."<br>"; 
            echo "Country: ".$country."<br>"; 
            echo "Encendido: ".$isdrive."<br>";  
          }
    
        }, function ($error) {
            var_dump($error);
        });
      }
    }
