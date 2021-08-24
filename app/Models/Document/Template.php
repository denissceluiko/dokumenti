<?php

namespace App\Models\Document;

use App\Imports\TemplateBatchImport;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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

        $bindings = $this->getRequestBindings($params);

        foreach($bindings['rows'] as $row => $values) {
            $proc->cloneRowAndSetValues($row, $values);
        }

        foreach($bindings['blocks'] as $block => $values) {
            $proc->cloneBlock($block, 0, true, false, $values);
        }
        $proc->setValues($bindings['bindings']);

        return $this->documents()->create([
            'name' => Document::cleanPath($this->resolveFilename($params)),
            'path' => Document::saveFile($proc->save()),
            'bindings' => $bindings,
        ]);
    }

    public function batch($path)
    {
        Excel::import(new TemplateBatchImport($this), $path);
    }

    public static function createFromUpload(Request $request)
    {
        $template = new self();
        $template->name = $request->name;
        $template->naming = $request->naming;
        $template->path = $request->file('template_file')->store(self::getStoragePrefix());
        $template->hash = $template->getFileHash();
        $template->bindings = $template->resolveFileBindings();
        $template->save();

        return $template;
    }

    public function updateFromUpload(Request $request)
    {
        $this->fill($request->only('name', 'naming'));

        if ($request->has('template_file')) {
            $oldTemplate = $this->getStoragePath();

            $this->path = $request->file('template_file')->store(self::getStoragePrefix());
            $this->hash = $this->getFileHash();
            $this->bindings = $this->resolveFileBindings();

            unlink($oldTemplate);
        }

        $this->save();
        return $this;
    }

    protected function resolveFileBindings()
    {
        $proc = new TemplateProcessor($this->getStoragePath());
        $bindings = $proc->getVariables();
        $rowMacros = $this->locateRowMacros($bindings);
        $bindings = $this->removeMacros($bindings, $rowMacros);
        $rowGroups = $this->groupRowMacros($rowMacros);
        $blockMacros = $this->locateBlockMacros($bindings);
        $bindings = $this->removeMacros($bindings, $blockMacros);
        $blockGroups = $this->groupBlockMacros($blockMacros);

        return [
            'rows' => $rowGroups,
            'blocks' => $blockGroups,
            'bindings' => $bindings,
        ];
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

    public function resolveAgain()
    {
        $this->bindings = $this->resolveFileBindings();
        $this->save();
    }

    protected function locateRowMacros(array $bindings)
    {
        $rows = preg_grep('/row__(.*)\.?(.*)/i', $bindings);
        return array_values($rows);
    }

    protected function locateBlockMacros(array $bindings)
    {
        $rows = preg_grep('/block__(.*)\.?(.*)/i', $bindings);
        return array_values($rows);
    }

    protected function removeMacros(array $bindings, array $macros)
    {
        return array_values(array_filter($bindings, function($binding) use ($macros) {
            return !in_array($binding, $macros);
        }));
    }

    protected function groupRowMacros(array $macros)
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][] = $macro.'.'.$cell;
            } else {
                $groups[$macro][] = $macro;
            }
        }
        return $groups;
    }

    protected function groupBlockMacros(array $macros)
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            $macro = ltrim($macro, '/');
            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][] = $macro.'.'.$cell;
            } elseif(!isset($groups[$macro])) {
                $groups[$macro] = [];
            }
        }
        return $groups;
    }

    protected function extractValues(array &$params, $type)
    {
        $rows = [];
        $rowNames = array_keys($this->bindings[$type]);
        $params = array_filter($params, function ($param, $key) use ($rowNames, &$rows) {
            if (in_array($key, $rowNames)) {
                $rows[$key] = $param;
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
        return $rows;
    }

    protected function getRequestBindings($params)
    {
        $rows = [];
        foreach($this->extractValues($params, 'rows') as $row => $value) {
            $rows[$row] = json_decode($value, true);
        }

        $blocks = [];
        foreach($this->extractValues($params, 'blocks') as $block => $value) {
            $blocks[$block] = json_decode($value, true);
        }

        return [
            'bindings' => collect($params)->only($this->bindings['bindings'])->toArray(),
            'rows' => $rows,
            'blocks' => $blocks,
        ];
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

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
