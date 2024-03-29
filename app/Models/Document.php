<?php

namespace App\Models;

use App\Models\Document\Template;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'path', 'bindings'];

    protected $casts = [
        'bindings' => 'array',
    ];

    protected const PATH = 'documents';

    public function delete()
    {
        unlink($this->getFullPath());
        return parent::delete();
    }

    public static function create($params)
    {
        return static::query()->create(array_replace($params, [
            'name' => self::cleanPath($params['filename']),
            'path' => self::saveFile($params['path']),
        ]));
    }

    public function regenerate()
    {
        if (file_exists($this->getFullPath())) {
            unlink($this->getFullPath());
        }

        $this->update([
            'path' => self::saveFile($this->template->regenerateDocument($this->bindings))
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

    public function template() {
        return $this->belongsTo(Template::class);
    }
}
