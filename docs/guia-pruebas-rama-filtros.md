# Guía de pruebas — rama `feature/filtros`

> Esta guía describe todo lo que hay que comprobar antes de fusionar `feature/filtros` con `main`.  
> No hace falta conocer el código; basta con seguir los pasos y anotar si el resultado coincide con el esperado.

---

## Índice

1. [Qué incluye esta rama](#1-qué-incluye-esta-rama)
2. [Requisitos previos](#2-requisitos-previos)
3. [Puesta en marcha](#3-puesta-en-marcha)
4. [Datos de prueba necesarios](#4-datos-de-prueba-necesarios)
5. [Casos de prueba — Vista Músicos](#5-casos-de-prueba--vista-músicos)
6. [Casos de prueba — Vista Bandas](#6-casos-de-prueba--vista-bandas)
7. [Casos de prueba — Comportamientos compartidos](#7-casos-de-prueba--comportamientos-compartidos)
8. [Checklist de sign-off](#8-checklist-de-sign-off)

---

## 1. Qué incluye esta rama

| Funcionalidad | Vista |
|---|---|
| Sidebar de filtros con géneros (checkboxes) | Músicos y Bandas |
| Filtro por instrumento (checkboxes) | Solo Músicos |
| Filtro de proximidad: campo de ciudad con autocomplete | Músicos y Bandas |
| Botón "Usar mi ubicación" (geolocalización del navegador) | Músicos y Bandas |
| Slider de radio 10–100 km (pasos de 10, defecto 20 km) | Músicos y Bandas |
| Slider deshabilitado si no hay ciudad seleccionada | Músicos y Bandas |
| Distancia en km mostrada en cada tarjeta | Músicos y Bandas |
| Botones "Aplicar filtros" y "Limpiar" | Músicos y Bandas |

---

## 2. Requisitos previos

### Software
- Servidor web y base de datos corriendo (el entorno de desarrollo habitual del proyecto).
- Navegador moderno: Chrome, Firefox o Edge actualizados.

### Clave de API de Google Maps
La clave debe estar configurada en el archivo `.env.local` (no se sube al repositorio):

```
GOOGLE_PLACES_API_KEY=tu_clave_aqui
```

La clave necesita las siguientes APIs activadas en Google Cloud Console:
- **Maps JavaScript API**
- **Places API**
- **Geocoding API**

Si no tienes la clave, pídela al responsable del proyecto. Sin ella el autocomplete de ciudad no funcionará, pero el resto de filtros (géneros, instrumentos) sí.

### HTTPS o localhost
El botón "Usar mi ubicación" solo funciona en conexiones seguras. En entorno local (`http://localhost` o `http://127.0.0.1`) funciona sin problema. En cualquier otra IP sin HTTPS el navegador bloqueará el permiso de geolocalización.

---

## 3. Puesta en marcha

```bash
# 1. Cambiar a la rama
git fetch origin
git checkout feature/filtros

# 2. Instalar dependencias PHP (por si hay cambios)
composer install

# 3. Limpiar caché de Symfony
php bin/console cache:clear

# 4. Aplicar migraciones (si las hay)
php bin/console doctrine:migrations:migrate

# 5. Cargar géneros (solo si tu base de datos no los tiene todavía)
php bin/console doctrine:fixtures:load --append --class=App\\DataFixtures\\GeneroFixtures
```

> **Sobre el paso 5:** el flag `--append` es obligatorio — sin él el comando borra toda la base de datos antes de cargar. Si ya tienes géneros (Rock, Jazz, Pop…) en la tabla `genero`, sáltate este paso.

Comprueba que puedes acceder a:
- `http://localhost/musico/list` → debe mostrar la página de músicos **con sidebar de filtros**.
- `http://localhost/banda/list` → debe mostrar la página de bandas **con sidebar de filtros**.

Si alguna de las dos rutas devuelve error 500, revisar los logs en `var/log/dev.log`.

---

## 4. Datos de prueba necesarios

Para que los filtros sean comprobables necesitas datos en la base de datos que cumplan estas condiciones. Coordina con el equipo si hay un dump de base de datos de prueba.

### Músicos
Necesitas al menos:
- **3 músicos con coordenadas guardadas** (campos `latitud` y `longitud` rellenos en su perfil). Que estén a distancias variadas entre sí (p. ej. uno en Madrid, otro en Barcelona, otro en Valencia).
- **1 músico sin coordenadas** (para verificar que no aparece al filtrar por proximidad).
- **Músicos con géneros diferentes** asignados (al menos 2 géneros distintos repartidos).
- **Músicos con instrumentos diferentes** asignados.

### Bandas
Necesitas al menos:
- **2 bandas con coordenadas guardadas**.
- **1 banda sin coordenadas**.
- **Bandas con géneros diferentes** asignados a través de la relación `generosMusicales` (no el campo de texto `generos`).

> **Cómo añadir coordenadas a un perfil existente:**  
> Entra en el perfil de edición del músico o banda, en el campo de ubicación escribe una ciudad y selecciona una sugerencia del autocomplete. Las coordenadas se guardan automáticamente. Guarda el perfil.

---

## 5. Casos de prueba — Vista Músicos

Accede a `http://localhost/musico/list` para todos estos tests.

---

### TC-M01 — Sin filtros activos

**Pasos:**
1. Abre la página sin ningún parámetro en la URL.

**Resultado esperado:**
- Se muestran **todos** los músicos registrados.
- El sidebar aparece a la izquierda con los filtros vacíos.
- El slider de radio está **deshabilitado** (aspecto tenue, no interactivo).
- La URL es simplemente `/musico/list`.

---

### TC-M02 — Filtro por un solo género

**Pasos:**
1. Marca un solo checkbox de género (p. ej. "Rock").
2. Pulsa "Aplicar filtros".

**Resultado esperado:**
- Solo aparecen músicos que tengan ese género asignado.
- El checkbox sigue marcado tras la recarga.
- La URL contiene `filtros[generos][]=<id>`.

---

### TC-M03 — Filtro por varios géneros

**Pasos:**
1. Marca dos o más checkboxes de géneros distintos.
2. Pulsa "Aplicar filtros".

**Resultado esperado:**
- Aparecen músicos que tengan **al menos uno** de los géneros seleccionados.
- Todos los checkboxes marcados siguen marcados tras la recarga.

---

### TC-M04 — Filtro por instrumento

**Pasos:**
1. Marca un checkbox de instrumento.
2. Pulsa "Aplicar filtros".

**Resultado esperado:**
- Solo aparecen músicos que toquen ese instrumento.
- El checkbox sigue marcado tras la recarga.

---

### TC-M05 — Género + Instrumento combinados

**Pasos:**
1. Marca un género Y un instrumento.
2. Pulsa "Aplicar filtros".

**Resultado esperado:**
- Solo aparecen músicos que cumplan **ambos** criterios a la vez.

---

### TC-M06 — Autocomplete de ciudad

**Pasos:**
1. Haz clic en el campo "Ciudad o región..." del sidebar.
2. Escribe una ciudad (p. ej. "Madrid").
3. Selecciona una de las sugerencias del desplegable de Google.

**Resultado esperado:**
- El campo muestra el nombre de la ciudad en formato "Ciudad, País".
- El slider de radio se **habilita** (aparece en color púrpura, es interactivo).
- La etiqueta del slider muestra "20 km" (valor por defecto).

---

### TC-M07 — Filtro de proximidad con autocomplete

**Pasos:**
1. Selecciona una ciudad mediante el autocomplete (ver TC-M06).
2. Mueve el slider a un valor (p. ej. 50 km).
3. Pulsa "Aplicar filtros".

**Resultado esperado:**
- Solo aparecen músicos cuyo perfil tenga coordenadas guardadas **y** estén dentro del radio elegido.
- Los músicos sin coordenadas no aparecen.
- En cada tarjeta aparece la distancia exacta (p. ej. "12.3 km") junto a la ciudad.
- El campo de ciudad y el slider conservan sus valores tras la recarga.

---

### TC-M08 — Botón "Usar mi ubicación"

**Pasos:**
1. Haz clic en el enlace/botón "Usar mi ubicación" bajo el campo de ciudad.
2. Cuando el navegador pida permiso, **acepta**.

**Resultado esperado:**
- El botón muestra "Obteniendo…" mientras trabaja.
- Al terminar, el campo de ciudad se rellena con tu ciudad actual (p. ej. "Valencia, España").
- El slider de radio se habilita.
- El botón vuelve a su estado normal.

---

### TC-M09 — Botón "Usar mi ubicación" — permiso denegado

**Pasos:**
1. Haz clic en "Usar mi ubicación".
2. Cuando el navegador pida permiso, **deniégalo**.

**Resultado esperado:**
- El botón vuelve a su estado original sin ningún error visible.
- El campo de ciudad queda vacío.
- El slider sigue deshabilitado.

---

### TC-M10 — Slider deshabilitado sin ciudad

**Pasos:**
1. Observa el slider con el campo de ciudad vacío.
2. Intenta arrastrar el slider.

**Resultado esperado:**
- El bloque del slider aparece tenue (opacidad reducida).
- No es posible moverlo.

---

### TC-M11 — Slider habilitado al escribir en el campo de ciudad

**Pasos:**
1. Escribe cualquier texto en el campo de ciudad (sin seleccionar sugerencia).

**Resultado esperado:**
- El slider se habilita en cuanto hay texto en el campo.
- *(Nota: sin coordenadas reales el filtro de distancia no se activará al aplicar, pero el slider debe estar interactivo.)*

---

### TC-M12 — Botón "Limpiar"

**Pasos:**
1. Aplica cualquier combinación de filtros.
2. Haz clic en "Limpiar".

**Resultado esperado:**
- Se redirige a `/musico/list` sin parámetros.
- Todos los checkboxes están desmarcados.
- El campo de ciudad está vacío.
- El slider vuelve a estar deshabilitado.
- Se muestran todos los músicos.

---

### TC-M13 — Ningún resultado

**Pasos:**
1. Aplica filtros muy restrictivos que no coincidan con ningún músico (p. ej. un radio de 10 km sobre una ciudad vacía de músicos registrados).
2. Pulsa "Aplicar filtros".

**Resultado esperado:**
- El grid no muestra tarjetas.
- Aparece el mensaje "No hay músicos registrados todavía." (o equivalente de estado vacío).

---

## 6. Casos de prueba — Vista Bandas

Accede a `http://localhost/banda/list`. Los tests son análogos a los de músicos con estas diferencias:

| Diferencia | Detalle |
|---|---|
| **No hay filtro de instrumento** | El sidebar de bandas solo tiene Género y Ubicación. No debe aparecer ningún bloque de "Instrumento". |
| **Campo de texto en tarjeta** | En lugar de "X años exp." aparece "Desde XXXX" (año de formación). |

Ejecuta los equivalentes de **TC-M01, TC-M02, TC-M03, TC-M06, TC-M07, TC-M08, TC-M09, TC-M10, TC-M12 y TC-M13** sobre la vista de bandas y verifica los mismos comportamientos.

**Test específico de bandas:**

### TC-B01 — Ausencia del bloque de instrumentos

**Pasos:**
1. Abre `http://localhost/banda/list`.
2. Observa el sidebar.

**Resultado esperado:**
- El sidebar muestra solo los bloques "Género" y "Ubicación".
- No existe ningún bloque "Instrumento".

---

## 7. Casos de prueba — Comportamientos compartidos

### TC-C01 — Degradado del slider

**Pasos:**
1. Con el campo de ciudad relleno, arrastra el slider a distintas posiciones.

**Resultado esperado:**
- La parte izquierda de la barra se colorea de **púrpura** hasta el punto del thumb.
- La etiqueta ("20 km", "50 km", etc.) se actualiza en tiempo real.
- Al soltar, el valor queda guardado hasta pulsar "Aplicar filtros".

---

### TC-C02 — URL compartible

**Pasos:**
1. Aplica un filtro (p. ej. un género).
2. Copia la URL completa de la barra del navegador.
3. Pégala en una pestaña nueva o en otro navegador.

**Resultado esperado:**
- La página carga con exactamente los mismos filtros aplicados.
- Los checkboxes marcados y el valor del slider se restauran.

---

### TC-C03 — Distancia en tarjeta solo con filtro activo

**Pasos:**
1. Visualiza el listado **sin** filtro de proximidad activo.
2. Activa el filtro de proximidad y aplícalo.

**Resultado esperado:**
- **Sin filtro:** ninguna tarjeta muestra "X km".
- **Con filtro:** cada tarjeta que tenga coordenadas muestra la distancia en km junto a la ciudad.

---

### TC-C04 — Músico/banda sin coordenadas con filtro de proximidad

**Pasos:**
1. Asegúrate de que existe al menos un músico o banda **sin** coordenadas en la base de datos.
2. Aplica un filtro de proximidad.

**Resultado esperado:**
- El músico/banda sin coordenadas **no aparece** en los resultados.
- No hay ningún error en pantalla.

---

## 8. Checklist de sign-off

Marca cada casilla cuando hayas verificado el comportamiento correcto. Firma con tu nombre al final.

### Vista Músicos

- [ ] TC-M01 — Sin filtros: se muestran todos los músicos, slider deshabilitado
- [ ] TC-M02 — Filtro por un género: resultado correcto, checkbox restaurado
- [ ] TC-M03 — Filtro por varios géneros: resultado correcto, todos los checkboxes restaurados
- [ ] TC-M04 — Filtro por instrumento: resultado correcto
- [ ] TC-M05 — Género + instrumento combinados: resultado correcto
- [ ] TC-M06 — Autocomplete de ciudad: campo relleno, slider habilitado
- [ ] TC-M07 — Filtro de proximidad: solo músicos dentro del radio, distancia en tarjeta
- [ ] TC-M08 — Botón "Usar mi ubicación" (permiso aceptado): ciudad y slider rellenados
- [ ] TC-M09 — Botón "Usar mi ubicación" (permiso denegado): botón vuelve a normal sin error
- [ ] TC-M10 — Slider deshabilitado sin ciudad: no interactivo, aspecto tenue
- [ ] TC-M11 — Slider habilitado al escribir en campo de ciudad
- [ ] TC-M12 — Botón "Limpiar": estado inicial restaurado
- [ ] TC-M13 — Sin resultados: mensaje de estado vacío visible

### Vista Bandas

- [ ] TC-B01 — Sin bloque de instrumentos en el sidebar
- [ ] Equivalentes de TC-M01, TC-M02, TC-M03, TC-M06, TC-M07, TC-M08, TC-M09, TC-M10, TC-M12, TC-M13

### Comportamientos compartidos

- [ ] TC-C01 — Degradado del slider en tiempo real
- [ ] TC-C02 — URL compartible: mismos filtros al recargar
- [ ] TC-C03 — Distancia en tarjeta solo cuando el filtro de proximidad está activo
- [ ] TC-C04 — Músico/banda sin coordenadas excluido al filtrar por proximidad

---

**Revisado por:** ___________________________

**Fecha:** ___________________________

**Resultado:** `APROBADO` / `RECHAZADO`

**Observaciones:**

> *(Si hay algo que no funciona, abre un issue o comenta en la PR indicando el número de TC, los pasos exactos que seguiste y qué resultado obtuviste.)*
