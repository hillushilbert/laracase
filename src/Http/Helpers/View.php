<?php

namespace Hillus\Laracase\Http\Helpers;
use Illuminate\Support\Facades\DB;

class View 
{

	public static function grid($tabela, $modulo){
		$return = '';
		try
		{
			$types = ['string','char','varchar','date','datetime','email','file','time','timestamp',
					  'text','mediumtext','longtext','json','jsonb','binary','integer',
					  'bigint','mediumint','tinyint','smallint','boolean','decimal',
					  'double','float','enum','select','radio','checkbox','Sim/Não',
					  'Ativo/Inativo','Sexo','Estados','readonly'];
					  
			$return = $return .= view('crud.'.$modulo.'.header_grid', [])->render();
			$colunas = DB::select('SHOW COLUMNS FROM '.$tabela);
			
			foreach($colunas as $idx=>$col){
				$params= [];
				$params['types'] = $types; 
				$params['field'] = $col->Field; 
				$params['label'] = $col->Field;
				$params['label'] = str_replace('id_','',$params['label']);
				$params['label'] = ucwords(str_replace('_',' ',$params['label']));
				if(strpos($params['label'],'Flag') !== false){
					$params['label'] = trim(str_replace('Flag','',$params['label'])) . ' ?';
				}
				$params['type']  = View::deparaType($col->Type);
				
				if(strpos($col->Field,'email') !== false && $params['type'] == 'string'){
					$params['type']  = 'email';
				}
				
				$params['required']  = ($col->Null == 'NO') ? 'S': 'N';
				$params['readonly']  = 'N';
				$params['gridFields']  = [];
				$params['searchFields']  = [];
				$return .= view('crud.'.$modulo.'.linha_grid', $params)->render();			
			}
	
		}catch(\Exception $e){
			$return = $e->getMessage();
		}
		return response($return);		
	}
	
	public static function deparaType($sType){
		
		if(strpos($sType,'int') !== false){
			return 'integer';
		} else if(strpos($sType,'char') !== false){
			$size = preg_replace("/[^0-9]/",'',$sType);
			if($size <= 200){
				return 'string';
			}else{
				return 'string';
			}
		} else if(strpos($sType,'decimal') !== false) {
			return 'decimal';
		} else if(strpos($sType,'double') !== false) {
			return 'decimal';
		} else if(strpos($sType,'date') !== false) {
			return 'date';
		} else if(strpos($sType,'timestamp') !== false) {
			return 'datetime';
		}else if(strpos($sType,'time') !== false) {
			return 'time';
		}else if(strpos($sType,'text') !== false) {
			return 'text';
		}else {
			return 'hidden';
		}
		
	}

	public static function getOptions($string){
		$return = [];
		$p1 = explode(';',$string);
		foreach($p1 as $s){
			$p2 = explode('|',$s);
			$return[$p2[0]] = $p2[1];
		}
		dump($return);
		return $return;
	}	
	
}