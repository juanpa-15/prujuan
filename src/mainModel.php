<?php
	namespace Juan\Pruebas;
	use \mysqli;

	if(file_exists(__DIR__."/../../config/server.php")){
		require_once __DIR__."/../../config/server.php";
	}


	class mainModel{
		protected function conectar(){
			mysqli_report(MYSQLI_REPORT_OFF);
			
			try {
				$enlace_conexion = new mysqli("localhost", "root", "", "cerberus_bdd");
				//  $enlace_conexion = new mysqli("localhost", "arcanoposada_adsob", "CDqaQeehjt9y", "arcanoposada_cerbeb"); 
			
				if ($enlace_conexion->connect_error) {
					throw new \Exception("No se realizó la conexión: " . $enlace_conexion->connect_error);
				}else {
					return $enlace_conexion;
				} 
			} catch (\Exception $e) {
				unset($enlace_conexion);
				return false; 
			}
		}
		protected function registrarNuevoVehiculo($placa, $tipo_vehiculo, $num_doc_propietario, $registrador_user){
			$query_buscar_propietario = "SELECT `placa_vehiculo`, `num_identificacion_persona` FROM vehiculos_personas WHERE `placa_vehiculo` = '$placa' AND `num_identificacion_persona` = '$num_doc_propietario'";

			$busqueda_propietario = $this->ejecutarConsulta($query_buscar_propietario);

			if (!$busqueda_propietario) {
				return false;
			}else {
				if ($busqueda_propietario->num_rows < 1) {
					$query_contador_vehiculos = "SELECT num_identificacion_persona FROM vehiculos_personas WHERE num_identificacion_persona = '$num_doc_propietario'";
					$contador_vehiculos = $this->ejecutarConsulta($query_contador_vehiculos);
					if (!$contador_vehiculos) {
						return false;
					}else{
						if ($contador_vehiculos->num_rows >= 5) {
							$mensaje=[
								"titulo"=>"Cantidad de vehiculos",
								"mensaje"=>"Lo sentimos la persona a la que le intentas registrar este vehiculo excedio su limite permitido de 5 vehiculos.",
								"icono"=> "info",
								"url"=> "http://localhost/Adso04/PROYECTOS/cerberus/lista_vehiculos/",
								"tipoMensaje"=>"redireccionar"
							];
							return $mensaje;
							
						}else {
							$fecha_hora_actual = date('Y-m-d H:i:s');
							$query_asociar_vehiculo = "INSERT INTO `vehiculos_personas`(`placa_vehiculo`, `tipo_vehiculo`, `num_identificacion_persona`, `permanencia`, `fecha_registro`, `num_id_usuario_que_registra`) VALUES ('$placa','$tipo_vehiculo','$num_doc_propietario','FUERA','$fecha_hora_actual','$registrador_user')";
		
							$asociar_vehiculo = $this->ejecutarConsulta($query_asociar_vehiculo);
							if (!$asociar_vehiculo) {
								$mensaje=[
									"titulo"=>"Error",
									"mensaje"=>"Lo sentimos, no nos pudimos conectar a la base de datos intentalo de nuevo mas tarde.",
									"icono"=> "error",
									"tipoMensaje"=>"normal"
								];
								return $mensaje;
							}else {
								$mensaje=[
									"titulo"=>"Vehiculo Asociado",
									"mensaje"=>"El vehiculo con placas ".$placa." ha sido registrado con exito.",
									"icono"=> "success",
									"tipoMensaje"=>"normal"
								];
								return $mensaje;
							}
						}
					}
				}else {
					$mensaje=[
						"titulo"=>"Ya eres propietario",
						"mensaje"=>"Tal pararece que la persona a la que se ententa asociar el vehiculo con placas ".$placa." ya lo tiene asociado.",
						"icono"=> "info",
						"tipoMensaje"=>"normal"
					];
					return $mensaje;
				}
			}



		}

		function consultarDatosUsuario($num_documento_usuario,$campos){
            $campos_seleccionados = implode(", ", $campos);
            $sentencia_aprendiz = "SELECT $campos_seleccionados FROM `aprendices` WHERE `num_identificacion` = '$num_documento_usuario' AND `estado` = 'ACTIVO'";
            $buscar_aprendiz = $this->ejecutarConsulta($sentencia_aprendiz);
            unset($sentencia_aprendiz);
            if (!$buscar_aprendiz) {
                unset($num_documento_usuario);
                return ["error_conexion","aprendices"];
            }else {
                if ($buscar_aprendiz->num_rows > 0) {
                    $dato_permanencia = $buscar_aprendiz->fetch_assoc();
                    $datos_aprendiz = ["aprendices","AP",$dato_permanencia];
                    $buscar_aprendiz->free();
                    unset($buscar_aprendiz, $dato_permanencia,$num_documento_usuario);
                    return $datos_aprendiz;
                } else {
                    $sentencia_funcionario = "SELECT $campos_seleccionados FROM `funcionarios` WHERE `num_identificacion` = '$num_documento_usuario' AND `estado` = 'ACTIVO'";
                    $buscar_funcionario = $this->ejecutarConsulta($sentencia_funcionario);
                    unset($sentencia_funcionario);
                    if (!$buscar_funcionario) {
                        unset($num_documento_usuario);
                        return ["error_conexion","funcionarios"];
                    }else {
                        if ($buscar_funcionario->num_rows > 0) {
                            $dato_permanencia = $buscar_funcionario->fetch_assoc();
                            $datos_funcionario = ["funcionarios","FU",$dato_permanencia];
                            $buscar_funcionario->free();
                            unset($buscar_funcionario, $dato_permanencia, $num_documento_usuario);
                            return $datos_funcionario;
                        } else {
                            $sentencia_vigilante = "SELECT $campos_seleccionados FROM `vigilantes` WHERE `num_identificacion` = '$num_documento_usuario' AND `estado` = 'ACTIVO'";
                            $buscar_vigilante = $this->ejecutarConsulta($sentencia_vigilante);
                            unset($sentencia_vigilante);
                            if (!$buscar_vigilante) {
                                unset($num_documento_usuario);
                                return ["error_conexion","vigilantes"];
                            }else {
                                if ($buscar_vigilante->num_rows > 0) {
                                    $dato_permanencia = $buscar_vigilante->fetch_assoc();
                                    $datos_vigilante = ["vigilantes","VI",$dato_permanencia];
                                    $buscar_vigilante->free();
                                    unset($buscar_vigilante, $dato_permanencia, $num_documento_usuario);
                                    return $datos_vigilante;
                                } else {
                                    $sentencia_visitante = "SELECT $campos_seleccionados FROM `visitantes` WHERE `num_identificacion` = '$num_documento_usuario' AND `estado` = 'ACTIVO'";
                                    $buscar_visitante = $this->ejecutarConsulta($sentencia_visitante);
                                    unset($sentencia_visitante);
                                    if (!$buscar_visitante) {
                                        unset($num_documento_usuario);
                                        return ["error_conexion","visitantes"];
                                    }else {
                                        if ($buscar_visitante->num_rows > 0) {
                                            $dato_permanencia = $buscar_visitante->fetch_assoc();
                                            $datos_visitante = ["visitantes","VS",$dato_permanencia];
                                            $buscar_visitante->free();
                                            unset($buscar_visitante, $dato_permanencia, $num_documento_usuario);
                                            return $datos_visitante;
                                        } else {
                                            unset($num_documento_usuario);
                                            return ["no_encontrado"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
		protected function ejecutarConsulta($consulta){
			$conexion = $this->conectar();
			if (!$conexion) {
				return false;
			}else {
				$sql=$conexion->query($consulta);
				$conexion->close();
				return $sql;
			}
		}
		protected function ejecutarInsert($consulta) {
			$conexion = $this->conectar(); 
			if ($conexion == 'conexion-fallida') {
				return 'conexion-fallida';
			} else {
				try {
					if ($conexion->query($consulta) === TRUE) {
						return true; 
					} else {
						throw new \Exception("Error en el INSERT: " . $conexion->error);
					}
				} catch (\Exception $e) {
					return "Ocurrió un error: " . $e->getMessage();
				} finally {
					$conexion->close();
				}
			}
		}
		
		public function limpiarDatos($dato){

			$palabras=["<script>","</script>","<script src","<script type=","SELECT * FROM","SELECT "," SELECT ","DELETE FROM","INSERT INTO","DROP TABLE","DROP DATABASE","TRUNCATE TABLE","SHOW TABLES","SHOW DATABASES","<?php","?>","--","^","<",">","==",";","::"];


			$dato=trim($dato);
			$dato=stripslashes($dato);

			foreach($palabras as $palabra){
				$dato=str_ireplace($palabra, "", $dato);
			}

			$dato=trim($dato);
			$dato=stripslashes($dato);

			return $dato;
		}

		protected function verificarDatos($filtro,$cadena){
			if(preg_match("/^".$filtro."$/", $cadena)){
				return false;
            }else{
                return true;
            }
		}

		protected function cambioVisitante($num_identificacion, $rol) {
			
			$num_identidad = $this->limpiarDatos($num_identificacion);
			$tabla_origen = "";
		
			
			switch($rol) {
				case "FN":
					$tabla_origen = "funcionarios";
					break;
				case "VG":
					$tabla_origen = "vigilantes";
					break;
				case "AP":
					$tabla_origen = "aprendices";
					break;
				default:
					return "Rol no válido";
			}
		
			
			$consulta_buscar = "SELECT * FROM $tabla_origen WHERE num_identificacion = '$num_identidad'";
			$resultado = $this->ejecutarConsulta($consulta_buscar);
		
			if ($resultado->num_rows > 0) {
				
				$fila = $resultado->fetch_assoc();
		
				
				$consulta_insertar = "INSERT INTO `visitantes`( `tipo_documento`, `num_identificacion`, `nombres`, `apellidos`, `correo`, `telefono`, `estado`, `fecha_hora_ultimo_ingreso`, `permanencia`, `fecha_hora_registro`) VALUES ('".$fila['tipo_documento']."','".$fila['num_identificacion']."','".$fila['nombres']."','".$fila['apellidos']."','".$fila['correo']."','".$fila['telefono']."','".$fila['estado']."','".$fila['fecha_hora_ultimo_ingreso']."','".$fila['permanencia']."','".$fila['fecha_hora_registro']."')";

			
				
				$ejecutar = $this->ejecutarInsert($consulta_insertar);

				if($ejecutar == false){
					$mensaje=[
						"titulo"=>"Error",
						"mensaje"=>"Lo sentimos, algo salio mal con el registro por favor intentalo de nuevo mas tarde, si el error persiste comunicate con un asesor.",
						"icono"=> "error",
						"tipoMensaje"=>"normal"
					];
					echo json_encode($mensaje);
					exit();
				} else{
					$consulta_eliminar = "DELETE FROM $tabla_origen WHERE num_identificacion = '$num_identidad'";
					$this->ejecutarConsulta($consulta_eliminar);
					$mensaje=[
						"titulo"=>"Informacion",
						"mensaje"=>"Accion realizada correctamente.",
						"icono"=> "info",
						"tipoMensaje"=>"normal"
					];
					echo json_encode($mensaje);
					exit();
				}
				return "Registro movido exitosamente.";
			} else {
				return "No se encontró el registro en la tabla de $rol.";
			} 
		}
		public function contador() {

			$totales = [
				'aprendices' => 0,
				'funcionarios' => 0,
				'vigilantes' => 0,
				'visitantes' => 0,
				'vehiculos_personas' => 0
			];
		
			$tablas = [
				'aprendices',
				'funcionarios',
				'vigilantes',
				'visitantes',
				'vehiculos_personas'
			];
		
			$total_general = 0;
			$total_personas = 0;
			for ($i = 0; $i < count($totales); $i++) { 
				if ($tablas[$i] == 'vehiculos_personas') {
					$sentencia = "SELECT COUNT(num_identificacion_persona) AS contador FROM vehiculos_personas WHERE permanencia = 'DENTRO'";
					$contador = $this->ejecutarConsulta($sentencia);
					$contador_resultado = $contador->fetch_assoc();
					$totales['vehiculos_personas'] = $contador_resultado['contador'];
		
				} else {
					$sentencia = "SELECT COUNT(num_identificacion) AS contador FROM $tablas[$i] WHERE permanencia = 'DENTRO'";
					$contador = $this->ejecutarConsulta($sentencia);
					$contador_resultado = $contador->fetch_assoc();
					$totales[$tablas[$i]] = $contador_resultado['contador'];
					$total_personas += $totales[$tablas[$i]];
				}
				
				$total_general += $totales[$tablas[$i]];
				
			}
		
			
			$porcentajes = [];
			foreach ($totales as $tipo => $cantidad) {
				$porcentajes[$tipo] = $total_general > 0 ? ($cantidad / $total_personas) * 100 : 0;
				if ($tipo == 'vehiculos_personas') {
					$porcentajes[$tipo] = $total_general > 0 ? ($total_personas / $total_general) * 100 : 0;
				}
			}
		
			return [
				'conteo' => $totales,
				'porcentajes' => $porcentajes,
				'total' => $total_general,
				'personas' => $total_personas
			];
		}
	}
