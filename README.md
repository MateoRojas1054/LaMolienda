# LaMolienda — Simple SI de caja para cafetería

Proyecto mínimo con PHP + SQLite ejecutado en Docker para llevar el control de ventas y gastos.

## Login
- **Admin**: usuario `admin`, contraseña `admin123` (puede registrar ventas y gastos).
- **Viewer**: usuario `viewer`, contraseña `viewer123` (solo puede ver información).

## Uso
- En la página principal se registra la **venta del día** y se añaden **gastos** (sin descripción).
- **Venta total (mes)**: suma acumulada de ventas desde el 1 del mes hasta hoy.
- **Saldo a favor (día)**: venta del día - gastos del día.
- **Saldo a favor (mes)**: suma de saldos a favor diarios del mes.
- El reporte muestra una tabla mensual con fecha, venta del día, venta total acumulada, gastos del día y saldo a favor acumulado.

## Levantar localmente
```sh
cd /home/krassny/Escritorio/LaMolienda
docker compose up -d
# Abrir http://localhost:8082
```

- En la página principal se registra la **venta del día** y se añaden **gastos** (sin descripción).
- **Venta total (mes)**: suma acumulada de ventas desde el 1 del mes hasta hoy.
- **Saldo a favor (día)**: venta del día - gastos del día.
- **Saldo a favor (mes)**: suma de saldos a favor diarios del mes.
- El reporte muestra una tabla mensual con fecha, venta del día, venta total acumulada, gastos del día y saldo a favor acumulado.

Datos:
- Se usa SQLite y el fichero se guarda en `src/data/cafeteria.sqlite` dentro del contenedor.
