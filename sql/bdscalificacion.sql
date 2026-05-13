CREATE TABLE `usuarios` (
  `id_usuario` bigint PRIMARY KEY AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL COMMENT 'ADMIN|DOCENTE|ESTUDIANTE|ACUDIENTE',
  `activo` boolean NOT NULL DEFAULT true,
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `estudiantes` (
  `id_estudiante` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` bigint UNIQUE NOT NULL,
  `codigo_estudiantil` varchar(30) UNIQUE NOT NULL,
  `fecha_nacimiento` date,
  `grado_actual` int
);

CREATE TABLE `acudientes` (
  `id_acudiente` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` bigint UNIQUE NOT NULL,
  `telefono` varchar(30)
);

CREATE TABLE `acudiente_estudiante` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_acudiente` bigint NOT NULL,
  `id_estudiante` bigint NOT NULL,
  `parentesco` varchar(50)
);

CREATE TABLE `cursos` (
  `id_curso` bigint PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: 9A',
  `grado` int NOT NULL,
  `anio` int NOT NULL COMMENT 'Ej: 2026'
);

CREATE TABLE `matriculas` (
  `id_matricula` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_estudiante` bigint NOT NULL,
  `id_curso` bigint NOT NULL,
  `fecha_matricula` date NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'ACTIVA' COMMENT 'ACTIVA|RETIRADA'
);

CREATE TABLE `materias` (
  `id_materia` bigint PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(80) UNIQUE NOT NULL,
  `intensidad_horaria` int
);

CREATE TABLE `docente_materia_curso` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_docente_usuario` bigint NOT NULL COMMENT 'Usuario con rol DOCENTE',
  `id_materia` bigint NOT NULL,
  `id_curso` bigint NOT NULL,
  `anio` int NOT NULL
);

CREATE TABLE `periodos` (
  `id_periodo` bigint PRIMARY KEY AUTO_INCREMENT,
  `anio` int NOT NULL,
  `nombre` varchar(20) NOT NULL COMMENT 'P1, P2, P3, P4',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `cerrado` boolean NOT NULL DEFAULT false
);

CREATE TABLE `evaluaciones` (
  `id_evaluacion` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_docente_materia_curso` bigint NOT NULL,
  `id_periodo` bigint NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `tipo` varchar(15) NOT NULL DEFAULT 'OTRO' COMMENT 'TAREA|QUIZ|EXAMEN|PROYECTO|OTRO',
  `fecha` date NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL COMMENT '0..100'
);

CREATE TABLE `notas` (
  `id_nota` bigint PRIMARY KEY AUTO_INCREMENT,
  `id_evaluacion` bigint NOT NULL,
  `id_matricula` bigint NOT NULL,
  `valor` decimal(5,2) NOT NULL COMMENT 'Escala 0-5 o 0-100',
  `observacion` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE UNIQUE INDEX `acudiente_estudiante_index_0` ON `acudiente_estudiante` (`id_acudiente`, `id_estudiante`);

CREATE UNIQUE INDEX `cursos_index_1` ON `cursos` (`nombre`, `anio`);

CREATE UNIQUE INDEX `matriculas_index_2` ON `matriculas` (`id_estudiante`, `id_curso`);

CREATE UNIQUE INDEX `docente_materia_curso_index_3` ON `docente_materia_curso` (`id_docente_usuario`, `id_materia`, `id_curso`, `anio`);

CREATE UNIQUE INDEX `periodos_index_4` ON `periodos` (`anio`, `nombre`);

CREATE UNIQUE INDEX `notas_index_5` ON `notas` (`id_evaluacion`, `id_matricula`);

ALTER TABLE `estudiantes` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `acudientes` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `acudiente_estudiante` ADD FOREIGN KEY (`id_acudiente`) REFERENCES `acudientes` (`id_acudiente`);

ALTER TABLE `acudiente_estudiante` ADD FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`);

ALTER TABLE `matriculas` ADD FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id_estudiante`);

ALTER TABLE `matriculas` ADD FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);

ALTER TABLE `docente_materia_curso` ADD FOREIGN KEY (`id_docente_usuario`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `docente_materia_curso` ADD FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`);

ALTER TABLE `docente_materia_curso` ADD FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);

ALTER TABLE `evaluaciones` ADD FOREIGN KEY (`id_docente_materia_curso`) REFERENCES `docente_materia_curso` (`id`);

ALTER TABLE `evaluaciones` ADD FOREIGN KEY (`id_periodo`) REFERENCES `periodos` (`id_periodo`);

ALTER TABLE `notas` ADD FOREIGN KEY (`id_evaluacion`) REFERENCES `evaluaciones` (`id_evaluacion`);

ALTER TABLE `notas` ADD FOREIGN KEY (`id_matricula`) REFERENCES `matriculas` (`id_matricula`);
