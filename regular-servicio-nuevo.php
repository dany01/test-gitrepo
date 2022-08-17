<?php
    
	// Definimos el timezone
	date_default_timezone_set('America/Mexico_City');
	// Incluimos el header
	include('header.php');
	// Incluimos la configuracion
	include('config.php');
	// Declaramos las variables
	$mensaje = array();
	// Validamos si vamos a guardar
	if (isset($_POST['accion']) == 'guardar') {
    // Ceclaramos por defecto el id del usuario
		$cliente_id = '';
    // Declaramos por defecto el id de la factura
		$facturacion_id = 0;
		// Creamos los datos para el servicio
		$telefono = $_POST['telefono'];
		$operador_id = $_POST['operador'];
		$grua_id = $_POST['grua'];
		$tipo_cliente = 'proveedor';
		$proveedor_id = $provid;
		$modelo = $_POST['modelo'];
		$marca = $_POST['marca'];
		$anio = $_POST['anio'];
		$transmision = $_POST['transmision'];
		$falla = $_POST['falla'];
		$otros = $_POST['otros'];
  	if ($falla == 'Otro') {$falla = $otros;}
		$ubicacion = json_encode(array('direccion' => $_POST['origen'], 'codigo_postal' => $_POST['codigo_postal_origen'], 'place_id' => $_POST['place_id_origen'], 'latlng' => array('lat' => (float)$_POST['origenlat'], 'lng' => (float)$_POST['origenlng'])));
		$destino = json_encode(array('direccion' => $_POST['destino'], 'codigo_postal' => $_POST['codigo_postal_destino'], 'place_id' => $_POST['place_id_destino'], 'latlng' => array('lat' => (float)$_POST['destinolat'], 'lng' => (float)$_POST['destinolng'])));
		$estatus = 'aceptado';
		$costo = $_POST['costo'];
		$metodopago = json_encode(array('tipo' => $_POST['metodo_pago'], 'id' => ''));
		$color = strtoupper($_POST['color']);
		$placas = strtoupper($_POST['placas']);
		$tipo = $_POST['tipo'];
		$tipovehiculo = $_POST['tipovehiculo'];
		$numeroeconomico = $_POST['numeroeconomico'];
		$maniobras = $_POST['maniobras'];
		$detalles_maniobras = $_POST['detalles_maniobras'];
		if (!$maniobras) {$maniobras = 0;}
		$peso = $_POST['peso'];
		$estado_sol = strtoupper($_POST['estado_sol']);
		$dia = $_POST['dia'];
		$hora = $_POST['hora'];
		$fecha = date('Y-m-d H:i:s');
		$costomaniobras = (empty($_POST['costomaniobras'])) ? 0 : $_POST['costomaniobras'];
		$tarifario = $_POST['tarifario'];
  	    $setType = array(); 
		$distancia = $_POST['distancia'];
        $totalkm = $_POST['totalkm'];
        $tiempo = $_POST['tiempo'];
        $tiempo_valor = $_POST['tiempo_valor'];
        $kilometrosExcedidos = $_POST['kilometrosExcedidos'];
        $lapso = json_encode(array('texto' => $tiempo, 'valor' => $tiempo_valor));
		$tipo_servicio = 'Regular';
        $clave_servicio = $_POST['clave_servicio'];
		// Creamos las caracteristicas del tipo
		if ($tipo == 'Auxilio vial') {
			// Creamos el arreglo de auxilio vial
			$setType = array('tipo' => 'Auxilio vial', 'detalles' => array('tipo_auxilio_vial' => $_POST['tipo_auxilio_vial']));
		}else if ($tipo == 'Programado'){
			// Creamos el arreglo de programado
			$setType = array('tipo' => 'Programado', 'detalles' => array('dia' => $_POST['dia'], 'hora' => $_POST['hora']));
		}else if ($tipo == 'Suministro de combustible'){
			// Creamos el arreglo de Suministro de combustible
			$setType = array('tipo' => 'Suministro de combustible', 'detalles' => array('cantidad' => $_POST['cantidad_litro'], 'costo' => $_POST['costo_gas']));
		}
		// Generamos el tipo de servico
        $setType = json_encode($setType);
        // Generamos el numero de serie
        $num_serie = $_POST['num_serie'];
        // Sentencia SQL
        
        // Obtenemos el idConsecutivo del operador para generar el idfolio de la secuencia de servicios de ese operador
        $consecutivo = "SELECT idConsecutivo FROM `usuarios` WHERE ID = $operador_id";
        // Generamos la consulta
        $consecutivo_query = $conn->query($consecutivo);
        
        if (mysqli_num_rows($consecutivo_query) != 0) {// Si existe el registro del operador
            
            while ($row = $consecutivo_query->fetch_array(MYSQLI_ASSOC)) {
                
                // Regeneramos las opciones
                $idConsecutivo = $row['idConsecutivo'];
                // Se crea el folio del operador usando el idConsecutivo de servicos al que va
                $idFolioOperador = $idConsecutivo + 1;
                
                // Actualizamos el idConsecutivo del operador al actual generador
                $conn->query("UPDATE `usuarios` SET idConsecutivo = $idFolioOperador WHERE ID = $operador_id");
            }
            
        } else {// Si falla al consultar el idConsecutivo del operador
            
            echo '<script> 
                    alert("Algo fallo Al obtener el consecutivo del operador, por favor intenta de nuevo."); 
                    location.reload();
                 </script>';
        }
        
        $servicio_nuevo = $conn->prepare("INSERT INTO `servicios`(`IDCLI`, `IDCHF`, `IDGRU`, `idrfc`, `tipocliente`, `proveedor_id`, `modelo`, `marca`, `anio`, `transmision`, `desperfecto`,`origen`,`destino`,`estado`,`estatus`,`costo`,`metodopago`, `distancia`,`totalkm`, `tiempo`, `color`,`placas`,`tipo`,`tipo_detalles`,`dia`,`hora`,`fecha_solicitud`,`tipovehiculo`,`numeroeconomico`,`peso`,`maniobras`,`detalles_maniobras`,`costomaniobras`,`tarifa`,`tipo_servicio`,`telefono`,`clave_servicio`, `num_serie`, `kilometrosExcedidos`, `idFolioOperador`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        // Generamos los parametros
        $servicio_nuevo->bind_param('iiiisississsssssssssssssssssssssssssssi', $cliente_id, $operador_id, $grua_id, $facturacion_id, $tipo_cliente, $proveedor_id, $modelo, $marca, $anio, $transmision, $falla, $ubicacion, $destino, $estado_sol, $estatus, $costo, $metodopago, $distancia, $totalkm, $lapso, $color, $placas, $tipo, $setType, $dia, $hora, $fecha, $tipovehiculo, $numeroeconomico, $peso, $maniobras, $detalles_maniobras, $costomaniobras, $tarifario, $tipo_servicio, $telefono, $clave_servicio, $num_serie, $kilometrosExcedidos, $idFolioOperador);
        // Ejecutamos la consulta y validamos la consulta de servicio nuevo
        if ($servicio_nuevo->execute()) {
              // Generamos el id del servicio
              $servicio_id = $servicio_nuevo->insert_id;
              // Authenticacion
              $auth_basic = base64_encode("ariana.elizalde@towlike.com:vD9xKF23mNVb");
              // Servicio
              $curl = curl_init();
              // Conexion de servicio
              curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.labsmobile.com/json/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => '{"message":"Tu servicio de grúa va en camino ¿Requieres factura? Ingresa al enlace https://towlike.com/app/facturacion.php?service='.$servicio_id.'&number='.$telefono.'&provider='.$proveedor_id.'", "tpoa":"Towlike","recipient":[{"msisdn":"52'.$telefono.'"}]}',
                CURLOPT_HTTPHEADER => array(
                  "Authorization: Basic ".$auth_basic,
                  "Cache-Control: no-cache",
                  "Content-Type: application/json"
                ),
              ));
              // Menejamos la respuesta
              // $response = curl_exec($curl);
              // Generamos la revision
              // $err = curl_error($curl);
              // Cerramos la conexion
              curl_close($curl);
                    // Creamos un mensaje satisfactorio
                    $mensaje = array(
                        'error' => false,
                        'mensaje' => 'El servicio se generó satisfactoriamente.' 
                    );
        } else {
                // Creamos un mensaje de error
                $mensaje = array(
                    'error' => true,
                    'mensaje' => 'El servicio no se generó, intentelo mas tarde.' 
                );
        }
	}
	// Obtenemos el UUID del socio
	$contrato_sql = "SELECT * FROM contratos WHERE id_socio = ".$provid;
	// Generamos la consulta
	$contratos_query = $conn->query($contrato_sql);
	// Almacenamos los datos del socio
	$contrato = $contratos_query->fetch_assoc();
    // Consultamos la base del cliente
    $base_sql = "SELECT * FROM region WHERE id_usr = ".$provid;
	// Generamos la consulta
	$base_query = $conn->query($base_sql);
	// Almacenamos los datos del socio
	$base = $base_query->fetch_assoc();
    // Almacenmaos
    $rangos = json_decode($base['rangos'], true);
    
    $ina = ($rangos) ? '' : 'disabled'; 

?>
<style>
    .success {
        display: block;
        padding: 8px;
        border-radius: 6px;
        background: #4BB543;
        color: #fff;
    }

    .error {
        display: block;
        padding: 8px;
        border-radius: 6px;
        background: #b0182e;
        color: #fff;
    }

    .noti-ranges {
        width: 100%;
        background: gold;
        margin-bottom: 10px;
        border-radius: 5px;
        padding: 15px;
        color: black;
        font-weight: 600;
    }

</style>
<div class="page-content-wrapper">
    <div class="page-content-wrapper-inner">

        <?php
            // Validamos si contiene rangos
            if(!$rangos){
                echo'<div class="noti-ranges">
                        Para realizar un servicio es necesario tener un rango de mapas
                    </div>';
            }
        ?>



        <div class="viewport-header" style="padding: 10px; border-radius:5px; margin-block:10px;">
            <h3>Crear servicio regular</h3>
        </div>


        <?php if (!empty($mensaje)): ?>
        <div class="col-lg-12">
            <div class="grid">
                <p class="grid-header"></p>
                <div class="grid-body">
                    <div class="item-wrapper">

                        <?php
								// Validamos el tipo de error
								$tipo_error = ($mensaje['error']) ? 'error' : 'success';
								// Mostramos el mensaje de error
								echo '<span class="'.$tipo_error.'">'.$mensaje['mensaje'].'</span>';
						?>
                    </div>
                </div>
            </div>
        </div>
        <?php else:?>
        <div class="row">
            <div class="col-lg-12">
                <div class="grid">
                    <p class="grid-header">Solicitud de servicio regular</p>
                    <div class="grid-body">
                        <div class="item-wrapper">
                            <form action="regular-servicio-nuevo.php" method="post">

                                <div class="row">

                                    <div class="col-lg-6">
                                        <label for="telefono">Teléfono celular:</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <img src="img/mexico.svg" style="width: 25px;">
                                                    <span style="font-size:12px;margin-left:5px;">+52</span>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control" id="telefono" placeholder="Teléfono" name="telefono" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" required>
                                        </div>
                                    </div>

                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group showcase_row_area">
                                            <label for="costo">
                                                Origen:
                                                <i class="mdi mdi-map-marker" style="font-size: 22px; color: red; position: absolute; top: -7px;"></i>
                                                <span style="font-size: 16px;color: red;position: absolute;top: -2px;margin-left: 23px;font-weight: 600;">B</span>
                                            </label>
                                            <input type="text" class="form-control" id="origen" placeholder="Origen" name="origen" style="text-transform:capitalize" required>
                                        </div>
                                        <div class="row">
                                            <div class="form-group showcase_row_area col-lg-6">
                                                <label for="costo">Latitud:</label>
                                                <input type="text" class="form-control" name="origenlat" id="origenlat">
                                            </div>
                                            <div class="form-group showcase_row_area col-lg-6">
                                                <label for="costo">Longitud:</label>
                                                <input type="text" class="form-control" name="origenlng" id="origenlng">
                                                <input type="hidden" name="codigo_postal_origen" id="codigo_postal_origen">
                                                <input type="hidden" name="place_id_origen" id="place_id_origen">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group showcase_row_area">
                                            <label for="costo">
                                                Destino:
                                                <i class="mdi mdi-map-marker" style="font-size: 22px; color: red; position: absolute; top: -7px;"></i>
                                                <span style="font-size: 16px;color: red;position: absolute;top: -2px;margin-left: 23px;font-weight: 600;">C</span>
                                            </label>
                                            <input type="text" class="form-control" id="destino" placeholder="Destino" style="text-transform:capitalize" name="destino" required>
                                        </div>
                                        <div class="row">
                                            <div class="form-group showcase_row_area col-lg-6">
                                                <label for="costo">Latitud:</label>
                                                <input type="text" class="form-control" name="destinolat" id="destinolat">
                                            </div>
                                            <div class="form-group showcase_row_area col-lg-6">
                                                <label for="costo">Longitud:</label>
                                                <input type="text" class="form-control" name="destinolng" id="destinolng">
                                                <input type="hidden" name="codigo_postal_destino" id="codigo_postal_destino">
                                                <input type="hidden" name="place_id_destino" id="place_id_destino">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div id="map"></div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="form-group showcase_row_area col-lg-6">
                                        <label for="costo">Tipo de servicio:</label>
                                        <select class="form-control custom-select" name="tipo" id="tipo" required>
                                            <option value="Regular">Regular</option>
                                            <option value="Programado">Programado</option>
                                            <option value="Suministro de combustible">Suministro de combustible</option>
                                            <option value="Auxilio vial">Auxilio vial</option>
                                        </select>
                                        <input type="hidden" id="clave_servicio" name="clave_servicio" value="78101803">
                                    </div>

                                    <div id="show_auxilio_vial" class="col-lg-6">
                                        <div class="row">
                                            <div class="form-group col-lg-12">
                                                <label for="tipo_auxilio_vial">Tipo de auxilio vial:</label>
                                                <select class="form-control custom-select" name="tipo_auxilio_vial" id="tipo_auxilio_vial">
                                                    <option value="Paso de corriente">Paso de corriente</option>
                                                    <option value="Cambio de llanta">Cambio de llanta</option>
                                                    <option value="Otros">Otros</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="show_suministro" class="col-lg-6">
                                        <div class="row">
                                            <div class="form-group col-lg-6">
                                                <label for="cantidad_litro">Cantidad por litro:</label>
                                                <input type="text" class="form-control" placeholder="Cantidad" id="cantidad_litro" name="cantidad_litro">
                                            </div>
                                            <div class="form-group col-lg-6">
                                                <label for="costo_gas">Costo por litro:</label>
                                                <input type="text" class="form-control" id="costo_gas" placeholder="Costo" name="costo_gas">
                                            </div>
                                        </div>
                                    </div>

                                    <div id="show_programado" class="col-lg-6">
                                        <div class="row">
                                            <div class="form-group col-lg-6">
                                                <label for="costo">Fecha:</label>
                                                <input type="text" class="form-control setdate" style="cursor: pointer;" id="dia" placeholder="dia" name="dia">
                                            </div>
                                            <div class="form-group col-lg-6">
                                                <label for="costo">Hora:</label>
                                                <input type="text" class="form-control setime" style="cursor: pointer;" id="hora" placeholder="hora" name="hora">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <hr>

                                <div class="row">
                                    <div class="form-group showcase_row_area col-lg-4">
                                        <label>Tipo de vehículo:</label>
                                        <select class="form-control custom-select" name="tipovehiculo" id="tipovehiculo" required>
                                            <option value="Automóvil">Automóvil</option>
                                            <option value="Autobús">Autobús</option>
                                            <option value="Camión">Camión</option>
                                            <option value="Maquinaria">Maquinaria</option>
                                            <option value="Motocicleta">Motocicleta</option>
                                            <option value="Pick Up o VAN">Pick Up o VAN</option>
                                            <option value="Torton">Torton</option>
                                            <option value="3 Toneladas">3 Toneladas</option>
                                            <option value="5 Toneladas">5 Toneladas</option>
                                            <option value="Otros">Otros</option>
                                        </select>
                                    </div>

                                    <div class="form-group showcase_row_area col-lg-4">
                                        <label for="marca">Marca del vehículo: <button class="add" type="button" data-toggle="modal" data-target="#marcaModal"><i class="mdi mdi-plus"></i></button></label>
                                        <select class="form-control custom-select" name="marca" id="marca" required>
                                            <option value="">Selecionar...</option>
                                            <?php
                                              // Generamos las opciones
                                              $options = '';
                                              // Generamso la consulta
                                              $sql_marcas = "SELECT * FROM marcas ORDER BY marca ASC";
                                              // Ejecutamos la consulta
                                              $marcas = $conn->query($sql_marcas);
                                              // Recorremos cada una de las marcas
                                              while ($marca_ = $marcas->fetch_array(MYSQLI_ASSOC)){
                                                // Regeneramos las opciones
                                                $options .= '<option marca_id="'.$marca_['id'].'" tipado="0" value="'.$marca_['marca'].'">'.$marca_['marca'].'</option>';
                                              }
                                              // Separamos
                                              $options .= '<optgroup label="----------"></optgroup>';
                                              // Generamso la consulta
                                              $sql_marcas_cliente = "SELECT * FROM cliente_marcas WHERE id_proveedor = ".$provid." ORDER BY marca ASC";
                                              // Ejecutamos la consulta
                                              $marcas_cliente = $conn->query($sql_marcas_cliente);
                                              // Recorremos cada una de las marcas
                                              while ($marca_cli = $marcas_cliente->fetch_array(MYSQLI_ASSOC)){
                                                // Regeneramos las opciones
                                                $options .= '<option marca_id="'.$marca_cli['id'].'" tipado="1" value="'.$marca_cli['marca'].'">'.$marca_cli['marca'].'</option>';
                                              }
                                              echo $options;
                                            ?>
                                            <?php
                                              // Generamos la peticion del ser
                                            // $query = "SELECT id, marca FROM marcas ORDER BY marca ASC";
                                            // $result = $conn->query($query);
                                            // while ($marca = $result->fetch_array(MYSQLI_ASSOC)) {
                                            //   echo '<option marca_id="' . $marca['id'] . '" value="' . $marca['marca'] . '">' . $marca['marca'] . '</option>';
                                            // }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group showcase_row_area col-lg-4">
                                        <label for="modelo">Modelo del vehículo: <button class="add" type="button" data-toggle="modal" data-target="#modeloModal"><i class="mdi mdi-plus"></i></button></label>
                                        <select class="form-control custom-select" name="modelo" id="modelo" disabled required>
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>

                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="anio">Año del vehículo:</label>
                                        <!--input type="text" class="form-control" id="anio" placeholder="Año" name="anio" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" required minlength="4" maxlength="4"-->
                                        <select class="form-control custom-select" name="anio" id="anio">
                                            <option value="">Seleccionar...</option>
                                            <script>
                                                var myDate = new Date();
                                                var year = myDate.getFullYear();
                                                for (var i = 1940; i < year + 1; i++) {
                                                    document.write('<option value="' + i + '">' + i + '</option>');
                                                }

                                            </script>
                                        </select>
                                    </div>

                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="costo">Color del vehiculo:</label>
                                        <input type="text" class="form-control" style="text-transform:capitalize" id="color" placeholder="Color" name="color" required>
                                    </div>

                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="costo">Placas del vehiculo:</label>
                                        <input type="text" class="form-control" style="text-transform:uppercase" id="placas" placeholder="Placas" name="placas" required minlength="7" maxlength="9">
                                    </div>

                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="costo">Número de serie:</label>
                                        <input type="text" class="form-control" style="text-transform:uppercase" id="num_serie" placeholder="Número de serie" name="num_serie" required>
                                    </div>

                                    //Test github
                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="costo">Tipo de Vehículo:</label>
                                        <input type="text" class="form-control" style="text-transform:uppercase" id="num_serie" placeholder="Número de serie" name="num_serie" required>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="form-group showcase_row_area col-lg-4">
                                        <br>
                                        <label><input type="checkbox" name="maniobras" value="1" id="maniobras"> Se necesitan maniobras</label>
                                    </div>
                                    <div class="form-group  showcase_row_area extra col-lg-4">
                                        <label for="numero_economico">Número económico:</label>
                                        <input type="text" class="form-control" style="text-transform:uppercase" id="numero_economico" name="numero_economico">
                                    </div>
                                    <div class="form-group  showcase_row_area extra col-lg-4">
                                        <label for="peso">Peso aproximado de carga:</label>
                                        <input type="text" class="form-control" name="peso" id="peso">
                                    </div>
                                    <div class="form-group  showcase_row_area extra col-lg-4">
                                        <label for="peso">Peso aproximado de carga:</label>
                                        <input type="text" class="form-control" name="peso" id="peso">
                                    </div>
                                </div>

                                <div id="detalle_maniobras">
                                    <div class="form-group">
                                        <label for="detalles_maniobras">Describa los detalles de las maniobras:</label>
                                        <textarea class="form-control" id="detalles_maniobras" name="detalles_maniobras" rows="3"></textarea>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="form-group showcase_row_area col-lg-4">
                                        <label for="costo">Tipo de falla:</label>
                                        <select class="form-control custom-select" name="falla" id="falla" style="text-transform:capitalize" required>
                                            <option value="Mecánica">Mecánica</option>
                                            <option value="Gasolina">Gasolina</option>
                                            <option value="Corriente">Corriente</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="form-group showcase_row_area otros col-lg-8">
                                        <label for="falla">Descripcion de la falla:</label>
                                        <input type="text" class="form-control" style="text-transform:capitalize" id="otros" placeholder="" name="otros">
                                    </div>
                                    div class="form-group showcase_row_area otros col-lg-8">
                                        <label for="falla">Descripcion de la falla 2:</label>
                                        <input type="text" class="form-control" style="text-transform:capitalize" id="otros" placeholder="" name="otros">
                                    </div>
                                    <div class="form-group showcase_row_area col-lg-4">
                                        <label for="costo">Tipo de transmisión:</label>
                                        <select class="form-control custom-select" name="transmision" id="transmision" style="text-transform:capitalize" required>
                                            <option value="Estándar">Estándar</option>
                                            <option value="Automática">Automática</option>
                                            <option value="Combinada">Combinada</option>
                                        </select>
                                    </div>
                                    <div class="form-group showcase_row_area col-lg-4">
                                        <label for="costo">Estado de origen:</label>
                                        <select class="form-control custom-select" name="estado_sol" id="estado_sol" style="text-transform:capitalize" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="Aguascalientes">Aguascalientes</option>
                                            <option value="Baja California">Baja California</option>
                                            <option value="Baja California Sur">Baja California Sur</option>
                                            <option value="Campeche">Campeche</option>
                                            <option value="Chiapas">Chiapas</option>
                                            <option value="Chihuahua">Chihuahua</option>
                                            <option value="CDMX">Ciudad de México</option>
                                            <option value="Coahuila">Coahuila</option>
                                            <option value="Colima">Colima</option>
                                            <option value="Durango">Durango</option>
                                            <option value="Estado de México">Estado de México</option>
                                            <option value="Guanajuato">Guanajuato</option>
                                            <option value="Guerrero">Guerrero</option>
                                            <option value="Hidalgo">Hidalgo</option>
                                            <option value="Jalisco">Jalisco</option>
                                            <option value="Michoacán">Michoacán</option>
                                            <option value="Morelos">Morelos</option>
                                            <option value="Nayarit">Nayarit</option>
                                            <option value="Nuevo León">Nuevo León</option>
                                            <option value="Oaxaca">Oaxaca</option>
                                            <option value="Puebla">Puebla</option>
                                            <option value="Querétaro">Querétaro</option>
                                            <option value="Quintana Roo">Quintana Roo</option>
                                            <option value="San Luis Potosí">San Luis Potosí</option>
                                            <option value="Sinaloa">Sinaloa</option>
                                            <option value="Sonora">Sonora</option>
                                            <option value="Tabasco">Tabasco</option>
                                            <option value="Tamaulipas">Tamaulipas</option>
                                            <option value="Tlaxcala">Tlaxcala</option>
                                            <option value="Veracruz">Veracruz</option>
                                            <option value="Yucatán">Yucatán</option>
                                            <option value="Zacatecas">Zacatecas</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="distancia">Distancia del servicio:</label>
                                        <input type="text" class="form-control" id="distancia" placeholder="Distancia" name="distancia" required readonly>
                                    </div>
                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="tiempo">Tiempo del servicio:</label>
                                        <input type="text" class="form-control" id="tiempo" placeholder="Tiempo en minutos" name="tiempo" required readonly>
                                        <input type="hidden" id="tiempo_valor" name="tiempo_valor">
                                    </div>
                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="kilometrosExcedidos">KM excedentes:</label>
                                        <input type="text" class="form-control costos" id="kilometrosExcedidos" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder="Kilómetros Excedentes" name="kilometrosExcedidos" required>
                                    </div>
                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="totalkm">Total de kilometros:</label>
                                        <input type="text" class="form-control" id="totalkm" placeholder="Total de kilometros" name="totalkm" required readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group showcase_row_area col-lg-3">
                                        <label for="distancia">Grúa:</label>
                                        <select class="form-control custom-select" name="grua" id="grua" required>
                                            <option value="">Seleccionar...</option>
                                            <?php
                                              // Linea de consulta
                                              $query_string = "SELECT * FROM `gruas` WHERE estatus = 'activa' AND proveedor_id=" . $provid;
                                              // Ejecutamos la consulta
                                              $result_grua = $conn->query($query_string);
                                              while ($grua = $result_grua->fetch_array(MYSQLI_ASSOC)) {
                                                echo'<option value="'.$grua['id'].'" data-id="'.$grua['tipo'].'" data-idope="'.$grua['operador_id'].'">'.$grua['unidad'].'</option>';
                                              }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group  showcase_row_area col-lg-3">
                                        <label for="distancia">Operador:</label>
                                        <select class="form-control custom-select" name="operador" id="operador" required>
                                            <option value="">Seleccionar...</option>
                                            <?php $sql = "SELECT * FROM `usuarios` WHERE tipo='operador' AND estatus = 'activo' AND proveedor_id=" . $provid;
                                                $result = $conn->query($sql);
                                                //print_r($result);
                                                $serv['IDCHF'] = '0';
                                                while ($gruas = $result->fetch_array(MYSQLI_ASSOC)) { ?>
                                            <option value="<?= $gruas['ID'] ?>" <?php if ($serv['IDCHF'] == $gruas['ID']) { ?> selected <?php } ?> data-token="<?= $gruas['pushID'] ?>"><?= $gruas['nombre'] ?> </option>
                                            <?php } ?>
                                        </select>

                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group showcase_row_area">
                                                    <label for="costo">Costo del servicio:</label>
                                                    <input type="text" class="form-control costos" id="costo" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder="Tarifa de servicio" name="costo" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group showcase_row_area" id="costomaniobras_render">
                                                    <label for="costomaniobras">Costo de maniobras:</label>
                                                    <input type="text" class="form-control costos" id="costomaniobras" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder="Tarifa de maniobra" name="costomaniobras">
                                                </div>
                                            </div>
                                            <div class="col-6" style="margin-top:15px;">
                                                <div class="form-group showcase_row_area">
                                                    <label for="metodo_pago">Método de pago:</label>
                                                    <select class="form-control custom-select" name="metodo_pago" id="metodo_pago" required>
                                                        <option value="">Seleccionar...</option>
                                                        <option value="Efectivo">Efectivo</option>
                                                        <option value="Cheque nominativo">Cheque nominativo</option>
                                                        <option value="Transferencia electrónica de fondos">Transferencia electrónica de fondos</option>
                                                        <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                                        <option value="Monedero electrónico">Monedero electrónico</option>
                                                        <option value="Dinero electrónico">Dinero electrónico</option>
                                                        <option value="Vales de despensa">Vales de despensa</option>
                                                        <option value="Dación en pago">Dación en pago</option>
                                                        <option value="Pago por subrogación">Pago por subrogación</option>
                                                        <option value="Pago por consignación">Pago por consignación</option>
                                                        <option value="Condonación">Condonación</option>
                                                        <option value="Compensación">Compensación</option>
                                                        <option value="Novación">Novación</option>
                                                        <option value="Confusión">Confusión</option>
                                                        <option value="Remisión de deuda">Remisión de deuda</option>
                                                        <option value="Prescripción o caducidad">Prescripción o caducidad</option>
                                                        <option value="A satisfacción del acreedor">A satisfacción del acreedor</option>
                                                        <option value="Tarjeta de débito">Tarjeta de débito</option>
                                                        <option value="Tarjeta de servicios">Tarjeta de servicios</option>
                                                        <option value="Aplicación de anticipos">Aplicación de anticipos</option>
                                                        <option value="Por definir">Por definir</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <input type="hidden" id="tarifario" name="tarifario" readonly>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="linea-costos">
                                            <div class="bolder">Total</div>
                                            <div class="bolder">
                                                <span id="total_servicio">$0.00</span>
                                            </div>
                                        </div>
                                        <div class="dividor"></div>
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:80%">Tarifa del servicio</td>
                                                <td>
                                                    <span id="tarifa_servicio">$0.00</span>
                                                </td>
                                            </tr>
                                            <tr id="lines_costo_maniobra">

                                            </tr>
                                            <tr>
                                                <?php
                                                    // Validamos si viene con cuaota web
                                                    if ($contrato['cuota_web'] == '1') {
                                                      echo'<td>Cuota de solicitud ('.$contrato['valor_cuota_web'].'%)</td>
                                                           <td><span id="tarifa_solicitud">$0.00</span></td>';
                                                    }
                                                ?>
                                            </tr>
                                        </table>
                                        <div class="dividor"></div>
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:80%">Subtotal</td>
                                                <td>
                                                    <span id="subtotal_servicio">$0.00</span>
                                                </td>
                                            </tr>
                                            <!-- <tr>
                                                <td>IVA (16%)</td>
                                                <td>
                                                    <span id="iva">$0.00</span>
                                                </td>
                                            </tr>-->
                                        </table>
                                        <?php
                                            // Validamos si viene con cuaota web
                                            if ($contrato['cuota_web'] == '1') {
                                              echo'<div style="background-color: cornsilk;padding: 10px;border-radius:10px;font-size: 12px;line-height: 16px;margin-top: 10px;">
                                                    *Cuota al cliente por concepto de comisión por servicio finalizado para Towlike
                                                  </div>';
                                            }
                                          ?>

                                    </div>
                                </div>

                                <hr>
                                <div id="rangos" style="display:none;">
                                    <?php echo $base['rangos'];?>
                                </div>

                                <input type="hidden" id="latitud_base" value="<?php echo $base['latitud'];?>">
                                <input type="hidden" id="longitud_base" value="<?php echo $base['longitud'];?>">
                                <input type="hidden" id="place_id_base" value="<?php echo $base['place_id'];?>">
                                <input type="hidden" id="cuota_solicitud" value="<?php echo ($contrato['cuota_web'] == '1') ? $contrato['valor_cuota_web'] : '0' ;?>">
                                <input type="hidden" value="guardar" name="accion">
                                <button type="submit" class="btn btn-sm btn-primary" <?php echo $ina;?>>Generar Servicio</button>
                                <button id="generar_cotizacion" type="button" style="margin-left: 4px;" class="btn btn-sm btn-primary" <?php echo $ina;?>>Generar Cotización</button>
                                <hr>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Agregado -->
            <div class="modal fade" id="vigenciaModal" tabindex="-1" role="dialog" aria-labelledby="cotizacionLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="marcaLabel">Nueva cotización</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <center>
                                <div class="form-group showcase_row_area">
                                    <div class="form-group showcase_row_area">
                                        <label for="vigencia">Vigencia de la cotización:</label>
                                        <input type="text" class="form-control setdate" style="cursor:pointer;" id="fecha_vigencia" placeholder="Fecha de vigencia" name="fecha_vigencia" required>
                                    </div>
                                </div>
                                <div class="form-group showcase_row_area">
                                    <label for="nombre_cliente">Nombre del cliente:</label>
                                    <input type="text" class="form-control costos" id="nombre_cliente" placeholder="Nombre del cliente" name="nombre_cliente" onKeyUp="document.getElementById(this.id).value=document.getElementById(this.id).value.toUpperCase()" value="">
                                    <div id="ultimo_registro"></div>
                                </div>
                            </center>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="borrarCliente();" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" id="add_quote">Generar</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Agregado -->

            <div class="modal fade" id="marcaModal" tabindex="-1" role="dialog" aria-labelledby="marcaLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="marcaLabel">Agregar nueva marca</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="marca_add_error"></div>
                            <label for="marca_add">Nombre de la marca:</label>
                            <input type="text" class="form-control" id="marca_add" placeholder="Marca">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" id="add_brand">Crear marca</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modeloModal" tabindex="-1" role="dialog" aria-labelledby="modeloLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modeloLabel">Agregar nuevo modelo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="modelo_add_error"></div>
                            <label for="marca_show">Marca:</label>
                            <select class="form-control custom-select" id="marca_show">
                                <option value="">Selecionar...</option>
                                <?php
                    // Generamos las opciones
                    $options = '';
                    // Generamso la consulta
                    $sql_marcas = "SELECT * FROM marcas ORDER BY marca ASC";
                    // Ejecutamos la consulta
                    $marcas = $conn->query($sql_marcas);
                    // Recorremos cada una de las marcas
                    while ($marca_ = $marcas->fetch_array(MYSQLI_ASSOC)){
                      // Regeneramos las opciones
                      $options .= '<option marca_id="'.$marca_['id'].'" tipado="0" value="'.$marca_['marca'].'">'.$marca_['marca'].'</option>';
                    }
                    // Separamos
                    $options .= '<optgroup label="----------"></optgroup>';
                    // Generamso la consulta
                    $sql_marcas_cliente = "SELECT * FROM cliente_marcas WHERE id_proveedor = ".$provid." ORDER BY marca ASC";
                    // Ejecutamos la consulta
                    $marcas_cliente = $conn->query($sql_marcas_cliente);
                    // Recorremos cada una de las marcas
                    while ($marca_cli = $marcas_cliente->fetch_array(MYSQLI_ASSOC)){
                      // Regeneramos las opciones
                      $options .= '<option marca_id="'.$marca_cli['id'].'" tipado="1" value="'.$marca_cli['marca'].'">'.$marca_cli['marca'].'</option>';
                    }
                    echo $options;
                  ?>
                            </select>
                            <label for="modelo_add" style="margin-top:10px;">Modelo:</label>
                            <input type="text" class="form-control" id="modelo_add" placeholder="Modelo">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" id="add_model">Crear modelo</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php
					$dias = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
					$hora = date("H:i");
					$dia = $dias[date("w")];
            ?>

            <style>
                .bolder {
                    font-size: 18px;
                    font-weight: bold;
                }

                #map {
                    height: 480px;
                    width: 100%;
                }


                .bac-img {
                    width: 40%;
                    height: auto;
                    margin: 10px 5%;
                }

                .facturar,
                #show_auxilio_vial,
                #show_programado,
                #show_suministro,
                .programado {
                    display: none;
                }

                .error {
                    color: #ea0022;
                    font-size: 10px;
                }

                #detalle_maniobras,
                #costomaniobras_render,
                .autonombre_credito {
                    display: none;
                }

                .otros,
                .extra {
                    display: none;
                }

                .linea-costos {
                    display: block;
                    font-size: 13px;
                }

                .linea-costos>div {
                    display: inline-block;
                    width: auto;
                }

                .dividor {
                    margin-top: 10px;
                    margin-bottom: 10px;
                    border: 0;
                    border-top: 1px solid rgba(0, 0, 0, 0.1);
                }

                .success {
                    display: block;
                    padding: 8px;
                    border-radius: 6px;
                    background: #4BB543;
                    color: #fff;
                    margin: 0px 20px;
                }

                .error {
                    display: block;
                    padding: 8px;
                    border-radius: 6px;
                    background: #b0182e;
                    color: #fff;
                    margin: 0px 20px;
                }

                .add {
                    background: #FF4500;
                    display: inline-block;
                    width: 25px;
                    height: 25px;
                    line-height: 2px;
                    font-weight: 700;
                    font-size: 16px;
                    border-radius: 20px;
                    border: none;
                    color: #FFF;
                    padding: 10px 0px;
                }

                .success_til {
                    display: block;
                    padding: 7px 10px;
                    border-radius: 6px;
                    background: #4BB543;
                    font-size: 13px;
                    color: #fff;
                    margin-bottom: 5px;
                }

                .error_til {
                    display: block;
                    padding: 7px 10px;
                    border-radius: 6px;
                    background: #b0182e;
                    font-size: 13px;
                    color: #fff;
                    margin-bottom: 5px;
                }

                .ult_registro {
                    background-color: #8e8e8ea1;
                    color: white;
                    margin-top: 10px;
                    border-radius: 4px;
                    padding: 4px;
                    width: 100%;
                }

                .lds-dual-ring {
                    display: inline-block;
                    width: 30px;
                    height: 30px;
                }

                .lds-dual-ring:after {
                    content: " ";
                    margin-top: 5px;
                    display: block;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    border: 4px solid #fff;
                    border-color: #fff transparent #fff transparent;
                    animation: lds-dual-ring 1.2s linear infinite;
                }

                @keyframes lds-dual-ring {
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
                }

            </style>

            <script>
                // Generamos el place id de la base
                var baseID = $('#place_id_base').val();
                // Definimos el arreglo de la base
                var dataBase = {
                    id: baseID,
                    name: 'Base'
                };
                // Definimos el arreglo del origen
                var dataOrigen = {
                    id: '',
                    name: 'Origen'
                };
                // Definimos la variable de ID destino
                var dataDestino = {
                    id: '',
                    name: 'Destino'
                };
                // Definimos el arreglo de la base
                var baseIcon = {
                    url: 'img/gruanaranja.png',
                    scaledSize: new google.maps.Size(32, 32)
                };
                // Base del socio (latitud)
                var latitud_base = parseFloat($('#latitud_base').val());
                // Base del socio (longitud)
                var longitud_base = parseFloat($('#longitud_base').val());
                // Constante de la configuracion del mapa
                const map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 12,
                    center: {
                        lat: latitud_base,
                        lng: longitud_base
                    }
                });
                // Creamos el servicio directionsService
                const directionsService = new google.maps.DirectionsService();
                // Definimos el servicio de detalles
                const placesService = new google.maps.places.PlacesService(map);
                // Creamos el servicio directionsRenderer
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    draggable: true,
                    map
                });
                // Generamoe la direccion del mapa
                directionsRenderer.setMap(map);
                // Domicilio de Origen autocompletado
                var origen = document.getElementById('origen');
                // Asignamos el autocomplete
                origen = new google.maps.places.Autocomplete(origen);
                // Añadimos las restricciones
                origen.setComponentRestrictions({
                    'country': 'mx'
                });
                // Obtenemos el tipo de valores
                origen.setFields(['place_id', 'address_component', 'geometry']);

                // Otorgamos un escucha
                origen.addListener('place_changed', origenLatitudLongitud);
                // Generamos la funcion del escucha
                function origenLatitudLongitud() {
                    // Obtenemos el lugar
                    var place = origen.getPlace();
                    // Almacenamos el id
                    dataOrigen.id = place.place_id;
                    // Obtenemos la latitud
                    var latitud = place.geometry.location.lat();
                    // Definimos el valor de la latitud
                    document.getElementById('origenlat').value = latitud;
                    // Obtenemos la longitud
                    var longitud = place.geometry.location.lng();
                    // Definimos el valor de la longitud
                    document.getElementById('origenlng').value = longitud;
                    // Obtenemos el codigo postal
                    var codigo_postal = place.address_components.find(addr => addr.types[0] === 'postal_code').short_name;
                    // Definimos el codigo postal
                    document.getElementById('codigo_postal_origen').value = codigo_postal;
                    // Definimos el plae id 
                    document.getElementById('place_id_origen').value = place.place_id;
                    // Dibujar ruta
                    drawRoute();
                }

                // Domicilio de Destino autocompletado
                var destino = document.getElementById('destino');
                // Asignamos el autocomplete
                destino = new google.maps.places.Autocomplete(destino);
                // Añadimos las restricciones
                destino.setComponentRestrictions({
                    'country': 'mx'
                });
                // Obtenemos el tipo de valores
                destino.setFields(['place_id', 'address_component', 'geometry']);

                // Otorgamos un escucha
                destino.addListener('place_changed', destinoLatitudLongitud);
                // Generamos la funcion del escucha
                function destinoLatitudLongitud() {
                    // Obtenemos el lugar
                    var place = destino.getPlace();
                    // Almacenamos el id
                    dataDestino.id = place.place_id;
                    // Obtenemos la latitud
                    var latitud = place.geometry.location.lat();
                    // Dibujamos la latitud
                    document.getElementById('destinolat').value = latitud;
                    // Obtenemos la longitud
                    var longitud = place.geometry.location.lng();
                    // Dibujamos la longitud
                    document.getElementById('destinolng').value = longitud;
                    // Obtenemos el codigo postal
                    var codigo_postal = place.address_components.find(addr => addr.types[0] === 'postal_code').short_name;
                    // Definimos el codigo postal
                    document.getElementById('codigo_postal_destino').value = codigo_postal;
                    // Definimos el place id
                    document.getElementById('place_id_destino').value = place.place_id;
                    // Dibujar ruta
                    drawRoute();
                }

                // Funcion para dibujar la ruta
                function drawRoute() {
                    console.log(':::DUBUJANDO RUTA:::');
                    // Validamos si contiene los dos datos
                    if (!dataOrigen.id || !dataDestino.id) {
                        return
                    };
                    console.log(':::PROCESANDO DUBUJANDO RUTA:::');
                    // Generamos el servicio
                    directionsService.route({
                        origin: {
                            placeId: dataBase.id
                        },
                        destination: {
                            placeId: dataDestino.id
                        },
                        waypoints: [{
                            location: {
                                placeId: dataOrigen.id
                            }
                        }],
                        travelMode: google.maps.TravelMode.DRIVING,
                    }).then((response, status) => {
                        // Dibujamos la ruta en el mapa
                        directionsRenderer.setDirections(response);
                    }).catch((e) => window.alert('Algo salio mal, por favor intentelo mas tarde' + status));
                }

                // Definimos un escucha 
                directionsRenderer.addListener('directions_changed', () => {
                    // Obtenemos la nueva direccion
                    const directions = directionsRenderer.getDirections();
                    // Validamos si contiene una direccion
                    if (directions) {
                        // Obtenemos las direcciones
                        var geocoded_waypoints = directions.geocoded_waypoints;
                        // Validamos si cambio el origen
                        if (!geocoded_waypoints.find(origen => origen.place_id == dataOrigen.id)) {
                            // Llamamos la funcion para obtener y dibujar las coordenadas
                            obtenerCoordenadas(geocoded_waypoints[1].place_id, 'origen');
                        }
                        // Validamos si cambio el destino
                        if (!geocoded_waypoints.find(destino => destino.place_id == dataDestino.id)) {
                            // Llamamos la funcion para obtener y dibujar las coordenadas
                            obtenerCoordenadas(geocoded_waypoints[2].place_id, 'destino');
                        }
                        // Ejecutamos la funcion para el total del recorrido
                        computeTotalDistance(directions);
                    }
                });

                // Computamos la distancia
                function computeTotalDistance(directions) {
                    // Definimos una variable para el conteo
                    let distancia = 0;
                    let tiempo = 0;
                    // Obtenemos la nueva ruta
                    const myroute = directions.routes[0];
                    // Validamos si cotiene
                    if (!myroute) {
                        // Salidmos de la funcion
                        return;
                    }
                    // Recorremos cada unos de los marcadores
                    for (let i = 0; i < myroute.legs.length; i++) {
                        // Sumamos la distancia
                        distancia += myroute.legs[i].distance.value;
                        // Sumamos el tiempo
                        tiempo += myroute.legs[i].duration.value;
                    }
                    // Parseamos la distancia
                    var totalDistancia = distancia / 1000;
                    // Parseamos el tiempo
                    var totalTiempo = tiempo / 60;
                    // Declaramos los km de ladistancia
                    document.getElementById('distancia').value = totalDistancia.toFixed(2) + ' km';
                    // Declaramos el total de los km de la distancia
                    document.getElementById('totalkm').value = totalDistancia.toFixed(2) + ' km';
                    // Generamoe l valor del tiempo
                    document.getElementById('tiempo').value = totalTiempo.toFixed(2) + ' min';
                    // Generamoe le valor del tiempo en valor
                    document.getElementById('tiempo_valor').value = tiempo;
                }

                function obtenerCoordenadas(request, type) {
                    // Generamos el servicio para obtener las coordenadas
                    placesService.getDetails({
                            placeId: request
                        },
                        // Ejecutamos un callback
                        function(results, status) {
                            // Pintamos el domicilio
                            document.getElementById(type).value = results.formatted_address;
                            // Pintamos la latitud
                            document.getElementById(type + 'lat').value = results.geometry.location.lat();
                            // Pintamos la longitud
                            document.getElementById(type + 'lng').value = results.geometry.location.lng();
                            // Pintamos el codigo postal
                            document.getElementById('codigo_postal_' + type).value = results.address_components.find(addr => addr.types[0] === 'postal_code').short_name;
                            // Pintamos el place id
                            document.getElementById('place_id_' + type).value = results.place_id;
                            // Validamos el tipo
                            if (type == 'origen') {
                                // Almacenamos el nuevo valor para origen
                                dataOrigen.id = results.place_id;
                            } else {
                                // Almacenamos el nuevo valor para origen
                                dataDestino.id = results.place_id;
                            }
                            drawRoute();
                        });
                }

                // Cargamos los rangos
                var rangos = $('#rangos').html();
                // Definimos los rangos a pintar
                rangos = JSON.parse(rangos);
                // Recorremos cada unos de las coordenadas
                for (let index = 0; index < rangos.length; index++) {
                    var controll = 'Poli' + index;
                    // Generamos la estructura
                    var structCoords = rangos[index].coordenadas;
                    // lo parseamos
                    var structCoords = JSON.parse(structCoords);
                    // Generamos el servicio de polilineas
                    var controll = new google.maps.Polygon({
                        paths: structCoords,
                        strokeColor: rangos[index].color,
                        strokeOpacity: 1,
                        strokeWeight: 3,
                        fillColor: rangos[index].color,
                        fillOpacity: 0.20,
                        zIndex: index,
                        geodesic: true
                    });
                    // Dibujamos los poligonos
                    controll.setMap(map);
                }

                // Escuchamos el cambio de la lat y lng del origen
                $('#origenlat, #origenlng').on('change', function() {
                    // Ejecutamos la funcion
                    obtenerDomicilioOrigen('origen');
                });

                // Obtenemos la nueva direccion basada en cordenadas
                function obtenerDomicilioOrigen(tipo) {
                    // Obtenemos el latitud
                    var lat = $('#' + tipo + 'lat').val();
                    // Obtenemos el longitus
                    var lng = $('#' + tipo + 'lng').val();
                    // Validamos si contenemos las coordenadas
                    if (lat == '' || lng == '') {
                        return;
                    };
                    // Generamos la peticion
                    $.ajax({
                        url: 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key=AIzaSyDy7uSi5TNN13bn0hxaZxdnRzQ8Hzeh0WI',
                        type: 'GET',
                        success: function(resultados) {
                            // Almacenamos la respuesta
                            results = resultados.results;
                            // Almacenamos el id
                            dataOrigen.id = results[0].place_id;
                            // Modificamos el input de la plataforma
                            $('#' + tipo).val(results[0].formatted_address);
                            // Obtenemos el codigo postal
                            var codigo_postal = results[0].address_components.find(addr => addr.types[0] === 'postal_code').short_name;
                            // Definimos el codigo postal
                            document.getElementById('codigo_postal_origen').value = codigo_postal;
                            // Definimos el place id
                            document.getElementById('place_id_origen').value = results[0].place_id;
                            // Dibujar ruta
                            drawRoute();
                        }
                    });
                }

                // Escuchamos el cambio de la lat y lng del destino
                $('#destinolat, #destinolng').on('change', function() {
                    // Ejecutamos la funcion
                    obtenerDomicilioDestino('destino');
                });

                // Obtenemos la nueva direccion basada en cordenadas
                function obtenerDomicilioDestino(tipo) {
                    // Obtenemos el latitud
                    var lat = $('#' + tipo + 'lat').val();
                    // Obtenemos el longitus
                    var lng = $('#' + tipo + 'lng').val();
                    // Validamos si contenemos las coordenadas
                    if (lat == '' || lng == '') {
                        return;
                    };
                    // Generamos la peticion
                    $.ajax({
                        url: 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key=AIzaSyDy7uSi5TNN13bn0hxaZxdnRzQ8Hzeh0WI',
                        type: 'GET',
                        success: function(resultados) {
                            // Almacenamos la respuesta
                            results = resultados.results;
                            // Almacenamos el id
                            dataDestino.id = results[0].place_id;
                            // Modificamos el input de la plataforma
                            $('#' + tipo).val(results[0].formatted_address);
                            // Obtenemos el codigo postal
                            var codigo_postal = results[0].address_components.find(addr => addr.types[0] === 'postal_code').short_name;
                            // Definimos el codigo postal
                            document.getElementById('codigo_postal_destino').value = codigo_postal;
                            // Definimos el place id
                            document.getElementById('place_id_destino').value = results[0].place_id;
                            // Dibujar ruta
                            drawRoute();
                        }
                    });
                }

                $('.setime').datetimepicker({
                    format: 'h:i a',
                    formatTime: 'h:i a',
                    step: 10,
                    datepicker: false,
                    timepicker: true
                });

                $('.setdate').datetimepicker({
                    format: 'Y/m/d',
                    step: 10,
                    datepicker: true,
                    timepicker: false
                });

                $('.autocompletar').devbridgeAutocomplete({
                    serviceUrl: 'https://towlike.com/app_test/rest/clientes_proveedor.php',
                    type: 'POST',
                    paramName: 'srch',
                    params: {
                        'provid': <?= $provid ?>,
                        'cliente': 'Regular'
                    },
                    dataType: 'json',
                    onSelect: function(suggestion) {

                        $('#id_cliente').val(suggestion.id);
                        $('#email').val(suggestion.email);
                        $('#telefono').val(suggestion.telefono);
                    }
                });


                $(document).ready(function() {

                    // Validamos los cambios del servicio
                    $('#tipo').on('change', function() {
                        // validamos si esta seleccionado
                        if ($(this).find(':selected').val() == '') {
                            // Ocultamos todos los campos
                            $('#show_auxilio_vial, #show_programado, #show_suministro').hide();
                        } else if ($(this).find(':selected').val() == 'Auxilio vial') {
                            // Mostramos el requerido
                            $('#show_auxilio_vial').show();
                            $('#show_programado, #show_suministro').hide();
                            // Le plantamos la clave de servicio
                            $('#clave_servicio').val('78181500');
                        } else if ($(this).find(':selected').val() == 'Programado') {
                            // Mostramos el requerido
                            $('#show_programado').show();
                            $('#show_auxilio_vial, #show_suministro').hide();
                            // Le plantamos la clave de servicio
                            $('#clave_servicio').val('78101803');
                        } else if ($(this).find(':selected').val() == 'Regular') {
                            // Mostramos el requerido
                            $('#show_auxilio_vial, #show_programado, #show_suministro').hide();
                            // Le plantamos la clave de servicio
                            $('#clave_servicio').val('78101803');
                        } else if ($(this).find(':selected').val() == 'Suministro de combustible') {
                            // Mostramos el requerido
                            $('#show_suministro').show();
                            $('#show_auxilio_vial, #show_programado').hide();
                            // Le plantamos la clave de servicio
                            $('#clave_servicio').val('78181701');
                        }
                    });


                    $('#falla').on('change', function() {
                        var falla = $(this).find(':selected').val();
                        if (falla == "Otro") {
                            $('.otros').show();
                            $('.otros').prop('required', true);
                        } else {
                            $('.otros').hide();
                            $('.otros').prop('required', false);
                            $('#otros').val('');
                        }
                    });

                    $('#tipovehiculo').on('change', function() {

                        var tipo = $(this).find(':selected').val();

                        if (tipo !== "Motocicleta" && tipo !== "Automóvil") {
                            $('.extra').show();
                        } else {
                            $('.extra').hide();
                            $('#numeroeconomico').val('');
                            $('#peso').val('');
                        }

                    });

                    $('#operador').on('change', function() {
                        var token = $(this).find(':selected').data('token');
                        console.log('token', token);
                        $('#pushID').val(token);
                    });

                    // Funcion para calcular los costos
                    function calcularCosto() {
                        // Limpiamos los valores previos
                        $('#tarifa_servicio').html('$0.00');
                        $('#subtotal_servicio').html('$0.00');
                        $('#total_servicio').html('$0.00');
                        $('#tarifario').val("");

                        // Almacenamos el valor del costo
                        costo_servicio = $('#costo').val();
                        // Validamos que contenga valor
                        if (costo_servicio == '') {
                            costo_servicio = 0;
                        }
                        // Parseamos el valor del servicio
                        costo_servicio = parseFloat(costo_servicio);
                        // Obtenemos la tarifa de la maniobra
                        costo_maniobra = $('#costomaniobras').val();
                        // Validamos que contenga valor
                        if (costo_maniobra == '') {
                            costo_maniobra = 0;
                        }
                        // Parseamos el valor de la maniobra
                        costo_maniobra = parseFloat(costo_maniobra);
                        // Almacenamos el costo de solicitud
                        cuota_solicitud = parseFloat($('#cuota_solicitud').val());
                        // Sumamos la tarifa de la maniobra
                        tarifa_servicio = costo_servicio + costo_maniobra;
                        // Creamos la tarifa de solicitud
                        tarifa_solicitud = (tarifa_servicio * cuota_solicitud) / 100;
                        // Generamos el subtotal
                        subtotal = tarifa_servicio + tarifa_solicitud;
                        // Generamos el total
                        total = subtotal;
                        // Mostramos y asignamos los costos
                        $('#tarifa_servicio').html(round(costo_servicio));
                        $('#tarifa_solicitud').html(round(tarifa_solicitud));
                        $('#tarifa_mi_maniobra').html(round(costo_maniobra));
                        $('#subtotal_servicio').html(round(subtotal));
                        $('#total_servicio').html(round(total));
                        $('#tarifario').val(redondeo(total));
                    }

                    // Capturamos los eventos
                    $('.costos').keyup(function() {
                        calcularCosto();
                    });


                    function round(num) {
                        const options = {
                            style: 'currency',
                            currency: 'MXN'
                        };
                        var m = Number((Math.abs(num) * 100).toPrecision(15));
                        var costo_precio = Math.round(m) / 100 * Math.sign(num);
                        const numberFormat = new Intl.NumberFormat('es-MX', options);
                        return numberFormat.format(costo_precio);
                    }

                    function redondeo(num) {
                        var m = Number((Math.abs(num) * 100).toPrecision(15));
                        return Math.round(m) / 100 * Math.sign(num);
                    }


                    $('#maniobras').on('change', function() {
                        // Validamos si contiene maniobras
                        if ($('#maniobras').is(":checked")) {
                            // Mostramos el input de detalles de maniobras
                            $('#detalle_maniobras').css('display', 'block');
                            // Mostramos el input del costo de la maniobra
                            $('#costomaniobras_render').css('display', 'block');
                            // Mostramos la linea del costo del servicio
                            $('#lines_costo_maniobra').html('<td style="width:80%">Tarifa de maniobra:</td><td><span id="tarifa_mi_maniobra">$0.00</span></td>');
                        } else {
                            // Ocultamos el input de detalles de maniobras
                            $('#detalle_maniobras').css('display', 'none');
                            // Ocultamos el input del costo de la maniobra
                            $('#costomaniobras_render').css('display', 'none');
                            // Ocultamos la linea del costo del servicio
                            $('#lines_costo_maniobra').html('');
                            // Dejamos un valor por default
                            $('#costomaniobras').val('');
                            // Recalculamos el costo
                            calcularCosto();
                        }
                    });

                    $('#costosssss').on('change', function() {

                        costo = parseFloat($('#costo').val());
                        costoman = parseFloat($('#costomaniobras').val());
                        total = costo + costoman;
                        console.log("gtotal: ", total);

                        $('.gtotal').html(total);

                    });

                });

                // Funcion al cambiar el codigo postal
                $('#marca').change(function() {
                    // Validamos el estado seleccionado
                    var marca = $('option:selected', this).attr('marca_id');
                    // Obtenemos el tipo de marca
                    var tipo = $('option:selected', this).attr('tipado');
                    // Obtenemos el id del proveedor
                    var proveedor = <?= $provid ?>;
                    // Validamos si esta vacio
                    if (marca == '' || tipo == '') {
                        // Dehabilitamos la colonia
                        $('#modelo').html('<option value="">Seleccionar...</option>');
                        $('#modelo').attr('disabled', true);
                        // Salimos de la funcion
                        return;
                    }
                    // Consultamos el domicilio a peticion de estado
                    var data = new FormData();
                    data.append('marca', marca);
                    data.append('tipo', tipo);
                    data.append('proveedor', proveedor);
                    // Consumimos el ajax
                    $.ajax({
                        url: 'getModelo.php',
                        method: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(respuesta) {
                            // Parseamos la respuesta
                            var response = JSON.parse(respuesta);
                            // Inicializamos el select
                            $('#modelo').html('<option value="">Seleccionar...</option>');
                            $('#modelo').attr('disabled', false);
                            // iteramos la respuesta
                            response.forEach(modelo => {
                                var model = modelo.modelo;
                                $('#modelo').append('<option value="' + model + '">' + model + '</option>');
                            });
                        }
                    })
                });


                /////////////////////// Agregado //////////////////////////////////////////
                var segundaBusqueda = false;

                $('#generar_cotizacion').click(function() {

                    // Si el numero del cliente esta ingresado
                    if ($('#telefono').val() != "") {

                        segundaBusqueda = false;

                        // Se busca en la tabla de clientes
                        buscarCliente(1);

                    } else { // Si no esta ingresado el numero del cliente

                        alert("Ingrese primero el telefono del cliente para poder continuar.");

                    }

                });

                function borrarCliente() {

                    $('#nombre_cliente').val("");
                    console.log("Se borra el nombre previo de cliente.");

                }

                function buscarCliente(consulta) {

                    var data = new FormData();
                    data.append('telefono', $('#telefono').val());
                    data.append('consulta', consulta);

                    // Consumimos el ajax
                    $.ajax({
                        url: 'buscarCliente.php',
                        method: 'POST',
                        data: data,
                        dataType: "json",
                        cache: false,
                        contentType: false,
                        processData: false,
                    }).done(function(respuesta) {

                        //console.group(respuesta);
                        $('#ultimo_registro').empty();

                        // Si hay un registro previo del cliente
                        if (respuesta.nombre != "") {

                            $('#nombre_cliente').val(respuesta.nombre);

                            if (consulta == 1) { // Si se encuentra registrado el cliente en el sistema

                                $('#ultimo_registro').html('<label class="ult_registro">Cliente registrado con ese numero en el sistema.</label>');

                            } else { // Si se encuentra un registro previo de ese cliente en cotizaciones pasadas

                                $('#ultimo_registro').html('<label class="ult_registro">Nombre de cliente registrado en ultima cotizacion realizada con ese numero.</label>');

                            }

                            $("#vigenciaModal").modal("show");

                        } else { // Si no hay un registro previo del cliente

                            // Si la busqueda primera no tuvo resultados
                            if (segundaBusqueda == false) {

                                // Se busca en la tabla de cotizaciones
                                buscarCliente(2);
                                segundaBusqueda = true;

                            } else { // Si las dos busqueda fueron realizadas

                                console.log("Sin registro previo del cliente.");
                                $('#ultimo_registro').html('');

                                $("#vigenciaModal").modal("show");

                            }

                        }

                    }).fail(function(fail) {

                        alert("Algo fallo al buscar el numero del cliente, por favor intente de nuevo.");
                        console.log("Error :" + message);
                        console.group(fail);
                        location.reload();

                    });


                } // Fin de buscarCliente()

                $('#add_quote').click(function() {


                    // Capturamos al proveedor
                    var proveedor = <?= $provid ?>;

                    var maniobras = $('#maniobras').is(":checked") ? 1 : 0;
                    var facturacion = $('#facturacion').is(":checked") ? 1 : 0;

                    // Enviamos los datos 
                    var data = new FormData();

                    data.append('telefono', $('#telefono').val());
                    data.append('operador', $('#operador').val());
                    data.append('grua', $('#grua').val());
                    data.append('proveedor', proveedor);
                    data.append('modelo', $('#modelo').val());
                    data.append('marca', $('#marca').val());
                    data.append('anio', $('#anio').val());
                    data.append('transmision', $('#transmision').val());
                    data.append('falla', $('#falla').val());
                    data.append('otros', $('#otros').val());
                    data.append('origen', $('#origen').val());
                    data.append('codigo_postal_origen', $('#codigo_postal_origen').val());
                    data.append('place_id_origen', $('#place_id_origen').val());
                    data.append('origenlat', $('#origenlat').val());
                    data.append('origenlng', $('#origenlng').val());
                    data.append('destino', $('#destino').val());
                    data.append('codigo_postal_destino', $('#codigo_postal_destino').val());
                    data.append('place_id_destino', $('#place_id_destino').val());
                    data.append('destinolat', $('#destinolat').val());
                    data.append('destinolng', $('#destinolng').val());
                    data.append('costo', $('#costo').val());
                    data.append('metodo_pago', $('#metodo_pago').val());
                    data.append('color', $('#color').val());
                    data.append('placas', $('#placas').val());
                    data.append('tipo', $('#tipo').val());
                    data.append('tipovehiculo', $('#tipovehiculo').val());
                    data.append('numeroeconomico', '');
                    data.append('maniobras', maniobras);
                    data.append('detalles_maniobras', $('#detalles_maniobras').val());
                    data.append('peso', $('#peso').val());
                    data.append('estado_sol', $('#estado_sol').val());
                    data.append('dia', $('#dia').val());
                    data.append('hora', $('#hora').val());
                    data.append('costomaniobras', $('#costomaniobras').val());
                    data.append('tarifario', $('#tarifario').val());
                    data.append('distancia', $('#distancia').val());
                    data.append('tiempo', $('#tiempo').val());
                    data.append('tiempo_valor', $('#tiempo_valor').val());
                    data.append('clave_servicio', $('#clave_servicio').val());
                    data.append('tipo_auxilio_vial', $('#tipo_auxilio_vial').val());
                    data.append('cantidad_litro', $('#cantidad_litro').val());
                    data.append('costo_gas', $('#costo_gas').val());
                    data.append('fecha_vigencia', $('#fecha_vigencia').val());
                    data.append('nombre_cliente', $('#nombre_cliente').val());
                    data.append('facturacion', facturacion);

                    // Consumimos el ajax
                    $.ajax({
                        url: 'createQuote.php',
                        method: 'POST',
                        data: data,
                        dataType: "json",
                        cache: false,
                        contentType: false,
                        processData: false,
                    }).done(function(respuesta) {

                        console.group(respuesta);

                        // Si los datos estan completos y se ejecuta bien el query
                        if (respuesta.status == 1) {

                            alert("Cotización guardada.");
                            location.reload();

                        } else if (respuesta.status == 2) { // Si estan incompletos los datos del formulario

                            alert("Complete todos los datos para poder continuar.");

                        } else if (respuesta.status == 3) { // Si falla el query o la conexión

                            alert("Algo fallo al guardar la cotización, por favor intente de nuevo.");
                            console.log("Error :" + respuesta.message);
                            location.reload();

                        } else if (respuesta.status == 4) { // Si estan incompletos los datos del formulario

                            alert("Complete todos los datos para poder continuar.");


                        }

                    }).fail(function(fail, message) {

                        alert("Algo fallo al guardar la cotización, por favor intente de nuevo.");
                        console.log("Error :" + fail);
                        location.reload();

                    });
                });

                /////////////////////// Agregado /////////////////////////////////////////

                $('#add_brand').click(function() {
                    // Almacanemos la marca
                    var marca_add = $('#marca_add').val();
                    // Validamos si contiene una marca
                    if (marca_add == '') {
                        // Agregamos el error
                        $('#marca_add_error').html('<div class="error_til">Por favor ingrese una marca</div>');
                        return;
                    }
                    // Anclamos el boton
                    $('#add_brand').prop('disabled', true);
                    // Remover cualquier tipo de errores
                    $('#marca_add_error').html('');
                    // Agregamos un cargador
                    $('#add_brand').html('<div class="lds-dual-ring"></div> Creando marca');
                    // Capturamos al proveedor
                    var proveedor = <?= $provid ?>;
                    // Enviamos los datos 
                    var data = new FormData();
                    data.append('marca', marca_add);
                    data.append('proveedor', proveedor);
                    // Consumimos el ajax
                    $.ajax({
                        url: 'createBrand.php',
                        method: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(respuesta) {
                            // Parseamos la respuesta
                            var response = JSON.parse(respuesta);
                            // Validamosla respuesta
                            if (response.success) {
                                // Mostramos mensaje
                                $('#marca_add_error').html('<div class="success_til">' + response.message + '</div>');
                                // Ejecutamos la funcion de adquirir nuevamente las marcas
                                GenerateBrands();
                            } else {
                                // Mostramos mensaje
                                $('#marca_add_error').html('<div class="error_til">' + response.message + '</div>');
                            }
                            // Removemos 
                            $('#add_brand').html('Creando marca');
                            // Anclamos el boton
                            $('#add_brand').prop('disabled', false);
                            // Removemos mensaje
                            $('#marca_add').val('');
                        }
                    })
                });

                function GenerateBrands() {
                    // Obtenemos el id del proveedor
                    var proveedor = <?= $provid ?>;
                    var data = new FormData();
                    // Añadimos el provedor
                    data.append('proveedor', proveedor);
                    // Consumimos el ajax
                    $.ajax({
                        url: 'showBrand.php',
                        method: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(respuesta) {
                            $('#marca').html(respuesta);
                            $('#marca_show').html(respuesta);
                        }
                    });
                }

                $('#add_model').click(function() {
                    // Almacanemos la marca
                    var marca_id = $('option:selected', '#marca_show').attr('marca_id');
                    // Almacenamos el tipo
                    var tipo = $('option:selected', '#marca_show').attr('tipado');
                    // Obtenemos el id del proveedor
                    var proveedor_id = <?= $provid ?>;
                    // Almacenamos el modelo
                    var modelo = $('#modelo_add').val();
                    // Validamos si contiene una marca
                    if (marca_id == '') {
                        // Agregamos el error
                        $('#modelo_add_error').html('<div class="error_til">Por favor selecione una marca</div>');
                        return;
                    }
                    // Validamos el modelo
                    if (modelo == '') {
                        // Agregamos el error
                        $('#modelo_add_error').html('<div class="error_til">Por favor ingrese un modelo</div>');
                        return;
                    }
                    // Anclamos el boton
                    $('#add_model').prop('disabled', true);
                    // Remover cualquier tipo de errores
                    $('#modelo_add_error').html('');
                    // Agregamos un cargador
                    $('#add_model').html('<div class="lds-dual-ring"></div> Creando modelo');
                    // Enviamos los datos 
                    var data = new FormData();
                    data.append('marca_id', marca_id);
                    data.append('tipo', tipo);
                    data.append('proveedor_id', proveedor_id);
                    data.append('modelo', modelo);
                    // Consumimos el ajax
                    $.ajax({
                        url: 'createModel.php',
                        method: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(respuesta) {
                            // Parseamos la respuesta
                            var response = JSON.parse(respuesta);
                            // Validamosla respuesta
                            if (response.success) {
                                // Mostramos mensaje
                                $('#modelo_add_error').html('<div class="success_til">' + response.message + '</div>');
                                // Ejecutamos la funcion de adquirir nuevamente las marcas
                                GenerateBrands();
                                $('#modelo').html('<option value="">Seleccionar...</option>');
                                $('#modelo').attr('disabled', true);
                            } else {
                                // Mostramos mensaje
                                $('#modelo_add_error').html('<div class="error_til">' + response.message + '</div>');
                            }
                            // Removemos 
                            $('#add_model').html('Crear marca');
                            // Anclamos el boton
                            $('#add_model').prop('disabled', false);
                            // Removemos mensaje
                            $('#modelo_add').val('');
                        }
                    })
                });
                

                // Detecta y calula los kilometros totales
                $('#kilometrosExcedidos').on('input', function(e) {

                    //console.log("Los km exedentes ingresados son:" + e.currentTarget.value);
                    
                    // Si se ingresan los km excedentes
                    if ($('#kilometrosExcedidos').val() != "") {
                        
                        // Se obtienen los km del recorrido
                        var kmRecorrido = $('#distancia').val();
                        
                        // Se eliminan los carateres alfabeticos y solo se dejan los numericos con punto decimal
                        kmRecorrido = kmRecorrido.replace(/^[0-9]+([.][0-9]+)?$/g, '');
                        // Se convierte en float el valor
                        kmRecorrido = parseFloat($('#distancia').val());
                        // Se hace la sumatoria de los kilometros del recorrido mas los excedentes
                        let kmTotales = kmRecorrido + parseFloat(e.currentTarget.value);
                        //console.log("Los km totales son:" + kmTotales + " km");
                        // Se agregan al input los km totales mostrando solo 2 decimales
                        $('#totalkm').val('' + kmTotales.toFixed(2) + ' km');
                        
                    } else { // Si no se ingreso nada
                        $('#totalkm').val($('#distancia').val());
                    }


                });

            </script>

        </div>
        <?php endif; ?>
    </div>
    <!-- content viewport ends -->
    <?php include('footer.php'); ?>
