# Fixtures — Carga Masiva de Perfiles y Bandas

**Proyecto:** Music Hub  
**Fecha:** Junio 2026  
**Framework:** Symfony 8.0 · PHP 8.4 · Doctrine ORM

---

## Índice

1. [Objetivo](#1-objetivo)
2. [Estructura de fixtures](#2-estructura-de-fixtures)
3. [Fotos de stock](#3-fotos-de-stock)
4. [Perfiles de músicos](#4-perfiles-de-músicos)
5. [Bandas y miembros](#5-bandas-y-miembros)
6. [Cómo ejecutar](#6-cómo-ejecutar)

---

## 1. Objetivo

Poblar la base de datos con datos realistas para poder demostrar y probar la aplicación sin depender de registros manuales. El volumen elegido (200 músicos, 100 bandas) es suficiente para que los filtros, búsquedas y listados muestren resultados variados.

---

## 2. Estructura de fixtures

| Clase | Descripción | Dependencias |
|---|---|---|
| `AppFixtures` | 10 instrumentos del sistema | — |
| `GeneroFixtures` | 19 géneros musicales | — |
| `MusicoMasivoFixtures` | 200 perfiles de músico (100 hombres + 100 mujeres) | AppFixtures, GeneroFixtures |
| `BandaMasivaFixtures` | 100 bandas con miembros asignados | MusicoMasivoFixtures |

`AppFixtures` y `GeneroFixtures` incluyen una comprobación de existencia para que sean seguros de ejecutar con `--append` sin duplicar datos.

---

## 3. Fotos de stock

### Por qué no se usan URLs externas

La plantilla de la aplicación espera un **nombre de archivo local** (`uploads/perfiles/foto.jpg`, `uploads/bandas/foto.jpg`), no una URL completa. Guardar URLs externas produce imágenes rotas si el servicio externo cambia o no está disponible.

### Por qué no se generan en PHP

El contenedor Docker de desarrollo no tiene instalada la extensión GD ni acceso a internet, lo que descarta tanto la generación programática de imágenes como la descarga en tiempo de ejecución del fixture.

### Solución adoptada

Las fotos se descargan **una sola vez desde Windows** (que sí tiene acceso a internet) usando PowerShell y la API gratuita de **Pexels**, y se almacenan en `src/DataFixtures/assets/fotos/`. El fixture simplemente las copia al directorio público correspondiente al ejecutarse.

```
src/DataFixtures/assets/fotos/
├── men_1.jpg … men_50.jpg       ← retratos masculinos (Pexels)
├── women_1.jpg … women_50.jpg   ← retratos femeninos (Pexels)
└── band_1.jpg … band_100.jpg    ← fotos de bandas (Pexels)
```

Al estar en el repositorio, cualquier entorno (Docker local, Railway, máquina de un compañero) dispone de las fotos sin necesitar internet ni pasos adicionales.

### Script de descarga

```powershell
$apiKey = "TU_API_KEY"
$dir = "src/DataFixtures/assets/fotos"
$headers = @{ "Authorization" = $apiKey }

# Retratos de personas
for ($i = 1; $i -le 50; $i++) {
    $r = Invoke-RestMethod "https://api.pexels.com/v1/search?query=man+face+portrait&per_page=50" -Headers $headers
    Invoke-WebRequest $r.photos[$i-1].src.medium -OutFile "$dir\men_$i.jpg"
    $r = Invoke-RestMethod "https://api.pexels.com/v1/search?query=woman+face+portrait&per_page=50" -Headers $headers
    Invoke-WebRequest $r.photos[$i-1].src.medium -OutFile "$dir\women_$i.jpg"
}

# Fotos de bandas
$pagina = 1; $n = 0
while ($n -lt 100) {
    $r = Invoke-RestMethod "https://api.pexels.com/v1/search?query=music+band+group&per_page=80&page=$pagina" -Headers $headers
    foreach ($p in $r.photos) {
        if ($n -ge 100) { break }
        $n++; Invoke-WebRequest $p.src.medium -OutFile "$dir\band_$n.jpg"
    }
    $pagina++
}
```

---

## 4. Perfiles de músicos

### Distribución equitativa

Para que los filtros de la aplicación devuelvan resultados en todas las combinaciones, los datos se distribuyen de forma cíclica en lugar de aleatoria pura:

- **Instrumento principal:** `$i % count($instrumentos)` → cada instrumento aparece exactamente 20 veces como principal.
- **Ciudad:** `$i % count($ciudades)` → 20 ciudades españolas, 10 perfiles por ciudad.
- **Años de experiencia:** `($i % 30) + 1` → valores del 1 al 30 distribuidos uniformemente.
- **Géneros:** 2-3 por perfil, seleccionados con offset para evitar siempre los mismos.

### Fotografías

Se asigna la foto `men_{n}.jpg` o `women_{n}.jpg` según el sexo del perfil, ciclando entre las 50 disponibles de cada tipo. Cada foto se copia con un nombre único (`musico_m_001.jpg`, etc.) para evitar colisiones.

### Credenciales

- **Emails:** `musico_m_001@musichub.com` … `musico_f_100@musichub.com`
- **Contraseña común:** `musichub123`

---

## 5. Bandas y miembros

### Creación de bandas

100 bandas con nombres ficticios en español, distribuidas entre las 20 ciudades y con año de formación entre 1975 y 2020.

### Asignación de miembros

Se toman 100 músicos al azar (de los 200 creados) como candidatos a pertenecer a bandas. El tamaño de cada banda sigue esta distribución ponderada:

| Miembros | % de bandas |
|---|---|
| 1 | 10 % |
| 2 | 20 % |
| 3 | 30 % |
| 4 | 20 % |
| 5 | 10 % |

Cada músico puede pertenecer a un máximo de 2 bandas. El primer miembro asignado recibe el rol de administrador (`es_administrador = true`). Todos los miembros se crean con `estado = 'aceptado'` para que aparezcan como miembros activos.

### Por qué este reparto

Con 100 bandas y 100 músicos candidatos, no es matemáticamente posible que cada músico esté en exactamente 1 banda si las bandas tienen varios miembros. El límite de 2 bandas por músico es el compromiso más cercano a la distribución pedida: la mayoría estará en 1 banda y una minoría en 2.

---

## 6. Cómo ejecutar

**Carga completa desde cero** (borra todos los datos existentes):

```bash
php bin/console doctrine:fixtures:load
```

**Añadir a datos existentes** (solo si instrumentos y géneros ya están cargados):

```bash
php bin/console doctrine:fixtures:load --append
```

> Si los filtros muestran duplicados, es señal de que `AppFixtures` o `GeneroFixtures` se ejecutaron varias veces con `--append` antes de que tuvieran la comprobación de existencia. La solución es hacer una carga completa sin `--append`.
