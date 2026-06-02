# Implementación de Géneros Musicales

**Fecha:** 2026-06-02  
**Rama:** feature/filtros  

---

## Contexto y motivación

La entidad `Banda` tenía un campo `generos` de tipo string donde los géneros se almacenaban como texto libre separado por comas (ej: `"Rock, Jazz, Flamenco"`). Esta estructura no permitía filtrar por género de forma fiable ni reutilizar los géneros entre músicos y bandas.

El objetivo es crear una entidad `Genero` normalizada, compartida entre `Musico` y `Banda`, que sirva de base para implementar filtros en las páginas de listado.

---

## Cambios realizados

### 1. Nueva entidad `src/Entity/Genero.php`

Entidad simple con dos campos:

| Campo  | Tipo       | Descripción              |
|--------|------------|--------------------------|
| `id`   | int (PK)   | Identificador auto-generado |
| `nombre` | string(50) | Nombre del género (ej: Rock, Jazz) |

Sigue el mismo patrón que `InstrumentoSistema`.

### 2. Nuevo repositorio `src/Repository/GeneroRepository.php`

Repositorio vacío, listo para añadir métodos de consulta cuando se implementen los filtros.

### 3. Relación en `Musico`

Se ha añadido una relación ManyToMany a `Genero`:

- **Propiedad:** `generosMusicales` (Collection)
- **Tabla de relación:** `musico_genero`
- **Métodos:** `getGenerosMusicales()`, `addGeneroMusical()`, `removeGeneroMusical()`

### 4. Relación en `Banda`

Se ha añadido la misma relación ManyToMany a `Genero`:

- **Propiedad:** `generosMusicales` (Collection)
- **Tabla de relación:** `banda_genero`
- **Métodos:** `getGenerosMusicales()`, `addGeneroMusical()`, `removeGeneroMusical()`

> **Importante:** El campo `generos` (string) original de `Banda` se ha conservado intacto para no romper las vistas existentes. En el futuro puede eliminarse una vez que todas las vistas usen la nueva relación.

### 5. Migración `migrations/Version20260602180509.php`

Tablas creadas en base de datos:

```sql
CREATE TABLE genero (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, PRIMARY KEY (id));
CREATE TABLE musico_genero (musico_id INT NOT NULL, genero_id INT NOT NULL, PRIMARY KEY (musico_id, genero_id));
CREATE TABLE banda_genero (banda_id INT NOT NULL, genero_id INT NOT NULL, PRIMARY KEY (banda_id, genero_id));
```

Todas las claves foráneas usan `ON DELETE CASCADE`.

---

## Estructura de tablas resultante

```
genero
├── id (PK)
└── nombre

musico_genero
├── musico_id (FK → musico.id)
└── genero_id (FK → genero.id)

banda_genero
├── banda_id (FK → banda.id)
└── genero_id (FK → genero.id)
```

---

## Próximos pasos

1. **Poblar la tabla `genero`** con géneros predefinidos mediante un fixture.
2. **Añadir los géneros al formulario** de creación/edición de músicos y bandas.
3. **Implementar los filtros** en `/musico/list` y `/banda/list` usando esta relación.
