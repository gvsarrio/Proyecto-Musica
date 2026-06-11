# Music Hub — Documento General del Proyecto

**Proyecto:** Music Hub  
**Fecha:** Junio 2026  
**Framework:** Symfony 8.0 · PHP 8.4 · MySQL · Doctrine ORM  
**Entorno de desarrollo:** Docker  
**Despliegue:** Railway (producción)

---

## Índice

1. [Descripción del proyecto](#1-descripción-del-proyecto)
2. [Tecnologías utilizadas](#2-tecnologías-utilizadas)
3. [Arquitectura de la aplicación](#3-arquitectura-de-la-aplicación)
4. [Modelo de datos](#4-modelo-de-datos)
5. [Sistema de autenticación y seguridad](#5-sistema-de-autenticación-y-seguridad)
6. [Módulo de músicos](#6-módulo-de-músicos)
7. [Módulo de bandas](#7-módulo-de-bandas)
8. [Sistema de búsqueda y filtros](#8-sistema-de-búsqueda-y-filtros)
9. [Geolocalización y proximidad](#9-geolocalización-y-proximidad)
10. [Paginación de listados](#10-paginación-de-listados)
11. [Panel de administración](#11-panel-de-administración)
12. [Diseño visual y frontend](#12-diseño-visual-y-frontend)
13. [Datos de prueba — Fixtures](#13-datos-de-prueba--fixtures)
14. [Despliegue en producción](#14-despliegue-en-producción)
15. [Estructura de ficheros](#15-estructura-de-ficheros)
16. [Resumen de funcionalidades implementadas](#16-resumen-de-funcionalidades-implementadas)

---

## 1. Descripción del proyecto

**Music Hub** es una plataforma web diseñada para conectar músicos y bandas en España. Su propósito principal es facilitar el encuentro entre músicos que buscan colaborar, ya sea formando nuevas bandas o incorporándose a proyectos musicales existentes.

### Problema que resuelve

Encontrar músicos o bandas con quien colaborar es un proceso que hoy en día ocurre de forma dispersa: grupos de redes sociales, foros, boca a boca. Music Hub centraliza ese proceso ofreciendo perfiles detallados, búsqueda por instrumento, género musical y proximidad geográfica, y un sistema estructurado de solicitudes de incorporación a bandas.

### Usuarios objetivo

- **Músicos individuales** que quieren darse a conocer y encontrar compañeros o bandas.
- **Bandas** que buscan completar su formación o encontrar músicos para colaboraciones.

### Flujo principal de uso

1. El usuario se registra y crea un perfil de músico indicando sus instrumentos, géneros musicales y ubicación.
2. Puede explorar el listado de músicos y bandas, filtrando por estilo musical, instrumento o proximidad.
3. Si encuentra una banda que le interesa, puede enviar una solicitud de unión.
4. Los administradores de la banda reciben la solicitud y la aceptan o rechazan.
5. Alternativamente, un músico con perfil puede crear su propia banda e invitar a otros músicos directamente.

---

## 2. Tecnologías utilizadas

### Backend

| Tecnología | Versión | Uso |
|---|---|---|
| PHP | 8.4 | Lenguaje principal del servidor |
| Symfony | 8.0 | Framework MVC |
| Doctrine ORM | 3.6 | Mapeo objeto-relacional |
| Doctrine Migrations | 4.0 | Gestión de esquema de base de datos |
| KnpPaginatorBundle | 6.10 | Paginación de listados |
| EasyAdminBundle | 5.0 | Panel de administración |
| SymfonyCasts Verify Email Bundle | 1.18 | Verificación de email (integrado, actualmente deshabilitado) |
| Symfony Security Bundle | 8.0 | Autenticación y control de acceso |
| Symfony Mailer | 8.0 | Envío de emails (configurado con null transport en dev) |

### Base de datos

| Tecnología | Uso |
|---|---|
| MySQL | Motor de base de datos principal |
| phpMyAdmin | Gestión visual de la base de datos (entorno Docker) |

### Frontend

| Tecnología | Uso |
|---|---|
| Twig 3.x | Motor de plantillas HTML |
| Stimulus (Hotwire) | Framework JavaScript reactivo basado en controladores |
| Turbo (Hotwire) | Navegación AJAX sin recargas completas |
| CSS propio | Diseño visual personalizado por secciones |
| Font Awesome | Iconografía |
| Bootstrap (utilidades) | Clases utilitarias de layout |
| Google Places API | Autocompletado de ubicaciones geográficas |

### Infraestructura

| Tecnología | Uso |
|---|---|
| Docker | Contenedores para desarrollo local (PHP, MySQL, phpMyAdmin) |
| Railway | Plataforma de despliegue en producción |
| Git / GitHub | Control de versiones y colaboración |

---

## 3. Arquitectura de la aplicación

La aplicación sigue el patrón **MVC (Model-View-Controller)** propio de Symfony:

- **Model:** Entidades Doctrine (`src/Entity/`) con sus repositorios (`src/Repository/`).
- **View:** Plantillas Twig (`templates/`), CSS propio (`public/css/`) y controladores Stimulus (`assets/controllers/`).
- **Controller:** Controladores PHP (`src/Controller/`) que orquestan la lógica de negocio.

### Capas de la aplicación

```
Petición HTTP
    ↓
Router (atributos #[Route] en controladores)
    ↓
Controller → Repository → Database
    ↓
Twig Template → Respuesta HTML
```

### Patrones adicionales

- **Repository Pattern:** Cada entidad tiene su repositorio con métodos de consulta personalizados. Los filtros de búsqueda (`findByFiltros`) están encapsulados en los repositorios, no en los controladores.
- **Traits:** La fórmula Haversine para calcular distancias geográficas está extraída en el trait `HasHaversine`, reutilizado por `MusicoRepository` y `BandaRepository`.
- **Form Types:** La lógica de construcción y validación de formularios está separada en clases `*Type` (`src/Form/`).
- **Symfony Security:** El control de acceso se declara en `security.yaml` y se complementa con `denyAccessUnlessGranted()` en los controladores.

---

## 4. Modelo de datos

### Diagrama conceptual de entidades

```
Usuario ──────────── Musico ─────────────── InstrumentoSistema
  (1:1)               │  │                    (N:M)
                      │  └──────────────── InstrumentoPersonalizado
                      │                       (N:M)
                      │
                      ├──── Genero (N:M)
                      │
                      └──── MiembroBanda ──── Banda
                               (N:1)           │
                                               └──── Genero (N:M)
```

### Descripción de entidades

#### Usuario
Entidad central de autenticación. Implementa `UserInterface` de Symfony.

| Campo | Tipo | Descripción |
|---|---|---|
| id | int | Identificador único |
| email | string(180) | Email único, usado como identificador de login |
| password | string | Contraseña hasheada (bcrypt/auto) |
| roles | array | Array de roles (`ROLE_USER`, `ROLE_ADMIN`) |
| isVerified | boolean | Si el email ha sido verificado |

Cada usuario puede tener exactamente un perfil de músico asociado (relación OneToOne).

---

#### Musico
Perfil público del músico, visible en el listado y buscable por filtros.

| Campo | Tipo | Descripción |
|---|---|---|
| id | int | Identificador único |
| nombre | string(100) | Nombre artístico o real |
| telefono | int | Teléfono de contacto (opcional) |
| biografia | text | Descripción libre de su trayectoria |
| ubicacion | string(255) | Ciudad o región |
| latitud | float | Coordenada para cálculo de proximidad |
| longitud | float | Coordenada para cálculo de proximidad |
| anyos_experiencia | int | Años de experiencia (0-100) |
| imagen_url | string(255) | Nombre del fichero de foto de perfil |
| creado_en | DateTime | Fecha de creación |
| actualizado_en | DateTime | Fecha de última modificación |

**Relaciones:**
- `usuario` (1:1) — El usuario propietario del perfil.
- `instrumentosSistema` (N:M) — Instrumentos estándar del catálogo.
- `instrumentosPersonalizados` (N:M) — Instrumentos creados por el propio usuario.
- `generosMusicales` (N:M) — Géneros musicales que interpreta.
- `miembroBandas` (1:N) — Relaciones con bandas (activas, pendientes, rechazadas, invitaciones).

---

#### Banda
Perfil público de una banda, gestionada por uno o varios administradores.

| Campo | Tipo | Descripción |
|---|---|---|
| id | int | Identificador único |
| nombre | string(100) | Nombre de la banda |
| biografia | text | Historia y descripción de la banda |
| anyo_formacion | int | Año en que se fundó |
| ubicacion | string(255) | Ciudad o región base |
| latitud | float | Coordenada para proximidad |
| longitud | float | Coordenada para proximidad |
| imagen_url | string(255) | Nombre del fichero de foto de banda |

**Relaciones:**
- `generosMusicales` (N:M) — Géneros musicales de la banda.
- `miembroBandas` (1:N) — Músicos asociados a la banda.

---

#### MiembroBanda
Tabla de unión entre Musico y Banda que almacena el estado de la relación.

| Campo | Tipo | Valores posibles | Descripción |
|---|---|---|---|
| id | int | — | Identificador único |
| estado | string(20) | `pendiente`, `aceptado`, `rechazado`, `invitado` | Estado actual de la relación |
| es_administrador | boolean | true/false | Si el músico administra la banda |
| rol_banda | string(100) | libre | Instrumentos que toca en esta banda concreta |

**Ciclo de vida de los estados:**

```
[músico solicita unirse]  → pendiente
[admin acepta]            → aceptado
[admin rechaza]           → rechazado
[admin invita músico]     → invitado
[músico acepta invitación]→ aceptado
[músico rechaza invitación] → rechazado
```

---

#### Genero
Catálogo de géneros musicales disponibles para músicos y bandas.

19 géneros predefinidos: Rock, Pop, Jazz, Blues, Flamenco, Clásica, Metal, Punk, Reggae, Hip-Hop, Electrónica, Folk, R&B, Soul, Funk, Country, Latina, Indie, Alternativo.

---

#### InstrumentoSistema
Catálogo de instrumentos predefinidos disponibles para todos los usuarios.

10 instrumentos: Voz, Guitarra Eléctrica, Bajo Eléctrico, Batería, Teclado / Piano, Guitarra Acústica, Saxofón, Violín, Sintetizador, Percusión.

---

#### InstrumentoPersonalizado
Instrumento creado por un usuario específico cuando el catálogo estándar no cubre su caso. Se crea vía AJAX desde el formulario de perfil y pertenece al usuario que lo creó.

---

## 5. Sistema de autenticación y seguridad

### Registro

El usuario se registra con email y contraseña. En el estado actual la verificación de email está **integrada pero deshabilitada**: el usuario queda verificado automáticamente tras el registro y es redirigido al inicio de sesión sin necesidad de confirmar el email. La infraestructura para verificación (bundle, email de confirmación, ruta `/verify/email`) existe y puede activarse cuando se configure un servidor SMTP real.

### Login

Formulario de login estándar de Symfony Security. El identificador de usuario es el email. La contraseña se hashea con algoritmo `auto` (bcrypt). Token CSRF habilitado en el formulario.

### Roles y jerarquía

```
ROLE_ADMIN
    ↑ hereda
ROLE_USER
```

- `ROLE_USER`: Acceso a crear/editar perfil de músico y banda, explorar listados.
- `ROLE_ADMIN`: Acceso adicional al panel de administración (`/admin`).

### Control de acceso por ruta

| Patrón de ruta | Rol requerido |
|---|---|
| `/admin/*` | ROLE_ADMIN |
| `/musico/*` | ROLE_USER |
| `/banda/*` | ROLE_USER |
| Resto | Público |

### Protecciones adicionales a nivel de controlador

Además del control de acceso por ruta, los controladores aplican verificaciones de propiedad:

- Un músico solo puede editar o eliminar **su propio perfil**.
- Solo el administrador de una banda puede editar la banda, gestionar solicitudes, invitar músicos, expulsar miembros o eliminar la banda.
- Un músico no puede eliminar su perfil mientras pertenezca a bandas activas.
- No se puede eliminar una banda si tiene más de un miembro aceptado.
- Siempre debe haber al menos un administrador en cada banda.

Todos los formularios de acción destructiva (eliminar, expulsar, salir) están protegidos con **tokens CSRF**.

---

## 6. Módulo de músicos

### Crear perfil

Un usuario autenticado puede crear **un único perfil de músico**. El formulario recoge:

- Nombre, teléfono (opcional), biografía.
- Ubicación con autocompletado de Google Places (guarda latitud y longitud automáticamente).
- Años de experiencia.
- Foto de perfil (JPG/PNG/WEBP, máximo 2MB, almacenada en `public/uploads/perfiles/`).
- Instrumentos del catálogo (selección múltiple) + posibilidad de añadir instrumentos personalizados vía AJAX sin recargar la página.
- Géneros musicales (selección múltiple).

### Ver perfil

La vista de detalle de un músico muestra toda su información pública. Si el visitante es un **administrador de banda**, verá un botón para invitar al músico a su banda. Si el visitante es el **propio músico**, verá sus invitaciones pendientes de bandas y podrá aceptarlas o rechazarlas desde la misma vista.

### Editar y eliminar perfil

El músico puede actualizar todos sus datos en cualquier momento. La eliminación del perfil está bloqueada si el músico pertenece a alguna banda activa (estado `aceptado`, `pendiente` o `invitado`), para mantener la integridad de las bandas.

---

## 7. Módulo de bandas

### Crear banda

Solo un usuario con perfil de músico puede crear una banda. Al crearla, el músico fundador queda automáticamente como miembro `aceptado` con `es_administrador = true`. El formulario recoge nombre, biografía, géneros, año de formación, ubicación y foto.

### Gestión de miembros

El sistema de membresía distingue dos vías de incorporación:

**Vía solicitud (músico toma la iniciativa):**
1. El músico ve una banda en el listado y pulsa "Solicitar unión".
2. Indica qué instrumentos tocaría en esa banda.
3. La solicitud queda en estado `pendiente`.
4. El administrador la acepta o rechaza desde el panel de solicitudes de la banda.

**Vía invitación (banda toma la iniciativa):**
1. El administrador ve un perfil de músico y pulsa "Invitar a mi banda".
2. La relación queda en estado `invitado`.
3. El músico recibe la invitación en su perfil y decide aceptarla o rechazarla.

### Panel de solicitudes (solo admins)

Los administradores acceden a una vista que muestra:
- Miembros actualmente aceptados con sus roles e instrumentos.
- Solicitudes pendientes de aprobación.
- Acciones: aceptar, rechazar, expulsar, promover a admin, quitar admin, editar rol.

### Reglas de negocio de bandas

- **Mínimo un admin:** No se puede quitar el rol de administrador al último admin ni expulsarlo.
- **Eliminación controlada:** Una banda solo puede eliminarse si tiene un único miembro (el admin que la elimina). Si hay más miembros aceptados, primero deben ser expulsados o deben salir voluntariamente.
- **Salida voluntaria de admins:** Un admin puede salir de la banda solo si hay otro admin que pueda hacerse cargo.

---

## 8. Sistema de búsqueda y filtros

El sidebar de filtros es un **componente Twig compartido** (`_filtros_sidebar.html.twig`) usado tanto en el listado de músicos como en el de bandas.

### Filtros disponibles

| Filtro | Músicos | Bandas |
|---|---|---|
| Género musical | ✓ (selección múltiple) | ✓ (selección múltiple) |
| Instrumento | ✓ | — |
| Proximidad (ciudad + radio) | ✓ | ✓ |

### Cómo funciona técnicamente

Los filtros se envían como parámetros GET con la estructura `filtros[generos][]=1&filtros[radio]=50`. El controlador los lee con `$request->query->all('filtros')` y los pasa al método `findByFiltros()` del repositorio correspondiente.

### Implementación en repositorios

El método `findByFiltros` construye una QueryBuilder de Doctrine que:

1. Si hay géneros seleccionados: hace `INNER JOIN` con la tabla `generosMusicales` y filtra por los IDs.
2. Si hay instrumentos seleccionados (solo músicos): hace `INNER JOIN` con `instrumentosSistema` y filtra.
3. Si hay ubicación y radio: recupera todos los resultados de los filtros anteriores y filtra en PHP usando la fórmula Haversine (ver sección 9).

El filtrado por radio se hace en PHP (no en SQL) porque MySQL no incluye funciones trigonométricas accesibles fácilmente en todos los entornos y la fórmula Haversine requiere varias operaciones matemáticas.

---

## 9. Geolocalización y proximidad

### Captura de coordenadas

Al escribir una ubicación en el formulario de perfil (músico o banda), el controlador Stimulus `google-places-autocomplete_controller.js` invoca la **API de Google Places** para mostrar sugerencias y capturar automáticamente la latitud y longitud exactas, que se almacenan en campos ocultos del formulario.

### Cálculo de distancia: Fórmula Haversine

La distancia entre dos puntos geográficos (coordenadas del usuario que filtra y coordenadas de cada músico/banda) se calcula con la **fórmula Haversine**, implementada en el trait `HasHaversine` reutilizado por ambos repositorios.

```
d = 2R · arcsin(√(sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlon/2)))
```

Donde R = 6371 km (radio de la Tierra).

### Flujo de búsqueda por proximidad

1. El usuario escribe su ciudad en el sidebar y usa el slider para seleccionar radio (10–100 km).
2. Alternativamente puede pulsar "Usar mi ubicación" (Geolocation API del navegador).
3. La latitud, longitud y radio se añaden como parámetros del filtro.
4. El repositorio calcula la distancia de cada perfil y devuelve solo los que están dentro del radio.
5. En la vista, cada tarjeta muestra la distancia exacta en kilómetros.

---

## 10. Paginación de listados

Los listados de músicos y bandas están paginados con **KnpPaginatorBundle**, mostrando **12 elementos por página**.

La paginación usa el modo **array** (en memoria): primero se obtiene el array completo con filtros y se calculan las distancias, y después el bundle recorta la página solicitada. Esto preserva la compatibilidad con el cálculo Haversine en PHP.

Los controles de paginación (botones Anterior / Siguiente + indicador de página) usan el estilo visual del proyecto (botón morado `btn-principal`) y están centrados bajo la lista de tarjetas.

Los parámetros de filtro activos se preservan automáticamente al cambiar de página.

---

## 11. Panel de administración

Implementado con **EasyAdminBundle 5.0**, accesible en `/admin` solo para usuarios con `ROLE_ADMIN`.

### Funcionalidades del admin

- **Gestión de usuarios:** listado de todos los usuarios registrados, con posibilidad de crear, editar y eliminar cuentas.
- **Vista de datos:** acceso a los datos de la base de datos de forma tabulada.

El panel de admin es la única vía para gestionar usuarios directamente (cambiar contraseñas, modificar roles, dar de baja cuentas).

---

## 12. Diseño visual y frontend

### Identidad visual

Music Hub tiene un diseño oscuro e inmersivo que evoca el mundo de la música en directo:

- **Fondo:** Degradado azul oscuro con blobs de color animados (púrpura, magenta, cian) que se mueven de forma fluida y reaccionan al movimiento del cursor en escritorio.
- **Colores principales:**
  - Morado/púrpura: `rgba(155, 48, 255, ...)` — botón principal, acentos.
  - Cian: `rgba(0, 242, 255, ...)` — botón confirmar/guardar, detalles.
  - Magenta: `rgba(255, 0, 255, ...)` — botón editar.
- **Tipografía:** Fuentes Google Fonts, con combinación de display bold para títulos y body legible para texto.
- **Bordes:** Redondeados con `border-radius: 50px` en botones y `border-radius` suaves en tarjetas.
- **Efectos:** `backdrop-filter: blur()` en tarjetas y botones para efecto glassmorphism. Glow effect en hover.

### Estructura de CSS

| Archivo | Ámbito |
|---|---|
| `base.css` | Estilos globales, navbar, notificaciones, componentes compartidos |
| `home.css` | Página de inicio |
| `auth.css` | Formularios de login y registro |
| `perfil.css` | Perfiles de músicos y bandas, tarjetas horizontales, filtros, formularios de perfil |

### Responsive

La aplicación está adaptada para móvil y tablet:
- El sidebar de filtros se oculta en móvil y aparece con un botón "Filtros".
- El grid de dos columnas de tarjetas pasa a una columna en pantallas pequeñas.
- Los formularios y botones se adaptan al ancho disponible.

### JavaScript con Stimulus

Stimulus gestiona los comportamientos interactivos sin jQuery ni frameworks pesados:

- **`google-places-autocomplete_controller`:** Invoca Google Places API para el campo de ubicación.
- **`csrf_protection_controller`:** Gestión de tokens CSRF en formularios dinámicos.
- Adición de instrumentos personalizados vía AJAX (fetch al endpoint `/instrumento/add`) sin recargar la página.

---

## 13. Datos de prueba — Fixtures

Para facilitar el desarrollo y las demostraciones, la aplicación incluye fixtures que cargan datos realistas automáticamente.

### Volumen de datos

| Fixture | Registros creados |
|---|---|
| AppFixtures | 10 instrumentos del sistema |
| GeneroFixtures | 19 géneros musicales |
| MusicoMasivoFixtures | 200 perfiles de músico (100 hombres + 100 mujeres) |
| BandaMasivaFixtures | 100 bandas con miembros asignados |

### Datos de los músicos generados

- Nombres y apellidos reales en español.
- Distribuidos en 20 ciudades españolas (Madrid, Barcelona, Valencia, Sevilla, Zaragoza, Málaga, Bilbao, Alicante, Córdoba, Valladolid, Murcia, Palma, Las Palmas, Santa Cruz de Tenerife, Granada, Salamanca, Toledo, Burgos, Pamplona, San Sebastián).
- Fotos de stock descargadas de Pexels (50 retratos masculinos + 50 femeninos), almacenadas en el repositorio.
- Biografías generadas según el instrumento principal.
- 2-3 géneros musicales por perfil.
- Años de experiencia entre 1 y 30.
- **Credenciales comunes:** email `musico_m_001@musichub.com` … `musico_f_100@musichub.com`, contraseña `musichub123`.

### Datos de las bandas generadas

- 100 bandas con nombres en español.
- Año de formación entre 1975 y 2020.
- Fotos de stock de grupos musicales (Pexels).
- 1 a 5 miembros por banda, tomados de los 200 músicos generados.
- Cada músico puede pertenecer a un máximo de 2 bandas.

### Cómo ejecutar los fixtures

```bash
php bin/console doctrine:fixtures:load
```

---

## 14. Despliegue en producción

La aplicación está desplegada en **Railway**, una plataforma PaaS que gestiona tanto la aplicación PHP como la base de datos MySQL.

### Servicios en Railway

- **Servicio PHP:** Contenedor con la aplicación Symfony.
- **Servicio MySQL:** Base de datos MySQL gestionada por Railway.

### Variables de entorno en producción

Railway inyecta la variable `DATABASE_URL` automáticamente con los datos de conexión del servicio MySQL. El resto de variables de entorno se configuran en el panel de Railway.

### Importar base de datos en Railway

Para subir datos locales a Railway se usa la consola MySQL integrada en el dashboard:

1. Exportar la base de datos local: `mysqldump -u root proyecto_musica > dump.sql`
2. Subir el fichero con el botón Upload de la consola Railway.
3. Importar desactivando temporalmente las comprobaciones de claves foráneas:

```bash
(echo "SET FOREIGN_KEY_CHECKS=0;"; cat /var/lib/mysql/dump.sql; echo "SET FOREIGN_KEY_CHECKS=1;") | mysql -u root -p$MYSQL_ROOT_PASSWORD railway
```

---

## 15. Estructura de ficheros

```
Proyecto-Musica/
├── assets/
│   ├── app.js
│   ├── stimulus_bootstrap.js
│   └── controllers/
│       ├── google-places-autocomplete_controller.js
│       └── csrf_protection_controller.js
├── config/
│   ├── packages/
│   │   ├── security.yaml
│   │   ├── doctrine.yaml
│   │   └── knp_paginator.yaml
│   └── routes.yaml
├── docs/                                   ← documentación técnica
│   ├── proyecto-general.md                 (este documento)
│   ├── gestion-bandas.md
│   ├── filtros-musicos.md
│   ├── filtros-bandas.md
│   ├── generos-musicales.md
│   ├── refactoring-instrumentos.md
│   ├── refactoring-filtros.md
│   ├── fixtures-carga-masiva.md
│   ├── paginacion-listados.md
│   ├── despliegue-railway.md
│   ├── fix-excepcion-borrar-banda.md
│   └── guia-pruebas-rama-filtros.md
├── public/
│   ├── css/
│   │   ├── base.css
│   │   ├── home.css
│   │   ├── auth.css
│   │   └── perfil.css
│   └── uploads/
│       ├── perfiles/                       ← fotos de músicos
│       └── bandas/                         ← fotos de bandas
├── src/
│   ├── Controller/
│   │   ├── InicioController.php
│   │   ├── LoginController.php
│   │   ├── RegistrationController.php
│   │   ├── MusicoController.php
│   │   ├── BandaController.php
│   │   ├── InstrumentoController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       └── UsuarioCrudController.php
│   ├── DataFixtures/
│   │   ├── AppFixtures.php
│   │   ├── GeneroFixtures.php
│   │   ├── MusicoMasivoFixtures.php
│   │   └── BandaMasivaFixtures.php
│   ├── Entity/
│   │   ├── Usuario.php
│   │   ├── Musico.php
│   │   ├── Banda.php
│   │   ├── MiembroBanda.php
│   │   ├── Genero.php
│   │   ├── InstrumentoSistema.php
│   │   └── InstrumentoPersonalizado.php
│   ├── Form/
│   │   ├── RegistrationFormType.php
│   │   ├── MusicoType.php
│   │   └── BandaType.php
│   └── Repository/
│       ├── Traits/
│       │   └── HasHaversine.php
│       ├── UsuarioRepository.php
│       ├── MusicoRepository.php
│       ├── BandaRepository.php
│       ├── MiembroBandaRepository.php
│       ├── GeneroRepository.php
│       ├── InstrumentoSistemaRepository.php
│       └── InstrumentoPersonalizadoRepository.php
└── templates/
    ├── base.html.twig
    ├── inicio/
    ├── login/
    ├── registration/
    ├── musico/
    ├── banda/
    ├── _filtros_sidebar.html.twig
    ├── _filtros_proximidad_script.html.twig
    └── bundles/KnpPaginatorBundle/Pagination/
        └── sliding.html.twig
```

---

## 16. Resumen de funcionalidades implementadas

### Autenticación y usuarios
- [x] Registro de usuarios con email y contraseña
- [x] Login / Logout con protección CSRF
- [x] Sistema de roles (ROLE_USER, ROLE_ADMIN)
- [x] Infraestructura de verificación de email (pendiente de activar con SMTP)

### Perfiles de músicos
- [x] Crear, editar y eliminar perfil de músico
- [x] Foto de perfil con subida de fichero
- [x] Selección de instrumentos del catálogo y personalizados (AJAX)
- [x] Selección de géneros musicales
- [x] Ubicación con autocompletado Google Places y coordenadas GPS
- [x] Vista pública del perfil con botón de invitación a banda

### Bandas
- [x] Crear, editar y eliminar banda
- [x] Foto de banda con subida de fichero
- [x] Sistema de solicitudes de unión (músico → banda)
- [x] Sistema de invitaciones (banda → músico)
- [x] Panel de gestión de miembros para administradores
- [x] Gestión de roles de administrador (promover, quitar)
- [x] Expulsión de miembros y salida voluntaria
- [x] Protecciones de integridad (mínimo un admin, bloqueo de eliminación con miembros)

### Búsqueda y descubrimiento
- [x] Listado de músicos con filtros por género, instrumento y proximidad
- [x] Listado de bandas con filtros por género y proximidad
- [x] Búsqueda por radio con slider (10-100 km)
- [x] Geolocalización automática del navegador
- [x] Visualización de distancia en km en cada tarjeta
- [x] Paginación de listados (12 por página)

### Administración
- [x] Panel de admin con EasyAdminBundle
- [x] CRUD de usuarios desde el panel

### Infraestructura
- [x] Entorno de desarrollo con Docker
- [x] Despliegue en producción con Railway
- [x] Fixtures con 200 músicos y 100 bandas con fotos reales
- [x] Documentación técnica de todas las funcionalidades

---

*Este documento ofrece una visión completa del proyecto Music Hub. Para el detalle técnico de cada funcionalidad, consultar los documentos específicos en la carpeta `docs/`.*
