# Cold Esthetic – Backend API

Backend API desarrollada en **Laravel 12** para la gestión de datos clínicos, administrativos y de contenido de la clínica estética **Cold Esthetic**.

El sistema centraliza:

- Registro de pacientes
- Valoraciones clínicas
- Procedimientos con precios personalizados
- Contenido visual (Before & After)
- Leads de contacto

## Contenido

- [Descripción](#descripción)
- [Modelo funcional](#modelo-funcional)
- [Modelo de datos (ERD)](#modelo-de-datos-erd)
- [Tecnologías](#tecnologías)
- [Funcionalidades](#funcionalidades)
- [Almacenamiento de imágenes](#almacenamiento-de-imágenes)
- [Instalación](#instalación)
- [Seguridad](#seguridad)
- [Autoras](#autoras)

## Descripción

Este backend expone una **API REST** orientada a uso administrativo, con exposición pública controlada de ciertos contenidos y fácil integración con aplicaciones web o móviles.

Permite administrar:

- Registro y gestión de pacientes.
- Registro de valoraciones clínicas realizadas por usuarios del sistema (remitentes).
- Selección de procedimientos por valoración, con precios definidos durante la evaluación.
- Cálculo automático de totales por valoración.
- Registro de antecedentes y notas clínicas.
- Administración de contenidos visuales (Before & After).
- Formularios de contacto para captura y análisis de leads.
- Estadísticas y seguimiento comercial.

## Modelo funcional

- Un paciente puede tener múltiples valoraciones.
- Cada valoración:
	- Es realizada por un usuario autenticado (remitente).
	- Contiene datos clínicos correspondientes a ese momento.
	- Incluye uno o más procedimientos seleccionados.
- Cada procedimiento:
	- Se activa mediante selección explícita.
	- Tiene un precio personalizado.
	- Puede incluir datos adicionales según el tipo (ej. pierna, faja).
- El total de la valoración se calcula sumando los precios de los procedimientos asociados.
- La información queda almacenada como un registro histórico de la sesión.

### Procedimientos y precios

- El sistema no maneja precios fijos ni catálogos cerrados.
- Solo se guardan los procedimientos seleccionados.
- Cada procedimiento tiene un precio obligatorio cuando está activo.
- Los datos adicionales se almacenan como metadata cuando aplica.

### Manejo de marca (brand_slug)

- Cada backend está asociado a **una sola marca**.
- `brand_slug`:
	- Se define en el archivo de configuración.
	- No se recibe desde el frontend.
	- Se asigna automáticamente a los registros creados.

## Modelo de datos (ERD)

Recomendación para que el diagrama se vea directo en GitHub:

1) Versiona el archivo fuente `.drawio`.
2) Exporta el diagrama a **PNG o SVG** y súbelo también.

Ejemplo de estructura recomendada:

```
docs/
	diagrams/
		database-erd.drawio
		database-erd.png
```

Cuando tengas el export, lo puedes mostrar así:

![Modelo de datos (ERD)](docs/diagrams/database-erd.png)

Y dejar el fuente para editar:

- Fuente (draw.io): docs/diagrams/database-erd.drawio

### ¿Cómo agregar un draw.io al repo?

Opción A (recomendada): **diagrams.net + export**

1. Crea/edita tu diagrama en https://app.diagrams.net/
2. Guarda el archivo como: `docs/diagrams/database-erd.drawio`
3. Exporta a PNG o SVG:
	 - **File → Export as → PNG** (o SVG)
	 - Guarda como: `docs/diagrams/database-erd.png`
4. Sube ambos archivos al repo.

Opción B: **editar en VS Code**

- Instala la extensión “Draw.io Integration” (ID: `hediet.vscode-drawio`).
- Abre el archivo `.drawio` desde VS Code, edita y exporta PNG/SVG para el README.

## Tecnologías

- PHP 8+
- Laravel Framework 12
- Laravel Eloquent ORM
- MySQL
- API REST
- Laravel Sanctum (autenticación)
- Laravel Storage (gestión de archivos)
- UUID
- Faker (factories y seeders)

## Funcionalidades

- Autenticación de usuarios administrativos.
- Gestión de pacientes.
- Registro de valoraciones clínicas.
- Selección de procedimientos con precios personalizados.
- Cálculo automático de totales.
- Registro de antecedentes y notas clínicas.
- Gestión de contenidos visuales (CRUD de imágenes Before & After).
- Subida y almacenamiento seguro de imágenes.
- Manejo de formularios de contacto (leads): validación, almacenamiento y estadísticas.
- Actualización parcial de registros.
- Eliminación automática de archivos asociados.
- Exposición pública controlada de contenidos.

## Almacenamiento de imágenes

Las imágenes se almacenan en:

`storage/app/public`

Y se exponen mediante el enlace simbólico:

`/public/storage`

Es obligatorio ejecutar:

```bash
php artisan storage:link
```

## Instalación

```bash
git clone https://github.com/karool-cc/perfectesthetic-backend.git
cd perfectesthetic-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configura las variables de entorno en `.env` según tu entorno.

## Seguridad

- Autenticación mediante Laravel Sanctum.
- Rutas protegidas para acciones administrativas.
- Rutas públicas para visualización de contenidos permitidos.
- Validación de datos en backend.

## Autoras

- Ximena Baquero
- Karol Cheverria


