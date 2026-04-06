# Arranque del proyecto

## Cada vez que abras el proyecto

### Pilar (Docker)

```
cd c:\docker-dev
docker compose up -d
```

### Cristian (XAMPP)

```
cd \ruta\a\Proyecto-Musica
C:\xampp\php\php.exe -S localhost:8000 -t public
```

### Frontend React (todos)

```
cd c:\docker-dev\proyectos\Proyecto-Musica\frontend
npm run dev
```

---

## Después de un git pull

```
docker compose exec php bash -lc "cd /var/www/html/Proyecto-Musica && composer install"
docker compose exec php bash -lc "cd /var/www/html/Proyecto-Musica && php bin/console cache:clear"
```

Cristian:

```
C:\xampp\php\php.exe composer.phar install
```

---

## Cuándo hacer qué

| Situación | Qué hacer |
|---|---|
| Después de `git pull` | `composer install` + `cache:clear` |
| Nueva entidad o campo | `cache:clear` + migraciones |
| Nueva dependencia PHP | `composer install` |
| Nueva dependencia JS | `npm install` |

---

## Lo que no hay que repetir

- `npm create vite@latest` → solo se hace una vez
- `composer require` → solo si se añade un paquete nuevo
