<?php
    
    use Juan\Pruebas\mainModel;


class vigilanteController extends mainModel {

    public function registrarVigilanteControlador(array $data) {
        
        if ($data['REQUEST_METHOD'] != 'POST') {
            return json_encode([
                "titulo" => "Peticion incorrecta",
                "mensaje" => "Lo sentimos, la accion que intentas realizar no es correcta",
                "icono" => "error",
                "tipoMensaje" => "redireccionar"
            ]);
        }
 
        // Validación de la existencia de variables POST y que vengan vacías
        if (empty($data['tipo_documento']) || empty($data['num_identificacion']) || empty($data['nombres']) || 
            empty($data['apellidos']) || empty($data['telefono']) || empty($data['correo']) || 
            empty($data['rol_usuario'])) {
            return json_encode([
                "titulo" => "No estas mandando los datos completos"
                
            ]);
        }
 
        // Limpieza y validación de datos
        $tipodocumento = $this->limpiarDatos($data['tipo_documento']);
        $numero_documento = $this->limpiarDatos($data['num_identificacion']);
        $nombre = $this->limpiarDatos($data['nombres']);
        $apellido = $this->limpiarDatos($data['apellidos']);
        $telefono = $this->limpiarDatos($data['telefono']);
        $email = $this->limpiarDatos($data['correo']);
        $rol = $this->limpiarDatos($data['rol_usuario']);
        $tipo_vehiculo = $this->limpiarDatos($data['tipo_vehiculo_vigilante']);
        $placa_vehiculo = $this->limpiarDatos($data['placa_vehiculo_vigilante']); 
        $usuario = $_SESSION['datos_usuario']['num_identificacion'];
 
        // Validación de campos
        $campos_invalidos = [];
        if ($this->verificarDatos('[A-Za-z ]{2,64}', $nombre)) {
            $campos_invalidos[] = 'NOMBRE(S)';
        }
        if ($this->verificarDatos('[A-Z]{2}', $tipodocumento)) {
            $campos_invalidos[] = 'TIPO DE DOCUMENTO';
        }
        if ($this->verificarDatos('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', $email)) {
            $campos_invalidos[] = 'CORREO ELECTRONICO';
        }
        if ($this->verificarDatos('[A-Za-z ]{2,64}', $apellido)) {
            $campos_invalidos[] = 'APELLIDO(S)';
        }
        if ($this->verificarDatos('[0-9]{6,15}', $numero_documento)) {
            $campos_invalidos[] = 'NUMERO DE DOCUMENTO';
        }
        if ($this->verificarDatos('[0-9]{10}', $telefono)) {
            $campos_invalidos[] = 'TELEFONO';
        }
 
        // Manejo de vehículo (opcional)
        if (!empty($tipo_vehiculo) && !empty($placa_vehiculo)) {
            if ($this->verificarDatos('[A-Z]{2,}', $tipo_vehiculo)) {
                $campos_invalidos[] = 'TIPO DE VEHICULO';
            }
            if ($this->verificarDatos('[A-Z0-9]{6,7}', $placa_vehiculo)) {
                $campos_invalidos[] = 'PLACA DE VEHICULO';
            }
        } elseif (!empty($tipo_vehiculo) && empty($placa_vehiculo)) {
            return json_encode([
                "titulo" => "Campo incompleto",
                "mensaje" => "Lo sentimos, el campo de PLACA DE VEHICULO está incompleto.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        } elseif (empty($tipo_vehiculo) && !empty($placa_vehiculo)) {
            return json_encode([
                "titulo" => "Campo incompleto",
                "mensaje" => "Lo sentimos, el campo de TIPO DE VEHICULO está incompleto.",
                "icono" => "error",
                " tipoMensaje" => "normal"
            ]);
        }
 
        // Verificación de campos inválidos
        if (!empty($campos_invalidos)) {
            $invalidos = implode(", ", $campos_invalidos);
            return json_encode([
                "titulo" => "Campos incompletos",
                "mensaje" => "Lo sentimos, los campos " . $invalidos . " no cumplen con el formato solicitado.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        // Buscar si existe un vigilante con el mismo número de identificación
        $buscar_vigilante_query = "
            SELECT 'aprendices' AS tabla, a.num_identificacion, a.estado 
            FROM aprendices a 
            WHERE num_identificacion = '$numero_documento' AND a.estado = 'ACTIVO'
            UNION ALL
            SELECT 'funcionarios' AS tabla, fn.num_identificacion, fn.estado 
            FROM funcionarios fn
            WHERE num_identificacion = '$numero_documento' AND fn.estado = 'ACTIVO'
            UNION ALL
            SELECT 'visitantes' AS tabla, vs.num_identificacion, vs.estado 
            FROM visitantes vs 
            WHERE num_identificacion = '$numero_documento' AND vs.estado = 'ACTIVO'
            UNION ALL
            SELECT 'vigilantes' AS tabla, vi.num_identificacion, vi.estado 
            FROM vigilantes vi 
            WHERE num_identificacion = '$numero_documento';
        ";
        $buscar_vigilante = $this->ejecutarConsulta($buscar_vigilante_query);
 
        if ($buscar_vigilante == 'conexion-fallida') {
            return json_encode([
                "titulo" => "Error de Conexion",
                "mensaje" => "Lo sentimos, algo salió mal con la conexión, por favor intentalo de nuevo mas tarde.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        if ($buscar_vigilante->num_rows < 1) {
            // Registro del nuevo vigilante
            $fecha_hora_actual = date('Y-m-d H:i:s');
            $registrar_vigilantes_query = "INSERT INTO `vigilantes`(`tipo_documento`, `num_identificacion`, `nombres`, `apellidos`, `correo`, `telefono`, `estado`, `rol_usuario`, `permanencia`, `fecha_hora_registro`) VALUES ('$tipodocumento','$numero_documento','$nombre','$apellido','$email','$telefono','ACTIVO','$rol','FUERA','$fecha_hora_actual')";
 
            $registrar_vigilantes = $this->ejecutarConsulta($registrar_vigilantes_query);
            if (!$registrar_vigilantes) {
                return json_encode([
                    "titulo" => "Error al registrar",
                    "mensaje" => "Lo sentimos, algo salió mal con el registro, por favor intentalo de nuevo mas tarde.",
                    "icono" => "error",
                    "tipoMensaje" => "normal"
                ]);
            }
 
            // Manejo de vehículo
            if (!empty($tipo_vehiculo) && !empty($placa_vehiculo)) {
                $vehiculo_persona = $this->registrarNuevoVehiculo($placa_vehiculo, $tipo_vehiculo, $numero_documento, $usuario);
                if (!$vehiculo_persona) {
                    return json_encode([
                        "titulo" => "Informacion",
                        "mensaje" => "Genial, el vigilante ha sido registrado, pero el registro del vehículo no ha sido exitoso.",
                        "icono" => "info",
                        "tipoMensaje" => "normal"
                    ]);
                }
            }
            
            $mensaje = [
                "titulo" => "Vigilante registrado"
            ];
            return json_encode($mensaje);
          
        } else {
            // Manejo del caso en que ya existe un vigilante con el mismo número de identificación
            $datos_repetidos = $buscar_vigilante->fetch_all();
            foreach ($datos_repetidos as $datos) {
                if ($datos[0] != 'vigilantes') {
                    if ($datos[2] == 'ACTIVO' || $datos[2] == 'PERMANECE') {
                        $userSinS = rtrim($datos[0], 's');
                        return json_encode([
                            "titulo" => "Informacion",
                            "mensaje" => $nombre . " con número de documento " . $numero_documento . " ya se encuentra en nuestra base de datos como " . $userSinS . ".",
                            "icono" => "info",
                            "tipoMensaje" => "normal"
                        ]);
                    } else {
                        return json_encode([
                            "titulo" => "Pendiente",
                            "mensaje" => "Pendiente por programar",
                            "icono" => "info",
                            "tipoMensaje" => "normal"
                        ]);
                    }
                } else {
                    if ($datos[2] == 'ACTIVO' || $datos[2] == 'PERMANECE') {
                        return json_encode([
                            
                            "titulo" => "El Vigilante " . $nombre . " ya se encuentra en nuestra base de datos como vigilante."
                            
                        ]);
                    } elseif ($datos[2] == 'INACTIVO') {
                        return json_encode([
                            "titulo" => "Informacion",
                            "mensaje" => $nombre . " con número de documento " . $numero_documento . " ya se encuentra en nuestra base de datos inactivo por algún motivo. Si deseas cambiar su estado a activo, deberá hacerlo una persona autorizada desde el apartado de vigilantes INACTIVOS.",
                            "icono" => "info",
                            "tipoMensaje" => "normal"
                        ]);
                    }
                }
            }
        }
    }
 
    public function obtenerVigilanteController(array $data) {
        $num_identificacion = $this->limpiarDatos($data['numero_documento_vg']);
        $consulta_vigilante_sql = "SELECT * FROM vigilantes WHERE num_identificacion = '$num_identificacion';";
        $consulta_vigilante = $this->ejecutarConsulta($consulta_vigilante_sql);
 
        if (!$consulta_vigilante) {
            return json_encode([
                "titulo" => "Error de la conexion",
                "mensaje" => "Lo sentimos, ocurrió un error con la base de datos, intentalo de nuevo mas tarde",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        if ($consulta_vigilante->num_rows < 1) {
            return json_encode([
                "titulo" => "Error de la conexion",
                "mensaje" => "Lo sentimos, el funcionario que intentaste editar no existe en la base de datos",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        return $consulta_vigilante->fetch_assoc();
    }
 
    public function editarVigilanteController(array $data) {
        if ($data['REQUEST_METHOD'] != 'POST') {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Solicitud denegada, intente de nuevo más tarde.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        if (empty($data['num_identificacion']) || empty($data['nombres']) || empty($data['apellidos']) || 
            empty($data['telefono']) || empty($data['correo']) || empty($data['rol_usuario']) || 
            empty($data['credencial'])) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Lo sentimos, ha ocurrido un error con alguno de los datos, intentalo de nuevo mas tarde.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        $numero_documento = $this->limpiarDatos($data['num_identificacion']);
        $nombre = $this->limpiarDatos($data['nombres']);
        $apellido = $this->limpiarDatos($data['apellidos']);
        $telefono = $this->limpiarDatos($data['telefono']);
        $email = $this->limpiarDatos($data['correo']);
        $rol = $this->limpiarDatos($data['rol_usuario']);
        $credencial = $this->limpiarDatos($data['credencial']);
 
        if ($this->verificarDatos('[0-9a-zA-Z]{6,16}', $credencial)) {
            return json_encode([
                "titulo" => "Credenciales ingresadas no validas"
            ]);
        }
 
        // Verificaciones de datos
        if ($this->verificarDatos('[A-Za-z ]{2,64}', $nombre) || 
            $this->verificarDatos('[A-Za-z ]{2,64}', $apellido) || 
            $this->verificarDatos('[0-9]{6,13}', $numero_documento) || 
            $this->verificarDatos('[a-zA ```php
            -Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', $email) || 
            $this->verificarDatos('\+?[0-9]{10,14}', $telefono)) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Datos ingresados no válidos",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        // Actualización de datos
        $consulta_actualizar_sql = "UPDATE vigilantes SET nombres = '$nombre', apellidos = '$apellido', correo = '$email', telefono = '$telefono', rol_usuario = '$rol'";
 
        if (!empty($credencial)) {
            $consulta_actualizar_sql .= ", credencial = MD5('$credencial')";
        }
 
        $consulta_actualizar_sql .= " WHERE num_identificacion = '$numero_documento'";
        $consulta_actualizar_vigilante = $this->ejecutarConsulta($consulta_actualizar_sql);
 
        if ($consulta_actualizar_vigilante != 1) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Ha ocurrido un error a la hora de actualizar el vigilante",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
 
        return json_encode([
            "titulo" => "Vigilante Actualizado"
        ]);
    }

}

class funcionarioController extends mainModel {

    function registrarFuncionarioControler(array $data){
        if ($data['REQUEST_METHOD'] != 'POST') {
            $mensaje = [
                "titulo" => "Peticion incorrecta",
                "mensaje" => "Lo sentimos, la accion que intentas realizar no es correcta",
                "icono" => "error",
                "tipoMensaje" => "redireccionar"
               
            ];
            return json_encode($mensaje);
        } else {
            $data = $data; // Recoger datos en una sola variable para facilidad
            $error = []; // Array para almacenar errores
        
            // Validar campos requeridos con empty
            $requiredFields = ['num_documento_funcionario', 'nombres_funcionarios', 'tipo_doc_funcionario', 'cargo_funcionario', 'correo_funcionario', 'apellidos_funcionarios', 'tipo_contrato_funcionario', 'telefono_funcionario'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    array_push($error, $field);
                }
            }
        
            // Validar número de documento
            if (!empty($data['num_documento_funcionario']) && 
                (strlen($data['num_documento_funcionario']) < 6 || strlen($data['num_documento_funcionario']) > 15 || !is_numeric($data['num_documento_funcionario']))) {
                array_push($error, 'numero de documento');
            }
        
            // Validar nombres
            if (!empty($data['nombres_funcionarios']) && $this->verificarDatos('[a-zA-Z\s]{2,64}', $data['nombres_funcionarios'])) {
                array_push($error, 'nombres');
            }
        
            // Validar correo
            if (!empty($data['correo_funcionario']) && 
                (strlen($data['correo_funcionario']) < 8 || strlen($data['correo_funcionario']) > 64 || $this->verificarDatos("[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}", $data['correo_funcionario']))) {
                array_push($error, 'correo');
            }
        
            // Validar apellidos
            if (!empty($data['apellidos_funcionarios']) && $this->verificarDatos('[a-zA-Z\s]{2,64}', $data['apellidos_funcionarios'])) {
                array_push($error, 'apellidos');
            }
        
            // Validar teléfono
            if (!empty($data['telefono_funcionario']) && 
                (strlen($data['telefono_funcionario']) < 10 || strlen($data['telefono_funcionario']) > 14)) {
                array_push($error, 'telefono');
            }
        
            // Validar fecha de finalización si es contrato temporal
            if (!empty($data['tipo_contrato_funcionario']) && $data['tipo_contrato_funcionario'] == "CT") {
                $fecha_actual = date('Y-m-d');
                $fecha_maxima = date('Y-m-d', strtotime('+5 years'));
                if (empty($data['fecha_finalizacion_contrato']) || 
                    $data['fecha_finalizacion_contrato'] < $fecha_actual || 
                    $data['fecha_finalizacion_contrato'] > $fecha_maxima) {
                    array_push($error, 'fecha de finalizacion');
                }
            }
        
            // Validar vehículo
            if (!empty($data['tipo_vehiculo_funcionario']) || !empty($data['placa_vehiculo_funcionario'])) {
                if (empty($data['tipo_vehiculo_funcionario']) || empty($data['placa_vehiculo_funcionario'])) {
                    array_push($error, 'vehiculo incompleto');
                }
                if (!empty($data['placa_vehiculo_funcionario']) && 
                    (strlen($data['placa_vehiculo_funcionario']) < 3 || strlen($data['placa_vehiculo_funcionario']) > 6)) {
                    array_push($error, 'placa');
                }
            }
        
            // Validar credenciales para ciertos cargos
            if (!empty($data['cargo_funcionario']) && 
                ($data['cargo_funcionario'] == "CO" || $data['cargo_funcionario'] == "SB") && 
                (empty($data['credenciales_funcionario']) || 
                 strlen($data['credenciales_funcionario']) < 6 || strlen($data['credenciales_funcionario']) > 16)) {
                array_push($error, 'credenciales');
            }
        
            // Si hay errores, devolver mensaje con los campos que fallaron
            if (!empty($error)) {
                $mensaje = [
                    "titulo" => "Lo sentimos, los campos no cumplen con los requisitos"
                
                ];
                return json_encode($mensaje);
            }
        
            // Validar que el funcionario no esté en otras tablas
            $num_documento_funcionario = trim($data['num_documento_funcionario']);
            $sentencia_verificar_estado = "SELECT 'aprendices' AS tabla, num_identificacion, estado 
                FROM aprendices 
                WHERE num_identificacion = '$num_documento_funcionario' 
                UNION ALL
                SELECT 'vigilantes' AS tabla, num_identificacion, estado
                FROM vigilantes 
                WHERE num_identificacion = '$num_documento_funcionario' 
                UNION ALL
                SELECT 'visitantes' AS tabla, num_identificacion, estado 
                FROM visitantes 
                WHERE num_identificacion = '$num_documento_funcionario' 
                UNION ALL
                SELECT 'funcionarios' AS tabla, num_identificacion, estado 
                FROM funcionarios 
                WHERE num_identificacion = '$num_documento_funcionario';";
        
            $buscar_usuario_tabla = $this->ejecutarConsulta($sentencia_verificar_estado);
            unset($sentencia_verificar_estado);
        
            if ($buscar_usuario_tabla == 'conexion-fallida') {
                $mensaje = [
                    "titulo" => "Error de Conexion",
                    "mensaje" => "Lo sentimos, parece que ha ocurrido un error de conexion a la base de datos. Intentelo mas tarde",
                    "icono" => "error",
                    "tipoMensaje" => "normal"
                ];
                return json_encode($mensaje);
            } else {
                if ($buscar_usuario_tabla->num_rows > 0) {
                    $datos_completos = $buscar_usuario_tabla->fetch_all(MYSQLI_ASSOC);
                    foreach ($datos_completos as $dato) {
                        if ($dato['tabla'] != 'visitantes') {
                            if ($dato['estado'] == 'ACTIVO') {
                                $mensaje_otra_tabla = [
                                    "titulo" => "Este usuario ya se encuentra en la base de datos con un estado activo"
                                   
                                ];
                                return json_encode($mensaje_otra_tabla);
                            } else {
                                $sentencia_estado_visitante = "DELETE FROM " . $dato['tabla'] . " WHERE `num_identificacion` = '$num_documento_funcionario'";
                                $estado_visitante = $this->ejecutarConsulta($sentencia_estado_visitante);
                                if ($estado_visitante != 1) {
                                    $mensaje = [
                                        "titulo" => "Error de Conexion",
                                        "mensaje" => "Lo sentimos, parece que ha ocurrido un error al intentar borrar el funcionario del grupo " . $dato['tabla'] . ". Intentelo mas tarde",
                                        "icono" => "error",
                                        "tipoMensaje" => "normal"
                                    ];
                                    return json_encode($mensaje);
                                }
                            }
                        }
                    }
                }
            }
        
            // Preparar datos para insertar
            $nombres_funcionarios = ucwords(strtolower(trim($data['nombres_funcionarios'])));
            $tipo_doc_funcionario = $data['tipo_doc_funcionario'];
            $cargo_funcionario = $data['cargo_funcionario'];
            $correo_funcionario = trim($data['correo_funcionario']);
            $apellidos_funcionarios = ucwords(strtolower(trim($data['apellidos_funcionarios'])));
            $tipo_contrato_funcionario = $data['tipo_contrato_funcionario'];
            $telefono_funcionario = trim($data['telefono_funcionario']);
            $usuario = $data['num_id_usuario_que_registra'];
        
            $fecha_finalizacion_contrato = $tipo_contrato_funcionario == "CT" ? "'" . date('Y-m-d', strtotime($data['fecha_finalizacion_contrato'])) . "'" : "NULL";
            $credenciales_funcionario = ($cargo_funcionario == "CO" || $cargo_funcionario == "SB") ? MD5(trim($data['credenciales_funcionario'])) : "NULL";
        
            $sentencia = "INSERT INTO `funcionarios`(`tipo_documento`, `num_identificacion`, `nombres`, `apellidos`, `correo`, `telefono`, `tipo_contrato`, `rol_usuario`, `estado`, `fecha_hora_ultimo_ingreso`, `permanencia`, `fecha_hora_registro`, `num_id_usuario_que_registra`, `fecha_finalizacion_contrato`, `credencial`) 
                VALUES ('$tipo_doc_funcionario', '$num_documento_funcionario', '$nombres_funcionarios', '$apellidos_funcionarios', '$correo_funcionario', '$telefono_funcionario', '$tipo_contrato_funcionario', '$cargo_funcionario', 'ACTIVO', '', 'FUERA', NOW(), '$usuario', $fecha_finalizacion_contrato, '$credenciales_funcionario');";
        
            $insertar_usuario = $this->ejecutarConsulta($sentencia);
        
            unset($sentencia);
        
            if (!$insertar_usuario) {
                // Manejo de error en caso de que el registro del usuario falle
                $mensaje = [
                    "titulo" => "Error de Conexion",
                    "mensaje" => $tipo_doc_funcionario.' '.$credenciales_funcionario,
                    "icono" => "error",
                    "tipoMensaje" => "normal"
                ];
                return json_encode($mensaje);
            } else {
                // Registro de vehículos, solo si el registro del funcionario es exitoso
                if (!empty($data['tipo_vehiculo_funcionario']) && !empty($data['placa_vehiculo_funcionario'])) {
                    $tipo_vehiculo_funcionario = $data['tipo_vehiculo_funcionario'];
                    $placa_vehiculo_funcionario = trim($data['placa_vehiculo_funcionario']);
            
                    $registrar_vehiculo = $this->registrarNuevoVehiculo(
                        $placa_vehiculo_funcionario,
                        $tipo_vehiculo_funcionario,
                        $num_documento_funcionario,
                        $usuario
                    );
            
                    // Verificación del registro del vehículo
                    if ($registrar_vehiculo === false) {
                        $mensaje = [
                            "titulo" => "Error de Conexion",
                            "mensaje" => "Ocurrio un error al intentar registrar el vehiculo. Por favor, intentelo mas tarde.",
                            "icono" => "error",
                            "tipoMensaje" => "normal"
                        ];
                        return json_encode($mensaje);
                    } elseif ($registrar_vehiculo === "") {
                        // En caso de que la función `registrarNuevoVehiculo` retorne una cadena vacía
                        $mensaje = [
                            "titulo" => "Advertencia",
                            "mensaje" => "No se pudo registrar el vehículo. Por favor, revise los datos e inténtelo de nuevo.",
                            "icono" => "warning",
                            "tipoMensaje" => "normal"
                        ];
                        return json_encode($mensaje);
                    }
                }
            
                // Mensaje de éxito para el registro del funcionario
                $mensaje = [
                    "titulo" => "FUN registrado"
                ];
                return json_encode($mensaje);
            }
            
                 
        }
        }



    public function obtenerFuncionarioController(array $data) {
        $num_identificacion = $this->limpiarDatos($data['num_documento_funcionario']);
        $consulta_funcionario_sql = "SELECT * FROM funcionarios WHERE num_identificacion = '$num_identificacion';";
        $consulta_funcionario = $this->ejecutarConsulta($consulta_funcionario_sql);
    
        if (!$consulta_funcionario) {
            return json_encode([
                "titulo" => "Error de la conexión",
                "mensaje" => "Lo sentimos, ocurrió un error con la base de datosSS. Inténtalo de nuevo más tarde.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
    
        if ($consulta_funcionario->num_rows < 1) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Lo sentimos, el funcionario que intentaste editar no existe en la base de datos.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
    
        return $consulta_funcionario->fetch_assoc();
    }
    
    public function editarFuncionarioController(array $data) {
        if ($data['REQUEST_METHOD'] != 'POST') {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Solicitud denegada. Intente de nuevo mas tarde.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
    
        if (empty($data['num_documento_funcionario']) || empty($data['nombres_funcionario']) || empty($data['apellidos_funcionario']) || 
            empty($data['telefono_funcionario']) || empty($data['correo_funcionario']) || empty($data['cargo_funcionario']) || 
            empty($data['tipo_contrato_funcionario'])) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Lo sentimos, ha ocurrido un error con alguno de los datos, intentalo de nuevo mas tarde.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
    
        // Limpieza de datos
        $num_identificacion = $this->limpiarDatos($data['num_documento_funcionario']);
        $nombre = $this->limpiarDatos($data['nombres_funcionario']);
        $apellidos = $this->limpiarDatos($data['apellidos_funcionario']);
        $telefono = $this->limpiarDatos($data['telefono_funcionario']);
        $correo = $this->limpiarDatos($data['correo_funcionario']);
        $cargo = $this->limpiarDatos($data['cargo_funcionario']);
        $tipo_contrato = $this->limpiarDatos($data['tipo_contrato_funcionario']);
    
        $fecha_finalizacion = "NULL";
        if (!empty($data['fecha_finalizacion_contrato']) && $tipo_contrato == "CT") {
            $fecha_finalizacion = $this->limpiarDatos($data['fecha_finalizacion_contrato']);
            $fecha_finalizacion = "'" . date('Y-m-d H:i:s', strtotime($fecha_finalizacion)) . "'";
        }
    
        $credencial = null;
        if (!empty($data['credenciales_funcionario'])) {
            $credencial = $this->limpiarDatos($data['credenciales_funcionario']);
            if ($this->verificarDatos('[0-9a-zA-Z]{6,16}', $credencial)) {
                return json_encode([
                    "titulo" => "Credenciales ingresadas no validas."
                  
                ]);
            }
        }
    
        // Validaciones adicionales
        if ($this->verificarDatos('[a-zA-Z\s]{2,64}', $nombre) || 
            $this->verificarDatos('[a-zA-Z\s]{2,64}', $apellidos) || 
            $this->verificarDatos('[0-9]{6,13}', $num_identificacion) || 
            $this->verificarDatos('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', $correo) || 
            $this->verificarDatos('\+?[0-9]{10,14}', $telefono)) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Datos ingresados no válidos.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
    
        // Construcción de la consulta de actualización
        $consulta_actualizar_sql = "UPDATE funcionarios SET nombres = '$nombre', apellidos = '$apellidos', correo = '$correo', ";
        $consulta_actualizar_sql .= "telefono = '$telefono', rol_usuario = '$cargo', tipo_contrato = '$tipo_contrato', fecha_finalizacion_contrato = $fecha_finalizacion";
    
        if ($credencial) {
            $consulta_actualizar_sql .= ", credencial = MD5('$credencial')";
        }
    
        $consulta_actualizar_sql .= " WHERE num_identificacion = '$num_identificacion'";
    
        $consulta_actualizar_funcionario = $this->ejecutarConsulta($consulta_actualizar_sql);
    
        if ($consulta_actualizar_funcionario != 1) {
            return json_encode([
                "titulo" => "Error",
                "mensaje" => "Ha ocurrido un error al actualizar el funcionario.",
                "icono" => "error",
                "tipoMensaje" => "normal"
            ]);
        }
    
        return json_encode([
            "titulo" => "Funcionario Actualizado",
            "mensaje" => "El funcionario se actualizo correctamente en la base de datos.",
            "icono" => "success",
            "tipoMensaje" => "normal"
        ]);
    }
    

}


 




    
    





    
	 