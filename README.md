# Storage Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codegaf/storagemanager.svg?style=flat-square)](https://packagist.org/packages/codegaf/storagemanager)
[![Total Downloads](https://img.shields.io/packagist/dt/codegaf/storagemanager.svg?style=flat-square)](https://packagist.org/packages/codegaf/storagemanager)

Wrapper de Spatie Media Library y gestión de ficheros con privacidad Spatie Permission para Laravel.

## Instalación

Puedes instalar el paquete via composer:

```bash
composer require codegaf/storagemanager dev-master
```

## Guía de uso

Storage manager está creado como wrapper de Spatie Media Library, recogiendo sus principales funciones y envolviéndolas en otras globales con una 
sintaxis más fácil de recordar. Además, valiéndose de la librería Spatie Permission, se ha predefinido una serie de funciones que aportan capas de
seguridad a niveles de roles, permisos e identificadores de usuario, al mismo tiempo que permite abrirse a nuevos casos.

El paquete se compone de las siguientes clases:

- StorageManagerController: de acceso a través de un publish, esta clase tiene predefinida una serie de métodos
 que serán accesibles a través de http. Conecta con el servicio StorageManagerService.
- StorageManagerService: de acceso a través de un publish, esta clase extiende de StorageManagerServiceBase y tiene creada
la función por defecto checkPermissions. En esta función podremos establecer los permisos de los archivos por colecciones.
En la clase StorageManagerServiceBase tenemos un ejemplo de cómo podría quedar una colección con permisos, utilizando los métodos
predefinidos: userHasRole, userHasRoles, userIsOwner, userHasPermission. Si necesitamos ampliar la librería con nuevas funciones
o sustituir alguna del base, podremos hacerlo en esta clase. 

``` php
/**
     * Ejemplo. Comprueba los permisos establecidos por colección y media
     *
     * @param Media $media
     * @return bool
     */
    private function checkPermissionsExample(Media $media) {
        switch ($media->collection_name) {
            case 'avatar':
                if (!$this->userHasRole($media)) {
                    return false;
                }

                if (!$this->userIsOwner($media)) {
                    return false;
                }
                
                if (!$this->userHasRoles($media)) {
                    return false;
                }

                if (!$this->userHasPermission($media, 'download')) {
                    return false;
                }

                return true;
            default:
                return true;
        }
    }
```

- StorageManagerServiceBase: Las funciones predefinidas en esta clase serán:

**file** -> (Media $media, string $conversionName = ''): Devuelve un archivo en función de los permisos. Puede devolver su conversión
si se especifica.

**addFile** -> (Model $model, UploadedFile $file, string collection, array customProperties = []): Añade un nuevo media al modelo.
A través de las custom properties podemos incluir permisos al archivo.

**addFiles** -> (Model $model, array $files, string $collection, array $customProperties): Añade múltiples medias a un modelo.

**addFileFromBase64** -> (Model $model, string $base64File, string $fileName, string $collection, array $customProperties = []): Añade un nuevo
media en formato base64 al modelo.

**updateCustomProperties** -> (Media $media, array $customProperties): Modifica los custom properties de un media.

**deleteCustomProperties** -> (Media, $media, array $customProperties): Elimina los custom properties pasados por parámetro.

**deleteFile** -> (Media $media, bool $force = false): Elimina un media de base de datos y su fichero asociado. Si pasamos true
como segundo parámetro eliminaremos definitivamente un softDelete.

**deleteModelFiles** -> (Model $model, string $collection = ''): Elimina todos los medias de un modelo o los de la colección
pasada por parámetro.

**reorderFiles** -> (array $ids, int $startOrder = 1): Ordena los media pasados por parámetros por defecto desde el número 1

**filesByCollection** -> (string $collection): Descarga un fichero zip con todas las imágenes originales de una colección.

**filesByModel** -> (array $data): Descarga un fichero zip con todos los ficheros de un modelo o los de la colección pasada por parámetro.

**filesByIds** -> (array $data): Descarga un fichero zip con todos los ficheros originales que correspondan a los ids pasados por
parámetro.

**userIsOwner** -> (Media $media): Comprueba si el media tiene el id del usuario autenticado entre sus custom properties.

**userHasRole** -> (Media $media): Comprueba si el media tiene el rol del usuario autenticado entre sus custom properties.

**userHasRoles** -> (Media $media): Comprueba si el media tiene el rol del usuario entre los roles de la custom properties.

**userHasPermission** -> (Media $media, string $permission): Comprueba si el usuario y el media tiene el permiso pasado por parámetro.

- CustomPathGenerator: Clase que hace override del comportamiento por defecto de Spatie a la hora de nombrar el path de los media. Se utiliza en el
paquete para encriptar la carpeta del media.

- Web.php: Recoge las rutas predefinidas del paquete con los middleware web y auth:

Una vez que hemos procedido a instalar el paquete tendremos que ejecutar el publish:

``` php
php vendor:publish --provider=Codegaf\StorageManager\StorageManagerProvider
```

Este comando publicará las clases StorageManagerController\StorageManagerController y StorageManagerService\StorageManagerService.

Por último, iremos al archivo config/medialibrary.php y en el índice "path_generator" añadiremos el CustomPathGenerator:

``` php
/*
     * The class that contains the strategy for determining a media file's path.
     */
    'path_generator' => \Codegaf\StorageManager\CustomPathGenerator::class,
```

Si estás cacheando el config no olvides hacer:

``` php
php artisan config:cache
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email isaaccamrod@gmail.com instead of using the issue tracker.

## Credits

- [Isaac Campos](https://github.com/10codesoftware)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.