# Paginación — Listados de Músicos y Bandas

**Proyecto:** Music Hub  
**Fecha:** Junio 2026  
**Framework:** Symfony 8.0 · PHP 8.4 · KnpPaginatorBundle v6.10

---

## Índice

1. [Objetivo](#1-objetivo)
2. [Bundle utilizado](#2-bundle-utilizado)
3. [Cambios en los controladores](#3-cambios-en-los-controladores)
4. [Plantilla de paginación personalizada](#4-plantilla-de-paginación-personalizada)
5. [Compatibilidad con filtros](#5-compatibilidad-con-filtros)
6. [Decisiones técnicas](#6-decisiones-técnicas)

---

## 1. Objetivo

Con 200 músicos y 100 bandas cargados vía fixtures, los listados devolvían todos los registros de golpe. La paginación limita la respuesta a **12 elementos por página**, mejorando los tiempos de carga y la legibilidad.

---

## 2. Bundle utilizado

Se instaló **KnpPaginatorBundle** (`knplabs/knp-paginator-bundle`):

```bash
composer require knplabs/knp-paginator-bundle
```

El bundle se configura automáticamente vía Symfony Flex. No requiere configuración adicional en `config/packages/`.

---

## 3. Cambios en los controladores

### Patrón aplicado (idéntico en músicos y bandas)

```php
use Knp\Component\Pager\PaginatorInterface;

public function index(
    ...,
    Request $request,
    PaginatorInterface $paginator
): Response {
    // 1. Obtener array completo (con filtros si los hay)
    $musicos = $hayFiltros
        ? $musicoRepository->findByFiltros(...)
        : $musicoRepository->findAll();

    // 2. Calcular distancias sobre el array completo (antes de paginar)
    $distancias = [...];

    // 3. Paginar el array
    $pagination = $paginator->paginate($musicos, $request->query->getInt('page', 1), 12);

    return $this->render('musico/index.html.twig', [
        'musicos'    => $pagination,  // misma clave que antes
        'distancias' => $distancias,
        ...
    ]);
}
```

Los archivos modificados son:
- `src/Controller/MusicoController.php` — método `index()`
- `src/Controller/BandaController.php` — método `index()`

### Por qué se calcula `$distancias` antes de paginar

El cálculo de distancias Haversine se hace en PHP recorriendo el array completo. Si se hiciera después de paginar, solo se calcularían las distancias de los 12 elementos visibles, perdiendo los datos de las páginas siguientes. Al calcular antes y pasar el mapa completo `[id => km]` a la vista, el template puede acceder a la distancia de cualquier elemento paginado con `distancias[musico.id]`.

---

## 4. Plantilla de paginación personalizada

El bundle usa por defecto una plantilla Bootstrap. Se sobreescribe con una plantilla propia siguiendo la convención de Symfony para bundles:

```
templates/bundles/KnpPaginatorBundle/Pagination/sliding.html.twig
```

La plantilla muestra únicamente los controles esenciales: botón anterior, indicador de página y botón siguiente. Usa la clase `btn-principal` del proyecto (morado `rgba(155, 48, 255, ...)`) y `grid-column: 1 / -1` para ocupar ambas columnas del grid de tarjetas:

```twig
{% if last > 1 %}
<div style="grid-column:1/-1;display:flex;align-items:center;justify-content:center;gap:1rem;...">
    {# botón anterior (deshabilitado en página 1) #}
    {# "Página X de Y" #}
    {# botón siguiente (deshabilitado en última página) #}
</div>
{% endif %}
```

### Por qué `grid-column: 1 / -1`

`.musicos-lista` es un grid CSS con `grid-template-columns: repeat(2, 1fr)`. Sin esta propiedad, el div de paginación ocupa solo una de las dos columnas y queda desplazado a la izquierda. Con `1 / -1` abarca todas las columnas y `justify-content: center` lo centra correctamente.

---

## 5. Compatibilidad con filtros

KnpPaginatorBundle lee todos los parámetros de la query string (`filtros[generos][]`, `filtros[radio]`, etc.) y los preserva automáticamente en los enlaces de paginación. No fue necesario ningún cambio en el sidebar de filtros ni en el script de proximidad.

Al aplicar filtros, la URL no lleva parámetro `page`, por lo que el controlador lee `page = 1` por defecto y la paginación arranca desde el principio del resultado filtrado.

---

## 6. Decisiones técnicas

### Paginación en memoria vs. en base de datos

KnpPaginatorBundle soporta dos modos:

| Modo | Cómo | Cuándo usarlo |
|---|---|---|
| **Array** (el elegido) | Carga todos los registros y recorta en PHP | Pocos registros; lógica post-query en PHP |
| **QueryBuilder** | Añade `LIMIT`/`OFFSET` en SQL | Tablas grandes; sin procesado PHP post-query |

Se eligió el modo array porque el cálculo de distancias Haversine se realiza en PHP después de la query. Migrar a QueryBuilder requeriría mover ese cálculo a SQL o a una segunda query, lo cual añade complejidad innecesaria para el volumen actual (200 músicos, 100 bandas).

### Elementos por página

Se fijaron **12 elementos** por página. Es suficiente para mostrar una selección variada en el listado sin cargar demasiados datos, y es divisible por 2 (columnas de escritorio) y por 1 (móvil), por lo que siempre forma filas completas.
