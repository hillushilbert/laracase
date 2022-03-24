<?php

namespace Hillus\Laracase\Http\Helpers;

use Illuminate\Support\Facades\DB;

class TableMetaCrud 
{

    protected $table;
    protected $fields;
    protected $labels;
    protected $required;
    protected $validation;
    protected $gridFields;
    protected $options;
    protected $readonly;
    protected $types;

    protected $pk;
    protected $timestamps;
    protected $filename;

    protected $colunas;

    public function __construct($table,$fields, $labels, $required, $validation, $gridFields , $options , $readonly, $types){
        $this->table = $table;
        $this->fields = $fields;
        $this->labels = $labels;
        $this->required = $required;
        $this->validation = $validation;
        $this->gridFields = $gridFields;
        $this->options = $options;
        $this->readonly = $readonly;
        $this->types = $types;

        $this->colunas = DB::select('SHOW COLUMNS FROM '.$table);

    }

    public function getPk(){
        return $this->pk;
    }

    public function getTimestamps(){
        return $this->timestamps;
    }    

    public function getFilename(){
        return $this->filename;
    }

    public function getRelationships(){
        // Relacionamentos
        // relationshipname#relationshiptype#args_separated_by_pipes
        // e.g. employees#hasMany#App\Employee|id|dept_id
        // user is responsible for ensuring these relationships are valid
        $relationships = [];
        $models = [];
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
        $rels = DB::select($sSQL,[env('DB_DATABASE'),$this->table]);
        foreach($rels as $r)
        {
            if(strpos($r->TABLE_NAME,'_x_') !== false) continue;
            $relationshipname = str_replace($this->table,'',$r->TABLE_NAME);
            $relationshipname = str_replace('sao','soe',$relationshipname).'s';
            $relationshipname = str_replace('_','',$relationshipname);
            //relationshiptype 
            $relationshiptype = 'hasMany';
            $args_separated_by_pipes = '';
            $m = str_replace('tb_','',$r->TABLE_NAME);
            $m = str_replace('_','',$m);
            $m = 'App\\'.ucfirst($m);
            $args_separated_by_pipes = $m.'|'.$r->COLUMN_NAME."|".$r->REFERENCED_COLUMN_NAME;
            $relationships[] = $relationshipname.'#'.$relationshiptype.'#'.$args_separated_by_pipes;
            // tenta criar os modelos
            //$models[] = \App\Http\Helpers\Modelo::cmd($r->TABLE_NAME);
        }
        return $relationships;
    }

    public function parser(){
        
        $ret = new \stdClass;
        $ret->fields = [];
        $ret->pk = [];
        $ret->validations = [];
        $ret->locales = 'pt-BR';
        $this->pk = '';
        $this->timestamps = 'no';
        foreach($this->colunas as $col ){
            
            if($col->Field == 'updated_at') {
                $this->timestamps = 'yes';
            }
            
            
            // caso nao esteja selecionado, ignora campo
            if(!in_array($col->Field,$this->fields)) continue;
            $f = new \stdClass;
            $f->name  = $col->Field;
            $f->type  = !empty($this->types[$col->Field])  
                        ? $this->types[$col->Field] 
                        : \App\Http\Helpers\View::deparaType($col->Type);
            
            $f->title = !empty($this->labels[$col->Field]) ? $this->labels[$col->Field] : $col->Field;
            //$f->readonly = ($readonly[$col->Field] == 'S') ? 'S' : '';
            $f->readonly = isset($this->readonly[$col->Field]) ? 'S' : '';
            
            $rules = $this->rules($col, $f);
            

            if($f->type == 'Sim/Não'){
                $f->type = 'select';
                $f->options = [''=>'','S'=>'Sim', 'N'=>'Não'];
            } else if($f->type == 'Ativo/Inativo'){
                $f->type = 'radio';
                $f->options = ['A'=>'Ativo', 'I'=>'Inativo']; 
            } else if($f->type == 'Sexo'){
                $f->type = 'select';
                $f->options = [''=>'','M'=>'Masculino', 'F'=>'Feminino'];
            } else if($f->type == 'Estados'){
                $f->type = 'select';
                //$f->options = \App\Http\Helpers\Estados::getArray();
            } else if($f->type == 'select'){
                $f->type = 'select';
                if(strpos($this->options[$col->Field],'App\\') !== false){
                    $f->options = $this->options[$col->Field];
                }else{
                    $f->options = \App\Http\Helpers\View::getOptions($this->options[$col->Field]);
                }
            }  
            
            $ret->fields[] = $f;
            if($col->Key == 'PRI'){
                $ret->pk[] = $col->Field;
                $this->pk = $col->Field;
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
        $this->filename = storage_path().'/../crud_json/'.$this->table.'.json';
        $handle = fopen($this->filename,'w+');
        fputs($handle,json_encode($ret , JSON_PRETTY_PRINT));
        fclose($handle);        
        
        return true;
    }

    /**
     * rules
     * 
     * @param $table
     * @param $table
     * 
     * 
     */
	public function rules($col, $f){


        $rules = [];
				
        // caso nao esteja selecionado, ignora campo        
        if($f->type == 'Sim/Não'){
            $rules[] = "\Illuminate\Validation\Rule::in(['S', 'N'])";		
        } else if($f->type == 'Ativo/Inativo'){
            $rules[] = "\Illuminate\Validation\Rule::in(['I', 'A'])";
        } else if($f->type == 'Sexo'){
            $rules[] = "\Illuminate\Validation\Rule::in(['M', 'F'])";					
        }  
        
        if($col->Null == 'NO' || (isset($this->required[$col->Field]) && $this->required[$col->Field] == 'S')){
            $rules[] = 'required';	
        }
        
        // validation unique 
        if($col->Key == 'UNI'){
            $rules[] = 'unique:'.$this->table.",".$col->Field.",'.\$request['".$this->pk."'].',".$this->pk;
        }				
            
        // Validacoes para campo string
        if(in_array($f->type,['text','string'])){
            /*{"field": "nome","rules": "required|max:100"},*/
            $size = preg_replace("/[^0-9]/",'',$col->Type);					
            $rules[] = "max:".$size; 
        }
        
        if(in_array($f->type,['date','timestamp'])){
            $rules[] = "date"; 
        }
        
        if(!empty($this->validation[$col->Field]) && in_array('CPF',$this->validation[$col->Field])){
            $rules[] = "new \App\Rules\CPF"; 
        }
        
        if(!empty($this->validation[$col->Field]) && in_array('CNPJ',$this->validation[$col->Field])){
            $rules[] = "new \App\Rules\CNPJ"; 
        }        

        return $rules;
    }

}