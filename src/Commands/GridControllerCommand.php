<?php

namespace Hillus\Laracase\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class GridControllerCommand extends AbstractControllerCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grid:controller
                            {name : The name of the controler grid.}
                            {--crud-name= : The name of the Crud.}
                            {--model-name= : The name of the Model.}
                            {--model-namespace= : The namespace of the Model.}
                            {--controller-namespace= : Namespace of the controller.}
                            {--view-path= : The name of the view path.}
                            {--fields= : Field names for the form & migration.}
                            {--validations= : Validation rules for the fields.}
                            {--route-group= : Prefix of the route group.}
                            {--pagination=25 : The amount of models per page for index pages.}
                            {--auth=no : The amount of models per page for index pages.}
                            {--primary-key=no : nome da chave primaria da tabela.}
                            {--force : Overwrite already existing controller.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller grid.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudgenerator.custom_template')
        ? config('crudgenerator.path') . '/controllerGrid.stub'
        : __DIR__ . '/../stubs/controllerGrid.stub';
    }


    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $viewPath = $this->option('view-path') ? $this->option('view-path') . '.' : '';
        $crudName = strtolower($this->option('crud-name'));
        $crudNameSingular = Str::singular($crudName);
        $modelName = $this->option('model-name');
        $modelNamespace = $this->option('model-namespace');
        $routeGroup = ($this->option('route-group')) ? $this->option('route-group') . '/' : '';
        $routePrefix = ($this->option('route-group')) ? $this->option('route-group') : '';
        $routePrefixCap = ucfirst($routePrefix);
        $perPage = intval($this->option('pagination'));
        $viewName = Str::snake($this->option('crud-name'), '-');
        $fields = $this->option('fields');
        $validations = rtrim($this->option('validations'), ';');
        $auth = rtrim($this->option('auth'), ';');
		$primaryKey = trim($this->option('primary-key'));

        $validationRules = '';
        if (trim($validations) != '') {
            $validationRules = "\$this->validate(\$request, [";

            $rules = explode(';', $validations);
            foreach ($rules as $v) {
                if (trim($v) == '') {
                    continue;
                }

                // extract field name and args
                $parts = explode('#', $v);
                $fieldName = trim($parts[0]);
                $rules = trim($parts[1]);
				if(strpos($rules,'|') === false){
					$validationRules .= "\n\t\t\t'$fieldName' => '$rules',";
				}else{
					$rs = explode('|',$rules);
					$validationRules .= "\n\t\t\t'$fieldName' => [\n\t\t\t\t";
					foreach($rs as $idx => $r){
						if(strpos($r,'new ') !== false || strpos($r,'Rule::') !== false){
							$validationRules .= $r;
						}else{
							$validationRules .= "'$r'";							
						}
						
						if($idx < (count($rs) -1)){
							$validationRules .= ",\n\t\t\t\t";	
						}
					}
					$validationRules .= "\n\t\t\t],";
				}
            }

            $validationRules = substr($validationRules, 0, -1); // lose the last comma
            $validationRules .= "\n\t\t]);";
        }
		
        if (\App::VERSION() < '5.3') {
            $snippet = <<<EOD
        if (\$request->hasFile('{{fieldName}}')) {
            \$file = \$request->file('{{fieldName}}');
            \$fileName = str_random(40) . '.' . \$file->getClientOriginalExtension();
            \$destinationPath = storage_path('/app/public/uploads');
            \$file->move(\$destinationPath, \$fileName);
            \$requestData['{{fieldName}}'] = 'uploads/' . \$fileName;
        }
EOD;
        } else {
            $snippet = <<<EOD
        if (\$request->hasFile('{{fieldName}}')) {
            \$requestData['{{fieldName}}'] = \$request->file('{{fieldName}}')
                ->store('uploads', 'public');
        }
EOD;
        }


        $fieldsArray = explode(';', $fields);
        $fileSnippet = '';
        $whereSnippet = '';
		$gridFields = [];
        $createAt = '';
        $updateAt = '';

        if ($fields) {
            $x = 0;
            foreach ($fieldsArray as $index => $item) {
                $itemArray = explode('#', $item);

                if (trim($itemArray[1]) == 'file') {
                    $fileSnippet .= str_replace('{{fieldName}}', trim($itemArray[0]), $snippet) . "\n";
                }

                $fieldName = trim($itemArray[0]);
				$gridFields[] = $fieldName;

                $whereSnippet .= ($index == 0) ? "where('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n                " : "->orWhere('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n                ";
            
                if(strtolower($fieldName) == 'created_at'){
                    $createAt = "\$requestData['created_at'] = \Carbon\Carbon::now();\n ";
                }

                if(strtolower($fieldName) == 'updated_at'){
                    $updateAt = "\$requestData['updated_at'] = \Carbon\Carbon::now();\n ";
                }
            }

            //$whereSnippet .= "->";
			
			
        }
		
		$gridFields = "'".implode("','",$gridFields )."',";

        $compactLookups = '';
		$lookups = "\n";
        if ($fields) {
            $variaveis = [];
			$fieldsArray = explode(';', $fields);
			foreach ($fieldsArray as $index => $item) {
                $itemArray = explode('#', $item);
				// tem select com model 
				if (trim($itemArray[1]) == 'select' && strpos($itemArray[2],'App\\') !== false ) 
				{
                    $partes = substr($itemArray[2], strpos($itemArray[2],'App\\'));
					//$partes = substr($partes,0,-3);
					$partes = str_replace('"',"",$partes);
					$partes = explode(':',$partes);
					
					$modelo = str_replace("\\\\","\\",$partes[0]);
					$variavel = "\$".strtolower(substr($modelo,4));
					$variaveis[] = strtolower(substr($modelo,4));
					$id = substr($partes[1],0,strpos($partes[1],'@'));
					$descricao = substr($partes[1],strpos($partes[1],'@@')+2,-2);
					$metaData = new \stdClass;
					$metaData->modelo = $modelo;
					$metaData->id = $id;
					$metaData->descricao = $descricao;
					//dd($metaData );
					$lookups .= "\t\t".$variavel . " = \\".$modelo."::get(); \n";
                }
                
            }
			if(!empty($variaveis)){
				$compactLookups = ",'".implode("','",$variaveis)."'";
			}
            $lookups .= "\n";
        }	

        

        return $this->replaceNamespace($stub, $name)
            ->replaceViewPath($stub, $viewPath)
            ->replaceViewName($stub, $viewName)
            ->replaceCrudName($stub, $crudName)
            ->replaceCrudNameSingular($stub, $crudNameSingular)
            ->replaceModelName($stub, $modelName)
            ->replaceModelNamespace($stub, $modelNamespace)
            ->replaceModelNamespaceSegments($stub, $modelNamespace)
            ->replaceRouteGroup($stub, $routeGroup)
            ->replaceRoutePrefix($stub, $routePrefix)
            ->replaceRoutePrefixCap($stub, $routePrefixCap)
            ->replaceValidationRules($stub, $validationRules)
			->replaceAuthMiddleare($stub, $auth)
            ->replacePaginationNumber($stub, $perPage)
            ->replaceFileSnippet($stub, $fileSnippet)
            ->replaceWhereSnippet($stub, $whereSnippet)
            ->replaceGridFields($stub, $gridFields)
            ->replacePrimaryKey($stub, $primaryKey)
            ->replaceLookups($stub, $lookups)
            ->replaceCompactLookups($stub, $compactLookups)
            ->replaceCreateAt($stub, $createAt)
            ->replaceUpdateAt($stub, $updateAt)
            ->replaceClass($stub, $name);
    }

    

}
