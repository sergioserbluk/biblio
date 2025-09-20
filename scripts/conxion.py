import psycopg2

# Datos de conexión
conexion = psycopg2.connect(
    dbname="biblioteca25",
    user="postgres",
    password="46315468",
    host="localhost",   # o la IP del servidor
    port="5432"         # puerto por defecto de PostgreSQL
)

# Crear cursor para ejecutar consultas
cursor = conexion.cursor()

# Ejecutar un INSERT como ejemplo
cursor.execute("""
    INSERT INTO editorial (id_editorial, nombre)
    VALUES (1, 'mojana')
""", (1, "Raul"))

# Guardar cambios
conexion.commit()

# Cerrar cursor y conexión
cursor.close()
conexion.close()
