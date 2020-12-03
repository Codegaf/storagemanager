<?php


namespace Codegaf\StorageManager;


use Spatie\MediaLibrary\Models\Media;

class StorageManagerService extends StorageManagerServiceBase
{
    public function checkPermissions(Media $media)
    {
        switch ($media->collection_name) {
            case 'avatar':
                if (!$this->userHasRole($media)) {
                    return false;
                }

                if (!$this->userIsOwner($media)) {
                    return false;
                }

                if (!$this->userHasPermission($media, 'download')) {
                    return false;
                }

                return true;

                break;
            default:
                return true;
        }
    }

}