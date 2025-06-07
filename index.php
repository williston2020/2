'localhost',
    'usuario' => 'root',
    'password' => '',
    'bd' => 'sistema_trabajadores'
];

try {
    $gestor = new GestorTrabajadores($config['host'], $config['usuario'], 
                                   $config['password'], $config['bd']);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesamiento de formularios
$mensaje = '';
$tipoMensaje = '';

if ($_POST) {
    try {
        if (isset($_POST['accion'])) {
            switch ($_POST['accion']) {
                case 'registrar':
                    $datosPersona = [
                        'cedula' => sanitizarEntrada($_POST['cedula']),
                        'tipo_cedula' => sanitizarEntrada($_POST['tipo_cedula']),
                        'primer_nombre' => sanitizarEntrada($_POST['primer_nombre']),
                        'segundo_nombre' => sanitizarEntrada($_POST['segundo_nombre']),
                        'apellido_paterno' => sanitizarEntrada($_POST['apellido_paterno']),
                        'apellido_materno' => sanitizarEntrada($_POST['apellido_materno'])
                    ];
                    
                    $datosTrabajo = [
                        'codigo_trabajador' => generarCodigoTrabajador($_POST['empresa'], rand(1, 9999)),
                        'cargo' => sanitizarEntrada($_POST['cargo']),
                        'empresa' => sanitizarEntrada($_POST['empresa']),
                        'salario_bruto' => floatval($_POST['salario_bruto'])
                    ];
                    
                    // Validar datos
                    $errores = $gestor->validarDatosPersona($datosPersona);
                    if (!empty($errores)) {
                        throw new Exception(implode(", ", $errores));
                    }
                    
                    $id = $gestor->registrarTrabajador($datosPersona, $datosTrabajo);
                    $mensaje = "Trabajador registrado exitosamente con ID: $id";
                    $tipoMensaje = 'success';
                    break;
                    
                case 'buscar':
                    $filtros = [];
                    if (!empty($_POST['filtro_empresa'])) {
                        $filtros['empresa'] = sanitizarEntrada($_POST['filtro_empresa']);
                    }
                    if (!empty($_POST['filtro_cargo'])) {
                        $filtros['cargo'] = sanitizarEntrada($_POST['filtro_cargo']);
                    }
                    if (!empty($_POST['filtro_estatus'])) {
                        $filtros['estatus'] = sanitizarEntrada($_POST['filtro_estatus']);
                    }
                    
                    $resultados = $gestor->buscarTrabajadores($filtros);
                    break;
            }
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipoMensaje = 'error';
    }
}

// Obtener estadísticas
$estadisticas = $gestor->obtenerEstadisticas();
?>




    
    
    
    
    


    

        
        

            

                
                Sistema de Gestión de Trabajadores
            

            
Base de datos normalizada (3FN) - Gestión integral de personal


        


        
        
        

            
            
        

        

        
        

                

                        
Trabajadores Activos

                        


                    

            

                

                        
Salario Promedio

                        


                    

            

                

                        
Top Empresa

                        

                            
                        


                    

            


        
        

            

                Registrar Nuevo Trabajador
            


            

                    
Datos Personales

                    
                    

                        
Cédula

                        
1-234-56789

                    


                    

                        
Tipo de Cédula

                        
Seleccionar...

                    


                    

                        
Primer Nombre

                        

                    


                    

                        
Segundo Nombre

                        

                    


                    

                        
Apellido Paterno

                        

                    


                    

                        
Apellido Materno

                        

                    

                

                    
Datos Laborales

                    
                    

                        
Empresa

                        

                    


                    

                        
Cargo

                        

                    


                    

                        
Salario Bruto

                        

                    


                    

                        
                            
                            Registrar Trabajador
                        
                    

                

        


        
        

            

                Buscar Trabajadores
            


            

                    
Empresa

                    
<?= htmlspecialchars($_POST['filtro_empresa'] ?? '') ?>

                

                    
Cargo

                    
<?= htmlspecialchars($_POST['filtro_cargo'] ?? '') ?>

                

                    
Estatus

                    
Todos

                

                        
                        Buscar
                    

        


        
        
        

            
Resultados de Búsqueda

            
            
                

                    
                    
No se encontraron trabajadores con los criterios especificados.


                

            
                

                    
Cédula	Nombre Completo	Código	Cargo	Empresa	Salario	Título

                                    
                                					
                                    
                                    
                                        

                                    
                                

                

                
                

                    Total de resultados: 
                

            
        

        
    

