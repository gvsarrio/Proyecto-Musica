# API automática de Symfony / API Platform

## Qué es

Esta API es la automática.
Symfony + API Platform lee las entidades en `src/Entity` y crea las rutas.
No necesitas crear controladores para los recursos básicos.

## Dónde está

- `src/Entity/*.php` → aquí están las entidades.
- `config/routes/api_platform.yaml` → pone el prefijo `/api`.
- `config/packages/api_platform.yaml` → configura API Platform.

## Cómo se crea un recurso

1. Abre `src/Entity`.
2. Crea o edita una clase PHP.
3. Añade `#[ApiResource]` antes de la clase.
4. Añade columnas con `#[ORM\Column]`.
5. Añade getter y setter.
6. Limpia la caché con:

```bash
php bin/console cache:clear
```

Esto es importante porque obliga a Symfony a leer los cambios nuevos y evita que use datos guardados en caché.

En Docker:

```bash
docker compose exec php bash -lc "cd /var/www/html/Proyecto-Musica && php bin/console cache:clear"
```

## Ejemplo rápido

```php
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity]
class Musico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    public function getId(): ?int { return $this->id; }
    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): static { $this->nombre = $nombre; return $this; }
}
```

## Cómo se gestiona

### Añadir o cambiar campos

1. Edita la entidad.
2. Añade/ajusta `#[ORM\Column]`.
3. Añade getter y setter.
4. Limpia caché.

### Cómo saber qué rutas existen

Abre:

- `http://localhost:8000/api/docs`

O usa este comando:

```bash
php bin/console debug:router | grep /api
```

## Cómo se utiliza

### Endpoints básicos

- `GET /api/musicos`
- `POST /api/musicos`
- `GET /api/musicos/{id}`
- `PATCH /api/musicos/{id}`
- `DELETE /api/musicos/{id}`

También:

- `/api/instrumentos`
- `/api/usuarios`
- `/api/instrumento_musicos`

### Ejemplo de React

```js
const res = await fetch('http://localhost:8000/api/musicos');
const data = await res.json();
const items = data['hydra:member'] || data;
console.log(items);
```

### ¿Qué es `hydra:member`?

API Platform usa JSON-LD para devolver listas.
JSON-LD es JSON con información extra sobre el formato y los datos.
En ese formato, `hydra:member` es donde están los datos reales del listado.
No es un grupo de serialización, es solo el campo del formato de salida.

- `hydra:member` contiene los objetos de la respuesta.
- Se usa para respuestas de lista (arrays), no para un solo objeto.
- `data['hydra:member'] || data` funciona porque si no hay JSON-LD, usa el propio objeto.

### Ejemplo de curl

```bash
curl http://localhost:8000/api/musicos
```

## Qué son los grupos

Los grupos sirven para controlar qué campos salen y qué campos entran.
Por ejemplo, puedes mostrar solo algunos datos en GET y aceptar otros solo en POST/PATCH.

## Ejemplo de grupo

```php
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']]
)]
class Musico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['read', 'write'])]
    private ?string $nombre = null;
}
```

- `read` = campo visible en respuestas GET.
- `write` = campo aceptado en POST/PATCH.

## Estado actual de los grupos

- No hay grupos creados en las entidades del proyecto.
- El ejemplo está solo en el documento.

## Resumen para estudiantes

- La API se crea con `#[ApiResource]` en cada entidad.
- No necesitas escribir controladores manuales.
- Usa `/api/docs` para ver las rutas.
- Si añades campos, limpia caché.
- Si quieres controlar campos, usa grupos.
