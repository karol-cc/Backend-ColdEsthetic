Cold Esthetic – Backend API

Backend API desarrollado en Laravel 12 para la gestión de datos clínicos, administrativos y de contenido de la clínica estética Cold Esthetic.

El sistema centraliza el registro de pacientes, valoraciones clínicas, procedimientos con precios personalizados, así como la administración de contenidos visuales (Before & After) y leads de contacto.

📌 Descripción del proyecto

Este backend proporciona una API REST que permite administrar:

Registro y gestión de pacientes.

Registro de valoraciones clínicas realizadas por usuarios del sistema (remitentes).

Selección de procedimientos por valoración, con precios definidos durante la evaluación.

Cálculo automático de totales por valoración.

Registro de antecedentes y notas clínicas.

Administración de contenidos visuales (Before & After).

Formularios de contacto para captura y análisis de leads.

Estadísticas y seguimiento comercial.

El sistema está orientado a un uso administrativo, con exposición pública controlada de ciertos contenidos, e integración con aplicaciones frontend web o móviles.

🧠 Modelo funcional (resumen)

Un paciente puede tener múltiples valoraciones.

Cada valoración:

Es realizada por un usuario autenticado (remitente).

Contiene datos clínicos correspondientes a ese momento.

Incluye uno o más procedimientos seleccionados.

Cada procedimiento:

Se activa mediante selección explícita.

Tiene un precio personalizado.

Puede incluir datos adicionales según el tipo (ej. pierna, faja).

El total de la valoración se calcula a partir de los procedimientos registrados.

La información queda almacenada como un registro histórico de la sesión.

🧱 Procedimientos y precios

El sistema no maneja precios fijos ni catálogos cerrados.

Solo se guardan los procedimientos seleccionados.

Cada procedimiento tiene un precio obligatorio cuando está activo.

Los datos adicionales se almacenan como metadata cuando aplica.

El total se obtiene sumando los precios de los procedimientos asociados a la valoración.

🏷️ Manejo de marca (brand_slug)

El sistema está preparado para operar bajo una marca definida por backend.

Cada backend está asociado a una sola marca.

El brand_slug:

Se define en el archivo de configuración.

No se recibe desde el frontend.

Se asigna automáticamente a los registros creados.

Esto garantiza consistencia y evita manipulación de datos.

🛠 Tecnologías utilizadas

PHP 8+

Laravel Framework 12

Laravel Eloquent ORM

MySQL

API REST

Laravel Sanctum (autenticación)

Laravel Storage (gestión de archivos)

UUID

Faker (factories y seeders)

⚙️ Funcionalidades principales

Autenticación de usuarios administrativos.

Gestión de pacientes.

Registro de valoraciones clínicas.

Selección de procedimientos con precios personalizados.

Cálculo automático de totales.

Registro de antecedentes y notas clínicas.

Gestión de contenidos visuales (CRUD de imágenes Before & After).

Subida y almacenamiento seguro de imágenes.

Manejo de formularios de contacto:

Registro de nombre, teléfono, correo, servicio de interés y mensaje.

Validación de datos.

Almacenamiento para análisis y estadísticas.

Estadísticas de servicios más solicitados.

Actualización parcial de registros.

Eliminación automática de archivos asociados.

Exposición pública controlada de contenidos visuales.

📁 Almacenamiento de imágenes

Las imágenes se almacenan en:

storage/app/public


Y se exponen mediante el enlace simbólico:

/public/storage


Es obligatorio ejecutar:

php artisan storage:link

🚀 Instalación y configuración
git clone https://github.com/karool-cc/perfectesthetic-backend.git
cd perfectesthetic-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve


Configurar las variables de entorno en el archivo .env según el entorno de ejecución.

🔒 Seguridad

Autenticación mediante Laravel Sanctum.

Rutas protegidas para acciones administrativas.

Rutas públicas para visualización de contenidos permitidos.

Validación de datos en backend.

👩‍💻 Autoras

Ximena Baquero

Karol Cheverria


