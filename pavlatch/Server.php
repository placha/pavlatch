<?php

namespace pavlatch;

use pavlatch\Exception\ServerException;

class Server
{


    /**
     * @var string
     */
    private $dir;

    private $imageOnly;

    public function __construct(array $config)
    {
        $this->dir = $config['dir'] ?? __DIR__ . '/../storage';
        $inputName = $config['inputName'] ?? 'FileContents';
        $this->imageOnly = $config['imageOnly'] ?? true;
        $secureKey = $config['secureKey'] ?? null;

        if ($secureKey === null) {
            throw new ServerException('Invalid configuration');
        }

        if ($_POST['secureKey'] !== $secureKey) {
            throw new ServerException('Forbidden');
        }


        if (!isset($_FILES[$inputName])) {
            throw new ServerException('Not files set');
        }

        $files = $_FILES[$inputName];
        if (!\is_array($files)) {
            throw new ServerException('Not array');
        }

        if (!isset($files['error'])) {
            throw new ServerException('Wrong array');
        }

        try {
            $receivedFile = new ReceivedFile($files);
        } catch (Exception\ReceivedFileException $e) {
            throw new ServerException('Upload error: ' . $e->getMessage());
        }

        if (false&&!$this->imageOnly) {
            if (!$receivedFile->isAllowedMimeType()) {
                throw new ServerException('Not allowed mime type');
            }

            if (!$receivedFile->isImage()) {
                throw new ServerException('Is not a image');
            }
        }

        if (!file_exists($this->dir)) {
            if (!mkdir($this->dir) && !is_dir($this->dir)) {
                throw new ServerException('Invalid storage folder.');
            }
        }

        $moveResult = move_uploaded_file($receivedFile->tmpName, $this->dir . '/' . $receivedFile->name);

        if (!$moveResult) {
            throw new ServerException('Cannot save uploaded file.');
        }
    }
}
