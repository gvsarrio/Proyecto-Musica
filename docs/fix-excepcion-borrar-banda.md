# Fix — Excepción al Eliminar Banda con Miembros

**Proyecto:** Music Hub  
**Rama:** `feature/fix-excepcion-borrar-banda`  
**Fecha:** Junio 2026  
**Framework:** Symfony 8.0 · PHP 8.4 · Doctrine ORM · MySQL · Twig

---

## Índice

1. [Descripción del problema](#1-descripción-del-problema)
2. [Causa técnica](#2-causa-técnica)
3. [Decisión de diseño](#3-decisión-de-diseño)
4. [Cambios realizados](#4-cambios-realizados)
5. [Comportamiento final](#5-comportamiento-final)

---

## 1. Descripción del problema

Al intentar eliminar una banda que tenía miembros, la aplicación devolvía un **error 500**. El modal de confirmación se mostraba correctamente, pero al confirmar la acción el servidor fallaba.

---

## 2. Causa técnica

La entidad `Banda` tiene una relación `OneToMany` con `MiembroBanda`:

```php
// src/Entity/Banda.php
#[ORM\OneToMany(targetEntity: MiembroBanda::class, mappedBy: 'banda')]
private Collection $miembroBandas;
```

La relación **no tenía `cascade: ['remove']`**, y la columna `banda_id` en la tabla `miembro_banda` es `NOT NULL` sin `ON DELETE CASCADE` en la base de datos. Al llamar a `$entityManager->remove($banda)`, la base de datos rechazaba la operación por violación de integridad referencial.

---

## 3. Decisión de diseño

Se descartó borrar la banda junto con todos sus miembros automáticamente. En su lugar se optó por **bloquear la eliminación si la banda tiene más de un miembro aceptado**, obligando al admin a eliminar a los demás miembros primero.

Caso permitido: el admin es el único miembro restante → puede borrar la banda.

---

## 4. Cambios realizados

### `src/Controller/BandaController.php` — método `delete()`

Se añaden dos comprobaciones antes de ejecutar el borrado:

1. **Bloqueo si hay otros miembros:** si hay más de 1 miembro con estado `aceptado`, se redirige con un mensaje de error sin borrar nada.
2. **Borrado manual de membresías:** cuando solo queda el admin, se eliminan primero todos los registros `MiembroBanda` asociados a la banda y después la banda. Esto evita la excepción de integridad referencial.

```php
$miembrosAceptados = $banda->getMiembroBandas()->filter(
    fn($mb) => $mb->getEstado() === 'aceptado'
);

if ($miembrosAceptados->count() > 1) {
    $this->addFlash('error', 'Debes eliminar a todos los miembros antes de borrar la banda.');
    return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()], Response::HTTP_SEE_OTHER);
}

foreach ($banda->getMiembroBandas() as $miembro) {
    $entityManager->remove($miembro);
}
$entityManager->remove($banda);
$entityManager->flush();
```

### `templates/banda/_delete_form.html.twig`

Se añade un **segundo modal bloqueante** con el mismo estilo visual que el modal de confirmación existente. El botón "Eliminar" abre uno u otro en función del número de miembros aceptados:

- **Banda con otros miembros** → modal bloqueante: *"Debes eliminar a todos los miembros de la banda antes de poder borrarla."*
- **Solo queda el admin** → modal de confirmación original

La lógica de selección se resuelve en Twig sin JavaScript adicional:

```twig
{% set otros_miembros = banda.miembroBandas|filter(mb => mb.estado == 'aceptado')|length > 1 %}

<button type="button" class="btn-eliminar"
    onclick="document.getElementById('{{ otros_miembros ? 'modal-eliminar-banda-bloqueado' : 'modal-eliminar-banda' }}').style.display='flex'">
    Eliminar
</button>
```

---

## 5. Comportamiento final

| Situación | Resultado |
|---|---|
| Banda con 2 o más miembros aceptados | Modal bloqueante. No se borra nada. |
| Admin es el único miembro | Modal de confirmación. Al confirmar, se borran la membresía y la banda. |
| Intento directo vía POST (sin modal) | El controlador comprueba igualmente y redirige con error si hay otros miembros. |
