<?php

namespace pavlatch;

use pavlatch\Exception\ServerException;

class Server
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $inputName;

    /**
     * @var bool
     */
    private $imageOnly;

    /**
     * @var Response
     */
    private $response;

    /**
     * Server constructor.
     *
     * @param array $config
     *
     * @throws ServerException
     */
    public function __construct(array $config)
    {
        $this->dir = $config['dir'] ?? __DIR__ . '/../storage';
        $this->inputName = $config['inputName'] ?? 'FileContents';
        $this->imageOnly = $config['imageOnly'] ?? true;
        $secureKey = $config['secureKey'] ?? null;

        if ($secureKey === null) {
            throw new ServerException('Invalid configuration');
        }

        if (($_POST['secureKey'] ?? null) !== $secureKey) {
            throw new ServerException('Forbidden', 400);
        }

        $this->response = $this->init();
    }

    /**
     * @throws ServerException
     */
    private function init(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? null) === 'exist') {
                return $this->existAction();
            }
            return $this->uploadAction();
        }

        throw new ServerException('Invalid method', 404);
    }

    private function existAction(): Response
    {
        $filename = str_replace('..', '', trim($_POST['filename']));
        $file = $this->dir . '/' . $filename;
        if (is_readable($file)) {
            return new Response('File found', 200);
        }
        return new Response('File not found', 404);
    }

    /**
     * @return Response
     * @throws ServerException
     */
    private function uploadAction(): Response
    {
        if (!isset($_FILES[$this->inputName])) {
            throw new ServerException('Not files set', 409);
        }

        $files = $_FILES[$this->inputName];
        if (!\is_array($files)) {
            throw new ServerException('Not array', 409);
        }

        if (!isset($files['error'])) {
            throw new ServerException('Wrong array', 409);
        }

        try {
            $receivedFile = new ReceivedFile($files);
        } catch (Exception\ReceivedFileException $e) {
            throw new ServerException('Upload error: ' . $e->getMessage());
        }

        if ($this->imageOnly) {
            if (!$receivedFile->isAllowedMimeType()) {
                throw new ServerException('Not allowed mime type', 409);
            }

            if (!$receivedFile->isImage()) {
                throw new ServerException('Is not a image', 409);
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

        return new Response('success', 201);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
