# Sistema de Chat — Mensajes entre Músicos

**Proyecto:** Music Hub  
**Rama de origen:** `feature/chat` (mergeada en `main`, junio 2026)  
**Fecha:** Junio 2026  
**Framework:** Symfony 8.0 · PHP 8.4 · Doctrine ORM · MySQL · Twig

---

## Índice

1. [Descripción general](#1-descripción-general)
2. [Arquitectura y archivos implicados](#2-arquitectura-y-archivos-implicados)
3. [Modelo de datos](#3-modelo-de-datos)
4. [Repositorios](#4-repositorios)
5. [Controlador — MensajesController](#5-controlador--mensajescontroller)
6. [Extensión Twig — mensajes_no_leidos()](#6-extensión-twig--mensajes_no_leidos)
7. [Icono de chat en el navbar](#7-icono-de-chat-en-el-navbar)
8. [Acceso desde tarjetas y perfiles de músico](#8-acceso-desde-tarjetas-y-perfiles-de-músico)
9. [Plantillas](#9-plantillas)
10. [Seguridad y control de acceso](#10-seguridad-y-control-de-acceso)
11. [Migración](#11-migración)
12. [Decisiones técnicas](#12-decisiones-técnicas)

---

## 1. Descripción general

El sistema de chat permite a cualquier músico registrado iniciar una conversación privada con otro músico. Cada par de usuarios tiene como máximo **una conversación**; si ya existe, el sistema redirige a ella en lugar de crear una nueva.

Las funcionalidades principales son:

| Funcionalidad | Descripción |
|---|---|
| Iniciar conversación | Desde la tarjeta del listado o desde el perfil de un músico |
| Enviar mensajes | Formulario en la vista de conversación |
| Marcar como leído | Al entrar en una conversación, los mensajes del otro usuario se marcan automáticamente como leídos |
| Contador de no leídos | Icono de chat en el navbar muestra un badge con el total de mensajes sin leer |
| Lista de conversaciones | Vista `/mensajes` con todas las conversaciones activas y preview del último mensaje |

---

## 2. Arquitectura y archivos implicados

```
Frontend
│
├── templates/mensajes/index.html.twig         — lista de conversaciones
├── templates/mensajes/conversacion.html.twig  — vista de una conversación
├── templates/base.html.twig                   — icono de chat en el navbar con badge
└── public/css/perfil.css                      — estilos (clases .mensajes-* y .chat-*)

Backend
│
├── src/Entity/Conversacion.php                — entidad conversación
├── src/Entity/Mensaje.php                     — entidad mensaje
├── src/Entity/Usuario.php                     — relaciones inversas hacia conversaciones y mensajes
├── src/Repository/ConversacionRepository.php  — consultas de conversaciones
├── src/Repository/MensajeRepository.php       — consultas de mensajes y contador de no leídos
├── src/Controller/MensajesController.php      — rutas: index, nuevo, conversacion
├── src/Form/MensajeType.php                   — formulario de envío de mensaje
├── src/Twig/Extension/MensajesExtension.php   — registro de la función Twig
└── src/Twig/Runtime/MensajesExtensionRuntime.php — lógica de mensajes_no_leidos()

Base de datos
│
├── conversacion   — tabla de conversaciones (par de usuarios)
└── mensaje        — tabla de mensajes individuales
```

---

## 3. Modelo de datos

### 3.1 Entidad `Conversacion`

```
conversacion
├── id              INT AUTO_INCREMENT PK
├── usuario_uno_id  FK → usuario.id
├── usuario_dos_id  FK → usuario.id
├── fecha_creacion  DATETIME
└── fecha_ultimo_mensaje  DATETIME (nullable)
```

No existe un campo "iniciador" diferenciado: el orden `usuarioUno` / `usuarioDos` es simplemente el orden en que se creó la conversación. Para determinar "el otro usuario" en las plantillas se compara con `app.user`:

```twig
{% if conversacion.usuarioUno == app.user %}
    {% set otroUsuario = conversacion.usuarioDos %}
{% else %}
    {% set otroUsuario = conversacion.usuarioUno %}
{% endif %}
```

### 3.2 Entidad `Mensaje`

```
mensaje
├── id               INT AUTO_INCREMENT PK
├── conversacion_id  FK → conversacion.id
├── remitente_id     FK → usuario.id
├── contenido        LONGTEXT
├── fecha_envio      DATETIME
└── leido            TINYINT (0 = no leído, 1 = leído)
```

El campo `leido` está referenciado al punto de vista del **destinatario**: un mensaje se marca como leído cuando el usuario que lo recibe entra en la conversación.

### 3.3 Relaciones en `Usuario`

La entidad `Usuario` tiene tres colecciones añadidas por el sistema de chat:

```php
#[ORM\OneToMany(targetEntity: Conversacion::class, mappedBy: 'usuarioUno')]
private Collection $conversacionesComoUsuarioUno;

#[ORM\OneToMany(targetEntity: Conversacion::class, mappedBy: 'usuarioDos')]
private Collection $conversacionesComoUsuarioDos;

#[ORM\OneToMany(targetEntity: Mensaje::class, mappedBy: 'remitente')]
private Collection $mensajes;
```

---

## 4. Repositorios

### 4.1 `ConversacionRepository`

**`buscarPorUsuario(Usuario $usuario): array`**

Devuelve todas las conversaciones en las que participa el usuario (como `usuarioUno` o como `usuarioDos`), ordenadas por `fechaUltimoMensaje` descendente para mostrar primero las más recientes.

**`buscarEntreUsuarios(Usuario $uno, Usuario $dos): ?Conversacion`**

Busca si ya existe una conversación entre los dos usuarios, independientemente del orden en que estén guardados. Utilizada en `MensajesController::nuevo()` para evitar duplicados:

```php
// Busca en ambas combinaciones posibles
WHERE (usuarioUno = $uno AND usuarioDos = $dos)
   OR (usuarioUno = $dos AND usuarioDos = $uno)
```

### 4.2 `MensajeRepository`

**`contarNoLeidosEnConversacion(Conversacion $conv, Usuario $usuario): int`**

Cuenta los mensajes de una conversación que no han sido leídos por el usuario dado (es decir, mensajes donde `remitente != $usuario` y `leido = false`). Usado en la lista de conversaciones para mostrar el badge por conversación.

---

## 5. Controlador — MensajesController

**Archivo:** `src/Controller/MensajesController.php`

### Ruta `GET /mensajes` → `app_mensajes`

Lista todas las conversaciones del usuario autenticado. Para cada conversación calcula cuántos mensajes no leídos hay y lo pasa a la plantilla como array `noLeidosPorConversacion[id] = N`.

### Ruta `GET /mensajes/nuevo/{id}` → `mensaje_nuevo`

El parámetro `{id}` es el **ID del usuario destinatario** (no del músico). Esta ruta es el punto de entrada del chat desde el listado o el perfil:

```
1. Comprueba que el usuario actual está autenticado
2. Comprueba que no se está escribiendo a sí mismo
3. Llama a buscarEntreUsuarios() para ver si ya existe conversación
   ├── Sí → redirectToRoute('mensaje_conversacion', id)
   └── No → crea nueva Conversacion, flush(), redirectToRoute('mensaje_conversacion', id)
```

De este modo el botón "Chat" siempre redirige a la conversación correcta, creándola solo si no existía.

### Ruta `GET|POST /mensajes/conversacion/{id}` → `mensaje_conversacion`

Muestra la conversación y procesa el envío de nuevos mensajes:

1. Verifica que el usuario actual pertenece a la conversación (seguridad).
2. Marca como leídos todos los mensajes del otro usuario en esta conversación.
3. Crea el formulario `MensajeType` y lo procesa si hay POST.
4. Al enviar, actualiza `fechaUltimoMensaje` en la conversación y persiste el mensaje.
5. Redirige a la misma ruta (patrón POST/Redirect/GET para evitar reenvíos al recargar).

---

## 6. Extensión Twig — `mensajes_no_leidos()`

**Archivos:**  
- `src/Twig/Extension/MensajesExtension.php` — registra la función  
- `src/Twig/Runtime/MensajesExtensionRuntime.php` — implementación lazy (runtime)

La función `mensajes_no_leidos()` está disponible en cualquier plantilla Twig de la aplicación y devuelve el total de mensajes sin leer del usuario autenticado en todas sus conversaciones.

Se implementó como **runtime extension** (en lugar de una extensión estándar) para que la lógica se instancie solo cuando se llama, no en cada request.

Uso en el navbar:

```twig
{% set totalMensajes = mensajes_no_leidos() %}
{% if totalMensajes > 0 %}
    <span class="nav-campana-badge">{{ totalMensajes }}</span>
{% endif %}
```

---

## 7. Icono de chat en el navbar

El icono de chat (`.nav-chat`, icono WeChat de Font Awesome) se encuentra en el grupo de iconos junto a la campana de notificaciones. Apunta a `app_mensajes` y muestra un badge con el total de mensajes no leídos cuando hay alguno.

### Código en `base.html.twig`

```twig
{% set totalMensajes = mensajes_no_leidos() %}
<a href="{{ path('app_mensajes') }}"
   class="nav-link nav-chat {{ app.request.attributes.get('_route') starts with 'app_mensajes' ? 'active' : '' }}"
   title="Chat">
    <i class="fa-brands fa-weixin"></i>
    {% if totalMensajes > 0 %}
        <span class="nav-campana-badge">{{ totalMensajes }}</span>
    {% endif %}
</a>
```

El badge reutiliza la clase `.nav-campana-badge` del sistema de notificaciones, manteniendo coherencia visual.

---

## 8. Acceso desde tarjetas y perfiles de músico

El botón "Chat" aparece en dos lugares. En ambos casos apunta a `mensaje_nuevo` con el **ID del usuario** del músico destino (no el ID del músico):

### Listado de músicos (`/musico/list`)

```twig
{% if app.user and app.user.musico is not null and app.user.musico != musico %}
    <a href="{{ path('mensaje_nuevo', {'id': musico.usuario.id}) }}" class="btn-chat btn-sm">Chat</a>
{% endif %}
```

La condición `app.user.musico != musico` evita que el botón aparezca en la propia tarjeta del usuario logueado.

### Perfil de músico (`/musico/{id}`)

```twig
{% if app.user == musico.usuario %}
    <a href="{{ path('app_musico_edit', {'id': musico.id}) }}" class="btn-editar">Editar</a>
{% elseif app.user and app.user.musico is not null %}
    <a href="{{ path('mensaje_nuevo', {'id': musico.usuario.id}) }}" class="btn-confirmar">Chat</a>
{% endif %}
```

Si es el propio perfil → botón "Editar". Si es el perfil de otro músico → botón "Chat".

---

## 9. Plantillas

### `templates/mensajes/index.html.twig`

Lista de conversaciones con:
- Avatar del otro músico (foto de perfil o icono SVG de nota musical como fallback).
- Nombre del músico, badge de no leídos y fecha del último mensaje en una fila.
- Preview del último mensaje (máximo 60 caracteres). Si lo envió el usuario actual, se precede de "Tú:".
- Borde más intenso en conversaciones con mensajes sin leer (`.mensajes-item-noleido`).

### `templates/mensajes/conversacion.html.twig`

Vista de chat con:
- Header fijo con flecha de vuelta a la lista, avatar y nombre del interlocutor.
- Área de mensajes con scroll automático al último mensaje (JavaScript al `load`).
- Burbujas diferenciadas: propias alineadas a la derecha con fondo morado, ajenas alineadas a la izquierda con fondo oscuro.
- Input de texto y botón de envío circular con gradiente.
- Envío con **Enter** (sin Shift): al pulsar Enter sin Shift se envía el formulario. Shift+Enter inserta un salto de línea.

---

## 10. Seguridad y control de acceso

Las rutas `/mensajes/*` están protegidas en `config/packages/security.yaml`:

```yaml
access_control:
    - { path: ^/mensajes, roles: ROLE_USER }
```

Adicionalmente, en el controlador se realizan comprobaciones explícitas:

| Comprobación | Dónde | Qué evita |
|---|---|---|
| `$usuarioActual instanceof Usuario` | Todas las acciones | Acceso sin autenticación |
| `$usuarioActual === $destinatario` | `nuevo()` | Que un usuario se escriba a sí mismo |
| `$conversacion->getUsuarioUno() !== $usuarioActual && $conversacion->getUsuarioDos() !== $usuarioActual` | `conversacion()` | Acceso a conversaciones ajenas por URL directa |

---

## 11. Migración

La migración `Version20260605221048` crea las tablas `conversacion` y `mensaje` con sus claves foráneas hacia `usuario`.

Para aplicarla en un entorno nuevo:

```bash
php bin/console doctrine:migrations:migrate
```

---

## 12. Decisiones técnicas

### Una conversación por par de usuarios

Se decidió que no pueden existir múltiples conversaciones entre el mismo par de usuarios. `buscarEntreUsuarios()` comprueba ambas combinaciones de `usuarioUno`/`usuarioDos` antes de crear una nueva. Esto simplifica la UX (el botón "Chat" siempre lleva al mismo hilo) y evita fragmentar los mensajes.

### El parámetro de `mensaje_nuevo` es el ID de `Usuario`, no de `Musico`

La ruta recibe el ID del `Usuario` porque la relación de conversación es entre usuarios, no entre perfiles de músico. Esto permite que en el futuro el chat sea extensible a otros tipos de usuarios (si los hubiera) sin cambiar la entidad `Conversacion`.

### Marcar como leído en la carga, no en el envío

Los mensajes se marcan como leídos cuando el destinatario **entra** en la conversación, no cuando el remitente los envía. Esto refleja el comportamiento habitual de aplicaciones de mensajería y evita tener que hacer peticiones adicionales para confirmar lectura.

### Cálculo de no leídos por conversación en el controlador, no en Twig

El array `noLeidosPorConversacion` se calcula en `MensajesController::index()` con una consulta por conversación antes de pasar los datos a la plantilla. Aunque genera N+1 queries, en el contexto de este proyecto el número de conversaciones por usuario es bajo. Alternativa no implementada: una query agregada con `GROUP BY conversacion_id`.

### Estilos encapsulados en clases nuevas

Los estilos de las páginas de mensajes se añadieron al final de `perfil.css` usando clases exclusivas (`.mensajes-*` y `.chat-*`), sin modificar ninguna clase existente.
