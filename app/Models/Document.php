<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'path'];

    protected const PATH = 'documents';

    public function delete()
    {
        unlink($this->getFullPath());
        return parent::delete();
    }

    public static function create($params)
    {
        return static::query()->create([
            'name' => self::cleanPath($params['filename']),
            'path' => self::saveFile($params['path'])
        ]);
    }

    public function getFullPath()
    {
        return storage_path('app/'.$this->path);
    }

    protected static function saveFile($path)
    {
        return Storage::putFile(self::PATH, new File($path));
    }

    protected static function cleanPath($path)
    {
        return str_replace('/', '.', $path);
    }
}
