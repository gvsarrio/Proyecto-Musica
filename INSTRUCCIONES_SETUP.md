# Instrucciones de setup

## Para Cristian (XAMPP)

1.  Abrir terminal y cambiar a la carpeta del proyecto:
    ```bash
    cd \ruta\a\Proyecto-Musica
    ```
2.  Ejecutar Composer:
    - Si Composer está global:
      ```bash
      composer install
      ```
    - Si no está global, usar PHP de XAMPP:
      ```bash
      C:\xampp\php\php.exe composer.phar install
      ```
3.  Si no hay `.env.local`, crearla copiando `.env`:
    ```bash
    copy .env .env.local
    ```
4.  Iniciar el servidor PHP desde la carpeta del proyecto:
    ```bash
    C:\xampp\php\php.exe -S localhost:8000 -t public
    ```
5.  Abrir en el navegador:
    - `http://localhost:8000/api`
    - `http://localhost:8000/api/docs`

## Para Pilar (Docker)

1.  Abrir terminal y cambiar a la carpeta del repo:
    ```bash
    cd c:\docker-dev
    ```
2.  Levantar los contenedores:
    ```bash
    docker compose up -d
    ```
3.  Instalar dependencias dentro del contenedor PHP:
    ```bash
    docker compose exec php bash -lc "cd /var/www/html/Proyecto-Musica && composer install"
    ```
4.  Borrar caché Symfony:
    ```bash
    docker compose exec php bash -lc "cd /var/www/html/Proyecto-Musica && php bin/console cache:clear"
    ```
5.  Abrir en el navegador:
    - `http://localhost:8000/api`
    - `http://localhost:8000/api/docs`

## Instalación del frontend React (todos)

1.  Asegurar que tienen Node.js instalado.
2.  Abrir terminal en el proyecto:
    ```
    cd c:\docker-dev\proyectos\Proyecto-Musica
    ```
3.  Crear el frontend React con Vite:
    ```
    npm create vite@latest frontend -- --template react
    ```
    Si te sale el menú de VITE y pone:
    -> Local: http://localhost:(tu puerto)/
    -> Network: use --host to expose
    -> press h + enter to show help

    Ya está instalado react.
    Si no, sigue los pasos siguientes.

4.  Entrar en la carpeta del frontend:
    ```
    cd frontend
    ```
5.  Instalar dependencias:
    ```
    npm install
    ```
6.  Levantar el frontend:
    ```
    npm run dev
    ```
7.  Abrir en el navegador la URL que indique Vite (normalmente `http://localhost:5173`).

> Este frontend usa el backend Symfony en `http://localhost:8000/api`.

## Notas directas

- No modificar `composer.json` ni `composer.lock` salvo que haya un conflicto real.
- Si falta `.env.local`, copiar `.env` y no subir cambios personales al repositorio.
- El backend ya está preparado con API Platform y CORS.
