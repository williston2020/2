conexion = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Función para validar cédula panameña
     * Prueba Unitaria #1: Validación de cédula
     */
    public function validarCedula($cedula) {
        if (empty($cedula) || !is_string($cedula)) {
            return false;
        }
        
        // Formato: 8-123-456 o 1-234-567890
        $patron = '/^[1-9]{1}-[0-9]{3,4}-[0-9]{3,6}$/';
        return preg_match($patron, $cedula);
    }
    
    /**
     * Función para calcular salario neto
     * Prueba Unitaria #2: Cálculo de salario
     */
    public function calcularSalarioNeto($salarioBruto, $deducciones = 0) {
        if (!is_numeric($salarioBruto) || $salarioBruto < 0) {
            throw new InvalidArgumentException("Salario bruto debe ser numérico y positivo");
        }
        
        $seguroSocial = $salarioBruto * 0.095; // 9.5%
        $seguroEducativo = $salarioBruto * 0.0125; // 1.25%
        
        $salarioNeto = $salarioBruto - $seguroSocial - $seguroEducativo - $deducciones;
        return max(0, $salarioNeto);
    }
    
    /**
     * Registrar nuevo trabajador (siguiendo 3FN)
     * Prueba de Integración #1: Registro completo
     */
    public function registrarTrabajador($datosPersona, $datosTrabajo, $datosProfesion = null) {
        try {
            $this->conexion->beginTransaction();
            
            // 1. Insertar persona
            $sqlPersona = "INSERT INTO persona (cedula, tipo_cedula, primer_nombre, segundo_nombre, 
                          apellido_paterno, apellido_materno) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtPersona = $this->conexion->prepare($sqlPersona);
            $stmtPersona->execute([
                $datosPersona['cedula'],
                $datosPersona['tipo_cedula'],
                $datosPersona['primer_nombre'],
                $datosPersona['segundo_nombre'] ?? null,
                $datosPersona['apellido_paterno'],
                $datosPersona['apellido_materno'] ?? null
            ]);
            
            $idPersona = $this->conexion->lastInsertId();
            
            // 2. Insertar trabajo
            $sqlTrabajo = "INSERT INTO trabajo (id_persona, codigo_trabajador, cargo, 
                          empresa, salario_bruto, estatus) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtTrabajo = $this->conexion->prepare($sqlTrabajo);
            $stmtTrabajo->execute([
                $idPersona,
                $datosTrabajo['codigo_trabajador'],
                $datosTrabajo['cargo'],
                $datosTrabajo['empresa'],
                $datosTrabajo['salario_bruto'],
                $datosTrabajo['estatus'] ?? 'ACTIVO'
            ]);
            
            // 3. Insertar profesión si existe
            if ($datosProfesion) {
                $sqlProfesion = "INSERT INTO profesion (id_persona, id_universidad, 
                                titulo_universitario) VALUES (?, ?, ?)";
                $stmtProfesion = $this->conexion->prepare($sqlProfesion);
                $stmtProfesion->execute([
                    $idPersona,
                    $datosProfesion['id_universidad'],
                    $datosProfesion['titulo_universitario']
                ]);
            }
            
            $this->conexion->commit();
            return $idPersona;
            
        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw new Exception("Error al registrar trabajador: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar trabajadores con filtros
     * Prueba de Integración #2: Búsqueda avanzada
     */
    public function buscarTrabajadores($filtros = []) {
        $sql = "SELECT p.id, p.cedula, p.primer_nombre, p.apellido_paterno,
                       t.codigo_trabajador, t.cargo, t.empresa, t.salario_bruto,
                       pr.titulo_universitario, u.nombre as universidad
                FROM persona p
                LEFT JOIN trabajo t ON p.id = t.id_persona
                LEFT JOIN profesion pr ON p.id = pr.id_persona  
                LEFT JOIN universidad u ON pr.id_universidad = u.id
                WHERE 1=1";
        
        $parametros = [];
        
        if (!empty($filtros['empresa'])) {
            $sql .= " AND t.empresa LIKE ?";
            $parametros[] = "%{$filtros['empresa']}%";
        }
        
        if (!empty($filtros['cargo'])) {
            $sql .= " AND t.cargo LIKE ?";
            $parametros[] = "%{$filtros['cargo']}%";
        }
        
        if (!empty($filtros['estatus'])) {
            $sql .= " AND t.estatus = ?";
            $parametros[] = $filtros['estatus'];
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar información de trabajador
     * Prueba Unitaria #3: Actualización de datos
     */
    public function actualizarTrabajador($id, $nuevosDatos) {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException("ID debe ser un número positivo");
        }
        
        $camposPermitidos = ['cargo', 'empresa', 'salario_bruto', 'estatus'];
        $setClauses = [];
        $parametros = [];
        
        foreach ($nuevosDatos as $campo => $valor) {
            if (in_array($campo, $camposPermitidos)) {
                $setClauses[] = "$campo = ?";
                $parametros[] = $valor;
            }
        }
        
        if (empty($setClauses)) {
            throw new InvalidArgumentException("No hay campos válidos para actualizar");
        }
        
        $sql = "UPDATE trabajo SET " . implode(", ", $setClauses) . " WHERE id_persona = ?";
        $parametros[] = $id;
        
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($parametros);
    }
    
    /**
     * Generar reporte de nómina
     * Prueba de Integración #3: Reporte completo
     */
    public function generarReporteNomina($fechaInicio, $fechaFin) {
        $sql = "SELECT p.primer_nombre, p.apellido_paterno, t.codigo_trabajador,
                       t.cargo, t.empresa, t.salario_bruto,
                       (t.salario_bruto * 0.095) as seguro_social,
                       (t.salario_bruto * 0.0125) as seguro_educativo,
                       (t.salario_bruto - (t.salario_bruto * 0.095) - (t.salario_bruto * 0.0125)) as salario_neto
                FROM persona p
                INNER JOIN trabajo t ON p.id = t.id_persona
                WHERE t.estatus = 'ACTIVO'
                AND t.fecha_registro BETWEEN ? AND ?
                ORDER BY t.empresa, p.apellido_paterno";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$fechaInicio, $fechaFin]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Validar datos de entrada
     * Prueba Unitaria #4: Validación de datos
     */
    public function validarDatosPersona($datos) {
        $errores = [];
        
        if (empty($datos['cedula']) || !$this->validarCedula($datos['cedula'])) {
            $errores[] = "Cédula inválida";
        }
        
        if (empty($datos['primer_nombre']) || strlen($datos['primer_nombre']) < 2) {
            $errores[] = "Primer nombre debe tener al menos 2 caracteres";
        }
        
        if (empty($datos['apellido_paterno']) || strlen($datos['apellido_paterno']) < 2) {
            $errores[] = "Apellido paterno debe tener al menos 2 caracteres";
        }
        
        return $errores;
    }
    
    /**
     * Obtener estadísticas generales
     * Prueba Unitaria #5: Cálculos estadísticos
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total trabajadores activos
        $sql = "SELECT COUNT(*) as total FROM trabajo WHERE estatus = 'ACTIVO'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['total_activos'] = $stmt->fetchColumn();
        
        // Salario promedio
        $sql = "SELECT AVG(salario_bruto) as promedio FROM trabajo WHERE estatus = 'ACTIVO'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['salario_promedio'] = round($stmt->fetchColumn(), 2);
        
        // Empresas con más trabajadores
        $sql = "SELECT empresa, COUNT(*) as cantidad FROM trabajo 
                WHERE estatus = 'ACTIVO' GROUP BY empresa ORDER BY cantidad DESC LIMIT 5";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['top_empresas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}

// Funciones auxiliares

/**
 * Función para sanitizar entrada
 * Prueba Unitaria #6: Sanitización de datos
 */
function sanitizarEntrada($dato) {
    if (is_string($dato)) {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }
    return $dato;
}

/**
 * Función para formatear salario
 * Prueba Unitaria #7: Formateo de datos
 */
function formatearSalario($salario, $moneda = 'B/.') {
    if (!is_numeric($salario)) {
        return "N/A";
    }
    return $moneda . ' ' . number_format($salario, 2, '.', ',');
}

/**
 * Función para generar código de trabajador
 * Prueba Unitaria #8: Generación de códigos
 */
function generarCodigoTrabajador($empresa, $consecutivo) {
    $prefijo = strtoupper(substr($empresa, 0, 3));
    $numero = str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    return $prefijo . '-' . $numero;
}

/**
 * Función para validar email
 * Prueba Unitaria #9: Validación email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Función para calcular edad
 * Prueba Unitaria #10: Cálculo de edad
 */
function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    return $hoy->diff($nacimiento)->y;
}
?>