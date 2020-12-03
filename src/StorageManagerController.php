<?php

namespace Codegaf\StorageManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaStream;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageManagerController extends Controller {

    protected $storageManagerService;

    public function __construct(StorageManagerService $storageManagerService) {
        $this->storageManagerService = $storageManagerService;
    }

    /**
     * Devuelve un archivo en función de los permisos. Puede devolver su conversión
     *
     * @param Media $media
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function file(Media $media, Request $request) {
        return $this->storageManagerService->file($media, $request->input('conversion') ?? '');
    }

    /**
     * Devuelve un listado de archivos empaquetados en un zip por colección
     *
     * @param string $collection
     * @return MediaStream
     */
    public function filesByCollection(string $collection) {
        return $this->storageManagerService->filesByCollection($collection);
    }

    /**
     * Devuelve un listado de archivos empaquetados en un zip por ids
     *
     * @param Request $request
     * @return MediaStream
     */
    public function filesByIds(Request $request) {
        return $this->storageManagerService->filesByIds($request->all());
    }

    /**
     * Devuelve todos los archivos de un modelo o solo de una colección en concreto
     *
     * @param Request $request
     * @return MediaStream
     */
    public function filesByModel(Request $request) {
        return $this->storageManagerService->filesByModel($request->all());
    }

}