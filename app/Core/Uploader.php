<?php

declare(strict_types=1);

namespace App\Core;

final class Uploader
{
    private array $allowedTypes;
    private int $maxSize;
    private string $basePath;
    private array $errors = [];

    public function __construct(?string $basePath = null)
    {
        $this->allowedTypes = explode(',', env('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif,pdf,ppt,pptx,zip,doc,docx,txt'));
        $this->maxSize = (int)env('UPLOAD_MAX_SIZE', 5242880);
        $this->basePath = $basePath ?? dirname(__DIR__, 2) . '/public/uploads';
    }

    public function upload(array $file, string $subDir = ''): array|false
    {
        $this->errors = [];

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire.',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé.',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
                UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
                UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload.',
            ];
            $this->errors[] = $errorMessages[$file['error']] ?? 'Erreur inconnue lors de l\'upload.';
            return false;
        }

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($fileSize > $this->maxSize) {
            $this->errors[] = sprintf(
                'Le fichier est trop volumineux (%s). Taille maximale: %s.',
                $this->formatSize($fileSize),
                $this->formatSize($this->maxSize)
            );
            return false;
        }

        if (!in_array($extension, $this->allowedTypes, true)) {
            $this->errors[] = sprintf(
                'Type de fichier non autorisé (%s). Types acceptés: %s.',
                $extension,
                implode(', ', $this->allowedTypes)
            );
            return false;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
        ];

        if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
            $this->errors[] = "Le type MIME du fichier ({$mimeType}) ne correspond pas à son extension.";
            return false;
        }

        $storageDir = $this->basePath;
        if ($subDir !== '') {
            $storageDir .= '/' . trim($subDir, '/');
        }

        if (!is_dir($storageDir)) {
            if (!mkdir($storageDir, 0755, true)) {
                $this->errors[] = 'Impossible de créer le répertoire de stockage.';
                return false;
            }
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $storageDir . '/' . $safeName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            $this->errors[] = 'Échec du déplacement du fichier uploadé.';
            return false;
        }

        chmod($destination, 0644);

        return [
            'path' => $subDir !== '' ? $subDir . '/' . $safeName : $safeName,
            'full_path' => $destination,
            'original_name' => $originalName,
            'safe_name' => $safeName,
            'size' => $fileSize,
            'type' => $extension,
            'mime_type' => $mimeType,
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getLastError(): string
    {
        return end($this->errors) ?: 'Erreur inconnue';
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    private function formatSize(int $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        $size = $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }
}
