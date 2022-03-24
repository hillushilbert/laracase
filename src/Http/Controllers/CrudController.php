<?php

namespace Hillus\Laracase\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\TableMetaCrud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Hillus\Laracase\Http\Helpers\View;

class CrudController extends Controller
{   
	private $pagesize = 5;
	
	public function index(Request $request)
	{
		$table =$request->input('tabela',false);
		$tables = DB::select('SHOW TABLES');
		$column = 'Tables_in_'.env('DB_DATABASE');
		$ajax = '';
		if(!empty(session('tabela'))){
			$ajax = View::grid(session('tabela'),'form');
		}
		
		return view('laracase::crud.new', ['tabelas'=>$tables, 'column'=>$column, 'ajax'=>$ajax]);
	}
	
	public function api(Request $request){
		$table =$request->input('tabela',false);
		$tables = DB::select('SHOW TABLES');
		$column = 'Tables_in_'.env('DB_DATABASE');
		$ajax = '';
		if(!empty(session('tabela'))){
			$ajax = View::grid(session('tabela'),'api');
		}
		
		return view('laracase::crud.api', ['tabelas'=>$tables, 'column'=>$column, 'ajax'=>$ajax]);
	}	
	
	
	public function grid(Request $request){
		$table =$request->input('tabela',false);
		$tables = DB::select('SHOW TABLES');
		$column = 'Tables_in_'.env('DB_DATABASE');
		$ajax = '';
		if(!empty(session('tabela'))){
			$ajax = View::grid(session('tabela'),'grid');
		}
		
		return view('laracase::crud.grid.index', ['tabelas'=>$tables, 'column'=>$column, 'ajax'=>$ajax]);
	}	
		
	
    /** Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function ajax(Request $request, $tabela)
    {
		$return = '';
		try
		{
			$modulo = $request->input('modulo',false);
			$return = View::grid($tabela,$modulo);
		}catch(\Exception $e){
			$return = $e->getMessage();
		}
		return ($return);
		//return response($return);
    }	

	/** Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storageApi(Request $request)
    {
		$table =$request->input('tabela',false);
		
		try
		{
			
			$model =ucwords(strtolower($request->input('model',false)));
			$group =strtolower($request->input('group','admin'));
			$auth =strtolower($request->input('auth','no'));
			
			$controllerFile = dirname(__FILE__).'/'.ucwords($group).'/'.ucwords($model).'Controller.php';
			if(is_file($controllerFile)) unlink($controllerFile);
			if(substr($model,strlen($model)-2,1) == 's'){
				$modelFile = app_path().'/'.ucwords(substr($model,0,-1)).'.php';
			}else{
				$modelFile = app_path().'/'.ucwords($model).'.php';
			}
			if(is_file($modelFile)) unlink($modelFile);
			
			$types =$request->input('type',false);
			$fields =$request->input('field',[]);
			$labels =$request->input('label',false);
			$validation = $request->input('validation',false);
			$gridFields = $request->input('gridFields',false);
			$options = $request->input('option',false);
			
			$required =$request->input('required',false);
			$readonly =$request->input('readonly',false);
			
			$requestData = $request->all();
			$colunas = DB::select('SHOW COLUMNS FROM '.$table);
			
			$ret = new \stdClass;
			$ret->fields = [];
			$ret->pk = [];
			$ret->validations = [];
			$ret->locales = 'pt-BR';
			$pk = '';
			$timestamps = 'no';
			foreach($colunas as $col ){
				
				if($col->Field == 'updated_at') {
					$timestamps = 'yes';
				}
				
				$rules = Hillus\Laracase\Http\Helpers\Rules::run($table, $col->Field, $types, $required, $validation);
				
				// caso nao esteja selecionado, ignora campo
				if(!in_array($col->Field,$fields)) continue;
				$f = new \stdClass;
				$f->name  = $col->Field;
				$f->type  = !empty($types[$col->Field])  ? $types[$col->Field] : View::deparaType($col->Type);
				$f->title = !empty($labels[$col->Field]) ? $labels[$col->Field] : $col->Field;
				$f->readonly = '';
				
				$ret->fields[] = $f;
				if($col->Key == 'PRI'){
					$ret->pk[] = $col->Field;
					$pk = $col->Field;
				}
				
				if(!empty($rules)){
					$r = new \stdClass;
					$r->field = $col->Field;
					$r->rules = implode('|',$rules);
					$ret->validations[] = $r;
				}
			}
			
			echo '<pre>';
			echo json_encode($ret , JSON_PRETTY_PRINT);
			$filename = storage_path().'/../crud_json/'.$table.'.api.json';
			$handle = fopen($filename,'w+');
			fputs($handle,json_encode($ret , JSON_PRETTY_PRINT));
			fclose($handle);
			$params = [
				'name'=>$model, 
				'--pk'=>$pk,
				'--fields_from_file'=>$filename,
				'--controller-namespace'=>ucwords($group),
				'--table'=>$table,
				'--timestamps'=>$timestamps,				
				'--route-group'=>$group, 
			];
			
			// colunas especificas 
			if(!empty($gridFields)){
				$params['--grid-fields'] = implode('|',$gridFields);
			}
			
			$cmd='crud:api';
			dump($cmd);
			dump($params);
			\Artisan::call($cmd,$params);	
			

		/*
		crud:api
		{name : The name of the Crud.}
		{--fields= : Field names for the form & migration.}
		{--fields_from_file= : Fields from a json file.}
		{--validations= : Validation rules for the fields.}
		{--controller-namespace= : Namespace of the controller.}
		{--model-namespace= : Namespace of the model inside "app" dir.}
		{--pk=id : The name of the primary key.}
		{--pagination=25 : The amount of models per page for index pages.}
		{--indexes= : The fields to add an index to.}
		{--foreign-keys= : The foreign keys for the table.}
		{--relationships= : The relationships for the model.}
		{--route=yes : Include Crud route to routes.php? yes|no.}
		{--route-group= : Prefix of the route group.}
		{--soft-deletes=no : Include soft deletes fields.}
		*/
			/*
			$this->call('crud:api', ['name' => $controllerNamespace . $name . 'Controller', '--crud-name' => $name, '--model-name' => $modelName, '--model-namespace' => $modelNamespace, '--view-path' => $viewPath, '--route-group' => $routeGroup, '--pagination' => $perPage, '--fields' => $fields, '--validations' => $validations, '--auth' => $auth]);
			$this->call('crud:model', ['name' => $modelNamespace . $modelName, '--fillable' => $fillable, '--table' => $tableName, '--pk' => $primaryKey, '--relationships' => $relationships, '--soft-deletes' => $softDeletes,'--timestamps'=>$timestamps]);
			*/
			
			//DB::rollback();
			$debug = ob_get_contents();
			//dd($request->all());
			return redirect()
					->back()
					->with('tabela' , $table)
					->with('debug' , $debug)
					->withInput();			
			
		}catch(\Exception $e){
			//return redirect('ligacoes/filter')->with('error', 'erro ao inserir: '.$e->getMessage());
			
			$debug = ob_get_contents();
			return redirect()
					->back()
					->with('tabela' , $table)
					->with('debug' , $debug)
					->withInput()
					->withErrors([$e->getMessage(), $e->getFile(), $e->getLine()]);
		}
    }
	
	
    /** Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storage(Request $request)
    {
		$table =$request->input('tabela',false);
		
		try
		{
			
			$model =ucwords(strtolower($request->input('model',false)));
			$group =strtolower($request->input('group','admin'));
			$auth =strtolower($request->input('auth','no'));
			$tabs = strtolower($request->input('tabs','no'));
			$migration = strtolower($request->input('migration','no'));
			$tabFields =  json_encode($request->input('tab_fields',[]));
			
			$controllerFile = dirname(__FILE__).'/'.ucwords($group).'/'.ucwords($model).'Controller.php';
			if(is_file($controllerFile)) unlink($controllerFile);
			$modelFile = dirname(__FILE__).'/../../'.ucwords(substr($model,0,-1)).'.php';
			if(is_file($modelFile)) unlink($modelFile);
			
			$types =$request->input('type',false);
			$fields =$request->input('field',[]);
			$labels =$request->input('label',false);
			$validation = $request->input('validation',false);
			$gridFields = $request->input('gridFields',false);
			$searchFields = $request->input('searchFields',false);
			$options = $request->input('option',false);
			
			$required =$request->input('required',false);
			$readonly =$request->input('readonly',false);
			
			$meta = new TableMetaCrud($table,$fields, $labels, $required, $validation, 
									  $gridFields , $options , $readonly, $types, $searchFields);
			$meta->parser();
			
			/*
			// Relacionamentos
			// relationshipname#relationshiptype#args_separated_by_pipes
            // e.g. employees#hasMany#App\Employee|id|dept_id
            // user is responsible for ensuring these relationships are valid
			$relationships = [];
			$models = [];
			$models[] = Hillus\Laracase\Http\Helpers\Modelo::ref($table);
			$sSQL = "SELECT 
					  TABLE_NAME,
					  COLUMN_NAME,
					  CONSTRAINT_NAME, 
					  REFERENCED_TABLE_NAME,
					  REFERENCED_COLUMN_NAME
					FROM
					  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					WHERE
					  REFERENCED_TABLE_SCHEMA = ? AND 
					  REFERENCED_TABLE_NAME = ?";
			$rels = DB::select($sSQL,[env('DB_DATABASE'),$table]);
			foreach($rels as $r)
			{
				if(strpos($r->TABLE_NAME,'_x_') !== false) continue;
				$relationshipname = str_replace($table,'',$r->TABLE_NAME);
				$relationshipname = str_replace('sao','soe',$relationshipname).'s';
				$relationshipname = str_replace('_','',$relationshipname);
				//relationshiptype 
				$relationshiptype = 'hasMany';
				$args_separated_by_pipes = '';
				$m = str_replace('tbl','',$r->TABLE_NAME);
				$m = str_replace('_','',$m);
				$m = 'App\\'.ucfirst($m);
				$args_separated_by_pipes = $m.'|'.$r->COLUMN_NAME."|".$r->REFERENCED_COLUMN_NAME;
				$relationships[] = $relationshipname.'#'.$relationshiptype.'#'.$args_separated_by_pipes;
				// tenta criar os modelos
				$models[] = Hillus\Laracase\Http\Helpers\Modelo::cmd($r->TABLE_NAME);
			}
			*/

			$params = [
				'name'=>$model, 
				'--table'=>$table, 
				'--pk'=>$meta->getPk(),
				'--fields_from_file'=>$meta->getFilename(),
				'--view-path'=>$group, 
				'--controller-namespace'=>ucwords($group), 
				'--route-group'=>$group, 
				'--form-helper'=>'html',
				'--timestamps'=>$meta->getTimestamps(),
				'--auth'=>$auth,
				'--use-tabs'=>$tabs,
				'--tab-fields'=>$tabFields,
				'--migration'=>$migration,
			];
			
			// adiciona os relacionamentos
			if(!empty($relationships)){
				$params['--relationships'] = implode(';',$meta->getRelationships());
			}
			
			// colunas especificas 
			if(!empty($gridFields)){
				$params['--grid-fields'] = implode('|',$gridFields);
			}

			// colunas especificas 
			if(!empty($searchFields)){
				$params['--search-fields'] = implode('|',$searchFields);
			}			
			
			$cmd='crud:generate';
			dump($cmd);
			dump($params);
			\Artisan::call($cmd,$params);
			
			//DB::rollback();
			$debug = ob_get_contents();
			//dd($request->all());
			return redirect()
					->back()
					->with('tabela' , $table)
					->with('debug' , $debug)
					->withInput();			
			
		}catch(\Exception $e){
			//return redirect('ligacoes/filter')->with('error', 'erro ao inserir: '.$e->getMessage());
			dump([$e->getMessage(), $e->getFile(), $e->getLine()]);
			dump( $e->getTrace()[0]);
			dd( $e->getTrace()[1]);
			$debug = ob_get_contents();
			return redirect()
					->back()
					->with('tabela' , $table)
					->with('debug' , $debug)
					->withInput()
					->withErrors([$e->getMessage(), $e->getFile(), $e->getLine()]);
		}
    }

	public function storageGrid(Request $request){
		
		$table =$request->input('tabela',false);
		
		try
		{
			$model =ucwords(strtolower($request->input('model',false)));
			$group =strtolower($request->input('group','admin'));
			$auth =strtolower($request->input('auth','no'));
			$buttons = strtoupper($request->input('button','N'));
			$tabs = strtolower($request->input('tabs','no'));
			$tabFields =  json_encode($request->input('tab_fields',[]));

			$controllerFile = dirname(__FILE__).'/'.ucwords($group).'/'.ucwords($model).'Controller.php';
			if(is_file($controllerFile)) unlink($controllerFile);
			$modelFile = dirname(__FILE__).'/../../'.ucwords(substr($model,0,-1)).'.php';
			if(is_file($modelFile)) unlink($modelFile);
			
			$types =$request->input('type',false);
			$fields = $request->input('field',[]);
			$groups = $request->input('groups',[]);
			$labels =$request->input('label',false);
			$validation = $request->input('validation',false);
			$gridFields = $request->input('gridFields',false);
			$options = $request->input('option',false);
			
			$required =$request->input('required',false);
			$readonly =$request->input('readonly',false);
			
			
			$meta = new TableMetaCrud($table,$fields, $labels, $required, $validation, $gridFields , $options , 
									  $readonly, $types);
			$meta->parser();
			
			/*
			// Relacionamentos
			// relationshipname#relationshiptype#args_separated_by_pipes
            // e.g. employees#hasMany#App\Employee|id|dept_id
            // user is responsible for ensuring these relationships are valid
			$relationships = [];
			$models = [];
			$models[] = Hillus\Laracase\Http\Helpers\Modelo::cmd($table);
			
			$sSQL = "SELECT 
					  TABLE_NAME,
					  COLUMN_NAME,
					  CONSTRAINT_NAME, 
					  REFERENCED_TABLE_NAME,
					  REFERENCED_COLUMN_NAME
					FROM
					  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					WHERE
					  REFERENCED_TABLE_SCHEMA = ? AND 
					  REFERENCED_TABLE_NAME = ?";
			$rels = DB::select($sSQL,[env('DB_DATABASE'),$table]);
			foreach($rels as $r)
			{
				if(strpos($r->TABLE_NAME,'_x_') !== false) continue;
				$relationshipname = str_replace($table,'',$r->TABLE_NAME);
				$relationshipname = str_replace('sao','soe',$relationshipname).'s';
				$relationshipname = str_replace('_','',$relationshipname);
				//relationshiptype 
				$relationshiptype = 'hasMany';
				$args_separated_by_pipes = '';
				$m = str_replace('tbl','',$r->TABLE_NAME);
				$m = str_replace('_','',$m);
				$m = 'App\\'.ucfirst($m);
				$args_separated_by_pipes = $m.'|'.$r->COLUMN_NAME."|".$r->REFERENCED_COLUMN_NAME;
				$relationships[] = $relationshipname.'#'.$relationshiptype.'#'.$args_separated_by_pipes;
				// tenta criar os modelos
				$models[] = Hillus\Laracase\Http\Helpers\Modelo::cmd($r->TABLE_NAME);
			}

			*/

			
			$params = [
				'name'=>$model, 
				'--table'=>$table, 
				'--pk'=>$meta->getPk(),
				'--fields_from_file'=>$meta->getFilename(),
				'--view-path'=>$group, 
				'--controller-namespace'=>ucwords($group), 
				'--route-group'=>$group, 
				'--form-helper'=>'grid',
				'--timestamps'=>$meta->getTimestamps(),
				'--auth'=>$auth,
				'--buttons'=>$buttons,
				'--use-tabs'=>$tabs,
				'--tab-fields'=>$tabFields,				
				//'--group-fields'=>implode('|',$groups),
			];

			
			// adiciona os relacionamentos
			if(!empty($relationships)){
				$params['--relationships'] = implode(';',$meta->getRelationships());
			}
			
			// colunas especificas 
			if(!empty($gridFields)){
				$params['--grid-fields'] = implode('|',$gridFields);
			}
			
			$cmd='grid:generate';
			dump($cmd);
			dump($params);
			\Artisan::call($cmd,$params);
			
			//DB::rollback();
			$debug = ob_get_contents();
			//dd($request->all());
			return redirect()
					->back()
					->with('tabela' , $table)
					->with('debug' , $debug)
					->withInput();			
			
		}catch(\Exception $e){
			//return redirect('ligacoes/filter')->with('error', 'erro ao inserir: '.$e->getMessage());
			
			$debug = ob_get_contents();
			dump($e->getMessage());
			dump($e->getLine());
			dump($e->getFile());
			dump($e->getTrace()[1]);
			dd("paradinha!!!!!");
			//dd($debug);
			return redirect()
					->back()
					->with('tabela' , $table)
					->with('debug' , $debug)
					->withInput()
					->withErrors([$e->getMessage()]);
		}		
	}
	
	
}