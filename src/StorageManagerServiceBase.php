<?php

namespace Codegaf\StorageManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaStream;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class StorageManagerServiceBase {

    /**
     * Gestiona los permisos por archivo
     *
     * @param Media $media
     */
    abstract public function checkPermissions(Media $media);

    /**
     * Devuelve un archivo en función de los permisos. Puede devolver su conversión
     *
     * @param Media $media
     * @param string $conversionName
     * @return BinaryFileResponse
     */
    public function file(Media $media, string $conversionName = '') {
        if (!$this->checkPermissions($media)) {
            abort(401);
        }

        return response()->file($media->getPath($conversionName));
    }

    /**
     * Añade un nuevo media al modelo
     *
     * @param Model $model
     * @param UploadedFile $file
     * @param string $collection
     * @param array $customProperties
     */
    public function addFile(Model $model, UploadedFile $file, string $collection, array $customProperties = []) {
        if (!empty($customProperties)) {
            $model->addMedia($file)->usingName(time())->withCustomProperties($customProperties)->toMediaCollection($collection);
        }
        else {
            $model->addMedia($file)->usingName(time())->toMediaCollection($collection);
        }
    }

    /**
     * Añade múltiples medias a un modelo
     *
     * @param Model $model
     * @param array $files
     * @param string $collection
     * @param array $customProperties
     */
    public function addFiles(Model $model, array $files, string $collection, array $customProperties = []) {
        foreach ($files as $file) {
            if (!empty($customProperties)) {
                $model->addMedia($file)->usingName(time())->withCustomProperties($customProperties)->toMediaCollection($collection);
            }
            else {
                $model->addMedia($file)->usingName(time())->toMediaCollection($collection);
            }
        }
    }

    /**
     * Añade un media en formato base64 al modelo
     *
     * @param Model $model
     * @param string $base64File
     * @param string $fileName con extensión
     * @param string $collection
     * @param array $customProperties
     */
    public function addFileFromBase64(Model $model, string $base64File, string $fileName, string $collection, array $customProperties = []) {
        if (!empty($customProperties)) {
            $model->addMediaFromBase64($base64File)->usingName(time())->usingFileName($fileName)->withCustomProperties($customProperties)->toMediaCollection($collection);
        }
        else {
            $model->addMediaFromBase64($base64File)->usingName(time())->usingFileName($fileName)->toMediaCollection($collection);
        }
    }

    /**
     * Actualiza una propiedad custom de un media
     *
     * @param Media $media
     * @param array $customProperties
     * @return Media
     */
    public function updateCustomProperties(Media $media, array $customProperties) {
        foreach ($customProperties as $key => $customProperty) {
            $media->setCustomProperty($key, $customProperty);
        }

        $media->save();

        return $media;
    }

    /**
     * Elimina las propiedades custom especificadas de un media
     *
     * @param Media $media
     * @param array $customProperties
     * @return Media
     */
    public function deleteCustomProperties(Media $media, array $customProperties) {
        foreach ($customProperties as $key => $customProperty) {
            $media->forgetCustomProperty($key);
        }

        $media->save();

        return $media;
    }

    /**
     * Elimina un media de base de datos y su fichero asociado
     *
     * @param Media $media
     * @param bool $force
     * @param bool $reorder
     * @throws \Exception
     */
    public function deleteFile(Media $media, bool $force = false, bool $reorder = false) {
        // TODO reordenar colección si la variable $reorder viene a true
        if ($force) {
            $media->forceDelete();
        }
        else {
            $media->delete();
        }
    }

    /**
     * Elimina todos los media de un modelo
     *
     * @param Model $model
     * @param string $collection
     */
    public function deleteModelFiles(Model $model, string $collection = '') {

        if ($collection) {
            $model->clearMediaCollection($collection);

        }
        else {
            $model->clearMediaCollection();
        }
    }

    /**
     * Ordena los media pasados por parámetros por defecto desde el número 1
     *
     * @param array $ids
     * @param int $startOrder
     */
    public function reorderFiles(array $ids, int $startOrder = 1) {
        Media::setNewOrder($ids, $startOrder);
    }

    /**
     * Descarga una fichero zip con todas las imágenes originales de la colección
     *
     * @param string $collection
     * @return MediaStream
     */
    public function filesByCollection(string $collection) {
        $files = Media::whereCollectionName($collection)->get();

        foreach ($files as $key => $file) {
            if (!$this->checkPermissions($file)) {
                unset($files[$key]);
            }
        }

        return MediaStream::create($collection.'.zip')->addMedia($files);
    }

    /**
     * Descarga un fichero zip con todos los ficheros de un modelo o con la colección pasada por parámetro
     *
     * @param array $data
     * @return MediaStream
     */
    public function filesByModel(array $data) {
        $model = ucfirst($data['modelclass']);

        if (!class_exists('App\\'.$model)) {
            $model = 'App\\Models\\'.$model;
        }

        $model = get_class(new $model);
        $model = $model::find($data['model']);

        if (!array_key_exists('collection', $data)) {
            $medias = collect();
            $collections = $model->media->pluck('collection_name')->unique();

            foreach ($collections as $collection) {
                $medias->add($model->getMedia($collection));
            }

            $medias = $medias->flatten();
        }
        else {
            $medias = $model->getMedia($data['collection']);
        }


        foreach ($medias as $key => $media) {
            if (!$this->checkPermissions($media)) {
                unset($medias[$key]);
            }
        }

        return MediaStream::create($data['modelclass'].'.zip')->addMedia($medias);
    }

    /**
     * Descarga un fichero zip con todas los ficheros originales que correspondan a los ids pasados por parámetro
     *
     * @param array $data
     * @return MediaStream
     */
    public function filesByIds(array $data) {
        $files = Media::whereIn('id', $data['ids'])->get();

        foreach ($files as $key => $file) {
            if (!$this->checkPermissions($file)) {
                unset($files[$key]);
            }
        }

        return MediaStream::create('files.zip')->addMedia($files);
    }

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

    // Functiones destinadas a permisos

    /**
     * Comprueba si el media tiene el id del usuario autenticado entre sus custom properties
     *
     * @param Media $media
     * @return bool|void
     */
    public function userIsOwner(Media $media) {

        if (array_key_exists('user_id', $media->custom_properties) && $media->custom_properties['user_id']) {
            return Auth::id() == $media->custom_properties['user_id'];
        }

        if (array_key_exists('user_ids', $media->custom_properties) && $media->custom_properties['user_ids']) {
            return in_array(Auth::id(), $media->custom_properties['user_ids']);
        }

        if (!array_key_exists('user_id', $media->custom_properties) && !array_key_exists('user_ids', $media->custom_properties)) {
            return true;
        }

        return true;
    }

    /**
     * Comprueba si el media tiene el rol del usuario autenticado entre sus custom properties
     *
     * @param Media $media
     * @return mixed
     */
    public function userHasRole(Media $media) {

        if (array_key_exists('role', $media->custom_properties) && $media->custom_properties['role']) {
            return Auth::user()->hasRole($media->custom_properties['role']);
        }

        if (!array_key_exists('role', $media->custom_properties)) {
            return true;
        }

        return false;
    }

    /**
     * Comprueba si el media tiene el rol del usuario entre los roles permitidos
     *
     * @param Media $media
     * @return bool|void
     */
    public function userHasRoles(Media $media) {

        if (array_key_exists('roles', $media->custom_properties) && $media->custom_properties['roles']) {
            return !empty(array_intersect(Auth::user()->getRoleNames()->toArray(), $media->custom_properties['roles']));
        }

        if (!array_key_exists('roles', $media->custom_properties)) {
            return true;
        }

        return true;
    }

    /**
     * Comprueba si el usuario tiene el permiso pasado por parámetro y a su vez el media lo exige
     *
     * @param Media $media
     * @param string $permission
     * @return bool
     */
    public function userHasPermission(Media $media, string $permission) {

        if (array_key_exists('permissions', $media->custom_properties) && $media->custom_properties['permissions']) {
            return Auth::user()->can($permission) && in_array($permission, $media->custom_properties['permissions']);
        }

        if (!array_key_exists('permissions', $media->custom_properties)) {
            return true;
        }

        return true;
    }





}