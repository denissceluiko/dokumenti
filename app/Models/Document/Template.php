<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class Template extends Model
{
    use HasFactory;
    protected const PATH = 'templates';

    protected $fillable = ['name', 'naming'];

    protected $casts = [
        'bindings' => 'array'
    ];

    public function compile($params)
    {
        $params = array_merge($params, [
            'x-template-name' => $this->name,
        ]);

        $proc = new TemplateProcessor($this->getStoragePath());
        $proc->setValues($params);
        return ['filename' => $this->resolveFilename($params), 'path' => $proc->save()];
    }

    public static function createFromUpload(Request $request)
    {
        $template = new self();
        $template->name = $request->name;
        $template->naming = $request->naming;
        $template->path = $request->file('template_file')->store(self::getStoragePrefix());
        $template->hash = $template->getFileHash();
        $template->bindings = $template->resolveBindings();
        $template->save();

        return $template;
    }

    protected function resolveBindings()
    {
        $proc = new TemplateProcessor($this->getStoragePath());
        return array_combine($proc->getVariables(), $proc->getVariables());
    }

    protected function resolveFilename($params)
    {
        $result = $this->naming;
        preg_match_all('/\$\{(.*?)}/i', $this->naming, $matches);

        for($i=0; $i<count($matches[0]); $i++)
        {
            $result = str_replace($matches[0][$i], $params[$matches[1][$i]], $result);
        }

        return $result.'.docx';
    }

    public function getFileHash()
    {
        return sha1_file($this->getStoragePath());
    }

    public static function getStoragePrefix()
    {
        return Template::PATH;
    }

    public function getStoragePath()
    {
        return storage_path('app/'.$this->path);
    }

    public function scopeHash(Builder $query, $hash)
    {
        return $query->where('hash', $hash);
    }
}
