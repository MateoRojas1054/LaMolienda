# LaMolienda — Simple SI de caja para cafetería

Proyecto mínimo con PHP + SQLite ejecutado en Docker para llevar el control de ventas y gastos.

Requisitos:
- Docker y Docker Compose

Levantar la aplicación:

```sh
cd /home/krassny/Escritorio/LaMolienda
docker compose up -d
# Abrir http://localhost:8080 en el navegador
```

Uso básico:
- En la página principal se pueden añadir ventas y gastos uno por uno.
- La app muestra el total de ventas, total de gastos y el neto del día actual.
- En "Ver reporte por fecha" puede consultarse historico por fecha.

- En la página principal se registra la **venta del día** y se añaden **gastos** (sin descripción).
- **Venta total (mes)**: suma acumulada de ventas desde el 1 del mes hasta hoy.
- **Saldo a favor (día)**: venta del día - gastos del día.
- **Saldo a favor (mes)**: suma de saldos a favor diarios del mes.
- El reporte muestra una tabla mensual con fecha, venta del día, venta total acumulada, gastos del día y saldo a favor acumulado.

Datos:
- Se usa SQLite y el fichero se guarda en `src/data/cafeteria.sqlite` dentro del contenedor.
