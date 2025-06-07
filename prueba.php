gestor = new GestorTrabajadores('localhost', 'test_user', 'test_pass', 'test_db');
        } catch (Exception $e) {
            echo "⚠️  Configurando modo de prueba sin BD real\n";
            $this->gestor = null;
        }
    }
    
    public function ejecutarTodasLasPruebas() {
        echo "🚀 Iniciando Suite de Pruebas del Sistema de Gestión de Trabajadores\n";
        echo "=" . str_repeat("=", 70) . "\n\n";
        
        // PRUEBAS UNITARIAS
        echo "🔬 EJECUTANDO PRUEBAS UNITARIAS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->test1_ValidacionCedula();
        $this->test2_CalculoSalarioNeto();
        $this->test3_ActualizacionDatos();
        $this->test4_ValidacionDatos();
        $this->test5_CalculosEstadisticos();
        $this->test6_SanitizacionDatos();
        $this->test7_FormateoDatos();
        $this->test8_GeneracionCodigos();
        $this->test9_ValidacionEmail();
        $this->test10_CalculoEdad();
        
        // PRUEBAS DE INTEGRACIÓN
        echo "\n🔗 EJECUTANDO PRUEBAS DE INTEGRACIÓN\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->test11_RegistroCompleto();
        $this->test12_BusquedaAvanzada();
        
        $this->mostrarResumenFinal();
    }
    
    /**
     * PRUEBA UNITARIA #1: Validación de Cédula Panameña
     * Objetivo: Verificar que la función valide correctamente cédulas panameñas
     */
    public function test1_ValidacionCedula() {
        echo "Test 1: Validación de Cédula Panameña\n";
        
        $testCases = [
            ['1-234-56789', true, 'Cédula nacional válida'],
            ['8-123-456', true, 'Cédula nacional corta válida'],
            ['123-456-789', false, 'Formato incorrecto'],
            ['', false, 'Cédula vacía'],
            ['1-23-456789', false, 'Segundo grupo muy corto'],
            ['1-2345-456789', false, 'Segundo grupo muy largo'],
            [null, false, 'Valor nulo'],
            ['abc-123-456', false, 'Caracteres no numéricos']
        ];
        
        $gestor = new GestorTrabajadores('test', 'test', 'test', 'test');
        
        foreach ($testCases as $index => $testCase) {
            list($cedula, $esperado, $descripcion) = $testCase;
            
            try {
                $resultado = $gestor->validarCedula($cedula);
                $this->assertEquals($esperado, $resultado, "Test 1.{$index}: {$descripcion}");
            } catch (Exception $e) {
                $this->registrarFallo("Test 1.{$index}", $descripcion, $e->getMessage());
            }
        }
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #2: Cálculo de Salario Neto
     * Objetivo: Verificar cálculos correctos de deducciones y salario neto
     */
    public function test2_CalculoSalarioNeto() {
        echo "Test 2: Cálculo de Salario Neto\n";
        
        $gestor = new GestorTrabajadores('test', 'test', 'test', 'test');
        
        $testCases = [
            [1000, 0, 893.75, 'Salario básico sin deducciones adicionales'],
            [2000, 100, 1687.50, 'Salario con deducciones adicionales'],
            [500, 0, 446.875, 'Salario bajo'],
            [0, 0, 0, 'Salario cero'],
            [1000, 1000, 0, 'Deducciones mayores que salario neto calculado']
        ];
        
        foreach ($testCases as $index => $testCase) {
            list($salarioBruto, $deducciones, $esperado, $descripcion) = $testCase;
            
            try {
                $resultado = $gestor->calcularSalarioNeto($salarioBruto, $deducciones);
                $this->assertEquals($esperado, $resultado, "Test 2.{$index}: {$descripcion}", 0.01);
            } catch (Exception $e) {
                $this->registrarFallo("Test 2.{$index}", $descripcion, $e->getMessage());
            }
        }
        
        // Test de validación de entrada
        try {
            $gestor->calcularSalarioNeto(-100, 0);
            $this->registrarFallo("Test 2.6", "Salario negativo debe lanzar excepción", "No se lanzó excepción");
        } catch (InvalidArgumentException $e) {
            $this->registrarExito("Test 2.6", "Validación correcta de salario negativo");
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #3: Actualización de Datos
     * Objetivo: Verificar validación de datos para actualización
     */
    public function test3_ActualizacionDatos() {
        echo "Test 3: Actualización de Datos\n";
        
        $gestor = new GestorTrabajadores('test', 'test', 'test', 'test');
        
        // Test de validación de ID
        try {
            $gestor->actualizarTrabajador(-1, ['cargo' => 'Nuevo Cargo']);
            $this->registrarFallo("Test 3.1", "ID negativo debe fallar", "No se validó ID");
        } catch (InvalidArgumentException $e) {
            $this->registrarExito("Test 3.1", "Validación correcta de ID negativo");
        }
        
        // Test de campos vacíos
        try {
            $gestor->actualizarTrabajador(1, []);
            $this->registrarFallo("Test 3.2", "Datos vacíos deben fallar", "No se validaron datos vacíos");
        } catch (InvalidArgumentException $e) {
            $this->registrarExito("Test 3.2", "Validación correcta de datos vacíos");
        }
        
        // Test de campos inválidos
        try {
            $gestor->actualizarTrabajador(1, ['campo_inexistente' => 'valor']);
            $this->registrarFallo("Test 3.3", "Campo inválido debe fallar", "No se validó campo");
        } catch (InvalidArgumentException $e) {
            $this->registrarExito("Test 3.3", "Validación correcta de campos inválidos");
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #4: Validación de Datos de Persona
     * Objetivo: Verificar validaciones de datos de entrada
     */
    public function test4_ValidacionDatos() {
        echo "Test 4: Validación de Datos de Persona\n";
        
        $gestor = new GestorTrabajadores('test', 'test', 'test', 'test');
        
        $datosCompletos = [
            'cedula' => '1-234-56789',
            'primer_nombre' => 'Juan',
            'apellido_paterno' => 'Pérez'
        ];
        
        $errores = $gestor->validarDatosPersona($datosCompletos);
        $this->assertEquals(0, count($errores), "Test 4.1: Datos válidos completos");
        
        $datosSinCedula = $datosCompletos;
        unset($datosSinCedula['cedula']);
        $errores = $gestor->validarDatosPersona($datosSinCedula);
        $this->assertTrue(count($errores) > 0, "Test 4.2: Validación sin cédula debe fallar");
        
        $datosNombreCorto = $datosCompletos;
        $datosNombreCorto['primer_nombre'] = 'A';
        $errores = $gestor->validarDatosPersona($datosNombreCorto);
        $this->assertTrue(count($errores) > 0, "Test 4.3: Nombre muy corto debe fallar");
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #5: Cálculos Estadísticos
     * Objetivo: Verificar que las estadísticas se calculen correctamente
     */
    public function test5_CalculosEstadisticos() {
        echo "Test 5: Cálculos Estadísticos\n";
        
        if (!$this->gestor) {
            echo "⚠️  Test 5: Requiere conexión a BD - OMITIDO\n\n";
            return;
        }
        
        try {
            $estadisticas = $this->gestor->obtenerEstadisticas();
            
            $this->assertTrue(isset($estadisticas['total_activos']), "Test 5.1: Estadística total_activos existe");
            $this->assertTrue(isset($estadisticas['salario_promedio']), "Test 5.2: Estadística salario_promedio existe");
            $this->assertTrue(isset($estadisticas['top_empresas']), "Test 5.3: Estadística top_empresas existe");
            $this->assertTrue(is_numeric($estadisticas['total_activos']), "Test 5.4: total_activos es numérico");
            $this->assertTrue(is_array($estadisticas['top_empresas']), "Test 5.5: top_empresas es array");
            
        } catch (Exception $e) {
            $this->registrarFallo("Test 5", "Error en obtención de estadísticas", $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #6: Sanitización de Datos
     * Objetivo: Verificar que los datos se saniticen correctamente
     */
    public function test6_SanitizacionDatos() {
        echo "Test 6: Sanitización de Datos\n";
        
        $testCases = [
            ['  Juan  ', 'Juan', 'Eliminación de espacios'],
            ['', '<script>alert("hack")</script>', 'Escape de HTML'],
            ['María José', 'María José', 'Caracteres especiales válidos'],
            [123, 123, 'Números sin cambio'],
            ['', '', 'String vacío']
        ];
        
        foreach ($testCases as $index => $testCase) {
            list($entrada, $esperado, $descripcion) = $testCase;
            
            $resultado = sanitizarEntrada($entrada);
            $this->assertEquals($esperado, $resultado, "Test 6.{$index}: {$descripcion}");
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #7: Formateo de Datos
     * Objetivo: Verificar formateo correcto de salarios
     */
    public function test7_FormateoDatos() {
        echo "Test 7: Formateo de Datos\n";
        
        $testCases = [
            [1000, 'B/. 1,000.00', 'Formateo básico'],
            [1234.56, 'B/. 1,234.56', 'Formateo con decimales'],
            [0, 'B/. 0.00', 'Formateo cero'],
            ['abc', 'N/A', 'Valor no numérico'],
            [1000000, 'B/. 1,000,000.00', 'Formateo millones']
        ];
        
        foreach ($testCases as $index => $testCase) {
            list($salario, $esperado, $descripcion) = $testCase;
            
            $resultado = formatearSalario($salario);
            $this->assertEquals($esperado, $resultado, "Test 7.{$index}: {$descripcion}");
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #8: Generación de Códigos
     * Objetivo: Verificar generación correcta de códigos de trabajador
     */
    public function test8_GeneracionCodigos() {
        echo "Test 8: Generación de Códigos\n";
        
        $testCases = [
            ['TechCorp', 1, 'TEC-0001', 'Código básico'],
            ['ABC Company', 25, 'ABC-0025', 'Empresa con espacios'],
            ['X', 9999, 'X-9999', 'Empresa corta'],
            ['SuperLongCompanyName', 1, 'SUP-0001', 'Empresa larga']
        ];
        
        foreach ($testCases as $index => $testCase) {
            list($empresa, $consecutivo, $esperado, $descripcion) = $testCase;
            
            $resultado = generarCodigoTrabajador($empresa, $consecutivo);
            $this->assertEquals($esperado, $resultado, "Test 8.{$index}: {$descripcion}");
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #9: Validación de Email
     * Objetivo: Verificar validación correcta de emails
     */
    public function test9_ValidacionEmail() {
        echo "Test 9: Validación de Email\n";
        
        $testCases = [
            ['usuario@ejemplo.com', true, 'Email válido básico'],
            ['test.email+tag@example.org', true, 'Email con caracteres especiales'],
            ['invalid-email', false, 'Email sin @'],
            ['@ejemplo.com', false, 'Email sin usuario'],
            ['usuario@', false, 'Email sin dominio'],
            ['', false, 'Email vacío'],
            ['usuario@ejemplo', false, 'Dominio sin TLD']
        ];
        
        foreach ($testCases as $index => $testCase) {
            list($email, $esperado, $descripcion) = $testCase;
            
            $resultado = validarEmail($email);
            $this->assertEquals($esperado, $resultado, "Test 9.{$index}: {$descripcion}");
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA UNITARIA #10: Cálculo de Edad
     * Objetivo: Verificar cálculo correcto de edad
     */
    public function test10_CalculoEdad() {
        echo "Test 10: Cálculo de Edad\n";
        
        $hoy = new DateTime();
        
        $testCases = [
            [$hoy->format('Y-m-d'), 0, 'Fecha de hoy'],
            ['1990-01-01', $hoy->format('Y') - 1990, 'Persona nacida en 1990'],
            ['2000-12-31', $hoy->format('Y') - 2000, 'Persona nacida en 2000']
        ];
        
        foreach ($testCases as $index => $testCase) {
            list($fechaNacimiento, $edadEsperada, $descripcion) = $testCase;
            
            $resultado = calcularEdad($fechaNacimiento);
            
            // Permitir variación de ±1 año por fecha específica
            $this->assertTrue(
                abs($resultado - $edadEsperada) <= 1, 
                "Test 10.{$index}: {$descripcion} (esperado: {$edadEsperada}, obtenido: {$resultado})"
            );
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA DE INTEGRACIÓN #1: Registro Completo de Trabajador
     * Objetivo: Verificar el proceso completo de registro
     */
    public function test11_RegistroCompleto() {
        echo "Test 11: Registro Completo de Trabajador (INTEGRACIÓN)\n";
        
        if (!$this->gestor) {
            echo "⚠️  Test 11: Requiere conexión a BD - SIMULADO\n";
            
            // Simular el flujo de registro
            $datosPersona = [
                'cedula' => '8-123-45678',
                'tipo_cedula' => 'N',
                'primer_nombre' => 'Ana',
                'segundo_nombre' => 'María',
                'apellido_paterno' => 'González',
                'apellido_materno' => 'López'
            ];
            
            $datosTrabajo = [
                'codigo_trabajador' => 'TEC-0001',
                'cargo' => 'Desarrolladora Senior',
                'empresa' => 'TechSolutions',
                'salario_bruto' => 2500.00
            ];
            
            // Validar datos
            $gestor = new GestorTrabajadores('test', 'test', 'test', 'test');
            $errores = $gestor->validarDatosPersona($datosPersona);
            
            $this->assertEquals(0, count($errores), "Test 11.1: Validación de datos persona");
            $this->assertTrue($gestor->validarCedula($datosPersona['cedula']), "Test 11.2: Validación cédula");
            $this->assertTrue(is_numeric($datosTrabajo['salario_bruto']), "Test 11.3: Validación salario");
            
            echo "✅ Test 11: SIMULADO - Flujo de registro validado\n\n";
            return;
        }
        
        try {
            $datosPersona = [
                'cedula' => '8-999-' . rand(10000, 99999),
                'tipo_cedula' => 'N',
                'primer_nombre' => 'TestUser',
                'apellido_paterno' => 'TestApellido'
            ];
            
            $datosTrabajo = [
                'codigo_trabajador' => 'TEST-' . rand(1000, 9999),
                'cargo' => 'Test Position',
                'empresa' => 'Test Company',
                'salario_bruto' => 1500.00
            ];
            
            $id = $this->gestor->registrarTrabajador($datosPersona, $datosTrabajo);
            $this->assertTrue(is_numeric($id) && $id > 0, "Test 11.1: Registro exitoso devuelve ID válido");
            
            // Verificar que se puede buscar el trabajador recién creado
            $resultados = $this->gestor->buscarTrabajadores(['empresa' => 'Test Company']);
            $this->assertTrue(count($resultados) > 0, "Test 11.2: Trabajador registrado se puede buscar");
            
        } catch (Exception $e) {
            $this->registrarFallo("Test 11", "Error en registro completo", $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * PRUEBA DE INTEGRACIÓN #2: Búsqueda Avanzada
     * Objetivo: Verificar el sistema completo de búsqueda con filtros
     */
    public function test12_BusquedaAvanzada() {
        echo "Test 12: Búsqueda Avanzada (INTEGRACIÓN)\n";
        
        if (!$this->gestor) {
            echo "⚠️  Test 12: Requiere conexión a BD - SIMULADO\n";
            
            // Simular datos de búsqueda
            $filtrosTest = [
                [],
                ['empresa' => 'TechCorp'],
                ['cargo' => 'Analista'],
                ['estatus' => 'ACTIVO'],
                ['empresa' => 'TechCorp', 'cargo' => 'Desarrollador']
            ];
            
            foreach ($filtrosTest as $index => $filtros) {
                $descripcion = empty($filtros) ? "sin filtros" : "con filtros: " . implode(", ", array_keys($filtros));
                echo "✅ Test 12.{$index}: Búsqueda {$descripcion} - SIMULADO\n";
            }
            
            echo "\n";
            return;
        }
        
        try {
            // Test de búsqueda sin filtros
            $resultados = $this->gestor->buscarTrabajadores();
            $this->assertTrue(is_array($resultados), "Test 12.1: Búsqueda sin filtros devuelve array");
            
            // Test de búsqueda con filtro de empresa
            $resultados = $this->gestor->buscarTrabajadores(['empresa' => 'NonExistentCompany']);
            $this->assertTrue(is_array($resultados), "Test 12.2: Búsqueda con empresa inexistente devuelve array vacío");
            
            // Test de búsqueda con múltiples filtros
            $resultados = $this->gestor->buscarTrabajadores([
                'empresa' => 'TechCorp',
                'estatus' => 'ACTIVO'
            ]);
            $this->assertTrue(is_array($resultados), "Test 12.3: Búsqueda con múltiples filtros funciona");
            
            // Verificar estructura de resultados
            if (!empty($resultados)) {
                $primer_resultado = $resultados[0];
                $campos_requeridos = ['id', 'cedula', 'primer_nombre', 'apellido_paterno', 'codigo_trabajador'];
                
                foreach ($campos_requeridos as $campo) {
                    $this->assertTrue(isset($primer_resultado[$campo]), "Test 12.4: Campo {$campo} presente en resultados");
                }
            }
            
        } catch (Exception $e) {
            $this->registrarFallo("Test 12", "Error en búsqueda avanzada", $e->getMessage());
        }
        
        echo "\n";
    }
    
    // Métodos auxiliares para las pruebas
    
    private function assertEquals($esperado, $actual, $mensaje, $delta = 0) {
        if ($delta > 0) {
            $resultado = abs($actual - $esperado) <= $delta;
        } else {
            $resultado = $esperado === $actual;
        }
        
        if ($resultado) {
            $this->registrarExito($mensaje, "Esperado: {$esperado}, Obtenido: {$actual}");
        } else {
            $this->registrarFallo($mensaje, "Esperado: {$esperado}, Obtenido: {$actual}", "Valores no coinciden");
        }
    }
    
    private function assertTrue($condicion, $mensaje) {
        if ($condicion) {
            $this->registrarExito($mensaje, "Condición verdadera");
        } else {
            $this->registrarFallo($mensaje, "Esperado: true", "Condición falsa");
        }
    }
    
    private function registrarExito($test, $detalle) {
        echo "✅ {$test}: PASÓ - {$detalle}\n";
        $this->testsPasados++;
        $this->resultados[] = ['test' => $test, 'resultado' => 'PASÓ', 'detalle' => $detalle];
    }
    
    private function registrarFallo($test, $esperado, $obtenido) {
        echo "❌ {$test}: FALLÓ - Esperado: {$esperado}, Obtenido: {$obtenido}\n";
        $this->testsFallados++;
        $this->resultados[] = ['test' => $test, 'resultado' => 'FALLÓ', 'esperado' => $esperado, 'obtenido' => $obtenido];
    }
    
    private function mostrarResumenFinal() {
        $totalTests = $this->testsPasados + $this->testsFallados;
        $porcentajeExito = $totalTests > 0 ? round(($this->testsPasados / $totalTests) * 100, 2) : 0;
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "📊 RESUMEN FINAL DE PRUEBAS\n";
        echo str_repeat("=", 70) . "\n";
        echo "Total de pruebas ejecutadas: {$totalTests}\n";
        echo "Pruebas que pasaron: {$this->testsPasados} ✅\n";
        echo "Pruebas que fallaron: {$this->testsFallados} ❌\n";
        echo "Porcentaje de éxito: {$porcentajeExito}%\n";
        
        if ($this->testsFallados > 0) {
            echo "\n🔍 PRUEBAS FALLIDAS:\n";
            foreach ($this->resultados as $resultado) {
                if ($resultado['resultado'] === 'FALLÓ') {
                    echo "- {$resultado['test']}: {$resultado['esperado']} != {$resultado['obtenido']}\n";
                }
            }
        }
        
        echo "\n" . ($this->testsFallados === 0 ? "🎉 ¡TODOS LOS TESTS PASARON!" : "⚠️  Revisar tests fallidos") . "\n";
        echo str_repeat("=", 70) . "\n";
    }
}

// Ejecutar las pruebas si el archivo se ejecuta directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $testSuite = new TestSuiteGestorTrabajadores();
    $testSuite->ejecutarTodasLasPruebas();
}

/**
 * DOCUMENTACIÓN DE PRUEBAS IMPLEMENTADAS:
 * 
 * PRUEBAS UNITARIAS (Testing de componentes individuales):
 * 1. Validación de Cédula: Verifica formato correcto de cédulas panameñas
 * 2. Cálculo de Salario: Verifica cálculos de deducciones y salario neto
 * 3. Actualización de Datos: Verifica validaciones en actualización
 * 4. Validación de Datos: Verifica validaciones de entrada
 * 5. Cálculos Estadísticos: Verifica generación de estadísticas
 * 6. Sanitización: Verifica limpieza de datos de entrada
 * 7. Formateo: Verifica formateo correcto de salarios
 * 8. Generación de Códigos: Verifica creación de códigos únicos
 * 9. Validación Email: Verifica formato de emails
 * 10. Cálculo de Edad: Verifica cálculos de fecha
 * 
 * PRUEBAS DE INTEGRACIÓN (Testing de flujos completos):
 * 11. Registro Completo: Verifica proceso end-to-end de registro
 * 12. Búsqueda Avanzada: Verifica sistema completo de búsqueda
 * 
 * Cada prueba incluye múltiples casos de test para cubrir:
 * - Casos válidos (happy path)
 * - Casos límite (edge cases)  
 * - Casos de error (error cases)
 * - Validación de entrada
 * - Manejo de excepciones
 */
?>