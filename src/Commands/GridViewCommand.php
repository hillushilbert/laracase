<?php

namespace Hillus\Laracase\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GridViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grid:view
                            {name : The name of the Crud.}
                            {--fields= : The field names for the form.}
                            {--view-path= : The name of the view path.}
                            {--route-group= : Prefix of the route group.}
                            {--pk=id : The name of the primary key.}
                            {--validations= : Validation rules for the fields.}
                            {--form-helper=grid : Helper for the form.}
                            {--custom-data= : Some additional values to use in the crud.}
                            {--grid-fields= : lista de campos da grid.}
                            {--buttons=N : adiciona os botoes a grid. }
                            {--group-fields= : lista de campos da quebra. }
                            {--use-tabs=no : Se o cadastro vai usar abas.}
                            {--tab-fields= : Quais são os campos por tabs. }                               
                            {--localize=no : Localize the view? yes|no.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the Grid.';

    /**
     * View Directory Path.
     *
     * @var string
     */
    protected $viewDirectoryPath;

    /**
     *  Form field types collection.
     *
     * @var array
     */
    protected $typeLookup = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'radio' => 'radio',
        'boolean' => 'radio',
        'enum' => 'select',
        'select' => 'select',
        'file' => 'file',
        'readonly' => 'readonly',
    ];

    /**
     * Variables that can be used in stubs
     *
     * @var array
     */
    protected $vars = [
        'formFields',
        'formFieldsHtml',
        'varName',
        'crudName',
        'crudNameCap',
        'crudNameSingular',
        'primaryKey',
        'modelName',
        'modelNameCap',
        'viewName',
        'routePrefix',
        'routePrefixCap',
        'routeGroup',
        'formHeadingHtml',
        'formModalJs',
        'formBodyHtml',
        'viewTemplateDir',
        'formBodyHtmlForShowView',
        'buttonsJsHeader',
        'buttonsJsonOption',
        'groupJsHeader',
        'datatableColumns',
        'datatableColumnsCount',
    ];

    /**
     * Form's fields.
     *
     * @var array
     */
    protected $formFields = [];

    /**
     * Html of Form's fields.
     *
     * @var string
     */
    protected $formFieldsHtml = '';

    /**
     * Number of columns to show from the table. Others are hidden.
     *
     * @var integer
     */
    protected $defaultColumnsToShow = 3;

    /**
     * Variable name with first letter in lowercase
     *
     * @var string
     */
    protected $varName = '';

    /**
     * Name of the Crud.
     *
     * @var string
     */
    protected $crudName = '';

    /**
     * Crud Name in capital form.
     *
     * @var string
     */
    protected $crudNameCap = '';

    /**
     * Crud Name in singular form.
     *
     * @var string
     */
    protected $crudNameSingular = '';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Name of the Model.
     *
     * @var string
     */
    protected $modelName = '';

    /**
     * Name of the Model with first letter in capital
     *
     * @var string
     */
    protected $modelNameCap = '';

    /**
     * Name of the View Dir.
     *
     * @var string
     */
    protected $viewName = '';

    /**
     * Prefix of the route
     *
     * @var string
     */
    protected $routePrefix = '';

    /**
     * Prefix of the route with first letter in capital letter
     *
     * @var string
     */
    protected $routePrefixCap = '';

    /**
     * Name or prefix of the Route Group.
     *
     * @var string
     */
    protected $routeGroup = '';

    /**
     * Html of the form heading.
     *
     * @var string
     */
    protected $formHeadingHtml = '';
    protected $formModalJs = '';
    protected $datatableColumns = '';
    protected $datatableColumnsCount = false;
    protected $fieldGroup = false;

    /**
     * Html of the form body.
     *
     * @var string
     */
    protected $formBodyHtml = '';

    /**
     * Html of view to show.
     *
     * @var string
     */
    protected $formBodyHtmlForShowView = '';

    /**
     * User defined values
     *
     * @var array
     */
    protected $customData = [];

    /**
     * Template directory where views are generated
     *
     * @var string
     */
    protected $viewTemplateDir = '';

    /**
     * Delimiter used for replacing values
     *
     * @var array
     */
    protected $delimiter;
	
    protected $gridFields;
    
    /**
     * Flag para exibicao dos botoes do DataTable
     * 
     * @var string
     */
    protected $buttons = 'N';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();

        if (config('laracase.view_columns_number')) {
            $this->defaultColumnsToShow = config('laracase.view_columns_number');
        }

        $this->delimiter = config('laracase.custom_delimiter')
            ? config('laracase.custom_delimiter')
            : ['%%', '%%'];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $formHelper = $this->option('form-helper');
		$this->viewDirectoryPath = config('laracase.custom_template')
            ? config('laracase.path') . 'views/' . $formHelper . '/'
            : __DIR__ . '/../stubs/views/' . $formHelper . '/';


        $this->crudName = strtolower($this->argument('name'));
        $this->varName = lcfirst($this->argument('name'));
        $this->crudNameCap = ucwords($this->crudName);
        $this->crudNameSingular = Str::singular($this->crudName);
        $this->modelName = Str::singular($this->argument('name'));
        $this->modelNameCap = ucfirst($this->modelName);
        $this->customData = $this->option('custom-data');
        $this->primaryKey = $this->option('pk');
        $this->routeGroup = ($this->option('route-group'))
            ? $this->option('route-group') . '/'
            : $this->option('route-group');
        $this->routePrefix = ($this->option('route-group')) ? $this->option('route-group') : '';
        $this->routePrefixCap = ucfirst($this->routePrefix);
        $this->viewName = Str::snake($this->argument('name'), '-');
        $this->buttons = $this->option('buttons');

        $this->useTabs = ($this->option('use-tabs')) ? trim($this->option('use-tabs'))  : 'no';
        $this->tabFields = ($this->option('tab-fields')) ? json_decode($this->option('tab-fields'))  : []; 


        $viewDirectory = config('view.paths')[0] . '/';
        if ($this->option('view-path')) {
            $this->userViewPath = $this->option('view-path');
            $path = $viewDirectory . $this->userViewPath . '/' . $this->viewName . '/';
        } else {
            $path = $viewDirectory . $this->viewName . '/';
        }

        $this->viewTemplateDir = isset($this->userViewPath)
            ? $this->userViewPath . '.' . $this->viewName
            : $this->viewName;
        
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $fields = $this->option('fields');
        $fieldsArray = explode(';', $fields);

        $this->formFields = [];

        $validations = $this->option('validations');
		if(!empty($this->option('grid-fields'))){
			$this->gridFields = explode('|',$this->option('grid-fields'));
        }
        
        if(!empty($this->option('group-fields'))){
            $this->fieldGroup = explode('|',$this->option('group-fields'));
        }

		$t = explode(';',$validations);
		$aValidations = [];
		foreach($t as $s){
			$parts = explode('#',$s);
			$aValidations[$parts[0]] = $parts[1];
		}
		
        if ($fields) {
            $x = 0;
            foreach ($fieldsArray as $item) {
                $itemArray = explode('#', $item);

                $this->formFields[$x]['name'] = trim($itemArray[0]);
                $this->formFields[$x]['type'] = trim($itemArray[1]);
                $this->formFields[$x]['pk'] = (trim($itemArray[0]) == $this->primaryKey) ? true : false;

			    //$this->formFields[$x]['required'] = preg_match('/' . $itemArray[0] . '/', $validations) ? true : false;
                if(isset($aValidations[$itemArray[0]]) && strpos($aValidations[$itemArray[0]],'required') !== false){
					$this->formFields[$x]['required'] = true;
				} else {
					$this->formFields[$x]['required'] = false;
				}
				
                if (($this->formFields[$x]['type'] === 'select'
                    || $this->formFields[$x]['type'] === 'enum'
                    || $this->formFields[$x]['type'] === 'radio')
                    && isset($itemArray[2])
                ) {
                    $options = trim($itemArray[2]);
                    $options = str_replace('options=', '', $options);

                    $this->formFields[$x]['options'] = $options;
                }
				$this->formFields[$x]['title'] = trim($itemArray[3]);
				$this->formFields[$x]['readonly'] = trim($itemArray[4]);
                
                $x++;
            }
        }

        // foreach ($this->formFields as $item) {
        //     $this->formFieldsHtml .= $this->createField($item);
        // }
        if($this->useTabs == 'yes'){
            $this->formFieldsHtml .= PHP_EOL.'<div class="nav-tabs-custom">';

            $this->formTabsHeader = [];
            $this->formTabFieldsHtml = [];
            foreach ($this->formFields as $item) {
                $field = $item['name'];
                $tab = intval($this->tabFields->$field);
                $class = ($tab == 0) ? 'active' : '';
                $this->formTabsHeader[$tab] = PHP_EOL.'        <li class="'.$class.'"><a href="#tab'.$tab.'" data-toggle="tab" aria-expanded="false">Tab'.$tab.'</a></li>';
     
                $this->formTabFieldsHtml[$tab][] =$this->createField($item);
            }
            $this->formFieldsHtml .= PHP_EOL.'    <ul class="nav nav-tabs">';
            $this->formFieldsHtml .= implode(PHP_EOL,$this->formTabsHeader);
            $this->formFieldsHtml .= PHP_EOL.'    </ul>';
            $this->formFieldsHtml .= PHP_EOL.'    <div class="tab-content">';
            foreach($this->formTabFieldsHtml as $tabId => $tabItens){
                $class = ($tabId == 0) ? ' active' : '';
                $this->formFieldsHtml .= PHP_EOL.'        <div class="tab-pane'.$class.'" id="tab'.$tabId.'">';
                $this->formFieldsHtml .= implode(PHP_EOL, $tabItens);
                $this->formFieldsHtml .= PHP_EOL.'        </div>
                <!-- /.tab-pane -->';   
            }
            $this->formFieldsHtml .= PHP_EOL.'    </div>';
            $this->formFieldsHtml .= '</div>';
            
        }
        else
        {
            foreach ($this->formFields as $item) {
                $this->formFieldsHtml .= $this->createField($item);
            }    
        }


        $i = 0;
        $optionButtons = '[';
        $this->datatableColumns = "columns: [";
		$this->datatableColumnsCount = !empty($this->gridFields) ?  count($this->gridFields) : 3;
        foreach ($this->formFields as $key => $value) {
            
			if(!empty($this->gridFields)){
				if(!in_array($value['name'],$this->gridFields))
				continue;
			} else if ($i == $this->defaultColumnsToShow) {
                break;
            }

            $field = $value['name'];
            $label = !empty($value['title'])
					 ? $value['title'] 
					 : ucwords(str_replace('_', ' ', $field));
            if ($this->option('localize') == 'yes') {
                //$label = '{{ trans(\'' . $this->crudName . '.' . $field . '\') }}';
            }

            $this->formHeadingHtml .= '<th>' . $label . '</th>'.PHP_EOL;
            $this->formBodyHtml .= '<td>{{ $item->' . $field . ' }}</td>';
            $this->datatableColumns .= " { data: '" . $field."'},";
            $this->formBodyHtmlForShowView .= '<tr><th> ' . $label . ' </th><td> {{ $%%crudNameSingular%%->' . $field . ' }} </td></tr>';

            $optionButtons .=$i.",";
            $i++;
        }
        $optionButtons = substr($optionButtons,0,-1)."]";
        $this->datatableColumns = substr($this->datatableColumns,0,-1)."]," . PHP_EOL;
		
		$this->formModalJs = "
	function resetForm(){ ".PHP_EOL;
	
	foreach ($this->formFields as $key => $value) {
			
            $field = $value['name'];
			if($value['name'] != $this->primaryKey) 
			$this->formModalJs .= "			\$('#".$field."').val('');".PHP_EOL;
	}
		
	$this->formModalJs .= "	
	}

	function loadForm(id){
		$.ajax({
			type:'GET',
			url:'/".$this->routeGroup.$this->viewName."/'+id,
			dataType: 'json',
			success:function(data){
				console.debug(data);";
	foreach ($this->formFields as $key => $value) {
			
            $field = $value['name'];
			if($value['name'] != $this->primaryKey) 
			$this->formModalJs .= "			\$('#".$field."').val(data.".$field.");".PHP_EOL;
	}
				
	$this->formModalJs .= "			
			},
			error:function(jqXHR , e){
				console.log(\"error\");
				var data = jqXHR.responseJSON;
				console.debug(data);
			}
		});		
	}

	$(\"#modal".$this->crudName."\").submit(function(e)
	{
		e.preventDefault();
		var _method = 'post';
		if($('#form_".$this->crudName."').attr('data-action') == 'create'){
			var url = '/".$this->routeGroup.$this->viewName."';
		}else if($('#form_".$this->crudName."').attr('data-action') == 'edit'){
			var url = '/".$this->routeGroup.$this->viewName."/'+$('#form_".$this->crudName."').attr('data-id');
			_method = 'PATCH';
		}
		
		$.ajax({
			type:'POST',
			url:url,
			dataType: 'json',
			data:{";
			
	    foreach ($this->formFields as $key => $value) {
                
                $field = $value['name'];
                if($value['name'] != $this->primaryKey) 
                $this->formModalJs .= "		".$field.": \$('#".$field."').val(),".PHP_EOL;
        }			

        $this->formModalJs .= "
                    _method : _method,
                    _token:'{{csrf_token()}}'
                },
			success:function(data){
				console.debug(data);
                table.ajax.reload();
				$('.modal').modal('hide');
			},
			error:function(jqXHR , e){
				console.log(\"error\");
				var data = jqXHR.responseJSON;
				console.debug(data);
				
				if(typeof data.errors === 'object')
				{";
		foreach ($this->formFields as $key => $value) {
			$field = $value['name'];
			$this->formModalJs .= "	
					if(data.errors.".$field." !== undefined){
						data.errors.".$field.".forEach(function(data, index) {
							//console.log(data);
							$('#feedback-".$field."').html(data);
							$('#".$field."').addClass('is-invalid');
						});
					}".PHP_EOL;
		}
	
		$this->formModalJs .= "						
				}else if(typeof data.message === 'string'){
					swal.fire('titulo',data.message,'error');
				}
			}
            });

            return false;
        });	
        
            ";

        if($this->fieldGroup){
            // adiciona ao html o complemento para agrupamentos
            $this->groupJsHeader = '            <script src="https://cdn.datatables.net/rowgroup/1.1.0/js/dataTables.rowGroup.min.js"></script>';
        
            // adiciona a lista de colunas ao json principal 
            $this->datatableColumns .= "			rowGroup: {
                dataSrc: ['".implode("','",$this->fieldGroup)."']
            },";
			
			// adiciona ordenacao 
			/*
			$this->datatableColumns .= "order: [";
			foreach($this->fieldGroup as $field){
				$this->datatableColumns .= "[".$field.", 'asc'],";
			}
			$this->datatableColumns = substr($this->datatableColumns,0,-1)."],".PHP_EOL;
			*/
        }else{
            $this->groupJsHeader = '';
        }    

        if($this->buttons == 'S'){
            $this->buttonsJsHeader = 
    '<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>';
        
            $this->buttonsJsonOption = 
            "dom: 'Blfrtip',
            buttons: [
                {
					text: '<i class=\"fa fa-plus\" aria-hidden=\"true\"></i> Adicionar',
					action: function ( e, dt, node, config ) {
						resetForm();
						// preenche os campos do form com os dados do registro
						$('#form_".$this->crudName."').attr('data-action','create');
						$(\"#modal".$this->crudName."\").modal();
					}
				},
                {
                    extend: 'csvHtml5',
                    'text': 'CSV',
                    exportOptions: {
                        columns: ".$optionButtons."
                    }
                },
                {
                    extend: 'excelHtml5',
                    'text': 'Excel',
                    exportOptions: {
                        columns: ".$optionButtons."
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    exportOptions: {
                        columns: ".$optionButtons."
                    }
                },
            ],";

        }else{
            $this->buttonsJsHeader = 
            '<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>';
            
            $this->buttonsJsonOption = 
            "dom: 'Blfrtip',
            buttons: [
                {
					text: '<i class=\"fa fa-plus\" aria-hidden=\"true\"></i> Adicionar',
					action: function ( e, dt, node, config ) {
						resetForm();
						// preenche os campos do form com os dados do registro
						$('#form_".$this->crudName."').attr('data-action','create');
						$(\"#modal".$this->crudName."\").modal();
					}
				}
            ],";
        }    
		
        $this->templateStubs($path);

        $this->info('View created successfully.');
    }

    /**
     * Default template configuration if not provided
     *
     * @return array
     */
    private function defaultTemplating()
    {
        return [
            'index' => ['formModalJs','formHeadingHtml', 'formBodyHtml', 'crudName', 'crudNameCap', 'modelName', 'viewName', 'routeGroup', 'primaryKey','viewTemplateDir','buttonsJsHeader','buttonsJsonOption','datatableColumns','groupJsHeader','datatableColumnsCount'],
            'form' => ['formFieldsHtml'],
            'create' => ['crudName', 'crudNameCap', 'modelName', 'modelNameCap', 'viewName', 'routeGroup', 'viewTemplateDir'],
            'edit' => ['crudName', 'crudNameSingular', 'crudNameCap', 'modelNameCap', 'modelName', 'viewName', 'routeGroup', 'primaryKey', 'viewTemplateDir'],
            'show' => ['formHeadingHtml', 'formBodyHtml', 'formBodyHtmlForShowView', 'crudName', 'crudNameSingular', 'crudNameCap', 'modelName', 'viewName', 'routeGroup', 'primaryKey'],
        ];
    }

    /**
     * Generate files from stub
     *
     * @param $path
     */
    protected function templateStubs($path)
    {
        // $dynamicViewTemplate = config('laracase.dynamic_view_template')
        //     ? config('laracase.dynamic_view_template')
        //     : $this->defaultTemplating();

        $dynamicViewTemplate = $this->defaultTemplating();

        //dd($dynamicViewTemplate);    
        foreach ($dynamicViewTemplate as $name => $vars) {
            $file = $this->viewDirectoryPath . $name . '.blade.stub';
            $newFile = $path . $name . '.blade.php';
            if (!File::copy($file, $newFile)) {
                echo "failed to copy $file...\n";
            } else {
                $this->templateVars($newFile, $vars);
                $this->userDefinedVars($newFile);
            }
        }
    }

    /**
     * Update specified values between delimiter with real values
     *
     * @param $file
     * @param $vars
     */
    protected function templateVars($file, $vars)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        foreach ($vars as $var) {
            $replace = $start . $var . $end;
            if (in_array($var, $this->vars)) {
                File::put($file, str_replace($replace, $this->$var, File::get($file)));
            }
        }
    }

    /**
     * Update custom values between delimiter with real values
     *
     * @param $file
     */
    protected function userDefinedVars($file)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        if ($this->customData !== null) {
            $customVars = explode(';', $this->customData);
            foreach ($customVars as $rawVar) {
                $arrayVar = explode('=', $rawVar);
                File::put($file, str_replace($start . $arrayVar[0] . $end, $arrayVar[1], File::get($file)));
            }
        }
    }

    /**
     * Form field wrapper.
     *
     * @param  string $item
     * @param  string $field
     *
     * @return string
     */
    protected function wrapField($item, $field)
    {
		$start = '';
		$end = '';
		if($item['pk'] == true){
			$start = "@if(\$formMode === 'edit')";
			$end = '@endif';			
		}
		
        $formGroup = File::get($this->viewDirectoryPath . 'form-fields/wrap-field.blade.stub');

        $labelText = "'" . ucwords(strtolower(str_replace('_', ' ', $item['name']))) . "'";
        $labelText = "'" . $item['title'] . "'";

        if ($this->option('localize') == 'yes') {
            $labelText = 'trans(\'' . $this->crudName . '.' . $item['name'] . '\')';
        }

        return sprintf($formGroup, $item['name'], $labelText, $field, $start, $end );
    }

    /**
     * Form field generator.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createField($item)
    {
        switch ($this->typeLookup[$item['type']]) {
            case 'password':
                return $this->createPasswordField($item);
            case 'datetime-local':
            case 'time':
                return $this->createInputField($item);
            case 'radio':
                return $this->createRadioField($item);
            case 'textarea':
                return $this->createTextareaField($item);
            case 'select':
            case 'enum':
                return $this->createSelectField($item);
			case 'readonly':
				return $this->createReadonlyField($item);
            default: // text
                return $this->createFormField($item);
        }
    }

    /**
     * Create a specific field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createFormField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';
        $readonly = ($item['readonly'] == 'S') ? 'readonly' : '';

        $markup = File::get($this->viewDirectoryPath . 'form-fields/form-field.blade.stub');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a password field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createPasswordField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';
		$readonly = ($item['readonly'] == 'S') ? 'readonly' : '';

        $markup = File::get($this->viewDirectoryPath . 'form-fields/password-field.blade.stub');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a generic input field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createInputField($item)
    {
		$start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';
		$readonly = ($item['readonly'] == 'S') ? 'readonly' : '';

        $markup = File::get($this->viewDirectoryPath . 'form-fields/input-field.blade.stub');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }
	
	protected function createReadonlyField($item){
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';
		$readonly = ($item['readonly'] == 'S') ? 'readonly' : '';
        
		$markup = File::get($this->viewDirectoryPath . 'form-fields/readonly-field.blade.stub');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );		
	}

    /**
     * Create a yes/no radio button group using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createRadioField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];
		if(empty($item['options'])){		
			$markup = File::get($this->viewDirectoryPath . 'form-fields/radio-field.blade.stub');
			$markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
			$markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);
		} else {
			$options = json_decode($item['options']);
			$markups = '<br>';
			//$markups = '<div class="form-radio">';
			$required = $item['required'] ? 'required' : '';
			$readonly = ($item['readonly'] == 'S') ? 'readonly' : '';
			
			$count = 1;
			foreach($options as $optionKey => $optionValue)
			{
				$markup = File::get($this->viewDirectoryPath . 'form-fields/radio-options-field.blade.stub');
				$markup = str_replace($start . 'required' . $end, $required, $markup);
				$markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
				$markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
				$markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
				$markup = str_replace($start . 'optionKey' . $end, $optionKey, $markup);
				$markup = str_replace($start . 'optionValue' . $end, $optionValue, $markup);
				$markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);
				$lastItem = '';
				if($count == count((array)$options)){
					$lastItem = '{!! $errors->first(\''.$item['name'].'\', \'<div class="invalid-feedback"> - :message</div>\') !!}';
				}
				$markup = str_replace($start . 'lastItem' . $end, $lastItem, $markup);
				
				$count++;
				$markups .= $markup;
			}
			$markups .= '';
			//dump($markups);
			//dd($item);
			
			return $this->wrapField(
				$item,
				$markups
			);			
			
		}
		
        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a textarea field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createTextareaField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';
		$readonly = ($item['readonly'] == 'S') ? 'readonly' : '';

        $markup = File::get($this->viewDirectoryPath . 'form-fields/textarea-field.blade.stub');
        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
        $markup = str_replace($start . 'fieldType' . $end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a select field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createSelectField($item)
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

		if(strpos($item['options'],'App\\') !== false){
			
			$partes = substr($item['options'], strpos($item['options'],'App\\'));
			//$partes = substr($partes,0,-3);
			$partes = str_replace('"',"",$partes);
			$partes = explode(':',$partes);
			
			$modelo = str_replace("\\\\","\\",$partes[0]);
			$variavel = "\$".strtolower(substr($modelo,4));
			$id = substr($partes[1],0,strpos($partes[1],'@'));
			$descricao = substr($partes[1],strpos($partes[1],'@@')+2);
			//dd($item['options']);
			$markup = File::get($this->viewDirectoryPath . 'form-fields/select2-field.blade.stub');
			$markup = str_replace($start . 'variavel' . $end, $variavel, $markup);
			$markup = str_replace($start . 'id' . $end, $id, $markup);
            $markup = str_replace($start . 'descricao' . $end, $descricao, $markup);
            //dd($markup);
        
      	} else {
			$markup = File::get($this->viewDirectoryPath . 'form-fields/select-field.blade.stub');
        }
        
		
        $required = $item['required'] ? 'required' : '';
		$readonly = ($item['readonly'] == 'S') ? 'readonly' : '';

        $markup = str_replace($start . 'required' . $end, $required, $markup);
        $markup = str_replace($start . 'readonly' . $end, $readonly, $markup);
        $markup = str_replace($start . 'options' . $end, $item['options'], $markup);
        $markup = str_replace($start . 'itemName' . $end, $item['name'], $markup);
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);
        
        return $this->wrapField(
            $item,
            $markup
        );
    }
}
