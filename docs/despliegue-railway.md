# Despliegue de MusicHub en Railway

## Contexto

MusicHub es una aplicación web desarrollada con **Symfony 8** (PHP), **MySQL** y **Stimulus/Turbo** como stack de frontend. Para el despliegue en producción se eligió **Railway** por su plan gratuito, soporte nativo de Docker y facilidad de configuración.

El despliegue se realizó en una rama separada (`feature/railway-deploy`) para no interferir con el trabajo del equipo.

---

## Por qué Railway y no Vercel

Vercel está diseñado para aplicaciones frontend estáticas y frameworks JavaScript. No soporta PHP de forma nativa ni bases de datos SQL relacionales. Railway, en cambio, permite desplegar cualquier aplicación mediante Docker y ofrece plugins de base de datos (MySQL, PostgreSQL) integrados en el mismo proyecto.

---

## Arquitectura del despliegue

```
GitHub (rama feature/railway-deploy)
        │
        ▼
Railway Build (Dockerfile)
        │
        ├── Servicio: Proyecto-Musica (PHP + Apache)
        └── Servicio: MySQL 9.4
```

Cada push a la rama `feature/railway-deploy` dispara automáticamente un nuevo build y deploy en Railway.

---

## Archivos creados para el despliegue

### `Dockerfile`

Define cómo se construye la imagen de la aplicación:

1. **Imagen base**: `php:8.4-apache` — PHP 8.4 con Apache preinstalado
2. **Extensiones PHP**: `pdo_mysql`, `intl`, `zip`, `opcache` — necesarias para Symfony y MySQL
3. **Configuración de Apache**: se establece `public/` como directorio raíz y se activa `mod_rewrite` para las URLs de Symfony
4. **Composer**: se instala desde su imagen oficial y se ejecuta con `--no-dev --optimize-autoloader` para producción
5. **Compilación de assets**: `php bin/console asset-map:compile` genera los ficheros JS y CSS estáticos en `public/assets/`
6. **Permisos**: se asignan permisos correctos a los directorios `var/` y `public/`

### `docker-entrypoint.sh`

Script que se ejecuta cada vez que el contenedor arranca, antes de iniciar Apache:

1. Configura Apache para usar el puerto que Railway asigna (`$PORT`, normalmente 8080)
2. Asegura que solo el módulo MPM correcto de Apache está cargado (`mpm_prefork`, necesario para `mod_php`)
3. Ejecuta las migraciones de base de datos: `php bin/console doctrine:migrations:migrate`
4. Inicia Apache

---

## Configuración en Railway

### Servicio de base de datos

Se añadió un plugin de **MySQL** al proyecto Railway. Esto crea automáticamente un servicio MySQL con sus credenciales y expone la variable `MYSQL_URL` con la cadena de conexión interna.

### Variables de entorno del servicio de la app

| Variable | Descripción |
|---|---|
| `APP_ENV` | `prod` — activa el modo producción de Symfony |
| `APP_SECRET` | Cadena aleatoria de 32+ caracteres para seguridad |
| `DATABASE_URL` | Referencia al servicio MySQL: `${{MySQL.MYSQL_URL}}?serverVersion=8.0&charset=utf8mb4` |
| `GOOGLE_PLACES_API_KEY` | Clave de la API de Google Places para el autocompletado de ubicaciones |
| `MAILER_DSN` | `null://null` (emails desactivados en esta fase) |
| `APP_SHARE_DIR` | `var/share` |

---

## Gestión de la base de datos

### Migraciones automáticas

Las migraciones de Doctrine se ejecutan automáticamente en cada deploy gracias al script de entrypoint. Esto garantiza que el esquema de la base de datos esté siempre actualizado sin intervención manual.

### Datos iniciales (seed)

Los instrumentos del sistema y los géneros musicales se cargan mediante una migración de datos (`Version20260603120000`), no mediante fixtures. Esto permite que los datos iniciales estén disponibles en producción sin necesidad de instalar el bundle de fixtures (que es una dependencia de desarrollo).

### Importación de datos de desarrollo

Para migrar datos desde el entorno local al entorno de producción se exportó la base de datos desde phpMyAdmin (solo datos, sin estructura) y se importó directamente en la consola MySQL de Railway.

---

## Flujo de despliegue continuo

Una vez configurado, el proceso de despliegue es completamente automático:

```
1. git push origin feature/railway-deploy
2. Railway detecta el push → inicia build con el Dockerfile
3. El contenedor arranca → el entrypoint ejecuta migraciones
4. Apache arranca en el puerto asignado
5. La app está disponible en la URL pública
```

---

## Consideraciones para producción

- **Plan gratuito de Railway**: incluye ~$5 de crédito mensual, suficiente para un proyecto en fase de demo o presentación.
- **Emails**: el sistema de verificación de email está configurado con `null://null`, por lo que los correos no se envían. Para activarlos habría que integrar un servicio SMTP (Brevo, Mailgun, etc.).
- **Merge a main**: cuando el equipo lo considere oportuno, la rama `feature/railway-deploy` puede fusionarse a `main` mediante Pull Request para que todos dispongan de la configuración de despliegue.
