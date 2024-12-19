<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../src/vigilantesController.php';



class vigilanteTest extends TestCase {
    private $dbMock;

    protected function setUp(): void
    {
        // Crear un mock de la conexión a la base de datos
        $this->dbMock = $this->createMock(PDO::class);
    }

     // Registramos un vigilante nuevo(sin el post)
     public function testProcesarFormularioDatosValidosvigi() 
     {
         $miClase = new vigilanteController($this->dbMock);
 
         
         $postData = [
             
             'tipo_documento' => 'CC',
             'num_identificacion' => '1112149201',
             'nombres' => 'juan pablo',
             'apellidos' => 'osorio',
             'telefono' => '3023646789',
             'correo' => 'osoriogal@gmail.com',
             'rol_usuario' => 'jv',
             'tipo_vehiculo_vigilante' => 'MT',
             'placa_vehiculo_vigilante' => 'CAB879',
             
         ];
 
        
         $resultado = $miClase->registrarVigilanteControlador($postData);
 
         $this->assertEquals(
             '{"titulo":"Peticion incorrecta","mensaje":"Lo sentimos, la accion que intentas realizar no es correcta","icono":"error","tipoMensaje":"redireccionar"}',
             $resultado
         );
     }

    // Registramos un vigilante nuevo
    public function testProcesarFormularioDatosValidos() 
    {
        $miClase = new vigilanteController($this->dbMock);

        
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_documento' => 'CC',
            'num_identificacion' => '11129219990',
            'nombres' => 'daniela',
            'apellidos' => 'quintero',
            'telefono' => '3023646789',
            'correo' => 'osoriogal@gmail.com',
            'rol_usuario' => 'jv',
            'tipo_vehiculo_vigilante' => 'MT',
            'placa_vehiculo_vigilante' => 'CAB879',
            
        ];

       
        $resultado = $miClase->registrarVigilanteControlador($postData);

        $this->assertEquals(
            '{"titulo":"Vigilante registrado"}',
            $resultado
        );
    }


     // Registramos un vigilante(no cumple con la cedula)
     public function testProcesarFormularioDatosValidoscedula() 
     {
         $miClase = new vigilanteController($this->dbMock);
 
         
         $postData = [
             'REQUEST_METHOD' => 'POST',
             'tipo_documento' => 'CC',
             'num_identificacion' => '222222222224398358938958493',
             'nombres' => 'juan pablo',
             'apellidos' => 'osorio',
             'telefono' => '3023646789',
             'correo' => 'osoriogal@gmail.com',
             'rol_usuario' => 'jv',
             'tipo_vehiculo_vigilante' => 'MT',
             'placa_vehiculo_vigilante' => 'CAB879',
             
         ];
 
        
         $resultado = $miClase->registrarVigilanteControlador($postData);
 
         $this->assertEquals(
             '{"titulo":"Campos incompletos","mensaje":"Lo sentimos, los campos NUMERO DE DOCUMENTO no cumplen con el formato solicitado.","icono":"error","tipoMensaje":"normal"}',
             $resultado
         );
     }


    // Vigilante que ya esta en la base de datos 
    public function testProcesarFormularioDatosValidosV() 
    {
        $miClase = new vigilanteController($this->dbMock);

        
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_documento' => 'CC',
            'num_identificacion' => '1112149555000',
            'nombres' => 'juan pablo',
            'apellidos' => 'osorio',
            'telefono' => '3023646789',
            'correo' => 'osoriogal@gmail.com',
            'rol_usuario' => 'jv',
            'tipo_vehiculo_vigilante' => 'MT',
            'placa_vehiculo_vigilante' => 'CAB879',
            
        ];

       
        $resultado = $miClase->registrarVigilanteControlador($postData);

        $this->assertEquals(
            '{"titulo":"El Vigilante juan pablo ya se encuentra en nuestra base de datos como vigilante."}',
            $resultado
        );
    }

    // Prueba de datos incompletos
    public function testProcesarFormularioDatosValidosVi() 
    {
        $miClase = new vigilanteController($this->dbMock);

        
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_documento' => 'CC',
            'num_identificacion' => '1112149555000',
            'nombres' => '',
            'apellidos' => 'osorio',
            'telefono' => '3023646789',
            'correo' => 'osoriogal@gmail.com',
            'rol_usuario' => 'jv',
            'tipo_vehiculo_vigilante' => 'MT',
            'placa_vehiculo_vigilante' => 'CAB879',
            
        ];

       
        $resultado = $miClase->registrarVigilanteControlador($postData);

        $this->assertEquals(
            '{"titulo":"No estas mandando los datos completos"}',
            $resultado
        );
    }

    

    
    //Editar un vigilante exitoso
    public function testEditarVigilanteControllerDatosValidos() 
    {
        $miClase = new vigilanteController($this->dbMock);

        $postData = [
            'REQUEST_METHOD' => 'POST',
            'num_identificacion' => '1112149201',
            'nombres' => 'kevin albertorrrrrr',
            'apellidos' => 'delgado',
            'telefono' => '3023646789',
            'correo' => 'osoriogal@gmail.com',
            'rol_usuario' => 'jv',
            'credencial' => 'password123',
        ];

        // Simular ejecución del método
        $resultado = $miClase->editarVigilanteController($postData);

        
        $this->assertEquals('{"titulo":"Vigilante Actualizado"}', $resultado);
    }

     //Editar un vigilante (credenciales no validas)
     public function testEditarVigilanteControllerDatosValidosVig() 
     {
         $miClase = new vigilanteController($this->dbMock);
 
         $postData = [
             'REQUEST_METHOD' => 'POST',
             'num_identificacion' => '1112149201',
             'nombres' => 'Dilan Adrian',
             'apellidos' => 'Zapata Ortiz',
             'telefono' => '3023646789',
             'correo' => 'osoriogal@gmail.com',
             'rol_usuario' => 'jv',
             'credencial' => '+++++==',
         ];
 
         // Simular ejecución del método
         $resultado = $miClase->editarVigilanteController($postData);
 
         
         $this->assertEquals('{"titulo":"Credenciales ingresadas no validas"}', $resultado);
     }

     //editar vigilante error con alguno de los datos(datos incompletos)
    public function testEditarVigilanteControllerDatosInvalidos() 
    {
        $miClase = new vigilanteController($this->dbMock);

        $postData = [
            'REQUEST_METHOD' => 'POST',
            'num_identificacion' => '1112149207',
            'nombres' => '',
            'apellidos' => 'Osorio',
            'telefono' => '3023646789',
            'correo' => 'osoriogal@gmail.com',
            'rol_usuario' => 'jv',
            'credencial' => 'password123',
        ];
        
 

        $resultado = $miClase->editarVigilanteController($postData);

        
        $this->assertEquals('{"titulo":"Error","mensaje":"Lo sentimos, ha ocurrido un error con alguno de los datos, intentalo de nuevo mas tarde.","icono":"error","tipoMensaje":"normal"}', $resultado);
    }

    public function testProcesarFormularioDatosValidosFuncio() 
    {
        $miClase = new funcionarioController($this->dbMock);

        // Registramos un funcionario(sin el post)
        $postData = [
           
            'tipo_doc_funcionario' => 'CC',
            'num_documento_funcionario' => '1112149901999',
            'nombres_funcionarios' => 'juan pablo',
            'apellidos_funcionarios' => 'osorio',
            'telefono_funcionario' => '3023646789',
            'correo_funcionario' => 'osoriogal@gmail.com',
            'cargo_funcionario' => 'CO',
            'tipo_vehiculo_funcionario' => 'MT',
            'placa_vehiculo_funcionario' => 'CAB879',
            'tipo_contrato_funcionario' => 'CT',
            'fecha_hora_registro' => '2024-09-26 22:51:55',
            'fecha_finalizacion_contrato' => '2025-10-12',
            'credenciales_funcionario' => '12345678',
            'num_id_usuario_que_registra' => '12345678566',
        ];
        
        
        $resultado = $miClase->registrarFuncionarioControler($postData);

        $this->assertEquals(
            '{"titulo":"Peticion incorrecta","mensaje":"Lo sentimos, la accion que intentas realizar no es correcta","icono":"error","tipoMensaje":"redireccionar"}',
            $resultado
        );
    }

    public function testProcesarFormularioDatosValidosF() 
    {
        $miClase = new funcionarioController($this->dbMock);

        // Registramos un funcionario nuevo
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_doc_funcionario' => 'CC',
            'num_documento_funcionario' => '123451119909',
            'nombres_funcionarios' => 'sofia',
            'apellidos_funcionarios' => 'perez',
            'telefono_funcionario' => '3023646789',
            'correo_funcionario' => 'osoriogal@gmail.com',
            'cargo_funcionario' => 'CO',
            'tipo_vehiculo_funcionario' => 'MT',
            'placa_vehiculo_funcionario' => 'CAB879',
            'tipo_contrato_funcionario' => 'CT',
            'fecha_hora_registro' => '2024-09-26 22:51:55',
            'fecha_finalizacion_contrato' => '2025-10-12',
            'credenciales_funcionario' => '12345678',
            'num_id_usuario_que_registra' => '12345678566',
        ];
        
        
        $resultado = $miClase->registrarFuncionarioControler($postData);

        $this->assertEquals(
            '{"titulo":"FUN registrado"}',
            $resultado
        );
    }

    public function testProcesarFormularioDatosValidoscedulaa() 
    {
        $miClase = new funcionarioController($this->dbMock);

        // Registramos un funcionario(la cedula no cumple con los requisitos)
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_doc_funcionario' => 'CC',
            'num_documento_funcionario' => '1112149902222257858745',
            'nombres_funcionarios' => 'juan pablo',
            'apellidos_funcionarios' => 'osorio',
            'telefono_funcionario' => '3023646789',
            'correo_funcionario' => 'osoriogal@gmail.com',
            'cargo_funcionario' => 'CO',
            'tipo_vehiculo_funcionario' => 'MT',
            'placa_vehiculo_funcionario' => 'CAB879',
            'tipo_contrato_funcionario' => 'CT',
            'fecha_hora_registro' => '2024-09-26 22:51:55',
            'fecha_finalizacion_contrato' => '2025-10-12',
            'credenciales_funcionario' => '12345678',
            'num_id_usuario_que_registra' => '12345678566',
        ];
        
        
        $resultado = $miClase->registrarFuncionarioControler($postData);

        $this->assertEquals(
            '{"titulo":"Lo sentimos, los campos no cumplen con los requisitos"}',
            $resultado
        );
    }

    public function testProcesarFormularioDatosValidosFu() 
    {
        $miClase = new funcionarioController($this->dbMock);

        // Funcionario que ya esta en la base de datos 
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_doc_funcionario' => 'CC',
            'num_documento_funcionario' => '11121490990',
            'nombres_funcionarios' => 'juan pablo',
            'apellidos_funcionarios' => 'osorio',
            'telefono_funcionario' => '3023646789',
            'correo_funcionario' => 'osoriogal@gmail.com',
            'cargo_funcionario' => 'CO',
            'tipo_vehiculo_funcionario' => 'MT',
            'placa_vehiculo_funcionario' => 'CAB879',
            'tipo_contrato_funcionario' => 'CT',
            'fecha_hora_registro' => '2024-09-26 22:51:55',
            'fecha_finalizacion_contrato' => '2025-10-12',
            'credenciales_funcionario' => '12345678',
            'num_id_usuario_que_registra' => '12345678566',
        ];
        
        
        $resultado = $miClase->registrarFuncionarioControler($postData);

        $this->assertEquals(
            '{"titulo":"Este usuario ya se encuentra en la base de datos con un estado activo"}',
            $resultado
        );
    }

    public function testProcesarFormularioDatosValidosFun() 
    {
        $miClase = new funcionarioController($this->dbMock);

        // Prueba de datos incompletos 
        $postData = [
            'REQUEST_METHOD' => 'POST',
            'tipo_doc_funcionario' => 'CC',
            'num_documento_funcionario' => '11121490990',
            'nombres_funcionarios' => '',
            'apellidos_funcionarios' => 'osorio',
            'telefono_funcionario' => '3023646789',
            'correo_funcionario' => 'osoriogal@gmail.com',
            'cargo_funcionario' => 'CO',
            'tipo_vehiculo_funcionario' => 'MT',
            'placa_vehiculo_funcionario' => 'CAB879',
            'tipo_contrato_funcionario' => 'CT',
            'fecha_hora_registro' => '2024-09-26 22:51:55',
            'fecha_finalizacion_contrato' => '2025-10-12',
            'credenciales_funcionario' => '12345678',
            'num_id_usuario_que_registra' => '12345678566',
        ];
        
        
        $resultado = $miClase->registrarFuncionarioControler($postData);

        $this->assertEquals(
            '{"titulo":"Lo sentimos, los campos no cumplen con los requisitos"}',
            $resultado
        );
    }


    //Editar un funcionario exitoso
    public function testEditarFuncionarioControllerDatosValidos() 
{
    $miClase = new funcionarioController($this->dbMock);

    $postData = [
        'REQUEST_METHOD' => 'POST',
        'nombres_funcionario' => 'kevinrrrr',
        'apellidos_funcionario' => 'marin',
        'num_documento_funcionario' => '1234567890',
        'cargo_funcionario' => 'Administrador',
        'tipo_contrato_funcionario' => 'CT',
        'correo_funcionario' => 'juan.osorio@example.com',
        'telefono_funcionario' => '3001234567',
        'credenciales_funcionario' => 'validPassword123',
        'fecha_finalizacion_contrato' => '2025-12-31',
        'fecha_registro' => '2024-01-01'
    ];

    // Simular ejecución del método
    $resultado = $miClase->editarFuncionarioController($postData);

    $this->assertEquals('{"titulo":"Funcionario Actualizado","mensaje":"El funcionario se actualizo correctamente en la base de datos.","icono":"success","tipoMensaje":"normal"}', $resultado);
}

    //Editar un funcionario (Credenciales incorrectas)
    public function testEditarFuncionarioControllerDatosValidosFunci() 
{
    $miClase = new funcionarioController($this->dbMock);

    $postData = [
        'REQUEST_METHOD' => 'POST',
        'nombres_funcionario' => 'Juan Pablo',
        'apellidos_funcionario' => 'Osorio',
        'num_documento_funcionario' => '1234567890',
        'cargo_funcionario' => 'Administrador',
        'tipo_contrato_funcionario' => 'CT',
        'correo_funcionario' => 'juan.osorio@example.com',
        'telefono_funcionario' => '3001234567',
        'credenciales_funcionario' => '++++=',
        'fecha_finalizacion_contrato' => '2025-12-31',
        'fecha_registro' => '2024-01-01'
    ];

    // Simular ejecución del método
    $resultado = $miClase->editarFuncionarioController($postData);

    $this->assertEquals( '{"titulo":"Credenciales ingresadas no validas."}',
    $resultado);
}

 //editar funcionario error con alguno de los datos(datos incompletos)
public function testEditarFuncionarioControllerDatosInvalidos() 
{
    $miClase = new funcionarioController($this->dbMock);

    $postData = [
        'REQUEST_METHOD' => 'POST',
        'nombres_funcionario' => '', // Campo vacío para simular dato inválido
        'apellidos_funcionario' => 'Osorio',
        'num_documento_funcionario' => '1234567890',
        'cargo_funcionario' => 'Administrador',
        'tipo_contrato_funcionario' => 'CT',
        'correo_funcionario' => 'juan.osorio@example.com',
        'telefono_funcionario' => '3001234567',
        'credenciales_funcionario' => 'validPassword123',
        'fecha_finalizacion_contrato' => '2025-12-31',
        'fecha_registro' => '2024-01-01'
    ];

    // Simular ejecución del método
    $resultado = $miClase->editarFuncionarioController($postData);

    $this->assertEquals('{"titulo":"Error","mensaje":"Lo sentimos, ha ocurrido un error con alguno de los datos, intentalo de nuevo mas tarde.","icono":"error","tipoMensaje":"normal"}', $resultado);
}

}
