# 📘 Manual de Usuario — FlexiWeek
**Sistema de Gestión de Cronogramas Semanales**

---

## 📑 Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Inicio de Sesión](#2-inicio-de-sesión)
3. [Interfaz Principal](#3-interfaz-principal)
4. [Gestión de Semanas (Períodos)](#4-gestión-de-semanas-períodos)
5. [Gestión de Tareas](#5-gestión-de-tareas)
6. [Gestión de Subtareas](#6-gestión-de-subtareas)
7. [Control de Tiempo y Progreso](#7-control-de-tiempo-y-progreso)
8. [Detalles de Tarea](#8-detalles-de-tarea)
9. [Estados de Tarea](#9-estados-de-tarea)
10. [Funciones Avanzadas](#10-funciones-avanzadas)
11. [Perfil y Configuración](#11-perfil-y-configuración)
12. [Preguntas Frecuentes](#12-preguntas-frecuentes)

---

## 1. Introducción

**FlexiWeek** es una aplicación web de gestión de tareas y cronogramas semanales que te permite organizar tu trabajo por períodos de tiempo, crear tareas con estimaciones de tiempo o basadas en subtareas, y llevar un control preciso del progreso de cada actividad.

### Características Principales

- ✅ **Gestión de períodos semanales** con fechas personalizables
- ✅ **Dos tipos de tareas**: Por tiempo estimado o por subtareas
- ✅ **Registro de tiempo trabajado** con cálculo automático de progreso
- ✅ **Subtareas con tiempo estimado** individual
- ✅ **Estados visuales** de tareas (Pendiente, En progreso, Pausada, Completada)
- ✅ **Drag & drop** para reordenar tareas y moverlas entre semanas
- ✅ **Interfaz oscura** tipo VS Code para reducir fatiga visual
- ✅ **Responsive** — funciona en escritorio y dispositivos móviles

### Credenciales de Acceso

Para este manual utilizaremos las siguientes credenciales de ejemplo:
- **Usuario:** `admin@cronograma.com`
- **Contraseña:** `123456789`

---

## 2. Inicio de Sesión

### 2.1 Acceder a la Aplicación

**Captura de pantalla:** *[INSERTAR: Pantalla de login]*

Al abrir la aplicación en tu navegador, serás dirigido automáticamente a la pantalla de inicio de sesión si no tienes una sesión activa.

### 2.2 Elementos de la Pantalla de Login

La pantalla de login presenta:

1. **Logo de FlexiWeek** — Ubicado en la parte superior central
2. **Formulario de autenticación** con fondo oscuro (#252526) y bordes sutiles
3. **Campos del formulario:**
   - **Correo electrónico** — Campo de texto con placeholder "email@example.com"
   - **Contraseña** — Campo de contraseña con ícono de ojo 👁 para mostrar/ocultar
   - **Recordarme** — Checkbox para mantener la sesión activa
4. **Botón "Iniciar sesión"** — Botón azul (#007fd4) prominente
5. **Enlaces adicionales:**
   - "¿Olvidaste tu contraseña?" — En la esquina superior derecha del campo de contraseña
   - "¿No tienes una cuenta? Regístrate" — Debajo del botón de login

### 2.3 Proceso de Inicio de Sesión

**Paso a paso:**

1. **Ingresa tu correo electrónico** en el primer campo
   - Ejemplo: `admin@cronograma.com`
   
2. **Ingresa tu contraseña** en el segundo campo
   - Ejemplo: `123456789`
   - Puedes hacer clic en el ícono del ojo para ver la contraseña mientras escribes

3. **(Opcional) Marca "Recordarme"** si deseas que el navegador mantenga tu sesión iniciada

4. **Haz clic en "Iniciar sesión"** o presiona `Enter`

5. **Redirección automática** — Si las credenciales son correctas, serás redirigido al planificador semanal

> **💡 Tip:** Si olvidaste tu contraseña, haz clic en "¿Olvidaste tu contraseña?" para iniciar el proceso de recuperación.

---

## 3. Interfaz Principal

**Captura de pantalla:** *[INSERTAR: Vista completa del planificador semanal]*

Una vez autenticado, accederás a la **vista principal del planificador**. La interfaz está dividida en tres áreas principales:

### 3.1 Estructura General

```
┌──────────────┬───────────────────────────────────────────────────┐
│              │         BARRA DE NAVEGACIÓN SUPERIOR              │
│  EXPLORADOR  │  [☰] Semana Actual    [LOGO]    [Usuario] [+Tarea]│
│   (Sidebar)  ├───────────────────────────────────────────────────┤
│              │                                                   │
│  📁 Semanas  │                                                   │
│  Activas     │           ÁREA PRINCIPAL DE TAREAS                │
│              │                                                   │
│  • Semana 7  │  ┌─────────────────────────────────────────────┐  │
│  • Semana 8  │  │ Estado │ Tarea │ Asignar │ Realiz │ Rest │  │  │
│              │  ├─────────────────────────────────────────────┤  │
│  ────────    │  │ [▶] En │ Tarea │ Subtar │  2h    │ 3h   │  │  │
│  📂 Semanas  │  │  progr │   1   │  ea 1  │  30m   │ 15m  │  │  │
│  Anteriores  │  └─────────────────────────────────────────────┘  │
│              │                                                   │
└──────────────┴───────────────────────────────────────────────────┘
```

### 3.2 Barra Lateral (Explorador de Semanas)

**Captura de pantalla:** *[INSERTAR: Sidebar con lista de semanas]*

La barra lateral izquierda es el **Explorador de Semanas**. Aquí puedes:

#### Elementos del Sidebar

1. **Encabezado "EXPLORADOR"** — Título en mayúsculas con estilo de código
2. **Botón "+ Nueva"** — Esquina superior derecha para crear nuevas semanas
3. **Lista de Semanas Activas** — Períodos cuya fecha de fin es hoy o posterior
4. **Sección "Semanas Anteriores"** — Períodos finalizados (colapsable)

#### Información de Cada Semana

Cada semana en la lista muestra:
- **Nombre** (ej: "Semana 7", "Sprint de Desarrollo")
- **Rango de fechas** en formato `dd/mm - dd/mm`
- **Indicador visual** — Borde azul si está seleccionada
- **Ícono de edición** (✏️) que aparece al pasar el mouse

#### Acciones Disponibles

| Acción | Cómo realizarla |
|--------|----------------|
| **Seleccionar semana** | Clic en el nombre de la semana |
| **Crear nueva semana** | Clic en "+ Nueva" |
| **Editar semana** | Clic en el ícono ✏️ |
| **Ver semanas pasadas** | Clic en "Semanas Anteriores ▶" |
| **Cerrar sidebar (móvil)** | Clic en la X o fuera del sidebar |

> **📱 Nota móvil:** En dispositivos móviles, el sidebar se muestra como un panel deslizable que se oculta automáticamente al seleccionar una semana.

### 3.3 Barra de Navegación Superior

**Captura de pantalla:** *[INSERTAR: Navbar con todos sus elementos]*

La barra superior contiene información y controles principales:

#### Zona Izquierda
- **Botón "☰ Semanas"** — Abre/cierra el sidebar
- **Nombre de la semana actual** — En color azul (#007fd4)
- **Rango de fechas** — Formato `dd/mm - dd/mm/yyyy`

#### Zona Central (solo escritorio)
- **Logo de FlexiWeek** — Imagen con efecto de sombra

#### Zona Derecha
- **Badge "Tareas: X"** — Contador de tareas en la semana actual
- **Botón "+ Tarea"** — Botón azul para crear nueva tarea
- **Menú de usuario** — Avatar con inicial + nombre

### 3.4 Área Principal de Tareas

**Captura de pantalla:** *[INSERTAR: Tabla de tareas con varias tareas de ejemplo]*

El área central muestra todas las tareas de la semana seleccionada en formato de tabla.

#### Columnas de la Tabla

| Columna | Descripción | Ancho |
|---------|-------------|-------|
| **Estado** | Badge de color con estado actual | 130px |
| **Actividad/Tarea** | Nombre + botón "Detalles" | Flexible |
| **Asignar a** | Selector de subtarea para tiempo | 160px |
| **Trabajo realizado** | Tiempo efectivo trabajado | 130px |
| **Trabajo restante** | Tiempo estimado pendiente | 130px |
| **Control de Tiempo** | Barra de progreso + input | 190px |
| **Editar** | Ícono de lápiz ✏️ | 80px |

#### Vista Móvil

En pantallas pequeñas (< 768px), la tabla se convierte en **tarjetas apiladas** con toda la información organizada verticalmente.

**Captura de pantalla:** *[INSERTAR: Vista móvil con tarjetas de tareas]*

---

## 4. Gestión de Semanas (Períodos)

Las semanas (o períodos) son los contenedores principales de tus tareas. Puedes crear tantas como necesites con fechas personalizadas.

### 4.1 Crear una Nueva Semana

**Captura de pantalla:** *[INSERTAR: Modal de creación de semana]*

#### Paso 1: Abrir el Formulario
- Haz clic en el botón **"+ Nueva"** en la esquina superior derecha del sidebar

#### Paso 2: Completar el Formulario

El modal muestra los siguientes campos:

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| **Semana** | Texto | No | Nombre identificador (ej: "Semana 7", "Sprint 3") |
| **Fecha Inicio** | Date picker | Sí | Primer día del período |
| **Fecha Fin** | Date picker | Sí | Último día del período |

**Detalles del formulario:**
- Título del modal: **"{ } Nueva Semana"** (los corchetes son decorativos)
- Fondo oscuro (#252526) con bordes sutiles (#333)
- Labels en gris (#7b7b7b) con fuente monoespaciada
- Inputs con fondo #3c3c3c

#### Paso 3: Guardar

1. **Completa los campos** según tus necesidades
2. Haz clic en **"Guardar Cambios"** (botón azul)
3. O presiona `Enter` en cualquier campo para guardar rápidamente

#### Validaciones

- ✅ La **Fecha Fin** debe ser igual o posterior a la **Fecha Inicio**
- ✅ El **nombre** es opcional pero recomendado para identificación rápida
- ⚠️ Si hay **solapamiento** con otras semanas, el sistema ajustará automáticamente

### 4.2 Resolución Automática de Conflictos

**Captura de pantalla:** *[INSERTAR: Diagrama de cómo se resuelven solapamientos]*

Cuando creas o editas una semana que se solapa con otras existentes, FlexiWeek aplica las siguientes reglas automáticamente:

#### Caso 1: Nueva semana cubre completamente a una existente
```
Antes:  [====Semana Vieja====]
Nueva:  [========Nueva Semana=========]
Después: [========Nueva Semana=========]
         (Semana vieja eliminada)
```

#### Caso 2: Solapamiento al final
```
Antes:  [====Semana Vieja====]
Nueva:              [===Nueva===]
Después: [=Vieja=] [===Nueva===]
         (Vieja acortada)
```

#### Caso 3: Solapamiento al inicio
```
Antes:              [====Semana Vieja====]
Nueva:  [===Nueva===]
Después: [===Nueva===] [=Vieja=]
         (Vieja acortada)
```

> **⚠️ Importante:** Los ajustes se realizan automáticamente sin confirmación. Las tareas de las semanas afectadas se mantienen intactas.

### 4.3 Editar una Semana Existente

**Paso a paso:**

1. **Localiza la semana** en el sidebar
2. **Haz clic en el ícono ✏️** que aparece a la derecha
3. **Modifica los campos** necesarios
4. **Guarda los cambios**

El formulario de edición es idéntico al de creación, pero con los datos actuales pre-cargados.

### 4.4 Seleccionar una Semana

Para ver las tareas de una semana específica:

1. **Haz clic en el nombre** de la semana en el sidebar
2. La semana se **resalta con borde azul**
3. El área principal **actualiza la tabla** de tareas
4. La **barra de navegación** muestra el nombre y fechas de la semana seleccionada

---

## 5. Gestión de Tareas

Las tareas son las unidades de trabajo dentro de cada semana. FlexiWeek soporta dos tipos de tareas según cómo se mida su progreso.

### 5.1 Tipos de Tareas

#### Tipo 1: Por Tiempo
- El progreso se calcula como: `(Tiempo Trabajado / Tiempo Estimado) × 100`
- Requiere especificar **horas y minutos** estimados
- Ideal para tareas con duración conocida

#### Tipo 2: Por Subtareas
- El progreso se calcula como: `(Subtareas Completadas / Total Subtareas) × 100`
- Requiere **al menos 1 subtarea**
- Ideal para tareas complejas divisibles en pasos

### 5.2 Crear una Nueva Tarea

**Captura de pantalla:** *[INSERTAR: Modal de creación de tarea - tipo "Por Tiempo"]*

#### Paso 1: Abrir el Formulario
- Haz clic en el botón **"+ Tarea"** en la barra de navegación superior

#### Paso 2: Campos Básicos

| Campo | Descripción | Validación |
|-------|-------------|------------|
| **Semana** | Selector de la semana destino | Requerido |
| **Nombre de la tarea** | Título descriptivo | Requerido, máx 255 caracteres |
| **Descripción / Detalles** | Notas, contexto, instrucciones | Opcional, texto largo |
| **Tipo de Tarea** | "Por Tiempo" o "Por Subtareas" | Requerido |

#### Paso 3A: Configuración "Por Tiempo"

Si seleccionas **"Por Tiempo"**, aparecen dos campos adicionales:

**Captura de pantalla:** *[INSERTAR: Sección de horas y minutos del formulario]*

- **Horas** — Campo numérico (mínimo 0)
- **Minutos** — Campo numérico (0-59)

**Validación importante:**
```
Tiempo total = (Horas × 60) + Minutos
Mínimo requerido: 10 minutos
```

> **⚠️ Advertencia:** Si el tiempo total es menor a 10 minutos, aparecerá un error: *"El tiempo estimado debe ser al menos 10 minutos"*

**Ejemplo válido:**
- Horas: `2`
- Minutos: `30`
- Total: `150 minutos` ✅

**Ejemplo inválido:**
- Horas: `0`
- Minutos: `5`
- Total: `5 minutos` ❌

#### Paso 3B: Configuración "Por Subtareas"

**Captura de pantalla:** *[INSERTAR: Modal con tipo "Por Subtareas" seleccionado]*

Si seleccionas **"Por Subtareas"**:

1. Aparece un **mensaje de advertencia** si no hay subtareas:
   ```
   ⚠️ Requerido: Debes agregar al menos 1 subtarea para poder 
   usar el control por subtareas.
   ```

2. La sección de **"Subtareas *"** se marca como obligatoria (asterisco rojo)

3. Debes agregar al menos una subtarea antes de poder guardar (ver sección 6)

#### Paso 4: Guardar la Tarea

1. **Revisa todos los campos**
2. Haz clic en **"Guardar"** (botón azul)
3. O presiona `Esc` para cancelar

**Mensajes de éxito:**
- La tarea aparece inmediatamente en la tabla
- El contador de tareas se actualiza
- El modal se cierra automáticamente

### 5.3 Editar una Tarea Existente

**Dos formas de abrir el editor:**

#### Opción 1: Clic en la fila
- Haz clic en **cualquier parte de la fila** de la tarea en la tabla
- Se abre el modal con todos los datos pre-cargados

#### Opción 2: Botón de edición
- Haz clic en el **ícono ✏️** en la columna "Editar"
- Mismo resultado que la opción 1

**Captura de pantalla:** *[INSERTAR: Modal de edición con datos pre-cargados]*

**Campos editables:**
- ✅ Nombre de la tarea
- ✅ Descripción
- ✅ Semana asignada (puedes mover la tarea)
- ✅ Tipo de tarea (con restricciones)
- ✅ Tiempo estimado (si es "Por Tiempo")
- ✅ Subtareas (agregar, editar, eliminar)

> **⚠️ Cambio de tipo:** Si cambias de "Por Tiempo" a "Por Subtareas", deberás agregar al menos 1 subtarea. Si cambias de "Por Subtareas" a "Por Tiempo", las subtareas se mantendrán pero el progreso se calculará por tiempo.

---

## 6. Gestión de Subtareas

Las subtareas permiten dividir tareas complejas en pasos más pequeños y manejables.

### 6.1 Agregar Subtareas

**Captura de pantalla:** *[INSERTAR: Sección de subtareas con 2-3 subtareas de ejemplo]*

#### Desde el Formulario de Tarea

1. **Abre** el formulario de creación o edición de tarea
2. **Desplázate** hasta la sección "Subtareas"
3. **Haz clic** en el enlace azul **"+ Agregar Subtarea"**
4. Aparece una **nueva tarjeta de subtarea**

### 6.2 Campos de una Subtarea

Cada subtarea contiene:

**Captura de pantalla:** *[INSERTAR: Detalle de una tarjeta de subtarea individual]*

| Elemento | Descripción |
|----------|-------------|
| **☐ Checkbox** | Marca la subtarea como completada/pendiente |
| **Título** | Nombre de la subtarea (campo de texto) |
| **🗑️ Eliminar** | Ícono rojo para borrar la subtarea |
| **Descripción** | Campo opcional para detalles adicionales |
| **⏱ Estimado** | Campos de horas y minutos estimados |

#### Diseño Visual

```
┌─────────────────────────────────────────────────────┐
│ ☐ [Título de la subtarea___________________] 🗑️    │
│                                                     │
│ [Detalles de la subtarea (opcional)__________]     │
│                                                     │
│ ⏱ Estimado:  [0] h  [30] m                         │
└─────────────────────────────────────────────────────┘
```

### 6.3 Completar el Formulario de Subtarea

**Ejemplo paso a paso:**

1. **Marca el checkbox** si la subtarea ya está completada
2. **Escribe el título**: "Diseñar mockups de la interfaz"
3. **(Opcional) Agrega descripción**: "Incluir versión móvil y escritorio"
4. **Estima el tiempo**:
   - Horas: `3`
   - Minutos: `0`
5. **Haz clic en "+ Agregar Subtarea"** para añadir otra

### 6.4 Eliminar una Subtarea

- Haz clic en el **ícono de papelera 🗑️** (rojo) a la derecha de la subtarea
- La subtarea se elimina **inmediatamente** sin confirmación
- Si eliminas todas las subtareas de una tarea "Por Subtareas", aparecerá el mensaje de advertencia

### 6.5 Subtareas en Tareas "Por Tiempo"

**Captura de pantalla:** *[INSERTAR: Tarea "Por Tiempo" con subtareas opcionales]*

> **💡 Flexibilidad:** Aunque una tarea sea "Por Tiempo", puedes agregar subtareas para organización. En este caso:
> - El **progreso** se calcula por tiempo trabajado
> - Las subtareas sirven como **checklist** organizativo
> - Puedes asignar tiempo a subtareas específicas

### 6.6 Cálculo de Tiempo en Subtareas

#### Tiempo Estimado Total
```
Tiempo estimado de la tarea = 
    Suma de tiempos estimados de todas las subtareas
```

#### Tiempo Trabajado
- Puedes asignar tiempo a la **tarea principal** o a **subtareas específicas**
- El selector "Asignar a" en la tabla principal permite elegir el destino

**Ejemplo:**
```
Tarea: Desarrollar módulo de reportes
├─ Subtarea 1: Diseño de base de datos (Est: 2h, Trabajado: 2h 30m)
├─ Subtarea 2: API endpoints (Est: 3h, Trabajado: 1h 15m)
└─ Subtarea 3: Interfaz de usuario (Est: 4h, Trabajado: 0h)

Tiempo estimado total: 9h
Tiempo trabajado total: 3h 45m
Progreso: 41.67%
```

---

## 7. Control de Tiempo y Progreso

FlexiWeek ofrece un sistema robusto de seguimiento de tiempo y cálculo automático de progreso.

### 7.1 Registrar Tiempo Trabajado

**Captura de pantalla:** *[INSERTAR: Columna "Control de Tiempo" con input y botón]*

#### Desde la Tabla Principal

**Paso a paso:**

1. **Localiza la tarea** en la tabla
2. **Selecciona el destino** en el selector "Asignar a":
   - `-- Tarea principal --` (por defecto)
   - O una subtarea específica
3. **Ingresa los minutos** en el campo numérico
4. **Registra el tiempo**:
   - Presiona `Enter`, o
   - Haz clic en el botón **"+"**

**Captura de pantalla:** *[INSERTAR: Selector "Asignar a" desplegado con subtareas]*

#### Ejemplo Práctico

**Situación:** Trabajaste 45 minutos en la "Subtarea 2: API endpoints"

1. Encuentra la tarea en la tabla
2. Abre el selector "Asignar a"
3. Selecciona "Subtarea 2: API endpoints"
4. Escribe `45` en el campo de minutos
5. Presiona Enter

**Resultado:**
- ✅ Se suman 45 minutos al tiempo trabajado de la Subtarea 2
- ✅ Se actualiza el tiempo total trabajado de la tarea
- ✅ Se recalcula el progreso
- ✅ La barra de progreso se anima hasta el nuevo porcentaje

### 7.2 Barra de Progreso

**Captura de pantalla:** *[INSERTAR: Diferentes estados de la barra de progreso]*

La barra de progreso muestra visualmente el avance de cada tarea:

#### Estados Visuales

| Progreso | Color | Descripción |
|----------|-------|-------------|
| 0% - 99% | Azul (#007fd4) | Tarea en progreso |
| 100%+ | Verde (#4ec9b0) | Tarea completada |

#### Información Mostrada

Encima de la barra:
```
2h 30m // 5h 0m                                    50%
└─┬──┘    └─┬──┘                                   └┬┘
  │         │                                       │
  │         └─ Tiempo estimado (en verde)          │
  │                                                 │
  └─ Tiempo trabajado (en azul)                    └─ Porcentaje
```

### 7.3 Cálculo de Progreso

#### Para Tareas "Por Tiempo"

```javascript
progreso = (tiempo_trabajado / tiempo_estimado) × 100
```

**Ejemplo:**
- Tiempo estimado: `5h 0m` = 300 minutos
- Tiempo trabajado: `2h 30m` = 150 minutos
- Progreso: `(150 / 300) × 100 = 50%`

> **📊 Nota:** El progreso puede superar el 100% si trabajas más tiempo del estimado.

#### Para Tareas "Por Subtareas"

```javascript
progreso = (subtareas_completadas / total_subtareas) × 100
```

**Ejemplo:**
- Total subtareas: 5
- Completadas: 3
- Progreso: `(3 / 5) × 100 = 60%`

### 7.4 Columnas de Tiempo en la Tabla

**Captura de pantalla:** *[INSERTAR: Columnas "Trabajo realizado" y "Trabajo restante"]*

#### Trabajo Realizado

Muestra el tiempo total trabajado en formato `Xh Ym`:
- **Color:** Azul claro (#9cdcfe)
- **Fuente:** Monoespaciada para alineación
- **Actualización:** Automática al registrar tiempo

#### Trabajo Restante

Muestra el tiempo estimado pendiente:

| Situación | Color | Ejemplo |
|-----------|-------|---------|
| Tiempo sobrante | Naranja (#ce9178) | `2h 30m` |
| Exactamente completado | Verde (#4ec9b0) | `0h 0m` |
| Tiempo excedido | Rojo (#f14c4c) | `-1h 15m` |

**Cálculo:**
```
Trabajo restante = Tiempo estimado - Tiempo trabajado
```

### 7.5 Vista Móvil del Control de Tiempo

**Captura de pantalla:** *[INSERTAR: Tarjeta móvil con métricas de tiempo]*

En dispositivos móviles, las métricas se organizan en una cuadrícula 2×2:

```
┌──────────────┬──────────────┐
│  Realizado   │   Restante   │
│    2h 30m    │    2h 30m    │
└──────────────┴──────────────┘
```

---

## 8. Detalles de Tarea

El panel de detalles proporciona una vista ampliada de la información de cada tarea.

### 8.1 Abrir el Panel de Detalles

**Captura de pantalla:** *[INSERTAR: Botón "Detalles" en la tabla]*

- Haz clic en el botón azul **"Detalles"** junto al nombre de la tarea
- Se abre un modal con información completa

### 8.2 Contenido del Panel

**Captura de pantalla:** *[INSERTAR: Modal de detalles completo]*

#### Encabezado
- **Ícono:** 📋
- **Título:** "Detalles de Tarea"
- **Nombre de la tarea** en gris debajo del título
- **Botón "Editar"** en la esquina superior derecha

#### Sección 1: Descripción de la Tarea

**Captura de pantalla:** *[INSERTAR: Sección de descripción en modo lectura y edición]*

**Modo Lectura:**
- Fondo oscuro (#1e1e1e)
- Texto formateado con saltos de línea preservados
- Mensaje "Sin descripción" si está vacía

**Modo Edición:**
- Textarea editable
- Placeholder: "Agregar descripción..."
- 4 filas de altura

#### Sección 2: Subtareas (si existen)

**Captura de pantalla:** *[INSERTAR: Lista de subtareas en el panel de detalles]*

**Resumen de Tiempos:**
```
┌─────────────────────────────────────────┐
│ ⏱ Estimado: 9h 0m    ✓ Invertido: 3h 45m │
└─────────────────────────────────────────┘
```

**Lista de Subtareas:**

Cada subtarea muestra:
- **Estado:** ✓ (verde) si completada, ○ (naranja) si pendiente
- **Título** de la subtarea
- **Tiempos:** Estimado en naranja, Invertido en verde entre paréntesis
- **Descripción** (si existe) en texto gris más pequeño

**Ejemplo visual:**
```
┌────────────────────────────────────────────────┐
│ ✓ Diseño de base de datos        2h 0m (2h 30m)│
│   Incluir diagrama ER y scripts SQL            │
├────────────────────────────────────────────────┤
│ ○ API endpoints                  3h 0m (1h 15m)│
│   REST API con autenticación JWT               │
├────────────────────────────────────────────────┤
│ ○ Interfaz de usuario            4h 0m (0h 0m) │
└────────────────────────────────────────────────┘
```

### 8.3 Editar desde Detalles

**Paso a paso:**

1. **Haz clic en "Editar"** en la esquina superior derecha
2. Los campos se vuelven **editables**:
   - Descripción de la tarea → Textarea
   - Descripciones de subtareas → Inputs de texto
3. **Modifica** lo que necesites
4. **Haz clic en "Guardar"** (botón azul)
5. O **"Cancelar"** para descartar cambios

**Captura de pantalla:** *[INSERTAR: Panel de detalles en modo edición]*

### 8.4 Cerrar el Panel

- Haz clic en **"Cerrar"** (botón gris inferior)
- Presiona la tecla **Esc**
- Haz clic **fuera del modal** (en el overlay oscuro)

---

## 9. Estados de Tarea

Los estados permiten categorizar visualmente el progreso de cada tarea.

### 9.1 Estados Disponibles

**Captura de pantalla:** *[INSERTAR: Los 4 badges de estado lado a lado]*

| Estado | Badge | Color | Cuándo Usar |
|--------|-------|-------|-------------|
| **Pendiente** | • Pendiente | Gris (#8b949e) | Tarea aún no iniciada |
| **En progreso** | ▶ En progreso | Azul (#79c0ff) | Tarea actualmente en trabajo |
| **Pausada** | ⏸ Pausada | Amarillo (#d29922) | Tarea temporalmente detenida |
| **Completada** | ✓ Completada | Verde (#7ee787) | Tarea finalizada |

### 9.2 Cambiar el Estado

**Captura de pantalla:** *[INSERTAR: Secuencia de cambio de estado]*

#### Método Manual

1. **Localiza el badge** en la columna "Estado"
2. **Haz clic** sobre el badge
3. El estado **cicla automáticamente**:
   ```
   Pendiente → En progreso → Pausada → Pendiente → ...
   ```

> **⚠️ Importante:** Debes hacer clic **directamente sobre el badge**, no en la fila de la tarea.

#### Cambio Automático

El estado cambia a **"Completada"** automáticamente cuando:
- El **progreso alcanza o supera el 100%**

Una vez en estado "Completada":
- El badge se **bloquea** (no se puede cambiar manualmente)
- El badge muestra **✓ Completada** en verde
- La tarea se considera **finalizada**

### 9.3 Colores y Significado Visual

**Captura de pantalla:** *[INSERTAR: Tabla con diferentes tareas en diferentes estados]*

Los colores están diseñados para proporcionar información rápida:

- **🔘 Gris:** Neutral, sin urgencia
- **🔵 Azul:** Activo, requiere atención
- **🟡 Amarillo:** Advertencia, bloqueado temporalmente
- **🟢 Verde:** Éxito, completado

---

## 10. Funciones Avanzadas

### 10.1 Drag & Drop — Reordenar Tareas

**Captura de pantalla:** *[INSERTAR: Tarea siendo arrastrada con el handle visible]*

#### En Escritorio

1. **Localiza el ícono ⠿** (6 puntos) a la izquierda del estado
2. **Haz clic y mantén presionado** sobre el ícono
3. **Arrastra** la tarea hacia arriba o abajo
4. **Suelta** en la nueva posición
5. El orden se **guarda automáticamente**

**Efectos visuales durante el arrastre:**
- La tarea arrastrada se vuelve **semi-transparente** (opacity: 0.8)
- Aparece una **sombra** debajo de la tarea
- Las demás tareas se **desplazan** para hacer espacio

#### En Móvil

1. **Mantén presionado** el ícono ⠿ por 100ms
2. **Arrastra** con el dedo
3. **Suelta** en la nueva posición

> **📱 Nota:** En móvil hay un pequeño delay para evitar conflictos con el scroll.

### 10.2 Drag & Drop — Mover Tareas Entre Semanas

**Captura de pantalla:** *[INSERTAR: Tarea siendo arrastrada hacia el sidebar]*

Esta función permite mover una tarea de una semana a otra visualmente:

#### Paso a Paso

1. **Arrastra una tarea** desde la tabla principal
2. **Muévela hacia el sidebar** (izquierda)
3. **Pasa sobre una semana** en la lista
4. La semana destino se **resalta con animación verde**
5. **Suelta** la tarea sobre la semana
6. La tarea **desaparece** de la vista actual
7. **Animación de confirmación** en la semana destino

**Captura de pantalla:** *[INSERTAR: Semana en el sidebar con efecto de "ready to receive"]*

#### Efectos Visuales

**Semana lista para recibir:**
```css
- Fondo verde semi-transparente pulsante
- Borde izquierdo verde brillante (3px)
- Escala ligeramente aumentada (1.02)
- Animación de pulso cada 0.8s
```

**Tarea recibida:**
```css
- Flash verde que se desvanece en 0.6s
- Confirmación visual del movimiento
```

### 10.3 Paginación

**Captura de pantalla:** *[INSERTAR: Controles de paginación debajo de la tabla]*

Si una semana tiene muchas tareas, aparecen controles de paginación:

- **Números de página** — Clic para ir a una página específica
- **Anterior / Siguiente** — Navegación secuencial
- **Indicador** — "Mostrando X-Y de Z tareas"

### 10.4 Atajos de Teclado

| Tecla | Acción |
|-------|--------|
| **Esc** | Cerrar modal abierto |
| **Enter** | Guardar formulario (si hay un campo enfocado) |
| **Enter** | Registrar tiempo (si el input de minutos está enfocado) |

---

## 11. Perfil y Configuración

### 11.1 Menú de Usuario

**Captura de pantalla:** *[INSERTAR: Menú desplegable de usuario abierto]*

#### Abrir el Menú

1. **Haz clic** en tu nombre/avatar en la esquina superior derecha
2. Se despliega un menú con opciones

#### Opciones Disponibles

**1. Perfil**
- Abre la página de configuración de cuenta
- Puedes cambiar:
  - Nombre
  - Email
  - Contraseña
  - Foto de perfil

**2. Cerrar Sesión**
- Finaliza tu sesión actual
- Te redirige al login
- Requiere volver a autenticarte para acceder

### 11.2 Configuración de Perfil

**Captura de pantalla:** *[INSERTAR: Página de configuración de perfil]*

(Detalles específicos dependen de la implementación de Laravel Breeze/Jetstream)

---

## 12. Preguntas Frecuentes

### ❓ ¿Puedo tener tareas sin subtareas?

**Sí.** Las subtareas son opcionales para tareas "Por Tiempo" y obligatorias solo para tareas "Por Subtareas".

### ❓ ¿Qué pasa si trabajo más tiempo del estimado?

El progreso superará el 100% y el campo "Trabajo restante" mostrará un valor negativo en rojo (ej: `-1h 30m`).

### ❓ ¿Puedo mover una tarea a una semana pasada?

**Sí.** Puedes arrastrar tareas a cualquier semana, incluyendo las de "Semanas Anteriores".

### ❓ ¿Cómo elimino una tarea?

Actualmente, la función de eliminación no está implementada visualmente. Como alternativa, puedes mover la tarea a una semana "Archivo" o editar su nombre para marcarla como eliminada.

### ❓ ¿Puedo cambiar el tipo de tarea después de crearla?

**Sí**, pero con restricciones:
- De "Por Tiempo" a "Por Subtareas": Debes agregar al menos 1 subtarea
- De "Por Subtareas" a "Por Tiempo": Las subtareas se mantienen pero el progreso se calcula por tiempo

### ❓ ¿Los datos se guardan automáticamente?

**Sí.** Todos los cambios (registro de tiempo, cambio de estado, reordenamiento) se guardan automáticamente en la base de datos.

### ❓ ¿Puedo usar FlexiWeek en mi teléfono?

**Sí.** La aplicación es completamente responsive y funciona en dispositivos móviles, aunque la experiencia óptima es en escritorio.

### ❓ ¿Qué navegadores son compatibles?

FlexiWeek funciona en todos los navegadores modernos:
- ✅ Chrome/Edge (recomendado)
- ✅ Firefox
- ✅ Safari
- ✅ Opera

### ❓ ¿Puedo exportar mis datos?

Esta función no está implementada actualmente. Los datos se almacenan en la base de datos del servidor.

---

## 📞 Soporte

Para reportar problemas o sugerir mejoras, contacta al administrador del sistema o al equipo de desarrollo.

---

**Versión del Manual:** 1.0  
**Fecha:** Febrero 2026  
**Aplicación:** FlexiWeek — Cronograma Semanal Flexible
