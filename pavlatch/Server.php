<?php

namespace pavlatch;

use FilesystemIterator;
use Intervention\Image\ImageManagerStatic as Image;
use pavlatch\Exception\ServerException;

class Server
{
    private string $dir;
    private string $secureKey;
    private string $inputName;
    private bool $imageOnly;
    private Response $response;
    private ?int $resizeWidth;
    private ?int $resizeHeight;
    private RouteResolver $routeResolver;

    public function __construct(array $config)
    {
        $this->dir = $config['dir'] ?? __DIR__ . '/../storage';
        $this->inputName = $config['inputName'] ?? 'FileContents';
        $this->imageOnly = $config['imageOnly'] ?? true;
        $this->secureKey = $config['secureKey'];
        [$this->resizeWidth, $this->resizeHeight] = $config['resize'];
        $this->routeResolver = new RouteResolver();
    }

    /**
     * @throws ServerException
     */
    public function run(): void
    {
        $route = $this->routeResolver->getRoute();
        if (!$route->isAllowed($this->secureKey)) {
            throw new ServerException('Forbidden', 400);
        }

        $this->response = $this->{$route->getActionName() . 'Action'}();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @throws ServerException
     */
    private function getAction(): void
    {
        $file = $this->getReadableFile();
        if ($file === null) {
            throw new ServerException('File not found', 404);
        }

        header('Location: ' . $file);
        exit;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @throws ServerException
     */
    private function thumbAction(): void
    {
        $file = $this->getReadableFile();
        if ($file === null) {
            throw new ServerException('File not found', 404);
        }

        $resizeWidth = $this->routeResolver->getRouteSection(2) ?? (string)$this->resizeWidth;
        $resizeWidth = $resizeWidth === 'null' ? null : (int)$resizeWidth;
        $resizeWidth = $resizeWidth > 8000 || $resizeWidth < 1 ? $this->resizeWidth : $resizeWidth;

        $resizeHeight = $this->routeResolver->getRouteSection(3) ?? (string)$this->resizeHeight;
        $resizeHeight = $resizeHeight === 'null' ? null : (int)$resizeHeight;
        $resizeHeight = $resizeHeight > 8000 || $resizeHeight < 1 ? $this->resizeHeight : $resizeHeight;

        $img = Image::make($file);
        $img->resize($resizeWidth, $resizeHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        echo $img->encode('webp')->response();
        exit;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function countAction(): Response
    {
        $fi = new FilesystemIterator($this->dir);
        return new Response('File count: ' . iterator_count($fi));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @throws ServerException
     */
    private function viewAction(): void
    {
        $file = $this->getReadableFile();
        if ($file === null) {
            throw new ServerException('File not found', 404);
        }

        $imageInfo = getimagesize($file) ?? [];
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;
        $mime = $imageInfo['mime'] ?? null;
        $fp = fopen($file, 'rb');
        header('Image-Width: ' . $width);
        header('Image-Height: ' . $height);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        fpassthru($fp);
        exit;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @return Response
     * @throws ServerException
     */
    private function existAction(): Response
    {
        if ($this->getReadableFile() !== null) {
            return new Response('File found', 204);
        }
        throw new ServerException('File not found', 404);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
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
        if (!is_array($files)) {
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

        if (!file_exists($this->dir) && !mkdir($this->dir) && !is_dir($this->dir)) {
            throw new ServerException('Invalid storage folder.');
        }

        $moveResult = move_uploaded_file($receivedFile->tmpName, $this->dir . '/' . $receivedFile->name);

        if (!$moveResult) {
            throw new ServerException('Cannot save uploaded file.');
        }

        return new Response('success', 201);
    }

    private function getReadableFile(): ?string
    {
        $file = $this->dir . '/' . $this->getFilename();
        if (is_readable($file)) {
            return $file;
        }
        return null;
    }

    private function getFilename(): string
    {
        $filename = explode('/', $_GET['route'])[0];
        return str_replace('..', '', trim($filename));
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
