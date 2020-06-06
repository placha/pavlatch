<?php

namespace pavlatch;

use pavlatch\Exception\ReceivedFileException;

class ReceivedFile
{
    private const ALLOWED_MIME_TYPES = [
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
    ];

    public $name;
    public $tmpName;
    private $type;
    private $size;
    private $error;

    public function __construct(array $fileData)
    {
        if (!array_key_exists('name', $fileData) ||
            !array_key_exists('tmp_name', $fileData) ||
            !array_key_exists('type', $fileData) ||
            !array_key_exists('size', $fileData) ||
            !array_key_exists('error', $fileData)) {
            throw new ReceivedFileException('Invalid received file data format');
        }

        $this->setFileData($fileData);
        if ($this->isError()) {
            throw new ReceivedFileException($this->getErrorMessage());
        }

        if (!is_readable($this->tmpName)) {
            throw new ReceivedFileException('Received file Is not readable');
        }
    }

    private function setFileData(array $fileData): void
    {
        $this->name = $fileData['name'];
        $this->tmpName = $fileData['tmp_name'];
        $this->type = $fileData['type'];
        $this->size = $fileData['size'];
        $this->error = $fileData['error'];
    }

    public function isAllowedMimeType(): bool
    {
        return \in_array($this->type, array_values(self::ALLOWED_MIME_TYPES), true);
    }

    public function isImage(): bool
    {
        return (bool)getimagesize($this->tmpName);
    }

    private function isError(): bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    private function getErrorMessage(): string
    {
        switch ($this->error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
                break;
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
                break;
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.';
                break;
            default:
                return 'Unknown upload error';
                break;
        }
    }
}
