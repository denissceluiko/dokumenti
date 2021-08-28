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

    protected $__params = [
        'bindings' => [],
        'rows' => [],
        'blocks' => [],
    ];

    public function generateNewDocument(array $params)
    {
        $processor = $this->setParams($params)->compile();

        return $this->documents()->create([
            'name' => Document::cleanPath($this->resolveFilename()),
            'path' => Document::saveFile($processor->save()),
            'bindings' => $this->__params,
        ]);
    }

    public function regenerateDocument(array $params)
    {
        return $this->setParams($params)->compile()->save();
    }

    protected function compile() : TemplateProcessor
    {
        $proc = new TemplateProcessor($this->getStoragePath());

        foreach($this->__params['rows'] as $row => $values) {
            $proc->cloneRowAndSetValues($row, $values);
        }

        foreach($this->__params['blocks'] as $block => $values) {
            $proc->cloneBlock($block, 0, true, false, $values);
        }
        $proc->setValues($this->__params['bindings']);

        return $proc;
    }

    public function batch($path)
    {
        Excel::import(new TemplateBatchImport($this), $path);
    }

    public function setParams($params)
    {
        $this->__params = $this->getBindings($params);
        return $this;
    }

    public static function createFromUpload(Request $request)
    {
        $template = new self();
        $template->name = $request->name;
        $template->naming = $request->naming;
        $template->path = $request->file('template_file')->store(self::getStoragePrefix());
        $template->hash = $template->getFileHash();
        $template->bindings = $template->getBindingsFromFile();
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
            $this->bindings = $this->getBindingsFromFile();

            unlink($oldTemplate);
        }

        $this->save();
        return $this;
    }

    protected function getBindingsFromFile()
    {
        $proc = new TemplateProcessor($this->getStoragePath());
        $bindings = $proc->getVariables();

        $rowMacros = $this->locateMacros('row', $bindings);
        $bindings = $this->removeMacros($bindings, $rowMacros);
        $rowGroups = $this->groupRowMacros($rowMacros);

        $blockMacros = $this->locateMacros('block', $bindings);
        $bindings = $this->removeMacros($bindings, $blockMacros);
        $blockGroups = $this->groupBlockMacros($blockMacros);

        return [
            'rows' => $rowGroups,
            'blocks' => $blockGroups,
            'bindings' => $bindings,
        ];
    }

    protected function resolveFilename()
    {
        $result = $this->naming;
        preg_match_all('/\$\{(.*?)}/i', $this->naming, $matches);

        for($i=0; $i<count($matches[0]); $i++)
        {
            $result = str_replace($matches[0][$i], $this->__params['bindings'][$matches[1][$i]], $result);
        }

        return $result.'.docx';
    }

    public function resolveAgain()
    {
        $this->bindings = $this->getBindingsFromFile();
        $this->save();
    }

    protected function locateMacros($type, array $macros)
    {
        $rows = preg_grep("/{$type}__(.*)\.?(.*)/i", $macros);
        return array_values($rows);
    }

    protected function removeMacros(array $bindings, array $macros)
    {
        return array_values(array_filter($bindings, function($binding) use ($macros) {
            return !in_array($binding, $macros);
        }));
    }

    /**
     * Groups row macros
     *
     * @param array $macros
     * @return array
     */
    protected function groupRowMacros(array $macros)
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][] = $macro.'.'.$cell;
            } else {
                // Row macro has at least one element, the one initializing it.
                $groups[$macro][] = $macro;
            }
        }
        return $groups;
    }

    /**
     * Groups block macros
     *
     * @param array $macros
     * @return array
     */
    protected function groupBlockMacros(array $macros)
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            // Catch closing macro
            $macro = ltrim($macro, '/');

            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][] = $macro.'.'.$cell;
            } elseif(!isset($groups[$macro])) {
                // Block macro can be empty inside
                $groups[$macro] = [];
            }
        }
        return $groups;
    }

    /**
     * Populates the template's $type of bindings with $params as a source.
     *
     * @param $type
     * @param array $params
     * @return array
     */
    protected function getValues($type, array &$params)
    {
        $result = [];
        $typeNames = array_keys($this->bindings[$type]);
        $params = array_filter($params, function ($param, $key) use ($typeNames, &$result) {
            if (in_array($key, $typeNames)) {
                $result[$key] = $param;
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
        return $result;
    }

    public function getBindings($params)
    {
        if (self::isBindingsArray($params)) {
            $params = array_merge(
                $params['bindings'],
                $params['rows'],
                $params['blocks']
            );
        }

        $rows = [];
        foreach($this->getValues('rows', $params) as $row => $value) {
            $rows[$row] = json_decode($value, true);
        }

        $blocks = [];
        foreach($this->getValues('blocks', $params) as $block => $value) {
            $blocks[$block] = json_decode($value, true);
        }

        return [
            'bindings' => collect($params)
                ->only($this->bindings['bindings'])
                ->merge(['x-template-name' => $this->name])
                ->toArray(),
            'rows' => $rows,
            'blocks' => $blocks,
        ];
    }

    public static function isBindingsArray($params)
    {
        return array_key_exists('bindings', $params) &&
            array_key_exists('rows', $params) &&
            array_key_exists('blocks', $params);
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
