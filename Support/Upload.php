<?php

namespace Modules\Common\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Upload
{
    protected $driver;

    protected $filesystem;

    public function __construct(
        protected UploadedFile $file,
        $driver
    ) {
        $this->driver = $driver;
        $this->filesystem = Storage::disk($driver);
    }

    public function formatFileName($path = 'default', $filename = '', $suffix = '', $format = '')
    {
        $suffix = $suffix ?: $this->file->extension();
        $filename = $filename ?: $this->file->getClientOriginalName();

        $replaceArr = [
            '{path}' => $path,
            '{year}' => date('Y'),
            '{mon}' => date('m'),
            '{day}' => date('d'),
            '{hour}' => date('H'),
            '{min}' => date('i'),
            '{sec}' => date('s'),
            '{timestamp}' => time(),
            '{random}' => Str::random(),
            '{random32}' => Str::random(32),
            '{uniqid}' => uniqid(),
            '{filename}' => substr($filename, 0, 15),
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.'.$suffix : '',
            '{filesha1}' => hash_file('sha1', $this->file->path()),
            '{filemd5}' => md5($this->file->path()),
        ];

        if (! $format && \array_key_exists('format', $config = $this->filesystem->getConfig())) {
            $format = $config['format'];
        }

        return str_replace(array_keys($replaceArr), array_values($replaceArr), $format);
    }

    public function upload($path)
    {
        $filename = $this->formatFileName($path);

        $params = [
            'filename' => $filename,
            'name' => substr(htmlspecialchars(strip_tags($this->file->getClientOriginalName())), 0, 100),
            'size' => $this->file->getSize(),
            'mimetype' => $this->file->getMimeType(),
            'storage' => $this->driver,
            'sha1' => hash_file('sha1', $this->file->path()),
        ];

        // db 查询

        $params['url'] = $this->filesystem->url($this->filesystem->putFileAs('', $this->file, $filename));

        return $params;
    }
}
