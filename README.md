# Descripción

Esta es una aplicación de gestión de tareas implementada con PHP puro utilizando el patrón MVC. Se ha modificado un framework base para adaptar la aplicación a las necesidades del proyecto. El punto de entrada es el archivo `index.php` que se encuentra en la carpeta `public`.

Se han implementado varios patrones de diseño como `Singleton, Factory y Repository` para asegurar la correcta inicialización de los repositorios y la flexibilidad en la gestión de las bases de datos y la persistencia de los datos.

Además, se ha mejorado la gestión de errores relacionados con el inicio de la aplicación, garantizando una experiencia amigable para el usuario sin que la aplicación se rompa.

Tambien se ha implementado un registro local de los errores lanzados por las Exceptions que salvaguardan el log de errores en la aplicación, se puede acceder a este log en la carpeta `logs`, en la raíz del proyecto

## ✔️	 Nivel 1

Persistencia en JSON

## ✔️	 Nivel 2

Persistencia en Mysql


## ✔️	 Nivel 3

Persistencia en MOngoDB


## 💻 Tecnologías Utilizadas

- **PHP**: Lenguaje de programación utilizado para desarrollar la aplicación.
- **Tailwind CSS**: Framework CSS utilizado para el diseño responsivo de las vistas.
- **MySQL**: Base de datos para almacenar usuarios y tareas.
- **JSON**: Persistencia de datos en formato JSON para algunos repositorios.

## 🔑 Requisitos

- **PHP 7.4+**
- **Servidor web local** (como Apache o Nginx) para ejecutar la aplicación.
- **MySQL** o **MariaDB** (si se utilizan bases de datos relacionales).
- **Tailwind CSS** (para estilos en la interfaz).
- **Composer** (opcional, para gestionar dependencias de PHP).

    illuminate/support 11.39.0 The Illuminate Support package.
    mongodb/mongodb    1.15.0  MongoDB driver library


## ☕ Instalación

1. Clona este repositorio en tu máquina local.


```git clone https://github.com/tu_usuario/nombre_del_repositorio.git```


2. Accede a la carpeta del proyecto.

```cd nombre_del_repositorio```

3. Asegúrate de tener un servidor local configurado y que apunte a la carpeta public como documento raíz.

4. Crea las bases de datos `users` y `tasks` utilizando los siguientes comandos SQL:

```
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    user INT NOT NULL,  -- Relaciona la tarea con un usuario
    CONSTRAINT fk_user FOREIGN KEY (user) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL
);
```

5. Asegúrate de configurar la base de datos correctamente en el archivo `settings.ini` (ubicado en la carpeta `config`, en la raíz del proyecto). Debes proporcionar el valor `json`, `mysql` o `mongodb` en `user_repository` y `task_repository` para que la aplicación trabaje con el tipo de persistencia correspondiente.

   **Nota**: El formato para el campo `uri` en MongoDB es:
   ```text
   mongodb://user:password!@localhost:27017/?authSource=admin

Donde admin es la base de datos utilizada para la autenticación de usuarios en MongoDB. Si tienes configurada otra base de datos principal para la autenticación de usuarios, asegúrate de cambiar admin por el nombre adecuado de tu base de datos de autenticación.

6. Asegurate de darle permisos de escritura a los archivos app/data Users.json y Tasks.json y a logs/app_errors.log


## ⏩ Ejecución

1. Inicia el servidor local apuntando a la carpeta public. Esto se puede hacer utilizando herramientas como `XAMPP, WAMP, MAMP` o configurando `Apache/Nginx` manualmente.

2. Accede a la aplicación desde tu navegador:

3. La aplicación debería cargarse correctamente. Si hay algún error al inicializar los repositorios, se mostrará un mensaje amigable sin que la aplicación se caiga, gracias a las modificaciones en el archivo `Router.php`.

4. A partir de ahí, podrás gestionar tareas y usuarios, crear nuevas tareas, editar o eliminar las existentes, y asociar tareas a usuarios.




